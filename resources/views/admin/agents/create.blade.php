@extends('layouts.app')

@section('title', 'Nouvel agent | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="mb-4">
        <a href="{{ $redirectTo ?: route('admin.agents.index') }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel ent-window p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Fenetre d'ajout</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvel agent</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez les informations personnelles et professionnelles de l'agent. Le rattachement se gère depuis la fiche de la structure concernée.</p>
                    </div>
                    <a href="{{ $redirectTo ?: route('admin.agents.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.agents.store') }}" enctype="multipart/form-data" class="mt-8 grid gap-5">
                    @csrf
                    @if($redirectTo)
                        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                    @endif

                    {{-- ── Identité ── --}}
                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="nom" class="text-sm font-semibold text-slate-700">Nom <span class="text-red-500">*</span></label>
                            <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Nom de famille">
                        </div>
                        <div class="space-y-2">
                            <label for="prenom" class="text-sm font-semibold text-slate-700">Prénom <span class="text-red-500">*</span></label>
                            <input id="prenom" name="prenom" type="text" value="{{ old('prenom') }}" required class="ent-input" placeholder="Prénom">
                        </div>
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="sexe" class="text-sm font-semibold text-slate-700">Sexe <span class="text-red-500">*</span></label>
                            <select id="sexe" name="sexe" required class="ent-select">
                                <option value="">Sélectionner</option>
                                <option value="homme" @selected(old('sexe') === 'homme')>Homme</option>
                                <option value="femme" @selected(old('sexe') === 'femme')>Femme</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="date_debut_fonction" class="text-sm font-semibold text-slate-700">Date de prise de fonction <span class="text-red-500">*</span></label>
                            <input id="date_debut_fonction" name="date_debut_fonction" type="date" value="{{ old('date_debut_fonction') }}" required class="ent-input">
                        </div>
                    </div>

                    {{-- ── Matricule ── --}}
                    <div class="space-y-2">
                        <label for="matricule" class="text-sm font-semibold text-slate-700">Matricule <span class="text-red-500">*</span></label>
                        <input id="matricule" name="matricule" type="text" value="{{ old('matricule') }}" required class="ent-input" placeholder="Ex : AGT-2026-001">
                    </div>

                    {{-- ── Rôle ── --}}
                    <div class="space-y-2">
                        <label for="role" class="text-sm font-semibold text-slate-700">Rôle <span class="text-red-500">*</span></label>
                        <select id="role" name="role" required class="ent-select">
                            <option value="">Sélectionner un rôle</option>
                            @foreach (\App\Models\Agent::ROLES as $val => $label)
                                <option value="{{ $val }}" @selected(old('role') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">Le rôle détermine dans quels selects cet agent apparaîtra lors de la gestion des structures.</p>
                    </div>

                    {{-- ── Fonction (obligatoire pour Agent et Conseiller DG) ── --}}
                    <div class="space-y-2" id="poste-wrap">
                        <label for="poste" class="text-sm font-semibold text-slate-700">
                            Fonction
                            <span id="poste-required-star" class="text-red-500 hidden">*</span>
                            <span id="poste-optional-label" class="text-xs font-normal text-slate-400">(optionnel)</span>
                        </label>
                        <input id="poste" name="poste" type="text"
                               value="{{ old('poste') }}"
                               list="postes-agent-list"
                               class="ent-input w-full"
                               placeholder="Ex : Caissier, Chargé de crédit…">
                        <datalist id="postes-agent-list">
                            @foreach (array_merge(...array_values($postesByFonction)) as $libelle)
                                <option value="{{ $libelle }}">
                            @endforeach
                        </datalist>
                        <p class="text-xs text-slate-500">La fonction réelle occupée au sein de la structure.</p>
                    </div>

                    {{-- ── Contact ── --}}
                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="numero_telephone" class="text-sm font-semibold text-slate-700">Numéro de téléphone <span class="text-red-500">*</span></label>
                            <input id="numero_telephone" name="numero_telephone" type="text" value="{{ old('numero_telephone') }}" required class="ent-input" placeholder="+226 70 00 00 00">
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-semibold text-slate-700">Email professionnel <span class="text-red-500">*</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="ent-input" placeholder="agent@rcpb.bf">
                            <p class="text-xs text-slate-500">Sert d'identifiant unique dans les listes de sélection.</p>
                        </div>
                    </div>

                    {{-- ── Photo ── --}}
                    <fieldset class="rounded-2xl border border-slate-200 bg-slate-50 p-5 space-y-4">
                        <legend class="px-2 text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Photo</legend>

                        {{-- Aperçu photo sélectionnée --}}
                        <div id="photo-preview-wrap" class="hidden flex items-center gap-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-3">
                            <img id="photo-preview-img" src="" alt="Aperçu" class="h-20 w-20 rounded-xl object-cover ring-2 ring-emerald-200">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-700" id="photo-preview-name">—</p>
                                <p class="text-xs text-slate-400">Photo sélectionnée</p>
                            </div>
                            <button type="button" onclick="clearPhoto()" class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-500 hover:bg-rose-200">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>

                        <div class="grid gap-5 lg:grid-cols-2">
                            {{-- Import fichier --}}
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Importer depuis l'appareil</label>
                                <label for="photo_import" class="flex cursor-pointer items-center gap-3 rounded-2xl border-2 border-dashed border-emerald-200 bg-white px-4 py-4 transition hover:border-emerald-400 hover:bg-emerald-50">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                                        <i class="fas fa-folder-open"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-700">Choisir un fichier</p>
                                        <p class="text-xs text-slate-400">JPG, PNG, WEBP — max 3 Mo</p>
                                    </div>
                                </label>
                                <input id="photo_import" name="photo_import" type="file" accept="image/*" class="sr-only"
                                       onchange="previewPhoto(this)">
                            </div>

                            {{-- Caméra --}}
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Prendre avec la caméra</label>
                                <button type="button" onclick="openCamera()"
                                        class="flex w-full cursor-pointer items-center gap-3 rounded-2xl border-2 border-dashed border-amber-200 bg-white px-4 py-4 transition hover:border-amber-400 hover:bg-amber-50">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                                        <i class="fas fa-camera"></i>
                                    </span>
                                    <div class="text-left min-w-0">
                                        <p class="text-sm font-bold text-slate-700">Ouvrir la caméra</p>
                                        <p class="text-xs text-slate-400">Desktop, mobile &amp; tablette</p>
                                    </div>
                                </button>
                                {{-- Input caché pour recevoir la photo capturée --}}
                                <input id="photo_camera" name="photo_camera" type="file" accept="image/*" class="sr-only">
                            </div>
                        </div>
                    </fieldset>

                    {{-- ── Modale Caméra ── --}}
                    <div id="camera-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm"></div>
                        <div class="relative w-full max-w-lg rounded-[28px] bg-slate-900 shadow-2xl overflow-hidden">
                            <div class="flex items-center justify-between px-5 py-4 border-b border-white/10">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400">
                                        <i class="fas fa-camera"></i>
                                    </span>
                                    <p class="text-sm font-black text-white">Prise de photo</p>
                                </div>
                                <button type="button" onclick="closeCamera()" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 text-white/60 hover:bg-rose-500 hover:text-white">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>

                            {{-- Flux vidéo --}}
                            <div class="relative bg-black">
                                <video id="camera-video" autoplay playsinline muted class="w-full max-h-72 object-cover"></video>
                                {{-- Grille viseur --}}
                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                    <div class="h-48 w-48 rounded-full border-2 border-white/20"></div>
                                </div>
                            </div>

                            {{-- Canvas caché pour capturer l'image --}}
                            <canvas id="camera-canvas" class="hidden"></canvas>

                            <div class="flex items-center justify-center gap-4 px-5 py-5">
                                {{-- Sélecteur caméra (avant/arrière) --}}
                                <button type="button" id="flip-camera-btn" onclick="flipCamera()" title="Retourner la caméra"
                                        class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white/70 transition hover:bg-white/20">
                                    <i class="fas fa-sync-alt"></i>
                                </button>

                                {{-- Bouton de capture --}}
                                <button type="button" onclick="capturePhoto()"
                                        class="flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-xl transition active:scale-90 hover:bg-amber-400">
                                    <i class="fas fa-circle text-slate-800 text-2xl"></i>
                                </button>

                                {{-- Placeholder symétrique --}}
                                <div class="h-11 w-11"></div>
                            </div>

                            <p id="camera-error" class="hidden px-5 pb-4 text-center text-xs text-rose-400">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Impossible d'accéder à la caméra. Vérifiez les permissions du navigateur.
                            </p>
                        </div>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Enregistrer l'agent
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
<script>
/* ── Aperçu photo importée ── */
function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('photo-preview-img').src = e.target.result;
        document.getElementById('photo-preview-name').textContent = file.name;
        document.getElementById('photo-preview-wrap').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
    // Vider la caméra si on importe un fichier
    document.getElementById('photo_camera').value = '';
}

function clearPhoto() {
    document.getElementById('photo_import').value = '';
    document.getElementById('photo_camera').value = '';
    document.getElementById('photo-preview-wrap').classList.add('hidden');
    document.getElementById('photo-preview-img').src = '';
}

/* ── Caméra ── */
let cameraStream = null;
let facingMode   = 'user'; // 'user' = avant, 'environment' = arrière

async function openCamera() {
    document.getElementById('camera-modal').classList.remove('hidden');
    document.getElementById('camera-error').classList.add('hidden');
    await startStream();
}

async function startStream() {
    stopStream();
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode, width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: false,
        });
        document.getElementById('camera-video').srcObject = cameraStream;
    } catch (err) {
        document.getElementById('camera-error').classList.remove('hidden');
        console.warn('Caméra non disponible :', err);
    }
}

