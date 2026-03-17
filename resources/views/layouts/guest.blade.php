<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Login') - {{ config('app.name', 'OfficeIT ERP') }}</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome 6 --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .auth-brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-brand .brand-logo {
            width: 56px;
            height: 56px;
            background: #3b82f6;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.35);
        }

        .auth-brand h2 {
            color: #fff;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 0.25rem;
        }

        .auth-brand p {
            color: #94a3b8;
            font-size: 0.85rem;
            margin: 0;
        }

        .auth-card {
            background: #fff;
            border-radius: 14px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: none;
        }

        .auth-card .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
        }

        .auth-card .form-control {
            border-radius: 8px;
            padding: 0.6rem 0.9rem;
            font-size: 0.875rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            transition: all 0.15s ease;
        }

        .auth-card .form-control:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .auth-card .input-group-text {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-right: none;
            color: #94a3b8;
            border-radius: 8px 0 0 8px;
        }

        .auth-card .input-group .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }

        .auth-card .btn-primary {
            background: #3b82f6;
            border: none;
            border-radius: 8px;
            padding: 0.65rem 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            width: 100%;
            transition: all 0.15s ease;
        }

        .auth-card .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
            font-size: 0.8rem;
        }

        .auth-footer a {
            color: #93c5fd;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            color: #bfdbfe;
            text-decoration: underline;
        }

        .form-check-label {
            font-size: 0.82rem;
            color: #475569;
        }

        .invalid-feedback {
            font-size: 0.78rem;
        }
    </style>

    @yield('styles')
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-brand">
            <div class="brand-logo">
                <i class="fas fa-building"></i>
            </div>
            <h2>{{ config('app.name', 'OfficeIT ERP') }}</h2>
            <p>International Office ERP System</p>
        </div>

        <div class="auth-card">
            @yield('content')
        </div>

        <div class="auth-footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'OfficeIT ERP') }}. All rights reserved.
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>
</html>
