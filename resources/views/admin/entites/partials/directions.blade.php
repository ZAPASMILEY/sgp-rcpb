<div class="flex items-center justify-between mb-4">
    <form method="GET" class="flex-1 mr-4">
        <input type="text" name="search_directions" placeholder="Rechercher une direction..." class="ent-input w-full" value="{{ request('search_directions') }}">
    </form>
    <button type="button" class="ent-btn ent-btn-primary" onclick="document.getElementById('modal-direction').classList.remove('hidden')">Ajouter une direction</button>
</div>
@include('admin.entites.partials.modal_direction')
</div>
<div>
    <h2 class="text-lg font-bold mb-4">Liste des directions</h2>
    @if($directions->isEmpty())
        <p>Aucune direction trouvée.</p>
    @else
        <ul class="list-disc pl-6">
            @foreach($directions as $direction)
                <li>{{ $direction->nom }}</li>
            @endforeach
        </ul>
    @endif
</div>