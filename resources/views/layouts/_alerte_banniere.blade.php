{{--
    Partial : banners d'alertes persistantes.

    Affiche chaque alerte non lue comme une bannière fixe en haut de page.
    La bannière reste visible jusqu'à ce que l'utilisateur clique « OK ».
    Les nouvelles alertes sont détectées via polling AJAX (toutes les 30 s).

    Variables injectées globalement par AppServiceProvider :
      $alertesNonLues      — Collection<Alerte>  (8 dernières non lues)
      $alertesNonLuesCount — int
--}}
@php
    $initialBanners = ($alertesNonLues ?? collect())
        ->sortBy(fn ($a) => match($a->priorite) {
            'critique' => 0, 'haute' => 1, 'moyenne' => 2, default => 3
        })
        ->map(fn ($a) => [
            'id'       => $a->id,
            'titre'    => $a->titre,
            'message'  => \Illuminate\Support\Str::limit($a->message ?? '', 120),
            'priorite' => $a->priorite,
            'age'      => $a->created_at->diffForHumans(),
        ])
        ->values();
@endphp

{{-- Conteneur des bannières (positionné via JS après chargement DOM) --}}
<div id="alertes-bannieres-container"
     role="alert"
     aria-live="polite"
     style="display:none; position:fixed; top:0; left:0; right:0; z-index:99998; box-shadow:0 2px 12px rgba(0,0,0,0.12);">
</div>

