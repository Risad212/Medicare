---
name: security
description: Security audit, OWASP, XSS, SQLi, IDOR, mass assignment, auth, and hardening for Laravel/PHP. Use ONLY when user asks for security audit, vulnerability, hardening, or OWASP checks.
---

# Security Skill

Use for security audits, vulnerabilities, and hardening.

## Checks
- OWASP Top 10: XSS `{!! !!}` → `{{ }}`, SQLi `whereRaw`, IDOR `doctor_id` check, mass assignment `fillable`, auth `role` escalation
- Laravel: `validate` whitelist, `throttle`, `csrf`, `Storage::disk('public')`, `APP_DEBUG=false`
- Deps: `composer audit`, `npm audit`
- Headers: `X-Frame`, `nosniff`, `CSP`, `HSTS` via `.htaccess`/middleware
- Files: `chmod 600 .env`, `adminer.php` block, `storage/logs` 640

## Medicare Fixes Done
- 16/18 fixed (IDOR, XSS, validation, throttle, headers, deps 13.29.0 clean)
- Skipped: `adminer.php` local, `.env` hardening per request
