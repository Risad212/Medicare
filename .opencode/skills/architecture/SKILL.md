---
name: architecture
description: System architecture, project structure, MVC, layered design, and component overview. Use ONLY when user asks for architecture, system design, project structure, or articulation of codebase.
---

# Architecture Skill

Use when user asks for architecture, system design, or project structure.

## Medicare Stack
- Laravel 13 MVC, MySQL, Vite + Tailwind, Blade
- Layers: Frontend → Auth → Patient/Doctor/Admin → Settings → Mail
- Key: `app/Http/Controllers`, `app/Models`, `resources/views`, `routes/web.php`, `config/`

## Guidelines
- Explain layered flow: Route → Middleware → Controller → Model → View
- Document `User` roles, `Appointment` token flow, `Doctor` relationships
- Keep diagrams concise, list file paths with :line
