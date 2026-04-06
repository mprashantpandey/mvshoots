<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card border-0 shadow-lg rounded-5">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="h3 mb-3">Forgot password</h1>
                        <p class="text-secondary">Enter the admin email address to send a reset link.</p>
                        @include('admin.partials.alerts')
                        <form method="POST" action="{{ route('admin.password.email') }}">
                            @csrf
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control rounded-4 mb-3" value="{{ old('email') }}" required>
                            <button class="btn btn-primary rounded-4 w-100" type="submit">Send Reset Link</button>
                        </form>
                        <a class="btn btn-link w-100 mt-3 text-decoration-none" href="{{ route('admin.login') }}">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
