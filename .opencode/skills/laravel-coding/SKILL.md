---
name: laravel-coding
description: Laravel 10/11/13 coding standards, Eloquent, Blade, validation, middleware, and security best practices. Use ONLY when user asks for Laravel development, PHP Laravel code, or Laravel standards.
---

# Laravel Coding Skill

Use for Laravel theme, controllers, Eloquent, Blade, validation, auth, and security.

## Standards
- PSR-12, `laravel/pint` for formatting
- Eloquent: use `fillable/guarded`, `casts`, relationships, `validate` + `FormRequest`
- Blade: `{{ }}` escaped, `@csrf`, `@can`, avoid `{!! !!}` unless `Purifier`
- Validation: `$request->validate()` with `exists`, `mimes`, `in`, `after_or_equal`
- Security: `throttle`, `authorize`, `hash`, `Storage::disk('public')`, `Str::random`

## Structure
```
app/Http/Controllers, Models, Middleware
resources/views, routes/web.php, config/
```

## Security
- Mass assignment: whitelist `only()`, remove `role` from fillable
- File: `image|mimes:jpg,png|max:2048`, `store('public')`
- Headers: `X-Frame`, `nosniff` via `.htaccess` or middleware
