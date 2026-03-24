@extends('layouts.app')

@section('title', 'Connexion admin | '.config('app.name', 'SGP-RCPB'))

@push('head')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Source Sans Pro', sans-serif;
            min-height: 100vh;
        }

        .login-page-fcpb {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-image: url('{{ asset("img/logo-fcpb.png") }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: min(70vw, 680px);
            background-color: #0f172a;
            background-attachment: fixed;
        }

        .login-page-fcpb::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.94), rgba(30, 41, 59, 0.85));
            backdrop-filter: blur(8px);
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-card-header {
            background: linear-gradient(135deg, #004085 0%, #0056b3 100%);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: none;
        }

        .login-card-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .login-card-header .subtitle {
            font-size: 0.95rem;
            opacity: 0.95;
            margin: 0;
        }

        .login-card-body {
            padding: 2.5rem 2rem;
        }

        .login-intro {
            color: #495057;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .alert {
            border-radius: 8px;
            border: 1px solid;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #004085;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(0, 64, 133, 0.1);
        }

        .form-control::placeholder {
            color: #adb5bd;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            background-color: #fff5f5;
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 1.1rem;
            height: 1.1rem;
            cursor: pointer;
            margin-right: 0.6rem;
            accent-color: #004085;
        }

        .checkbox-group label {
            margin: 0;
            color: #495057;
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            user-select: none;
        }

        .btn-login {
            width: 100%;
            padding: 0.95rem;
            background: linear-gradient(135deg, #004085 0%, #0056b3 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 64, 133, 0.3);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #003366 0%, #004b99 100%);
            box-shadow: 0 6px 20px rgba(0, 64, 133, 0.4);
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .login-footer {
            padding: 1.5rem 2rem;
            background: #f8f9fa;
            border-top: 1px solid #e0e6ed;
            text-align: center;
        }

        .login-footer a {
            color: #004085;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .login-help {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .help-text {
            color: #6c757d;
            font-size: 0.8rem;
        }

        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
            }

            .login-page-fcpb {
                background-size: min(90vw, 300px);
                padding: 1rem;
            }

            .login-card-body {
                padding: 1.5rem 1.2rem;
            }

            .login-card-header {
                padding: 1.5rem 1.2rem;
            }

            .login-card-header h1 {
                font-size: 1.5rem;
            }

            .login-footer {
                padding: 1rem 1.2rem;
            }
        }

        .spinner-border-sm {
            display: inline-block;
            width: 0.8rem;
            height: 0.8rem;
            vertical-align: text-bottom;
            border: 2px solid white;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spinner-border {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@section('content')
    <main class="login-page-fcpb">
        <div class="login-container">
            <div class="login-card">
                <div class="login-card-header">
                    <h1><b>SGP</b>-RCPB</h1>
                    <p class="subtitle">Plateforme d'Administration</p>
                </div>

                <div class="login-card-body">
                    <p class="login-intro">
                        <i class="fas fa-lock"></i> Connectez-vous avec vos identifiants
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <strong><i class="fas fa-exclamation-circle"></i> Erreur :</strong><br>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.store') }}" id="login-form" novalidate>
                        @csrf

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Adresse Email
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="votre.email@example.com"
                                aria-label="Adresse email"
                                aria-describedby="email-error"
                            >
                            @error('email')
                                <div class="invalid-feedback" id="email-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label for="password">
                                    <i class="fas fa-lock"></i> Mot de passe
                                </label>
                            </div>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="••••••••"
                                aria-label="Mot de passe"
                                aria-describedby="password-error"
                            >
                            @error('password')
                                <div class="invalid-feedback" id="password-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="checkbox-group">
                            <input
                                type="checkbox"
                                id="remember"
                                name="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <label for="remember">
                                Se souvenir de moi
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="btn-login"
                            id="submit-btn"
                            aria-label="Bouton de connexion"
                        >
                            <span id="btn-text">
                                <i class="fas fa-sign-in-alt"></i> Connexion
                            </span>
                        </button>
                    </form>
                </div>

                <div class="login-footer">
                    <a href="{{ route('login') }}" title="Retourner à la page d'accueil">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');

            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();

                    form.classList.add('was-validated');
                    return;
                }

                // Add loading state
                submitBtn.disabled = true;
                btnText.innerHTML = '<span class="spinner-border-sm"></span>Connexion en cours...';

                form.classList.add('was-validated');
            });

            // Restore button state on error or page load
            if (form.classList.contains('was-validated')) {
                submitBtn.disabled = false;
                btnText.innerHTML = '<i class="fas fa-sign-in-alt"></i> Connexion';
            }

            // Add input validation styling
            const inputs = form.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });

                input.addEventListener('focus', function() {
                    this.classList.remove('is-invalid');
                });
            });
        });
    </script>
@endpush
