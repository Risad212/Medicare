<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register — MediCare Hospital</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    @vite(['resources/css/admin.css'])
</head>

<body class="bg-panel font-body text-ink antialiased">

<div class="flex min-h-screen">

    {{-- ===== Brand panel (desktop) ===== --}}
    <aside class="relative hidden w-[44%] flex-col justify-between overflow-hidden bg-teal-dk p-10 text-white lg:flex">
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-20 h-80 w-80 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute right-16 top-1/2 h-40 w-40 rounded-full border-[22px] border-gold/40"></div>

        <a href="{{ route('home') }}" class="relative flex items-center gap-2.5 no-underline">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-[20px] font-extrabold text-teal-dk">M</span>
            <span class="text-[24px] font-extrabold tracking-tight text-white">MediCare</span>
        </a>

        <div class="relative">
            <p class="text-[13px] font-bold uppercase tracking-[0.14em] text-white/70">Patient registration</p>
            <h1 class="mt-2 font-display text-[38px] font-bold leading-[1.1] tracking-tight">Your health,<br>one account.</h1>
            <p class="mt-3 max-w-[380px] text-[15px] leading-relaxed text-white/80">Create a free patient account to book visits, track prescriptions and reach our care teams in minutes.</p>

            <ul class="mt-7 flex list-none flex-col gap-3.5 p-0">
                <li class="flex items-center gap-3 text-[14px] font-semibold text-white/90">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-[17px]"><i class="bi bi-calendar-check"></i></span>
                    Book appointments with any doctor
                </li>
                <li class="flex items-center gap-3 text-[14px] font-semibold text-white/90">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-[17px]"><i class="bi bi-capsule"></i></span>
                    Prescriptions &amp; lab reports in one place
                </li>
                <li class="flex items-center gap-3 text-[14px] font-semibold text-white/90">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-[17px]"><i class="bi bi-droplet"></i></span>
                    Blood requests &amp; visit history
                </li>
            </ul>
        </div>

        <p class="relative m-0 text-[12px] text-white/60">© {{ now()->year }} MediCare Hospital · Made with ♥ for care teams</p>
    </aside>

    {{-- ===== Form column ===== --}}
    <main class="flex flex-1 items-center justify-center p-6 sm:p-10">
        <div class="w-full max-w-[420px]">

            <a href="{{ route('home') }}" class="mb-6 flex items-center gap-2.5 no-underline lg:hidden">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-dk text-[18px] font-extrabold text-white">M</span>
                <span class="text-[22px] font-extrabold tracking-tight text-ink">MediCare</span>
            </a>

            <div class="welly-card p-7 sm:p-8">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <span class="welly-stat-icon !h-12 !w-12 !text-[24px]"><i class="bi bi-person-plus"></i></span>
                    <h2 class="mb-1 mt-3 text-[24px] font-extrabold tracking-tight text-ink">Create your account</h2>
                    <p class="m-0 mb-5 text-[13px] text-mut">Registers you as a patient — free, takes a minute.</p>

                    <div class="mc-f mb-3.5">
                        <label for="name">Full name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Your full name" required autofocus autocomplete="name">
                        @error('name')
                            <span class="mc-hint text-red" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mc-f mb-3.5">
                        <label for="email">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
                        @error('email')
                            <span class="mc-hint text-red" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mc-f mb-3.5">
                        <label for="password">Password</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" placeholder="Min. 8 characters" required autocomplete="new-password" class="!pr-11">
                            <button type="button" data-pass-toggle="password" class="absolute right-1 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-[16px] text-mut hover:bg-line-2 hover:text-ink" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="mc-hint text-red" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mc-f mb-4">
                        <label for="password-confirm">Confirm password</label>
                        <div class="relative">
                            <input id="password-confirm" type="password" name="password_confirmation" placeholder="Repeat your password" required autocomplete="new-password" class="!pr-11">
                            <button type="button" data-pass-toggle="password-confirm" class="absolute right-1 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-[16px] text-mut hover:bg-line-2 hover:text-ink" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="mc-btn w-full justify-center !py-3"><i class="bi bi-person-check"></i> REGISTER</button>

                    <div class="my-4 flex items-center gap-3 text-[11px] font-bold uppercase tracking-wider text-faint">
                        <span class="h-px flex-1 bg-line"></span> or <span class="h-px flex-1 bg-line"></span>
                    </div>

                    <a href="{{ route('auth.google.redirect') }}" class="mc-btn ghost w-full justify-center !py-2.5">
                        <i class="bi bi-google text-red"></i> Sign up with Google
                    </a>
                    <p class="m-0 mt-1.5 text-center text-[12px] text-mut">Instant patient account — no password needed.</p>

                    <p class="m-0 mt-4 text-center text-[13px] text-ink-2">Already have an account? <a href="{{ route('login') }}" class="font-bold text-teal-dk no-underline hover:underline">Sign in</a></p>
                </form>
            </div>

            <p class="m-0 mt-4 text-center text-[12px] text-faint">By registering you get a patient account · Staff accounts are created by admin</p>
        </div>
    </main>
</div>

<script>
    (function () {
        document.querySelectorAll('[data-pass-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-pass-toggle'));
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });
    })();
</script>

</body>

</html>
