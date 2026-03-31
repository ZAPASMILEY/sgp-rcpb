@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')

@section('content')
    <div class="py-6">
        @livewire('admin.admin-user-crud')
    </div>
@endsection
