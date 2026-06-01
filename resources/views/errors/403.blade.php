<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accès interdit — SGP-RCPB</title>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-slate-50 flex items-center justify-center">
    <div class="text-center px-6 py-16 max-w-md mx-auto">
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-100">
            <svg class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-slate-800">Accès refusé</h1>
        <p class="mt-3 text-sm text-slate-500">
            Vous n'êtes pas autorisé à accéder à cette page.<br>
            Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur.
        </p>

        @auth
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}"
               class="mt-8 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                Retour
            </a>
        @else
            <a href="{{ route('login') }}"
               class="mt-8 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition">
                Se connecter
            </a>
        @endauth
    </div>
</body>
</html>
