@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-[#f0f7e6] py-8">
    <div class="w-full max-w-xl p-12 bg-white rounded-2xl shadow-2xl border border-emerald-100">
        <div class="flex flex-col items-center mb-6">
            <img src="/img/logo-fcpb.png" alt="Logo RCPB" class="h-20 w-20 rounded-full shadow border-2 border-emerald-100 bg-white mb-2">
            <h2 class="text-2xl font-black text-emerald-700 tracking-tight mb-1">Connexion</h2>
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-widest">Système de gestion de la performance</p>
        </div>
        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-gray-700 font-semibold mb-1">Email</label>
                <input id="email" type="email" name="email" required autofocus class="w-full px-3 py-2 border border-emerald-200 rounded focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-[#f8fafc]">
            </div>
            <div>
                <label for="password" class="block text-gray-700 font-semibold mb-1">Mot de passe</label>
                <input id="password" type="password" name="password" required class="w-full px-3 py-2 border border-emerald-200 rounded focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-[#f8fafc]">
            </div>
            <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-2 rounded-full hover:bg-emerald-700 transition text-lg tracking-wide shadow">Se connecter</button>
        </form>
    </div>
</div>
@endsection
