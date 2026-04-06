<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at top left, rgba(59,130,246,0.22), transparent 28%),
                radial-gradient(circle at bottom right, rgba(16,185,129,0.2), transparent 26%),
                #f3f6fb;
        }
        .auth-card {
            width: min(460px, 92vw);
            border-radius: 32px;
            border: 1px solid rgba(148,163,184,0.2);
            background: rgba(255,255,255,0.88);
            box-shadow: 0 30px 70px rgba(15,23,42,0.12);
            backdrop-filter: blur(16px);
        }
        .form-control {
            min-height: 52px;
            border-radius: 16px;
        }
        .btn {
            border-radius: 16px;
            min-height: 52px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="auth-card p-4 p-md-5">
        <div class="mb-4">
            <div class="small text-secondary text-uppercase">Photoshoot Booking Platform</div>
            <h1 class="h3 mt-2 mb-2">Admin Login</h1>
            <p class="text-secondary mb-0">Email and password access for the operations dashboard.</p>
        </div>

        @include('admin.partials.alerts')

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Password</label>
                    <a href="{{ route('admin.password.request') }}" class="small text-decoration-none">Forgot password?</a>
                </div>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Keep me signed in</label>
            </div>
            <button class="btn btn-primary w-100" type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>
