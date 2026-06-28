<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="{{asset('backend-assets/css/main.css')}}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <title>Register - Vali Admin</title>
</head>

<body>
    <section class="material-half-bg">
        <div class="cover"></div>
    </section>

    <section class="login-content">
        <div class="logo">
            <h1>Vali</h1>
        </div>
        <div class="login-box register-box">
            <form class="login-form" method="POST" action="{{ route('register') }}">
                @csrf
                <h3 class="login-head"><i class="bi bi-person-plus me-2"></i>REGISTER</h3>

                <div class="mb-3">
                    <label class="form-label">NAME</label>
                    <input id="name" type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        name="name" value="{{ old('name') }}"
                        placeholder="Full Name" required autocomplete="name" autofocus>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">EMAIL</label>
                    <input id="email" type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}"
                        placeholder="Email" required autocomplete="email">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">PASSWORD</label>
                    <input id="password" type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password" placeholder="Password"
                        required autocomplete="new-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">CONFIRM PASSWORD</label>
                    <input id="password-confirm" type="password"
                        class="form-control"
                        name="password_confirmation"
                        placeholder="Confirm Password"
                        required autocomplete="new-password">
                </div>

                <div class="mb-3 btn-container d-grid">
                    <button class="btn btn-primary btn-block">
                        <i class="bi bi-person-check me-2 fs-5"></i>REGISTER
                    </button>
                </div>

                <div class="mb-3 mt-2 text-center">
                    <p class="semibold-text mb-0">
                        Already have an account?
                        <a href="{{ route('login') }}">Sign In</a>
                    </p>
                </div>
            </form>
        </div>
    </section>

    <script src="{{ asset('backend-assets/js/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('backend-assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('backend-assets/js/main.js') }}"></script>
</body>

</html>