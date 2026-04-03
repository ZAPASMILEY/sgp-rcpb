@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <div class="py-6">
        @livewire('admin.admin-user-crud')
    </div>
@endsection
