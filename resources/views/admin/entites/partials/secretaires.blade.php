<div class="flex items-center justify-between mb-4">
    <form method="GET" class="flex-1 mr-4">
        <input type="text" name="search_secretaires" placeholder="Rechercher un secrétaire..." class="ent-input w-full" value="{{ request('search_secretaires') }}">
    </form>
    <button type="button" class="ent-btn ent-btn-primary mb-4" onclick="document.getElementById('modal-secretaire').classList.remove('hidden')">Ajouter un secrétaire</button>
</div>

<!-- Modal d'ajout de secrétaire -->
<div id="modal-secretaire" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-lg relative">
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-red-500" onclick="document.getElementById('modal-secretaire').classList.add('hidden')">&times;</button>
        <h2 class="text-xl font-bold mb-4">Ajouter une secrétaire</h2>
        <form method="POST" action="{{ route('admin.secretaires.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="prenom" class="block text-sm font-semibold">Prénom</label>
                    <input id="prenom" name="prenom" type="text" class="ent-input w-full" required>
                </div>
                <div>
                    <label for="nom" class="block text-sm font-semibold">Nom</label>
                    <input id="nom" name="nom" type="text" class="ent-input w-full" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-semibold">Email</label>
                <input id="email" name="email" type="email" class="ent-input w-full" required>
            </div>
            <div class="mb-4">
                <label for="direction_id" class="block text-sm font-semibold">Direction rattachée</label>
                <select id="direction_id" name="direction_id" class="ent-input w-full" required>
                    <option value="">Sélectionner une direction</option>
                    @foreach($directions as $direction)
                        <option value="{{ $direction->id }}">{{ $direction->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="date_prise_fonction" class="block text-sm font-semibold">Date de prise de fonction</label>
                <input id="date_prise_fonction" name="date_prise_fonction" type="date" class="ent-input w-full" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="ent-btn ent-btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<script>
// Fermer la modale avec la touche Echap
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('modal-secretaire').classList.add('hidden');
    }
});
</script>

<div>
    <h2 class="text-lg font-bold mb-4">Liste des secrétaires</h2>
    @if($secretaires->isEmpty())
        <p>Aucun secrétaire trouvé.</p>
    @else
        <ul class="list-disc pl-6">
            @foreach($secretaires as $secretaire)
                <li>{{ $secretaire->prenom ?? '' }} {{ $secretaire->nom ?? $secretaire->directeur_nom ?? '' }}</li>
            @endforeach
        </ul>
    @endif
</div>