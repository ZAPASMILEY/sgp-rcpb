<div class="flex items-center justify-between mb-4">
    <form method="GET" class="flex-1 mr-4">
        <input type="text" name="search_services" placeholder="Rechercher un service..." class="ent-input w-full" value="{{ request('search_services') }}">
    </form>
    <a href="{{ route('admin.services.create') }}" data-open-create-modal data-modal-title="Ajouter un service" class="ent-btn ent-btn-primary">Ajouter un service</a>
</div>
<div>
    <h2 class="text-lg font-bold mb-4">Liste des services</h2>
    @if($services->isEmpty())
        <p>Aucun service trouvé.</p>
    @else
        <ul class="list-disc pl-6">
            @foreach($services as $service)
                <li>{{ $service->nom }}</li>
            @endforeach
        </ul>
    @endif
</div>
