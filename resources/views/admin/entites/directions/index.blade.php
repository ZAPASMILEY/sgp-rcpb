@extends('layouts.app')

@section('title', 'Directions | SGP-RCPB')

@push('head')
<style>
    /* Reset & Background */
    .app-content-header { display: none !important; }
    .app-main { background: #f8fafc !important; } /* Fond gris très clair pour faire ressortir les cartes */
    
    .fdi-wrapper { padding: 2rem 0; font-family: 'Inter', sans-serif; }
    .fdi-container { max-width: 1100px; margin: auto; }

    /* Glassmorphism Header */
    .header-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem 2rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02);
        margin-bottom: 2rem;
    }

    /* Nouveau Style de Table (Lignes espacées) */
    .custom-table { width: 100%; border-spacing: 0 0.75rem; border-collapse: separate; }
    .custom-table thead th { 
        padding: 0 1.5rem 0.5rem; 
        font-size: 11px; 
        text-transform: uppercase; 
        color: #94a3b8; 
        font-weight: 700; 
        letter-spacing: 1px;
    }

    .table-row { 
        background: white; 
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 16px;
    }
    .table-row:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 8px 20px rgba(0,0,0,0.04); 
    }

    .table-row td { 
        padding: 1.25rem 1.5rem; 
        border-top: 1px solid #f1f5f9; 
        border-bottom: 1px solid #f1f5f9; 
        background: white;
    }
    .table-row td:first-child { border-left: 1px solid #f1f5f9; border-radius: 16px 0 0 16px; }
    .table-row td:last-child { border-right: 1px solid #f1f5f9; border-radius: 0 16px 16px 0; }

    /* Badges & Buttons */
    .btn-add {
        background: #10b981;
        color: white;
        padding: 0.7rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }
    .btn-add:hover { background: #059669; color: white; transform: scale(1.02); }

    .action-btn {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #f1f5f9; color: #64748b;
        transition: 0.2s;
    }
    .action-btn:hover { background: #e2e8f0; color: #1e293b; }
    .btn-delete:hover { background: #fee2e2; color: #ef4444; }

    .service-badge {
        background: #ecfdf5;
        color: #059669;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.8rem;
        border: 1px solid #d1fae5;
    }
</style>
@endpush

@section('content')
<div class="fdi-wrapper">
    <div class="fdi-container">
        
        <div class="header-card flex flex-col md:flex-row justify-between items-center">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Directions</h1>
                <p class="text-slate-400 text-sm font-medium">Gestion des structures de la Faîtière</p>
            </div>
            <a href="{{ route('admin.entites.directions.create') }}" class="btn-add">
                <i class="fas fa-plus-circle mr-2"></i> Nouvelle Direction
            </a>
        </div>

        <div class="header-card">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500 mb-2">Trier par :</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.entites.directions.index', array_merge(request()->query(), ['sort' => ''])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $sort === '' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            Récent
                        </a>
                        <a href="{{ route('admin.entites.directions.index', array_merge(request()->query(), ['sort' => 'highest'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $sort === 'highest' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            <i class="fas fa-arrow-down"></i> Note plus forte
                        </a>
                        <a href="{{ route('admin.entites.directions.index', array_merge(request()->query(), ['sort' => 'lowest'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $sort === 'lowest' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            <i class="fas fa-arrow-up"></i> Note plus faible
                        </a>
                        <a href="{{ route('admin.entites.directions.index', array_merge(request()->query(), ['sort' => 'rated'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $sort === 'rated' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            <i class="fas fa-check-circle"></i> Déjà notés
                        </a>
                        <a href="{{ route('admin.entites.directions.index', array_merge(request()->query(), ['sort' => 'not_rated'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $sort === 'not_rated' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            <i class="fas fa-circle"></i> Pas encore notés
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th class="text-left">Ordre</th>
                    <th class="text-left">Détails de la Direction</th>
                    <th class="text-left">Responsable</th>
                    <th class="text-center">Note</th>
                    <th class="text-center">Services</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($directions as $direction)
                <tr class="table-row">
                    <td>
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 font-bold text-sm">
                            {{ $loop->iteration + ($directions->currentPage() - 1) * $directions->perPage() }}
                        </span>
                    </td>
                    <td>
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-4 font-bold">
                                {{ substr($direction->nom, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-base leading-tight">{{ $direction->nom }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">ID: #{{ $direction->id }}</div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="font-semibold text-slate-700">{{ $direction->directeur_prenom }} {{ $direction->directeur_nom }}</div>
                        <div class="text-xs text-emerald-600 font-medium tracking-wide lowercase">{{ $direction->directeur_email ?? 'Pas d\'email' }}</div>
                    </td>

                    <td class="text-center">
                        @if($notes[$direction->id] ?? null)
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full font-bold text-sm"
                                  style="background: linear-gradient(135deg, #16a34a, #22c55e); color: white;">
                                {{ number_format($notes[$direction->id], 2) }}
                            </span>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <a href="{{ route('admin.services.index', ['direction_id' => $direction->id, 'source' => 'faitiere']) }}" class="service-badge hover:bg-emerald-100 transition">
                            {{ $direction->services_count ?? 0 }} Services
                        </a>
                    </td>

                    <td class="text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.directions.show', $direction) }}" class="action-btn" title="Voir">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.directions.edit', $direction) }}" class="action-btn" title="Modifier">
                                <i class="far fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.directions.destroy', $direction) }}" class="inline" onsubmit="return confirm('Confirmer la suppression ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn btn-delete">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-20 text-slate-400 font-medium">
                        <img src="https://illustrations.popsy.co/gray/empty-folder.svg" class="w-32 mx-auto mb-4 opacity-50">
                        Aucune donnée disponible.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-6">
            {{ $directions->links() }}
        </div>
    </div>
</div>
@endsection