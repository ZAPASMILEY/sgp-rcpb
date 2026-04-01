@extends('layouts.app')

@section('title', 'Services caisse | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="admin-panel p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Caisses / Services</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Services de {{ $caisse->nom }}</h1>
                        <p class="mt-2 text-sm text-slate-600">Liste des services propres a la direction de cette caisse.</p>
                    </div>
                    <a href="{{ route('admin.caisses.show', $caisse) }}" class="ent-btn ent-btn-soft">Retour a la caisse</a>
                </div>
            </section>

            <section class="admin-panel p-6">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">
                    Aucun service propre n'est rattache a cette caisse.
                </div>
            </section>
        </div>
    </div>
@endsection
