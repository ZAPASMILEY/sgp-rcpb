<div class="flex items-center justify-between mb-4">
    <form method="GET" class="flex-1 mr-4">
        <input type="text" name="search_agents" placeholder="Rechercher un agent..." class="ent-input w-full" value="{{ request('search_agents') }}">
    </form>
    <a href="#" class="ent-btn ent-btn-primary">Ajouter un agent</a>
</div>
<div>
    <h2 class="text-lg font-bold mb-4">Liste des agents</h2>
    @if($agents->isEmpty())
        <p>Aucun agent trouvé.</p>
    @else
        <ul class="list-disc pl-6">
            @foreach($agents as $agent)
                <li>{{ $agent->prenom ?? '' }} {{ $agent->nom ?? '' }}</li>
            @endforeach
        </ul>
    @endif
</div>