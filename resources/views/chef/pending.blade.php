<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte en attente | {{ config('app.name', 'SGP-RCPB') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen bg-[#f1f5f9] flex items-center justify-center px-4">

    <div class="w-full max-w-lg">
        <div class="rounded-[32px] bg-white p-10 shadow-xl text-center">

            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-amber-100">
                <i class="fas fa-clock text-4xl text-amber-500"></i>
            </div>

            <p class="text-xs font-black uppercase tracking-[0.25em] text-amber-500">Configuration requise</p>
            <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900">
                Compte non encore configuré
            </h1>
            <p class="mt-4 text-sm text-slate-500 leading-relaxed">
                Votre compte <strong>Chef</strong> est bien créé, mais il n'est pas encore associé à une structure
                (service, agence ou guichet).
                <br><br>
                L'administrateur doit compléter la configuration en éditant la structure concernée et en vous
                désignant comme chef responsable.
            </p>

            <div class="mt-8 rounded-2xl border border-amber-100 bg-amber-50 px-5 py-4 text-left">
                <p class="text-xs font-black uppercase tracking-wider text-amber-600">À faire par l'administrateur</p>
                <ol class="mt-3 space-y-2 text-sm text-amber-800">
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 text-[10px] font-black">1</span>
                        Aller dans <strong>Administration → Structures</strong> (Services, Agences ou Guichets)
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 text-[10px] font-black">2</span>
                        Modifier la structure dont <strong>{{ auth()->user()->name }}</strong> est responsable
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 text-[10px] font-black">3</span>
                        Sélectionner cet agent dans le champ <strong>Chef responsable</strong>
                    </li>
                </ol>
            </div>

            <form method="POST" action="{{ route('chef.pending.logout') }}" class="mt-8">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <i class="fas fa-sign-out-alt text-slate-400"></i>
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>

</body>
</html>
