@extends('layouts.app')

@section('title', 'Connexion admin | '.config('app.name', 'SGP-RCPB'))

@push('head')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}">
    <style>
        body {
            min-height: 100vh;
        }

        .login-page-fcpb {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background-image: url('{{ asset("img/logo-fcpb.png") }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: min(70vw, 680px);
            background-color: #0f172a;
        }

        .login-page-fcpb::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.78));
        }

        .login-box {
            position: relative;
            z-index: 1;
            width: min(100%, 400px);
        }

        .login-card-body .input-group-text {
            background-color: #f8fafc;
        }
    </style>
@endpush

@section('content')
    <main class="login-page-fcpb">
        <div class="login-box">
            <div class="card card-outline card-primary shadow-lg">
                <div class="card-header text-center border-0 pt-4">
                    <a href="#" class="h4 mb-0 text-dark"><b>SGP</b>-RCPB</a>
                    <p class="text-muted small mb-0 mt-1">Administration</p>
                </div>
                <div class="card-body login-card-body px-4 pb-4">
                    <p class="login-box-msg">Connectez-vous pour continuer</p>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2 px-3" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.store') }}">
                        @csrf

                        <div class="input-group mb-3">
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="form-control"
                                placeholder="Adresse email"
                            >
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>

                        <div class="input-group mb-3">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                class="form-control"
                                placeholder="Mot de passe"
                            >
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-7">
                                <div class="icheck-primary">
                                    <input type="checkbox" id="remember" name="remember" value="1">
                                    <label for="remember">Se souvenir de moi</label>
                                </div>
                            </div>
                            <div class="col-5">
                                <button type="submit" class="btn btn-primary btn-block">Connexion</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>
@endpush
