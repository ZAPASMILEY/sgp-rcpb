<div class="mb-5 flex items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-slate-800">Services de la faitiere</h2>
        <p class="mt-1 text-sm text-slate-400">Affichage rapide des services rattaches au siege principal.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.services.index', ['source' => 'faitiere']) }}" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
            Voir liste
        </a>
        <a href="{{ route('admin.services.create') }}" data-open-create-modal data-modal-title="Ajouter un service" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-cyan-500 to-sky-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-cyan-200 transition hover:-translate-y-0.5">
            Ajouter
        </a>
    </div>
</div>

@if($services->isEmpty())
    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-400">
        Aucun service trouve.
    </div>
@else
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($services as $service)
            <article class="rounded-[24px] border border-slate-100 bg-slate-50/80 p-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-cyan-500 shadow-sm">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="truncate font-black text-slate-800">{{ $service->nom }}</h3>
                        <p class="truncate text-sm text-slate-500">{{ $service->direction?->nom ?? 'Sans direction' }}</p>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
