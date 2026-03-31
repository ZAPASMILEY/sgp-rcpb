<!-- Modal d'ajout de direction -->
<div id="modal-direction" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-lg relative">
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-red-500" onclick="document.getElementById('modal-direction').classList.add('hidden')">&times;</button>
        <h2 class="text-xl font-bold mb-4">Ajouter une direction</h2>
        <form method="POST" action="{{ route('admin.entites.directions.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="nom" class="block text-sm font-semibold">Nom de la direction</label>
                    <input id="nom" name="nom" type="text" class="ent-input w-full" required>
                </div>
                <div>
                    <label for="directeur_prenom" class="block text-sm font-semibold">Prénom du directeur</label>
                    <input id="directeur_prenom" name="directeur_prenom" type="text" class="ent-input w-full" required>
                </div>
                <div>
                    <label for="directeur_nom" class="block text-sm font-semibold">Nom du directeur</label>
                    <input id="directeur_nom" name="directeur_nom" type="text" class="ent-input w-full" required>
                </div>
                <div>
                    <label for="directeur_email" class="block text-sm font-semibold">Email du directeur</label>
                    <input id="directeur_email" name="directeur_email" type="email" class="ent-input w-full" required>
                </div>
                <div>
                    <label for="date_prise_fonction" class="block text-sm font-semibold">Date de prise de fonction</label>
                    <input id="date_prise_fonction" name="date_prise_fonction" type="date" class="ent-input w-full" required>
                </div>
            </div>
            <button type="submit" class="ent-btn ent-btn-primary w-full">Enregistrer la direction</button>
        </form>
    </div>
</div>
