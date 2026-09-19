<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in — MediCare Hospital</title>

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
            <p class="text-[13px] font-bold uppercase tracking-[0.14em] text-white/70">Hospital admin portal</p>
            <h1 class="mt-2 font-display text-[38px] font-bold leading-[1.1] tracking-tight">Care runs<br>on good systems.</h1>
            <p class="mt-3 max-w-[380px] text-[15px] leading-relaxed text-white/80">One sign-in for the front desk, the lab, the blood bank and the doctors — with role-based access on every module.</p>

            <ul class="mt-7 flex list-none flex-col gap-3.5 p-0">
                <li class="flex items-center gap-3 text-[14px] font-semibold text-white/90">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-[17px]"><i class="bi bi-calendar-check"></i></span>
                    Appointments, doctors &amp; duty rosters
                </li>
                <li class="flex items-center gap-3 text-[14px] font-semibold text-white/90">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-[17px]"><i class="bi bi-droplet"></i></span>
                    Laboratory, pharmacy &amp; blood bank
                </li>
                <li class="flex items-center gap-3 text-[14px] font-semibold text-white/90">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-[17px]"><i class="bi bi-shield-check"></i></span>
                    Roles &amp; permissions per staffer
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

            <div class="welly-card p-7 sm:p-8" id="mc-login-card">
                {{-- Sign-in form --}}
                <form method="POST" action="{{ route('login') }}" id="mc-login-form">
                    @csrf
                    <span class="welly-stat-icon !h-12 !w-12 !text-[24px]"><i class="bi bi-person"></i></span>
                    <h2 class="mb-1 mt-3 text-[24px] font-extrabold tracking-tight text-ink">Welcome back</h2>
                    <p class="m-0 mb-5 text-[13px] text-mut">Sign in to your hospital account.</p>

                    @if(session('status'))
                        <div class="mb-3 rounded-lg bg-green-bg px-4 py-2.5 text-[13px] text-green-t">{{ session('status') }}</div>
                    @endif

                    <div class="mc-f mb-3.5">
                        <label for="email">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@hospital.com" required autofocus autocomplete="email">
                        @error('email')
                            <span class="mc-hint text-red" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mc-f mb-2">
                        <label for="password">Password</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password" class="!pr-11">
                            <button type="button" id="mc-pass-toggle" class="absolute right-1 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-[16px] text-mut hover:bg-line-2 hover:text-ink" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="mc-hint text-red" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4 flex items-center justify-between gap-2 pt-1">
                        <label class="flex cursor-pointer items-center gap-2 text-[13px] font-semibold text-ink-2">
                            <input type="checkbox" name="remember" class="h-4 w-4 accent-teal" {{ old('remember') ? 'checked' : '' }}> Stay signed in
                        </label>
                        <button type="button" class="border-0 bg-transparent p-0 text-[13px] font-bold text-teal-dk hover:underline" data-flip>Forgot password?</button>
                    </div>

                    <button type="submit" class="mc-btn w-full justify-center !py-3"><i class="bi bi-box-arrow-in-right"></i> SIGN IN</button>

                    <div class="my-4 flex items-center gap-3 text-[11px] font-bold uppercase tracking-wider text-faint">
                        <span class="h-px flex-1 bg-line"></span> or <span class="h-px flex-1 bg-line"></span>
                    </div>

                    <a href="{{ route('auth.google.redirect') }}" class="mc-btn ghost w-full justify-center !py-2.5">
                        <i class="bi bi-google text-red"></i> Continue with Google
                    </a>
                    <p class="m-0 mt-1.5 text-center text-[12px] text-mut">For patients — auto-creates a patient account.</p>

                    <p class="m-0 mt-4 text-center text-[13px] text-ink-2">Don't have an account? <a href="{{ route('register') }}" class="font-bold text-teal-dk no-underline hover:underline">Register</a></p>
                </form>

                {{-- Forgot-password form --}}
                <form method="POST" action="{{ route('password.email') }}" id="mc-forgot-form" class="hidden">
                    @csrf
                    <span class="welly-stat-icon !h-12 !w-12 !text-[24px]"><i class="bi bi-person-lock"></i></span>
                    <h2 class="mb-1 mt-3 text-[24px] font-extrabold tracking-tight text-ink">Reset password</h2>
                    <p class="m-0 mb-5 text-[13px] text-mut">Enter your account email — we'll send a reset link.</p>

                    <div class="mc-f mb-4">
                        <label for="forgot-email">Email address</label>
                        <input id="forgot-email" type="email" name="email" value="{{ old('email') }}" placeholder="you@hospital.com" required autocomplete="email">
                        @error('email')
                            <span class="mc-hint text-red" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="mc-btn w-full justify-center !py-3"><i class="bi bi-unlock"></i> Send reset link</button>

                    <button type="button" class="mt-3 flex items-center gap-1.5 border-0 bg-transparent p-0 text-[13px] font-bold text-teal-dk hover:underline" data-flip><i class="bi bi-chevron-left"></i> Back to sign in</button>
                </form>
            </div>

            <p class="m-0 mt-4 text-center text-[12px] text-faint">Protected by role-based access · Admin, doctor &amp; staff accounts</p>
        </div>
    </main>
</div>

<script>
    (function () {
        var login = document.getElementById('mc-login-form');
        var forgot = document.getElementById('mc-forgot-form');
        document.querySelectorAll('[data-flip]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                login.classList.toggle('hidden');
                forgot.classList.toggle('hidden');
            });
        });
        var pass = document.getElementById('password');
        var toggle = document.getElementById('mc-pass-toggle');
        if (pass && toggle) {
            toggle.addEventListener('click', function () {
                var show = pass.type === 'password';
                pass.type = show ? 'text' : 'password';
                toggle.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        }
    })();
</script>

</body>

</html>
