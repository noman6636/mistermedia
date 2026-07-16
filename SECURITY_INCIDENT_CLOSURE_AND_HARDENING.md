# Security Incident Closure and Production Hardening

Date: 2026-07-16
Project: mistermedia_code

## 1) Incident Closure Checklist (Do First)

Use this list in order. Mark each item done with date/time and owner.

### A. Containment (Immediate)

- [ ] Put app in maintenance mode if suspicious activity is still ongoing.
- [ ] Block suspicious source IPs at firewall/WAF.
- [ ] Disable any unknown admin users or service accounts.
- [ ] Preserve evidence before cleanup: web logs, PHP logs, DB logs, auth logs.

### B. Credential and Session Reset (Immediate)

- [ ] Force reset all admin and operator passwords.
- [ ] Invalidate all active sessions (server-side session purge + forced login).
- [ ] Rotate database credentials.
- [ ] Rotate API keys/tokens (payment, marketplace, SMTP, third-party integrations).
- [ ] Rotate cPanel/hosting/SSH credentials.

### C. Scope and Impact Validation

- [ ] Review access logs for attacker window (first seen to last seen).
- [ ] Identify touched endpoints (especially AJAX/mutation endpoints).
- [ ] Confirm no unauthorized data exports (CSV, invoices, customer records).
- [ ] Confirm no unauthorized account creation or privilege escalation.

### D. Code and File Integrity Verification

- [ ] Verify risky legacy files are removed from webroot and backups.
- [ ] Verify no obfuscated code remains in custom code.
- [ ] Verify session/auth hardening is active in production deployment.
- [ ] Verify file permissions are least-privilege for web directories.

### E. Recovery and Monitoring

- [ ] Re-enable normal traffic after containment checks pass.
- [ ] Enable temporary high-verbosity monitoring for 7-14 days.
- [ ] Add alert thresholds for login anomalies and write-heavy endpoints.
- [ ] Prepare management summary with root cause, impact, and remediation.

## 2) Production Hardening Checklist (Keep Permanent)

### A. Authentication and Session Security

- [ ] Enforce strong password policy and periodic rotation for admins.
- [ ] Add MFA for admin logins.
- [ ] Set secure session cookie flags: HttpOnly, Secure, SameSite.
- [ ] Regenerate session ID after login and privilege changes.
- [ ] Add account lockout/rate limit for repeated login failures.

### B. Authorization and Endpoint Safety

- [ ] Enforce role-based authorization on every mutation endpoint.
- [ ] Require POST for state-changing actions.
- [ ] Add CSRF protection for all authenticated write operations.
- [ ] Centralize input validation and strict allowlists.
- [ ] Reject unknown action parameters by default.

### C. Server and PHP Hardening

- [ ] Disable dangerous PHP functions where possible (exec/system/shell_exec/passthru/proc_open/popen).
- [ ] Disable display_errors in production; log errors securely.
- [ ] Restrict writable directories and prevent script execution in upload/temp folders.
- [ ] Enforce correct file/folder permissions and ownership.
- [ ] Keep PHP, web server, and OS patches current.

### D. Data and Database Controls

- [ ] Ensure prepared statements everywhere user input touches SQL.
- [ ] Enforce least-privilege DB user permissions.
- [ ] Encrypt sensitive data at rest and in transit.
- [ ] Back up database daily and test restore monthly.

### E. Monitoring, Detection, and Response

- [ ] Centralize logs (web, app, DB, auth) with retention policy.
- [ ] Create alerts for suspicious patterns:
  - [ ] Excessive login failures
  - [ ] Spikes in AJAX writes
  - [ ] Access to removed/legacy script paths
  - [ ] Unexpected admin actions out of normal hours
- [ ] Maintain an incident runbook with owner contacts.
- [ ] Run quarterly security reviews and dependency audits.

### F. Deployment and Change Safety

- [ ] Keep operational scripts outside public webroot.
- [ ] Remove backup/test/dev files from production.
- [ ] Require code review for auth/session/endpoint changes.
- [ ] Keep separate staging and production secrets.
- [ ] Maintain a deployment rollback plan.

## 3) Evidence Log Template

Use this mini template during or after an incident.

- Incident ID:
- Detected At:
- Reporter:
- First Suspicious IOC:
- Affected Systems:
- Containment Actions:
- Credentials Rotated:
- Data Impact:
- Root Cause:
- Files/Endpoints Involved:
- Recovery Completed At:
- Follow-up Tasks:

## 4) Sign-off

- Security Owner: ______________________
- Tech Lead: ___________________________
- Operations: __________________________
- Final Closure Date: __________________
