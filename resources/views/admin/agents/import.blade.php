@extends('layouts.app')

@section('title', 'Import CSV Agents | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="space-y-6 pb-8">

    {{-- En-tête --}}
    <div class="rounded-2xl overflow-hidden shadow-lg">
        <div style="background: linear-gradient(135deg, #059669 0%, #0891b2 60%, #2563eb 100%)" class="px-8 py-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-white text-2xl">
                        <i class="fas fa-file-csv"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white">Import CSV — Agents</h1>
                        <p class="text-xs text-white/70 mt-1 font-semibold">Importer plusieurs centaines d'agents en une seule opération</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.agents.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/20 border border-white/30 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/30">
                        <i class="fas fa-arrow-left text-xs"></i> Retour
                    </a>
                    <a href="{{ route('admin.agents.import.template') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold shadow-sm transition hover:bg-slate-100"
                       style="color:#059669">
                        <i class="fas fa-download text-xs"></i> Télécharger le modèle
                    </a>
                </div>
            </div>
        </div>

        {{-- Bande info --}}
        <div class="bg-white border-t border-slate-100 grid grid-cols-3 divide-x divide-slate-100">
            <div class="px-6 py-3 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                <span class="text-xs font-bold text-slate-600">Délimiteur auto-détecté (<code>;</code> ou <code>,</code>)</span>
            </div>
            <div class="px-6 py-3 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                <span class="text-xs font-bold text-slate-600">Doublons ignorés ligne par ligne</span>
            </div>
            <div class="px-6 py-3 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                <span class="text-xs font-bold text-slate-600">Compte de connexion créé automatiquement</span>
            </div>
        </div>
    </div>

    {{-- Résultats import précédent --}}
    @if(session('import_result'))
        @php $result = session('import_result'); @endphp
        <div class="rounded-2xl overflow-hidden shadow-sm border border-slate-100">
            <div class="px-6 py-4 flex items-center gap-3"
                 style="background: linear-gradient(to right, #ecfdf5, #f0fdf4)">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check-double"></i>
                </div>
                <div>
                    <p class="font-black text-emerald-800">
                        {{ $result['imported'] }} agent(s) importé(s) avec succès
                        @if(count($result['errors']) > 0)
                            — {{ count($result['errors']) }} ligne(s) ignorée(s)
                        @endif
                    </p>
                    <p class="text-xs text-emerald-600 mt-0.5">Mot de passe par défaut pour tous les nouveaux comptes : <strong>11111111</strong></p>
                </div>
            </div>
            @if(count($result['errors']) > 0)
                <div class="p-6 space-y-2">
                    <p class="text-xs font-black uppercase tracking-wider text-rose-600 mb-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Lignes ignorées
                    </p>
                    @foreach($result['errors'] as $err)
                        <div class="flex items-start gap-2 rounded-xl bg-rose-50 border border-rose-100 px-4 py-2.5 text-xs text-rose-700">
                            <i class="fas fa-times-circle mt-0.5 shrink-0 text-rose-400"></i>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-start gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
            <i class="fas fa-exclamation-circle mt-0.5 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Formulaire upload --}}
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-100">
            <div class="px-6 py-3 border-b border-slate-100 flex items-center gap-2"
                 style="background: linear-gradient(to right, #ecfdf5, #f0fdf4)">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 text-xs">
                    <i class="fas fa-upload"></i>
                </div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">Charger un fichier CSV</p>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('admin.agents.import.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    {{-- Zone de dépôt --}}
                    <div id="drop-zone"
                         class="relative rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center transition hover:border-emerald-400 hover:bg-emerald-50/30 cursor-pointer"
                         onclick="document.getElementById('csv_file').click()">
                        <div id="drop-icon" class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 text-2xl mx-auto mb-3">
                            <i class="fas fa-file-csv"></i>
                        </div>
                        <p id="drop-label" class="text-sm font-bold text-slate-500">Glissez votre fichier CSV ici</p>
                        <p class="text-xs text-slate-400 mt-1">ou cliquez pour parcourir — max 10 Mo</p>
                        <input id="csv_file" name="csv_file" type="file" accept=".csv,.txt" class="hidden"
                               onchange="handleFileSelect(this)">
                    </div>

                    @error('csv_file')
                        <p class="text-xs text-rose-600 font-bold"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror

                    <button type="submit" id="submit-btn" disabled
                            class="w-full py-3.5 rounded-2xl text-sm font-black text-white shadow-lg transition disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-90"
                            style="background: linear-gradient(to right, #059669, #0891b2)">
                        <i class="fas fa-rocket mr-2"></i> Lancer l'import
                    </button>
                </form>
            </div>
        </div>

        {{-- Guide colonnes --}}
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-100">
            <div class="px-6 py-3 border-b border-slate-100 flex items-center gap-2"
                 style="background: linear-gradient(to right, #eff6ff, #f5f3ff)">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-600 text-xs">
                    <i class="fas fa-table"></i>
                </div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-blue-700">Colonnes attendues</p>
            </div>
            <div class="p-6">
                <p class="text-xs text-slate-500 mb-4">La première ligne doit être l'en-tête. Ordre libre, noms exacts requis.</p>
                <div class="space-y-2">
                    @php
                        $columns = [
                            ['nom',                 true,  'Nom de famille'],
                            ['prenom',              true,  'Prénom'],
                            ['sexe',                true,  'homme / femme (ou H / F)'],
                            ['matricule',           true,  'Matricule unique'],
                            ['role',                true,  'Voir liste ci-dessous'],
                            ['email',               false, 'Adresse e-mail (unique)'],
                            ['numero_telephone',    false, 'Téléphone (unique)'],
                            ['poste',               false, 'Obligatoire si rôle = Agent ou Conseiller DG'],
                            ['date_debut_fonction', false, 'Format : AAAA-MM-JJ'],
                        ];
                    @endphp
                    @foreach($columns as [$col, $req, $desc])
                        <div class="flex items-center gap-3 rounded-xl px-3 py-2 {{ $req ? 'bg-slate-50' : '' }}">
                            <code class="w-44 shrink-0 text-xs font-black {{ $req ? 'text-slate-800' : 'text-slate-400' }}">{{ $col }}</code>
                            @if($req)
                                <span class="shrink-0 rounded-full bg-rose-100 px-1.5 py-0.5 text-[9px] font-black text-rose-600">REQ</span>
                            @else
                                <span class="shrink-0 rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-black text-slate-400">OPT</span>
                            @endif
                            <span class="text-xs text-slate-500">{{ $desc }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 rounded-xl bg-slate-50 p-4">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Rôles valides</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(array_keys($roles) as $r)
                            <span class="rounded-lg bg-white border border-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ $r }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function handleFileSelect(input) {
    var file = input.files[0];
    if (!file) return;
    var label   = document.getElementById('drop-label');
    var icon    = document.getElementById('drop-icon');
    var btn     = document.getElementById('submit-btn');
    var zone    = document.getElementById('drop-zone');

    label.textContent = file.name;
    label.classList.add('text-emerald-700');
    icon.classList.remove('bg-slate-100', 'text-slate-400');
    icon.classList.add('bg-emerald-100', 'text-emerald-600');
    zone.classList.add('border-emerald-400', 'bg-emerald-50/30');
    zone.classList.remove('border-slate-200', 'bg-slate-50');
    btn.disabled = false;
}

// Drag & drop
var zone = document.getElementById('drop-zone');
zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('border-emerald-400'); });
zone.addEventListener('dragleave', function() { zone.classList.remove('border-emerald-400'); });
zone.addEventListener('drop', function(e) {
    e.preventDefault();
    var file = e.dataTransfer.files[0];
    if (!file) return;
    var input = document.getElementById('csv_file');
    var dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    handleFileSelect(input);
});
</script>
@endsection
