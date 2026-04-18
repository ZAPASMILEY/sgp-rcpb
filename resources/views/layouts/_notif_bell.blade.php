{{--
    Partial : cloche de notifications (in-app).
    Variables injectées globalement via AppServiceProvider :
      $alertesNonLues        — Collection<Alerte>
      $alertesNonLuesCount   — int
    Paramètre optionnel :
      $bellId  — suffixe unique pour éviter les conflits d'ID (défaut : 'main')
--}}
@php $bellId = $bellId ?? 'main'; @endphp

<div class="relative" id="notif-bell-wrapper-{{ $bellId }}">
    <button
        type="button"
        aria-label="Notifications"
        onclick="document.getElementById('notif-dropdown-{{ $bellId }}').classList.toggle('hidden')"
        class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 shadow-sm transition hover:border-slate-300 hover:text-slate-600"
    >
        <i class="fas fa-bell text-sm"></i>
        @if(($alertesNonLuesCount ?? 0) > 0)
            <span class="absolute -right-1 -top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-black text-white leading-none">
                {{ ($alertesNonLuesCount ?? 0) > 99 ? '99+' : $alertesNonLuesCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div id="notif-dropdown-{{ $bellId }}"
         class="absolute right-0 top-full z-50 mt-2 hidden w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">

        {{-- En-tête dropdown --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div class="flex items-center gap-2">
                <i class="fas fa-bell text-xs text-slate-400"></i>
                <p class="text-sm font-black text-slate-800">Notifications</p>
                @if(($alertesNonLuesCount ?? 0) > 0)
                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-black text-rose-600">
                        {{ $alertesNonLuesCount }}
                    </span>
                @endif
            </div>
            @if(($alertesNonLuesCount ?? 0) > 0)
                <form method="POST" action="{{ route('alertes.lire-tout') }}">
                    @csrf
                    <button type="submit" class="text-[11px] font-bold text-emerald-600 hover:underline">
                        Tout marquer lu
                    </button>
                </form>
            @endif
        </div>

        {{-- Liste --}}
        <div class="max-h-80 overflow-y-auto divide-y divide-slate-50">
            @forelse(($alertesNonLues ?? collect()) as $notif)
                @php
                    $iconBg = match($notif->priorite) {
                        'critique' => 'bg-red-100 text-red-500',
                        'haute'    => 'bg-orange-100 text-orange-500',
                        'moyenne'  => 'bg-blue-100 text-blue-500',
                        default    => 'bg-slate-100 text-slate-400',
                    };
                    $icon = in_array($notif->priorite, ['critique','haute'])
                        ? 'fa-circle-exclamation'
                        : 'fa-bell';
                @endphp
                <div class="flex items-start gap-3 px-4 py-3 transition hover:bg-slate-50">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $iconBg }}">
                        <i class="fas {{ $icon }} text-xs"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-slate-800">{{ $notif->titre }}</p>
                        @if ($notif->message)
                            <p class="mt-0.5 text-[11px] text-slate-500 line-clamp-2">{{ Str::limit($notif->message, 70) }}</p>
                        @endif
                        <p class="mt-1 text-[10px] font-semibold text-slate-300">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <i class="fas fa-check-circle text-2xl text-emerald-300"></i>
                    <p class="mt-2 text-sm font-semibold text-slate-400">Aucune nouvelle notification</p>
                </div>
            @endforelse
        </div>

        {{-- Pied dropdown --}}
        <div class="border-t border-slate-100 px-4 py-2.5 text-center">
            <p class="text-[11px] text-slate-300">Les notifications lues disparaissent automatiquement</p>
        </div>
    </div>
</div>

{{-- Fermer en cliquant ailleurs --}}
<script>
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('notif-bell-wrapper-{{ $bellId }}');
    const dropdown = document.getElementById('notif-dropdown-{{ $bellId }}');
    if (wrapper && dropdown && !wrapper.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
