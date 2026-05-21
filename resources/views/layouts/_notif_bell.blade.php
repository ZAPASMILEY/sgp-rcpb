{{--
    Partial : cloche de notifications (in-app, polling toutes les 30 s).
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
        id="notif-bell-btn-{{ $bellId }}"
        class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 shadow-sm transition hover:border-slate-300 hover:text-slate-600"
    >
        <i class="fas fa-bell text-sm"></i>
        <span id="notif-badge-{{ $bellId }}"
              class="absolute -right-1 -top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-black text-white leading-none {{ ($alertesNonLuesCount ?? 0) > 0 ? '' : 'hidden' }}">
            {{ ($alertesNonLuesCount ?? 0) > 99 ? '99+' : ($alertesNonLuesCount ?? 0) }}
        </span>
    </button>
</div>

{{-- Dropdown téléporté au niveau du <body> via JS --}}
<div id="notif-dropdown-{{ $bellId }}"
     class="hidden w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
     style="position:fixed; z-index:99999; top:0; right:0;">

    {{-- En-tête --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
        <div class="flex items-center gap-2">
            <i class="fas fa-bell text-xs text-slate-400"></i>
            <p class="text-sm font-black text-slate-800">Notifications</p>
            <span id="notif-count-label-{{ $bellId }}"
                  class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-black text-rose-600 {{ ($alertesNonLuesCount ?? 0) > 0 ? '' : 'hidden' }}">
                {{ $alertesNonLuesCount ?? 0 }}
            </span>
        </div>
        <button type="button" id="notif-lire-tout-btn-{{ $bellId }}"
                class="text-[11px] font-bold text-emerald-600 hover:underline {{ ($alertesNonLuesCount ?? 0) > 0 ? '' : 'hidden' }}">
            Tout marquer lu
        </button>
    </div>

    {{-- Liste (rendue côté Blade au premier chargement, mise à jour via AJAX ensuite) --}}
    <div id="notif-list-{{ $bellId }}" class="max-h-80 overflow-y-auto divide-y divide-slate-50">
        @forelse(($alertesNonLues ?? collect()) as $notif)
            @php
                $iconBg = match($notif->priorite) {
                    'critique' => 'bg-red-100 text-red-500',
                    'haute'    => 'bg-orange-100 text-orange-500',
                    'moyenne'  => 'bg-blue-100 text-blue-500',
                    default    => 'bg-slate-100 text-slate-400',
                };
                $icon = in_array($notif->priorite, ['critique','haute']) ? 'fa-circle-exclamation' : 'fa-bell';
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

    {{-- Pied --}}
    <div class="border-t border-slate-100 px-4 py-2.5 flex items-center justify-between gap-2">
        <p class="text-[11px] text-slate-300">Actualisé toutes les 30 s</p>
        <a href="{{ route('notifications.index') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-3 py-1.5 text-[11px] font-bold text-slate-600 transition hover:bg-slate-100">
            <i class="fas fa-list text-[9px]"></i> Voir toutes
        </a>
    </div>
</div>

<script>
(function () {
    var bellId      = '{{ $bellId }}';
    var csrfToken   = '{{ csrf_token() }}';
    var btn         = document.getElementById('notif-bell-btn-'      + bellId);
    var dropdown    = document.getElementById('notif-dropdown-'      + bellId);
    var badge       = document.getElementById('notif-badge-'         + bellId);
    var countLbl    = document.getElementById('notif-count-label-'   + bellId);
    var list        = document.getElementById('notif-list-'          + bellId);
    var lireToutBtn = document.getElementById('notif-lire-tout-btn-' + bellId);

    if (!btn || !dropdown) return;

    // Téléporter le dropdown au niveau du <body>
    document.body.appendChild(dropdown);

    // ── Positionnement ───────────────────────────────────────────────────────
    function reposition() {
        var rect = btn.getBoundingClientRect();
        dropdown.style.top   = (rect.bottom + 8) + 'px';
        dropdown.style.right = (window.innerWidth - rect.right) + 'px';
        dropdown.style.left  = 'auto';
    }

    // ── Toggle ───────────────────────────────────────────────────────────────
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (dropdown.classList.contains('hidden')) {
            reposition();
            dropdown.classList.remove('hidden');
            refresh(); // rafraîchir à l'ouverture
        } else {
            dropdown.classList.add('hidden');
        }
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    window.addEventListener('scroll', function () {
        if (!dropdown.classList.contains('hidden')) reposition();
    }, { passive: true });

    window.addEventListener('resize', function () {
        if (!dropdown.classList.contains('hidden')) reposition();
    }, { passive: true });

    // ── Helpers rendu HTML ───────────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function iconBgClass(priorite) {
        if (priorite === 'critique') return 'bg-red-100 text-red-500';
        if (priorite === 'haute')    return 'bg-orange-100 text-orange-500';
        if (priorite === 'moyenne')  return 'bg-blue-100 text-blue-500';
        return 'bg-slate-100 text-slate-400';
    }

    function iconName(priorite) {
        return (priorite === 'critique' || priorite === 'haute') ? 'fa-circle-exclamation' : 'fa-bell';
    }

    function renderList(items) {
        if (!items || items.length === 0) {
            list.innerHTML =
                '<div class="px-4 py-10 text-center">'
                + '<i class="fas fa-check-circle text-2xl text-emerald-300"></i>'
                + '<p class="mt-2 text-sm font-semibold text-slate-400">Aucune nouvelle notification</p>'
                + '</div>';
            return;
        }
        list.innerHTML = items.map(function (n) {
            return '<div class="flex items-start gap-3 px-4 py-3 transition hover:bg-slate-50">'
                + '<div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ' + iconBgClass(n.priorite) + '">'
                + '<i class="fas ' + iconName(n.priorite) + ' text-xs"></i></div>'
                + '<div class="min-w-0 flex-1">'
                + '<p class="truncate text-sm font-bold text-slate-800">' + escHtml(n.titre) + '</p>'
                + (n.message ? '<p class="mt-0.5 text-[11px] text-slate-500 line-clamp-2">' + escHtml(n.message) + '</p>' : '')
                + '<p class="mt-1 text-[10px] font-semibold text-slate-300">' + escHtml(n.age) + '</p>'
                + '</div></div>';
        }).join('');
    }

    function updateBadge(count) {
        var label = count > 99 ? '99+' : String(count);
        badge.textContent    = label;
        countLbl.textContent = label;
        if (count > 0) {
            badge.classList.remove('hidden');
            countLbl.classList.remove('hidden');
            lireToutBtn.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
            countLbl.classList.add('hidden');
            lireToutBtn.classList.add('hidden');
        }
    }

    // ── Fetch JSON ───────────────────────────────────────────────────────────
    function refresh() {
        fetch('/alertes/non-lues', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data) return;
            updateBadge(data.count);
            renderList(data.items);
        })
        .catch(function () {}); // silencieux (session expirée, réseau, etc.)
    }

    // ── Polling toutes les 30 secondes ───────────────────────────────────────
    setInterval(refresh, 60000);

    // ── Tout marquer lu via AJAX ─────────────────────────────────────────────
    if (lireToutBtn) {
        lireToutBtn.addEventListener('click', function () {
            fetch('/alertes/lire-tout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN':      csrfToken,
                    'X-Requested-With':  'XMLHttpRequest',
                    'Accept':            'application/json',
                },
                credentials: 'same-origin',
            })
            .then(function () { refresh(); })
            .catch(function () {});
        });
    }
})();
</script>
