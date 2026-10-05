# MediCare Application Structure

## Request and response flow

```mermaid
flowchart LR
    Browser["Browser"]
    Routes["routes/web.php"]
    Middleware["Middleware"]
    Controller["HTTP Controller"]
    Request["Form Request / validation"]
    Service["Application Service"]
    Model["Eloquent Model"]
    Database[("Database")]
    Inertia["Inertia response"]
    React["React public and admin pages"]
    Blade["Blade page"]

    Browser --> Routes --> Middleware --> Controller
    Controller --> Request
    Controller --> Service
    Service --> Model --> Database
    Controller --> Inertia --> React --> Browser
    Controller --> Blade --> Browser
```

## Application layers

```mermaid
flowchart TB
    subgraph Presentation["Presentation"]
        Blade["Blade authentication, patient/doctor portals, optional modules"]
        React["resources/js/Pages · React public site and admin screens"]
        Inertia["Inertia adapter"]
        Assets["resources/js · resources/css · resources/sass"]
    end

    subgraph HTTP["HTTP layer"]
        WebRoutes["routes/web.php"]
        Controllers["app/Http/Controllers"]
        Requests["app/Http/Requests"]
        Middleware["app/Http/Middleware"]
    end

    subgraph Domain["Application and data"]
        Services["app/Services"]
        Models["app/Models"]
        Modules["app/Modules/*"]
    end

    Database[("SQLite / MySQL")]

    WebRoutes --> Middleware --> Controllers
    Controllers --> Requests
    Controllers --> Services
    Services --> Models --> Database
    Controllers --> Blade
    Controllers --> Inertia --> React
    Modules --> Services
    Modules --> Models
    Assets --> Blade
    Assets --> React
```

## Main source directories

```text
app/
├── Console/       Scheduled and command-line tasks
├── Http/
│   ├── Controllers/ HTTP request handlers
│   ├── Middleware/  Request filters and access gates
│   └── Requests/    Input validation
├── Mail/           Email message classes
├── Models/         Core Eloquent models
├── Modules/        Separately organized application modules
├── Notifications/  User notifications
├── Observers/      Model lifecycle observers
├── Providers/      Laravel service providers
├── Services/       Shared application logic
└── Support/        Shared support utilities

database/
├── factories/      Model factories
├── migrations/     Database schema history
└── seeders/        Database seed definitions

resources/
├── css/            Frontend and admin styles
├── js/             Frontend scripts and React public/admin pages
├── sass/           Sass stylesheets
└── views/          Blade templates

routes/
├── web.php         Web routes
└── console.php     Console routes
```

## User-facing areas

```mermaid
flowchart TB
    User["Signed-in user"]
    Role{"Account role"}
    Admin["Admin and staff area · React/Inertia migration in progress"]
    Doctor["Doctor portal · Blade"]
    Patient["Patient profile · Blade"]
    Public["Public website · React/Inertia"]

    User --> Role
    Role --> Admin
    Role --> Doctor
    Role --> Patient
    Public --> User
```
