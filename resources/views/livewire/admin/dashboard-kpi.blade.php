<div class="flex flex-col lg:flex-row gap-6 mb-8">
    <!-- Bloc Disponibilité Admin -->
    <div class="bg-white rounded-2xl shadow p-6 flex flex-col items-center justify-center min-w-[260px] max-w-xs mb-4 lg:mb-0">
        @php
            $user = auth()->user();
            $initial = $user ? strtoupper(mb_substr($user->name, 0, 1)) : 'A';
        @endphp
        <div class="w-20 h-20 rounded-full bg-emerald-600 flex items-center justify-center text-white text-3xl font-bold mb-3">
            @if($user && $user->profile_photo_url)
                <img src="{{ $user->profile_photo_url }}" alt="Photo de profil" class="w-20 h-20 rounded-full object-cover" />
            @else
                {{ $initial }}
            @endif
        </div>
        <div class="text-center">
            <div class="font-semibold text-lg text-slate-800">{{ $user ? $user->name : 'Administrateur' }}</div>
            <div class="text-xs text-slate-500 mb-1">{{ $user && $user->role ? $user->role : 'Administrateur SGP-RCPB' }}</div>
            <div class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-semibold inline-block mt-2">Connecté</div>
        </div>
    </div>
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6 flex-1">
    <!-- Directions -->
    <a href="{{ route('admin.directions.index') }}" aria-label="Voir les directions"
       class="group bg-gradient-to-br from-blue-100 to-blue-50 rounded-2xl shadow hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex flex-col justify-between p-6 min-h-[120px] relative">
        <div class="flex items-center justify-between">
            <span class="text-lg font-semibold text-blue-700">Directions</span>
            <span class="text-3xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M9 21V9a3 3 0 013-3h0a3 3 0 013 3v12" /></svg>
            </span>
        </div>
        <div class="mt-4 flex items-end justify-between">
            <span class="text-4xl font-bold text-blue-800">{{ $totalDirections }}</span>
            <span class="ml-2 text-xs text-blue-600">Voir la liste</span>
        </div>
    </a>
    <!-- Caisses -->
    <a href="{{ route('admin.caisses.index') }}" aria-label="Voir les caisses"
       class="group bg-gradient-to-br from-green-100 to-green-50 rounded-2xl shadow hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex flex-col justify-between p-6 min-h-[120px] relative">
        <div class="flex items-center justify-between">
            <span class="text-lg font-semibold text-green-700">Caisses</span>
            <span class="text-3xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 10c-4.41 0-8-1.79-8-4V7c0-2.21 3.59-4 8-4s8 1.79 8 4v7c0 2.21-3.59 4-8 4z"/></svg>
            </span>
        </div>
        <div class="mt-4 flex items-end justify-between">
            <span class="text-4xl font-bold text-green-800">{{ $totalCaisses }}</span>
            <span class="ml-2 text-xs text-green-600">Voir la liste</span>
        </div>
    </a>
    <!-- Agences -->
    <a href="{{ route('admin.agences.index') }}" aria-label="Voir les agences"
       class="group bg-gradient-to-br from-yellow-100 to-yellow-50 rounded-2xl shadow hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex flex-col justify-between p-6 min-h-[120px] relative">
        <div class="flex items-center justify-between">
            <span class="text-lg font-semibold text-yellow-700">Agences</span>
            <span class="text-3xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M9 21V9a3 3 0 013-3h0a3 3 0 013 3v12" /></svg>
            </span>
        </div>
        <div class="mt-4 flex items-end justify-between">
            <span class="text-4xl font-bold text-yellow-800">{{ $totalAgences }}</span>
            <span class="ml-2 text-xs text-yellow-600">Voir la liste</span>
        </div>
    </a>
    <!-- Guichets -->
    <a href="{{ route('admin.guichets.index') }}" aria-label="Voir les guichets"
       class="group bg-gradient-to-br from-purple-100 to-purple-50 rounded-2xl shadow hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex flex-col justify-between p-6 min-h-[120px] relative">
        <div class="flex items-center justify-between">
            <span class="text-lg font-semibold text-purple-700">Guichets</span>
            <span class="text-3xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v7a2 2 0 002 2h12a2 2 0 002-2v-7a2 2 0 00-2-2z" /></svg>
            </span>
        </div>
        <div class="mt-4 flex items-end justify-between">
            <span class="text-4xl font-bold text-purple-800">{{ $totalGuichets }}</span>
            <span class="ml-2 text-xs text-purple-600">Voir la liste</span>
        </div>
    </a>
    <!-- Agents -->
    <a href="{{ route('admin.agents.index') }}" aria-label="Voir les agents"
       class="group bg-gradient-to-br from-pink-100 to-pink-50 rounded-2xl shadow hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex flex-col justify-between p-6 min-h-[120px] relative">
        <div class="flex items-center justify-between">
            <span class="text-lg font-semibold text-pink-700">Agents</span>
            <span class="text-3xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </span>
        </div>
        <div class="mt-4 flex items-end justify-between">
            <span class="text-4xl font-bold text-pink-800">{{ $totalAgents }}</span>
            @if($newAgentsThisWeek > 0)
                <span class="ml-2 bg-pink-600 text-white text-xs px-2 py-0.5 rounded-full animate-bounce">+{{ $newAgentsThisWeek }} cette semaine</span>
            @endif
        </div>
    </a>
</div>
