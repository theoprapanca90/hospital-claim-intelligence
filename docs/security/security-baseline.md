# Security Baseline — Initial Checklist

- [ ] Define data classification and approved processing locations.
- [ ] Use least-privilege RBAC and separate clinical, coder, verifier, auditor and administrator roles.
- [ ] Keep secrets out of source control; use a secret manager or protected environment files.
- [ ] TLS for service-to-service traffic; encrypt disks, backups and removable media.
- [ ] Restrict MySQL, Qdrant and Ollama to private interfaces.
- [ ] Centralize tamper-evident audit events with access monitoring.
- [ ] Malware scan and validate Excel/PDF uploads; enforce size and type limits.
- [ ] Apply retention, deletion, legal hold and access-review policies.
- [ ] Test backup restoration and offline recovery.
- [ ] Threat-model cloud synchronization and outbound network paths.
- [ ] Perform security testing before any production/pilot use.

This checklist is not evidence that any control has been implemented.