function stopStream() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(t => t.stop());
        cameraStream = null;
    }
}

function closeCamera() {
    stopStream();
    document.getElementById('camera-modal').classList.add('hidden');
    document.getElementById('camera-video').srcObject = null;
}

async function flipCamera() {
    facingMode = facingMode === 'user' ? 'environment' : 'user';
    await startStream();
}

function capturePhoto() {
    const video  = document.getElementById('camera-video');
    const canvas = document.getElementById('camera-canvas');
    canvas.width  = video.videoWidth  || 640;
    canvas.height = video.videoHeight || 480;
    canvas.getContext('2d').drawImage(video, 0, 0);

    canvas.toBlob(blob => {
        if (!blob) return;
        const file = new File([blob], 'camera_' + Date.now() + '.jpg', { type: 'image/jpeg' });
        const dt   = new DataTransfer();
        dt.items.add(file);
        document.getElementById('photo_camera').files = dt.files;
        document.getElementById('photo_import').value = '';

        // Aperçu
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('photo-preview-img').src = e.target.result;
            document.getElementById('photo-preview-name').textContent = 'Photo prise avec la caméra';
            document.getElementById('photo-preview-wrap').classList.remove('hidden');
        };
        reader.readAsDataURL(file);

        closeCamera();
    }, 'image/jpeg', 0.92);
}

// Champ Fonction : visible et obligatoire uniquement pour Agent et Conseiller DG
(function () {
    var ROLES_AVEC_FONCTION = ['Agent', 'Conseiller DG'];
    var roleSelect   = document.getElementById('role');
    var posteWrap    = document.getElementById('poste-wrap');
    var posteInput   = document.getElementById('poste');
    var requiredStar = document.getElementById('poste-required-star');
    var optionalLbl  = document.getElementById('poste-optional-label');

    function updatePosteField() {
        var role = roleSelect ? roleSelect.value : '';
        var show = ROLES_AVEC_FONCTION.includes(role);
        if (posteWrap)   posteWrap.classList.toggle('hidden', !show);
        if (posteInput)  { posteInput.required = show; if (!show) posteInput.value = ''; }
        if (requiredStar) requiredStar.classList.toggle('hidden', !show);
        if (optionalLbl)  optionalLbl.classList.toggle('hidden', true);
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', updatePosteField);
        updatePosteField();
    }
})();
</script>
@endpush
