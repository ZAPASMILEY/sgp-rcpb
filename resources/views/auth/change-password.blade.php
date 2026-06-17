<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Changer votre mot de passe — SGP-RCPB</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased" style="font-family:'Inter',sans-serif; background:#f1f5f9; display:flex; align-items:center; justify-content:center; min-height:100vh;">

    <div class="w-full max-w-md px-4">

        {{-- Logo / En-tête --}}
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-md ring-2 ring-amber-200">
                <i class="fas fa-lock text-2xl text-amber-500"></i>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Changement de mot de passe</h1>
            <p class="mt-1 text-sm text-slate-500">
                Bienvenue <span class="font-semibold text-slate-700">{{ auth()->user()->name }}</span>.
                Vous devez définir un nouveau mot de passe avant de continuer.
            </p>
        </div>

        {{-- Alerte obligatoire --}}
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
            <i class="fas fa-triangle-exclamation mt-0.5 shrink-0 text-amber-500"></i>
            <p class="text-sm font-semibold text-amber-700">
                Votre mot de passe actuel est temporaire. Vous ne pouvez pas accéder à l'application tant qu'il n'a pas été changé.
            </p>
        </div>

        {{-- Formulaire --}}
        <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100">

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li><i class="fas fa-circle-exclamation mr-1"></i>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.change.update') }}" class="space-y-5">
                @csrf

                {{-- Nouveau mot de passe --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nouveau mot de passe
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               minlength="8"
                               autocomplete="new-password"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-4 pr-10 text-sm font-medium text-slate-800 placeholder-slate-300 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition @error('password') border-rose-300 bg-rose-50 @enderror"
                               placeholder="Minimum 8 caractères">
                        <button type="button" onclick="togglePwd('password', 'eye1')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <i id="eye1" class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Confirmer le mot de passe
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               required
                               minlength="8"
                               autocomplete="new-password"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-4 pr-10 text-sm font-medium text-slate-800 placeholder-slate-300 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition"
                               placeholder="Répétez le mot de passe">
                        <button type="button" onclick="togglePwd('password_confirmation', 'eye2')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <i id="eye2" class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Indicateur de force --}}
                <div>
                    <div class="flex gap-1 mb-1">
                        <div id="str1" class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></div>
                        <div id="str2" class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></div>
                        <div id="str3" class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></div>
                        <div id="str4" class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></div>
                    </div>
                    <p id="str-label" class="text-[11px] text-slate-400"></p>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 active:scale-[.98]">
                    <i class="fas fa-check mr-2"></i>Valider et accéder à l'application
                </button>
            </form>
        </div>

        {{-- Déconnexion --}}
        @php
            $logoutRoute = match(true) {
                auth()->user()->isPca()
                    => 'pca.logout',
                method_exists(auth()->user(), 'isDg') && auth()->user()->isDg()
                    => 'dg.logout',
                auth()->user()->role === 'DGA'
                    => 'dga.logout',
                in_array(auth()->user()->role, ['Assistante_Dg','Conseillers_Dg','Secretaire_Assistante'], true)
                    => 'subordonne.logout',
                in_array(auth()->user()->role, ['Directeur_Direction','Directeur_Technique','Directeur_Caisse'], true)
                    => 'directeur.logout',
                in_array(auth()->user()->role, ['Chef_Service','Chef_Agence','Chef_Guichet'], true)
                    => 'chef.logout',
                auth()->user()->role === 'RH'
                    => 'rh.logout',
                auth()->user()->isPersonnel()
                    => 'personnel.logout',
                default
                    => 'admin.logout',
            };
        @endphp
        <div class="mt-4 text-center">
            <form method="POST" action="{{ route($logoutRoute) }}" class="inline">
                @csrf
                <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition">
                    <i class="fas fa-arrow-left mr-1"></i>Se déconnecter
                </button>
            </form>
        </div>

    </div>

    <script>
        function togglePwd(fieldId, iconId) {
            var f = document.getElementById(fieldId);
            var i = document.getElementById(iconId);
            if (f.type === 'password') {
                f.type = 'text';
                i.className = 'fas fa-eye-slash text-sm';
            } else {
                f.type = 'password';
                i.className = 'fas fa-eye text-sm';
            }
        }

        // Indicateur de force
        document.getElementById('password').addEventListener('input', function () {
            var v = this.value;
            var score = 0;
            if (v.length >= 8)  score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;

            var colors = ['bg-rose-400', 'bg-orange-400', 'bg-amber-400', 'bg-emerald-500'];
            var labels = ['Trop faible', 'Faible', 'Moyen', 'Fort'];

            for (var i = 1; i <= 4; i++) {
                var el = document.getElementById('str' + i);
                el.className = 'h-1.5 flex-1 rounded-full transition-colors ' + (i <= score ? colors[score - 1] : 'bg-slate-200');
            }
            document.getElementById('str-label').textContent = v.length > 0 ? labels[score - 1] || '' : '';
        });
    </script>

</body>
</html>
