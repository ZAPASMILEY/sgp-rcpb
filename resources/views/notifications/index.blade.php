@extends($layout)

@section('title', 'Mes notifications · ' . config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-700 via-slate-600 to-slate-800 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-10 left-1/3 h-40 w-40 rounded-full bg-slate-400/10 blur-2xl"></div>

        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-xl text-white shadow ring-1 ring-white/20">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-slate-300">Mon espace</p>
                    <h1 class="mt-0.5 text-2xl font-black text-white">Mes notifications</h1>
                    <p class="mt-0.5 text-sm text-slate-300/80">
                        Toutes vos alertes et messages reçus
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if ($totalNonLues > 0)
                    <form method="POST" action="{{ route('notifications.lire-tout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                            <i class="fas fa-check-double text-xs"></i>
                            Tout marquer lu
                            <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1.5 text-[10px] font-black">
                                {{ $totalNonLues }}
                            </span>
                        </button>
                    </form>
                @endif
                @if ($notifications->isNotEmpty())
                    <form method="POST" action="{{ route('notifications.supprimer-tout') }}"
                          onsubmit="return confirm('Supprimer toutes vos notifications ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-rose-500/80 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-rose-600">
                            <i class="fas fa-trash text-xs"></i>
                            Tout supprimer
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-3xl px-4 pt-6 lg:px-8">

        @if (session('status'))
            <div class="mb-4 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle"></i> {{ session('status') }}
            </div>
        @endif

        @if ($notifications->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-20 text-center shadow-sm">
                <i class="fas fa-bell-slash text-4xl text-slate-200"></i>
                <p class="mt-4 text-base font-black text-slate-400">Aucune notification</p>
                <p class="mt-1 text-sm text-slate-300">Vous n'avez reçu aucune notification pour l'instant.</p>
            </div>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($notifications as $notif)
                    @php
                        $lu = (bool) $notif->pivot->lu;
                        $iconBg = match($notif->priorite) {
                            'critique' => 'bg-red-100 text-red-500',
                            'haute'    => 'bg-orange-100 text-orange-500',
                            'moyenne'  => 'bg-blue-100 text-blue-500',
                            default    => 'bg-slate-100 text-slate-400',
                        };
                        $icon = in_array($notif->priorite, ['critique', 'haute'])
                            ? 'fa-circle-exclamation'
                            : 'fa-bell';
                        $borderCls = $lu ? 'border-slate-100' : 'border-blue-100';
                        $bgCls     = $lu ? 'bg-white' : 'bg-blue-50/40';
                        $badgeCls  = match($notif->priorite) {
                            'critique' => 'bg-red-100 text-red-600',
                            'haute'    => 'bg-orange-100 text-orange-600',
                            'moyenne'  => 'bg-blue-100 text-blue-600',
                            default    => 'bg-slate-100 text-slate-500',
                        };
                        $prioriteLabel = match($notif->priorite) {
                            'critique' => 'Critique',
                            'haute'    => 'Haute',
                            'moyenne'  => 'Moyenne',
                            default    => 'Normale',
                        };
                    @endphp

                    <div class="relative rounded-2xl border {{ $borderCls }} {{ $bgCls }} px-5 py-4 shadow-sm transition">
                        {{-- Indicateur non lu --}}
                        @if (! $lu)
                            <span class="absolute right-4 top-4 h-2 w-2 rounded-full bg-blue-500"></span>
                        @endif

                        <div class="flex items-start gap-4">
                            {{-- Icône priorité --}}
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $iconBg }} mt-0.5">
                                <i class="fas {{ $icon }} text-sm"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                {{-- Titre + badges --}}
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <p class="text-sm font-black text-slate-800">{{ $notif->titre }}</p>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $badgeCls }}">
                                        {{ $prioriteLabel }}
                                    </span>
                                    @if (! $lu)
                                        <span class="inline-flex items-center rounded-full bg-blue-500 px-2 py-0.5 text-[10px] font-bold text-white">
                                            Nouveau
                                        </span>
                                    @endif
                                </div>

                                {{-- Message complet --}}
                                @if ($notif->message)
                                    <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap">{{ $notif->message }}</p>
                                @endif

                                {{-- Méta --}}
                                <div class="mt-2 flex flex-wrap items-center gap-3">
                                    <span class="text-[11px] text-slate-400">
                                        <i class="fas fa-clock mr-1"></i>{{ $notif->created_at->diffForHumans() }}
                                        · {{ $notif->created_at->translatedFormat('d M Y à H:i') }}
                                    </span>
                                    @if ($lu && $notif->pivot->lu_at)
                                        <span class="text-[11px] text-slate-300">
                                            <i class="fas fa-check mr-1"></i>Lu le {{ \Carbon\Carbon::parse($notif->pivot->lu_at)->translatedFormat('d M Y à H:i') }}
                                        </span>
                                    @endif
                                    @if ($notif->lien)
                                        <a href="{{ $notif->lien }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-600 transition hover:bg-blue-100">
                                            <i class="fas fa-arrow-up-right-from-square text-[9px]"></i> Consulter
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Action marquer lu --}}
                            @if (! $lu)
                                <form method="POST" action="{{ route('notifications.marquer-lu', $notif) }}" class="shrink-0">
                                    @csrf
                                    <button type="submit"
                                        title="Marquer comme lu"
                                        class="flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 transition hover:border-emerald-300 hover:text-emerald-600">
                                        <i class="fas fa-check text-xs"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">{{ $notifications->count() }} résultat(s)</div>
        @endif

    </div>
</div>
@endsection
