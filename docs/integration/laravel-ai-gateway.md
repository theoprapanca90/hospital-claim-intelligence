# Laravel to HCI AI Gateway Integration

This document defines the Laravel-side adapter. The repository does not yet contain a generated Laravel application; add these files after generating Laravel 12 under `apps/web`. Do not treat the integration as active until authentication, hospital membership, rate limiting and tenant policies are implemented.

## 1. Environment

Add to `apps/web/.env`:

```dotenv
HCI_AI_URL=http://127.0.0.1:8001
HCI_AI_SERVICE_TOKEN=the_same_secret_as_root_env
HCI_AI_TIMEOUT=130
```

Keep the token secret. Never commit either `.env` file.

## 2. Configuration

Add to `apps/web/config/services.php`:

```php
'hci_ai' => [
    'url' => env('HCI_AI_URL', 'http://127.0.0.1:8001'),
    'token' => env('HCI_AI_SERVICE_TOKEN'),
    'timeout' => (int) env('HCI_AI_TIMEOUT', 130),
],
```

## 3. AI client service

Create `apps/web/app/Services/AI/AIClient.php`:

```php
<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

class AIClient
{
    public function generate(string $prompt): array
    {
        $token = config('services.hci_ai.token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('HCI AI service token is not configured.');
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['X-HCI-Service-Token' => $token])
                ->connectTimeout(5)
                ->timeout(config('services.hci_ai.timeout', 130))
                ->post(rtrim(config('services.hci_ai.url'), '/') . '/v1/generate', [
                    'prompt' => $prompt,
                ])
                ->throw();

            $data = $response->json();

            if (! is_array($data)) {
                throw new RuntimeException('HCI AI Gateway returned an invalid response.');
            }

            return $data;
        } catch (ConnectionException|RequestException $exception) {
            throw new RuntimeException('HCI AI Gateway is unavailable.', previous: $exception);
        }
    }
}
```

## 4. Controller

Create `apps/web/app/Http/Controllers/Api/AIController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AI\AIClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AIController extends Controller
{
    public function generate(Request $request, AIClient $client): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'min:1', 'max:8000'],
        ]);

        try {
            $result = $client->generate($validated['prompt']);

            return response()->json(['data' => $result]);
        } catch (RuntimeException) {
            return response()->json([
                'message' => 'AI service is temporarily unavailable.',
            ], 503);
        }
    }
}
```

Do not write prompts, responses or patient identifiers to Laravel logs. In the clinical workflow, the caller must first resolve the authenticated user's hospital membership and authorize access to the specific encounter. This generic endpoint is for local integration testing only.

## 5. API route

After installing Laravel Sanctum with `php artisan install:api`, add to `apps/web/routes/api.php`:

```php
use App\Http\Controllers\Api\AIController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:10,1'])
    ->prefix('v1')
    ->group(function () {
        Route::post('/ai/generate', [AIController::class, 'generate']);
    });
```

For a real HCI endpoint, add a dedicated permission such as `ai.generate`, hospital membership middleware, encounter-level policy checks, request correlation IDs and an audit event that records metadata only (not clinical text).

## 6. Test from Laravel

With a valid authenticated test user:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/ai/generate \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TEST_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"prompt":"Jelaskan secara umum apa itu ICD-10."}'
```

Use synthetic, non-identifiable content only. Do not connect the endpoint to SIMRS patient records until authentication, tenant isolation, audit, retention and security tests pass.
