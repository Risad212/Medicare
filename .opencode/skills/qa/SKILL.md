---
name: qa
description: QA, testing, PHPUnit, Pest, Feature/Unit tests, and quality checks for Laravel. Use ONLY when user asks for QA, testing, test creation, or quality assurance for Laravel.
---

# QA Skill (Laravel)

Use for Laravel QA, testing, and quality checks.

## Commands
- `php artisan test` / `phpunit` — run Feature/Unit
- `php artisan test --filter=Example` — single test
- `php artisan make:test FeatureTest` / `make:test UnitTest --unit`

## Standards
- Feature: `RefreshDatabase`, `actingAs($user)`, `assertStatus`, `assertDatabaseHas`
- Test DB: `phpunit.xml` `DB_CONNECTION=sqlite :memory:` — ensure migrations run (fix `general_settings` seed)
- Coverage: Controllers `validate`, `authorize`, `throttle`; Models `fillable`; Routes `middleware`

## Checklist
- [ ] `composer audit` 0
- [ ] `php -l` syntax OK
- [ ] `php artisan test` green
- [ ] No `{!! !!}` XSS, no `except()` mass assignment