<script>
(function () {
    'use strict';

    var CSRF   = '{{ csrf_token() }}';
    var wrap   = document.getElementById('alertes-bannieres-container');
    if (!wrap) return;

    var shownIds = {};   // id → true (déjà affiché)

    // ── Palettes couleur par priorité ──────────────────────────────────────────
    function palette(priorite) {
        switch (priorite) {
            case 'critique': return { bg:'#fef2f2', border:'#fca5a5', text:'#7f1d1d', accent:'#dc2626', iconBg:'#fee2e2', icon:'fa-triangle-exclamation', label:'CRITIQUE' };
            case 'haute':    return { bg:'#fff7ed', border:'#fdba74', text:'#7c2d12', accent:'#ea580c', iconBg:'#ffedd5', icon:'fa-circle-exclamation',    label:'HAUTE'    };
            case 'moyenne':  return { bg:'#eff6ff', border:'#93c5fd', text:'#1e3a8a', accent:'#2563eb', iconBg:'#dbeafe', icon:'fa-bell',                  label:'MOYENNE'  };
            default:         return { bg:'#f8fafc', border:'#cbd5e1', text:'#334155', accent:'#64748b', iconBg:'#f1f5f9', icon:'fa-bell',                  label:'BASSE'    };
        }
    }

    // ── Échappement HTML ────────────────────────────────────────────────────────
    function esc(s) {
        return String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Ajuster le décalage du contenu principal ──────────────────────────────
    function adjust() {
        var h = wrap.offsetHeight;
        // Décaler le .main-content (layouts avec sidebar)
        var mc = document.querySelector('.main-content');
        if (mc) mc.style.paddingTop = h > 0 ? h + 'px' : '';
        // Décaler le body pour les layouts sans sidebar ou en cas de besoin
        document.body.style.paddingTop = mc ? '' : (h > 0 ? h + 'px' : '');
    }

    // ── Construire un élément bannière ─────────────────────────────────────────
    function makeBanner(notif) {
        var p   = palette(notif.priorite);
        var div = document.createElement('div');
        div.id  = 'alert-banner-' + notif.id;
        div.setAttribute('data-alerte-id', notif.id);
        div.style.cssText =
            'display:flex; align-items:center; gap:12px;'
            + 'padding:9px 16px;'
            + 'background:' + p.bg + ';'
            + 'border-bottom:1px solid ' + p.border + ';'
            + 'transition:opacity .3s ease, max-height .35s ease;'
            + 'overflow:hidden; max-height:80px;';

        div.innerHTML =
            // Icône
            '<div style="flex-shrink:0;width:30px;height:30px;border-radius:8px;'
                + 'background:' + p.iconBg + ';display:flex;align-items:center;justify-content:center;">'
                + '<i class="fas ' + p.icon + '" style="color:' + p.accent + ';font-size:13px;"></i>'
            + '</div>'
            // Texte
            + '<div style="flex:1;min-width:0;">'
                + '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">'
                    + '<span style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;'
                        + 'color:' + p.accent + ';background:' + p.iconBg + ';'
                        + 'padding:1px 6px;border-radius:20px;">' + esc(p.label) + '</span>'
                    + '<span style="font-size:13px;font-weight:700;color:' + p.text + ';">' + esc(notif.titre) + '</span>'
                + '</div>'
                + (notif.message
                    ? '<p style="margin:2px 0 0;font-size:11px;color:' + p.text + ';opacity:.75;'
                        + 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:700px;">'
                        + esc(notif.message) + '</p>'
                    : '')
            + '</div>'
            // Horodatage
            + '<span style="flex-shrink:0;font-size:10px;color:' + p.text + ';opacity:.5;">' + esc(notif.age) + '</span>'
            // Bouton OK
            + '<button type="button" onclick="sgpDismissAlert(' + notif.id + ')" '
                + 'style="flex-shrink:0;padding:5px 14px;border-radius:6px;'
                + 'background:' + p.accent + ';color:#fff;'
                + 'font-size:11px;font-weight:700;border:none;cursor:pointer;'
                + 'letter-spacing:.05em;transition:opacity .2s;" '
                + 'onmouseover="this.style.opacity=\'.8\'" onmouseout="this.style.opacity=\'1\'">'
                + 'OK'
            + '</button>';

        return div;
    }

    // ── Ajouter une bannière (si pas déjà affichée) ───────────────────────────
    function addBanner(notif) {
        if (shownIds[notif.id]) return;
        shownIds[notif.id] = true;
        wrap.style.display = 'block';
        wrap.appendChild(makeBanner(notif));
        adjust();
    }

    // ── Dismiss : marquer lu + retirer la bannière ────────────────────────────
    window.sgpDismissAlert = function (id) {
        // Appel AJAX silencieux
        fetch('/mes-notifications/' + id + '/marquer-lu', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':     CSRF,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
            credentials: 'same-origin',
        }).catch(function () {});

        // Animer la sortie
        var banner = document.getElementById('alert-banner-' + id);
        if (banner) {
            banner.style.opacity    = '0';
            banner.style.maxHeight  = '0';
            banner.style.padding    = '0 16px';
            setTimeout(function () {
                if (banner.parentNode) banner.parentNode.removeChild(banner);
                if (wrap.children.length === 0) wrap.style.display = 'none';
                adjust();
            }, 370);
        }

        delete shownIds[id];
    };

    // ── Tri par priorité ───────────────────────────────────────────────────────
    var PRIO = { critique: 0, haute: 1, moyenne: 2, basse: 3 };
    function sorted(items) {
        return items.slice().sort(function (a, b) {
            return (PRIO[a.priorite] ?? 9) - (PRIO[b.priorite] ?? 9);
        });
    }

    // ── Initialisation (données serveur au chargement de la page) ─────────────
    sorted(@json($initialBanners)).forEach(addBanner);

    // ── Polling : détecter les nouvelles alertes ───────────────────────────────
    function poll() {
        fetch('/alertes/non-lues', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
            credentials: 'same-origin',
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data || !data.items) return;
            sorted(data.items).forEach(function (notif) {
                if (!shownIds[notif.id]) addBanner(notif);
            });
        })
        .catch(function () {});  // silencieux (session expirée, réseau)
    }

    setInterval(poll, 30000);

    // ── Recalculer lors du redimensionnement ───────────────────────────────────
    window.addEventListener('resize', adjust, { passive: true });

})();
</script>
