@extends('layouts.app')

@section('title', 'Connexion admin | '.config('app.name', 'SGP-RCPB'))

@section('content')
        <main class="admin-shell flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
            <section class="admin-panel w-full max-w-md p-6 sm:p-8">
                <div class="space-y-2">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Administration</p>
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950">Connexion</h1>
                    <p class="text-sm text-slate-600">
                        Connectez-vous pour acceder au tableau de bord de pilotage des evaluations.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="email" class="text-sm font-semibold text-slate-700">Adresse email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-slate-950"
                            placeholder="admin@sgp-rcpb.local"
                        >
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm font-semibold text-slate-700">Mot de passe</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-slate-950"
                            placeholder="Votre mot de passe"
                        >
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        Se souvenir de moi
                    </label>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Se connecter
                    </button>
                </form>
            </section>
        </main>
@endsection
