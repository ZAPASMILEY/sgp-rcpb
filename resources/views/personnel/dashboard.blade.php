@extends('layouts.app')

@section('title', 'Mon espace | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-2xl">
            <section class="admin-panel p-6 sm:p-8">
                <div class="space-y-1">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace personnel</p>
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950">Bienvenue, {{ $user->name }}</h1>
                </div>

                <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                    <p class="font-semibold">Votre compte est actif.</p>
                    <p class="mt-1">Votre espace personnel est en cours de configuration. Veuillez contacter l'administrateur pour plus d'informations.</p>
                </div>

                <div class="mt-6 space-y-3 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-700">
                    <div class="flex gap-2">
                        <span class="font-semibold w-28 shrink-0">Nom :</span>
                        <span>{{ $user->name }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="font-semibold w-28 shrink-0">Identifiant :</span>
                        <span>{{ $user->email }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="font-semibold w-28 shrink-0">Role :</span>
                        <span class="capitalize">{{ $user->role }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="ent-btn ent-btn-soft text-sm">
                        Se deconnecter
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
