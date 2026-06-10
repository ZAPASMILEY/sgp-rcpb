{{--
    Bannière visible quand les évaluations ou objectifs sont désactivés.
    Variables attendues (partagées par AppServiceProvider) :
      $evaluationsEnabled, $objectifsEnabled
      $evaluationsDisabledMessage, $objectifsDisabledMessage
    Usage : @include('layouts._features_notice')
--}}
@if(!$evaluationsEnabled || !$objectifsEnabled)
<div class="mx-4 mt-4 lg:mx-8 space-y-2">
    @if(!$evaluationsEnabled)
    <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
        <i class="fas fa-lock mt-0.5 text-sm text-amber-500 shrink-0"></i>
        <div class="min-w-0">
            <p class="text-sm font-black text-amber-800">Évaluations désactivées</p>
            @if($evaluationsDisabledMessage)
                <p class="mt-0.5 text-xs text-amber-700">{{ $evaluationsDisabledMessage }}</p>
            @else
                <p class="mt-0.5 text-xs text-amber-700">La création et soumission d'évaluations est temporairement bloquée par l'administrateur.</p>
            @endif
        </div>
    </div>
    @endif
    @if(!$objectifsEnabled)
    <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
        <i class="fas fa-lock mt-0.5 text-sm text-amber-500 shrink-0"></i>
        <div class="min-w-0">
            <p class="text-sm font-black text-amber-800">Assignation d'objectifs désactivée</p>
            @if($objectifsDisabledMessage)
                <p class="mt-0.5 text-xs text-amber-700">{{ $objectifsDisabledMessage }}</p>
            @else
                <p class="mt-0.5 text-xs text-amber-700">L'assignation de fiches d'objectifs est temporairement bloquée par l'administrateur.</p>
            @endif
        </div>
    </div>
    @endif
</div>
@endif
