{{--
    Partial JS partagé — formulaires create ET edit d'évaluation.
    IDs attendus dans la page :
      eval-objective-options    — tableau de fiches d'objectifs
      eval-subjective-templates — critères subjectifs initiaux
      eval-objective-old        — critères objectifs old() (après erreur)
      eval-formations-old       — formations old()
      eval-experiences-old      — expériences old()
      eval-agents-data          — (optionnel) données agents pour auto-fill
      eval-prefilled-agent      — (optionnel) id agent pré-sélectionné
--}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    let objectiveOptions       = JSON.parse(document.getElementById('eval-objective-options')?.textContent || '[]');
    const subjectiveTemplates  = JSON.parse(document.getElementById('eval-subjective-templates')?.textContent || '[]');
    const oldObjectiveCriteria = JSON.parse(document.getElementById('eval-objective-old')?.textContent || '[]');
    const oldFormations        = JSON.parse(document.getElementById('eval-formations-old')?.textContent || 'null');
    const oldExperiences       = JSON.parse(document.getElementById('eval-experiences-old')?.textContent || '[]');
    const prefilledAgentId     = JSON.parse(document.getElementById('eval-prefilled-agent')?.textContent || 'null');

    let subjectiveIndexCounter = 0;
    let objectiveIndexCounter  = 0;
    let formationIndexCounter  = 0;
    let experienceIndexCounter = 0;

    const objectiveSelector        = document.getElementById('objective-fiche-selector');
    const addSelectedObjectivesBtn = document.getElementById('add-selected-objectives');
    const objectiveChoiceContainer = document.getElementById('objective-choice-container');
    const objectiveContainer       = document.getElementById('objective-criteria-container');
    const subjectiveContainer      = document.getElementById('subjective-criteria-container');
    const addSubjectiveBtn         = document.getElementById('add-subjective-criterion');
    const formationsRows           = document.getElementById('formations-rows');
    const experiencesRows          = document.getElementById('experiences-rows');
    const addFormationRowBtn       = document.getElementById('add-formation-row');
    const addExperienceRowBtn      = document.getElementById('add-experience-row');
    const summaryMoyenneObjectifs  = document.getElementById('summary-moyenne-objectifs');
    const summaryNoteObjectifs     = document.getElementById('summary-note-objectifs');
    const summaryMoyenneSubjectifs = document.getElementById('summary-moyenne-subjectifs');
    const summaryNoteSubjectifs    = document.getElementById('summary-note-subjectifs');
    const summaryNoteFinale        = document.getElementById('summary-note-finale');

    function formatScore(v) { return Number(v || 0).toFixed(2).replace('.', ','); }

    function updateScoreSummary() {
        function getCriterionNote(card) {
            const subNotes = Array.from(card.querySelectorAll('[data-subcriteria-container] input[name$="[note]"]'))
                .map(i => Number(i.value)).filter(v => !isNaN(v) && v > 0);
            if (subNotes.length > 0) return subNotes.reduce((s, v) => s + v, 0) / subNotes.length;
            const di = card.querySelector('.eval-note-directe-input');
            return di ? (Number(di.value) || 0) : 0;
        }
        const objCards  = Array.from(document.querySelectorAll('#objective-criteria-container article'));
        const subjCards = Array.from(document.querySelectorAll('#subjective-criteria-container article'));
        const objNotes  = objCards.map(getCriterionNote).filter(v => v > 0);
        const subjNotes = subjCards.map(getCriterionNote).filter(v => v > 0);
        const mObj  = objNotes.length  ? objNotes.reduce((s, v) => s + v, 0) / objNotes.length  : 0;
        const mSubj = subjNotes.length ? subjNotes.reduce((s, v) => s + v, 0) / subjNotes.length : 0;
        const nObj  = mObj * 0.75, nSubj = mSubj * 0.25;
        if (summaryMoyenneObjectifs)  summaryMoyenneObjectifs.textContent  = formatScore(mObj);
        if (summaryNoteObjectifs)     summaryNoteObjectifs.textContent     = formatScore(nObj);
        if (summaryMoyenneSubjectifs) summaryMoyenneSubjectifs.textContent = formatScore(mSubj);
        if (summaryNoteSubjectifs)    summaryNoteSubjectifs.textContent    = formatScore(nSubj);
        const noteFinale = (nObj + nSubj) * 2;
        if (summaryNoteFinale)        summaryNoteFinale.textContent        = formatScore(noteFinale);
        // Sidebar live
        const sidebarNote = document.getElementById('sidebar-note');
        const sidebarObj  = document.getElementById('sidebar-obj');
        const sidebarSubj = document.getElementById('sidebar-subj');
        if (sidebarNote) sidebarNote.textContent = formatScore(noteFinale);
        if (sidebarObj)  sidebarObj.textContent  = formatScore(nObj);
        if (sidebarSubj) sidebarSubj.textContent = formatScore(nSubj);
    }

    function escapeHtml(v) {
        return String(v ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function renderSubcriterion(path, parentIdx, sub, subIdx) {
        const w = document.createElement('div');
        w.className = 'grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 grid-cols-[1.6fr_100px_1fr_auto]';
        w.innerHTML = `
            <input type="hidden" name="${path}[${parentIdx}][subcriteria][${subIdx}][source_fiche_objectif_objectif_id]" value="${sub.source_fiche_objectif_objectif_id ?? ''}">
            <input type="text"   name="${path}[${parentIdx}][subcriteria][${subIdx}][libelle]" value="${escapeHtml(sub.libelle ?? '')}" class="ent-input" placeholder="Sous-critère">
            <input type="number" name="${path}[${parentIdx}][subcriteria][${subIdx}][note]" value="${sub.note ?? 1}" min="1" max="5" step="1" class="ent-input" placeholder="Note" oninput="this.value=Math.min(Math.max(parseInt(this.value)||1,1),5)">
            <input type="text"   name="${path}[${parentIdx}][subcriteria][${subIdx}][observation]" value="${escapeHtml(sub.observation ?? '')}" class="ent-input" placeholder="Observation">
            <button type="button" class="ent-btn ent-btn-soft" data-remove-sub>Supprimer</button>
        `;
        w.querySelector('[data-remove-sub]').addEventListener('click', () => {
            const container = w.closest('[data-subcriteria-container]');
            w.remove();
            updateScoreSummary();
            if (container) {
                const noteWrapper = container.closest('article')?.querySelector('.eval-note-directe-wrapper');
                if (noteWrapper) noteWrapper.style.display = container.children.length > 0 ? 'none' : '';
            }
        });
        return w;
    }

    function renderCriterion(path, criterion, idx, options = {}) {
        const article = document.createElement('article');
        article.className = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
        const titleReadonly = options.titleReadonly === true;
        const allowRemove   = options.allowRemoveCriterion !== false;
        const ficheId    = criterion.source_fiche_objectif_id ?? '';
        const objectifId = criterion.source_fiche_objectif_objectif_id ?? '';
        const templateId = path === 'subjective_criteres' ? (criterion.id ?? criterion.source_template_id ?? '') : '';

        article.innerHTML = `
            <div class="grid gap-4 md:grid-cols-[1.5fr_1fr]">
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Critère</label>
                    <input type="text" name="${path}[${idx}][titre]" value="${escapeHtml(criterion.titre ?? '')}" class="ent-input" placeholder="Titre du critère" ${titleReadonly ? 'readonly' : ''}>
                    <input type="hidden" name="${path}[${idx}][source_template_id]"                value="${escapeHtml(path === 'subjective_criteres' ? templateId : '')}">
                    <input type="hidden" name="${path}[${idx}][source_fiche_objectif_id]"          value="${escapeHtml(path === 'objective_criteres' ? ficheId : '')}">
                    <input type="hidden" name="${path}[${idx}][source_fiche_objectif_objectif_id]" value="${escapeHtml(path === 'objective_criteres' ? objectifId : '')}">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Observation globale</label>
                    <input type="text" name="${path}[${idx}][observation]" value="${escapeHtml(criterion.observation ?? '')}" class="ent-input" placeholder="Observation globale">
                </div>
            </div>
            <div class="mt-4 space-y-3" data-subcriteria-container></div>
            <div class="eval-note-directe-wrapper mt-3 flex items-center gap-3">
                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 whitespace-nowrap">Note directe</label>
                <input type="number" name="${path}[${idx}][note_directe]" value="${criterion.note_directe ?? criterion.note_globale ?? 1}" min="1" max="5" step="1"
                       class="ent-input eval-note-directe-input w-24"
                       oninput="this.value=Math.min(Math.max(parseInt(this.value)||1,1),5)">
                <span class="text-xs text-slate-400">(utilisée si aucun sous-critère)</span>
            </div>
            <div class="mt-4 flex justify-between">
                <button type="button" class="ent-btn ent-btn-soft" data-add-sub>Ajouter un sous-critère</button>
                ${allowRemove
                    ? '<button type="button" class="ent-btn ent-btn-soft" data-remove-criterion>Supprimer le critère</button>'
                    : '<span class="text-xs font-medium text-slate-400">Critère issu de la fiche sélectionnée</span>'}
            </div>
        `;

        const subContainer    = article.querySelector('[data-subcriteria-container]');
        const addSubBtn       = article.querySelector('[data-add-sub]');
        const removeBtn       = article.querySelector('[data-remove-criterion]');
        const noteDirecteWrap = article.querySelector('.eval-note-directe-wrapper');
        let subIdx = 0;

        (criterion.subcriteria || []).forEach(sub => {
            subContainer.appendChild(renderSubcriterion(path, idx, sub, subIdx));
            subIdx++;
        });
        noteDirecteWrap.style.display = subContainer.children.length > 0 ? 'none' : '';

        addSubBtn.addEventListener('click', () => {
            subContainer.appendChild(renderSubcriterion(path, idx, { libelle: '', note: 1, observation: '' }, subIdx));
            subIdx++;
            noteDirecteWrap.style.display = 'none';
            updateScoreSummary();
        });
        if (removeBtn) {
            removeBtn.addEventListener('click', () => { article.remove(); updateScoreSummary(); });
        }
        return article;
    }

    // ── Sélecteur de fiches d'objectifs ──────────────────────────────────────
    if (objectiveSelector) {
        function populateObjectiveSelector() {
            objectiveSelector.innerHTML = '<option value="">Sélectionner une fiche d\'objectif</option>';
            objectiveOptions.forEach(item => {
                const opt = document.createElement('option');
                opt.value = String(item.id);
                opt.textContent = `${item.titre} (échéance ${item.date_echeance})`;
                objectiveSelector.appendChild(opt);
            });
            if (objectiveOptions.length === 1) objectiveSelector.value = String(objectiveOptions[0].id);
            renderObjectiveChoices();
        }
        // Exposer globalement pour que le second bloc puisse recharger les fiches via AJAX
        window.sgpReloadObjectives = function(newOptions) {
            objectiveOptions = newOptions;
            populateObjectiveSelector();
        };

        function getSelectedFiche() {
            return objectiveOptions.find(o => String(o.id) === objectiveSelector.value) || null;
        }

        function renderObjectiveChoices() {
            if (!objectiveChoiceContainer) return;
            const selected = getSelectedFiche();
            if (!selected) {
                objectiveChoiceContainer.innerHTML = 'Sélectionnez une fiche pour afficher ses objectifs.';
                return;
            }
            const alreadySelected = new Set(
                Array.from(objectiveContainer.querySelectorAll('input[name^="objective_criteres"][name$="[source_fiche_objectif_objectif_id]"]'))
                    .map(i => String(i.value)).filter(Boolean)
            );
            const objectifs = selected.objectifs || [];
            if (!objectifs.length) {
                objectiveChoiceContainer.innerHTML = 'Cette fiche ne contient aucun objectif disponible.';
                return;
            }
            objectiveChoiceContainer.innerHTML = `
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-slate-800">Objectifs disponibles dans « ${escapeHtml(selected.titre)} »</p>
                    <div class="space-y-2" data-objective-choice-list></div>
                </div>
            `;
            const list = objectiveChoiceContainer.querySelector('[data-objective-choice-list]');
            objectifs.forEach(item => {
                const row = document.createElement('label');
                row.className = 'flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3';
                row.innerHTML = `
                    <input type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600"
                           value="${item.source_fiche_objectif_objectif_id}"
                           data-objective-title="${escapeHtml(item.titre ?? '')}"
                           ${alreadySelected.has(String(item.source_fiche_objectif_objectif_id)) ? 'checked disabled' : ''}>
                    <span class="text-sm text-slate-700">${escapeHtml(item.titre ?? '')}</span>
                `;
                list.appendChild(row);
            });
        }

        function addSelectedObjectives() {
            const selected = getSelectedFiche();
            if (!selected) return;
            const checked = Array.from(objectiveChoiceContainer?.querySelectorAll('input[type="checkbox"]:checked:not(:disabled)') || []);
            if (!checked.length) return;

            // IDs déjà présents dans le formulaire — évite les doublons
            const alreadyAdded = new Set(
                Array.from(objectiveContainer.querySelectorAll('input[name$="[source_fiche_objectif_objectif_id]"]'))
                    .map(i => String(i.value)).filter(Boolean)
            );

            checked.forEach(input => {
                const objId = String(input.value);
                if (alreadyAdded.has(objId)) return;
                alreadyAdded.add(objId);
                objectiveContainer.appendChild(renderCriterion('objective_criteres', {
                    titre: input.dataset.objectiveTitle || '',
                    source_fiche_objectif_id: selected.id,
                    source_fiche_objectif_objectif_id: input.value,
                    observation: '',
                    subcriteria: [{ libelle: '', note: 1, observation: '' }],
                }, objectiveIndexCounter, { titleReadonly: true, allowRemoveCriterion: true }));
                objectiveIndexCounter++;
            });
            renderObjectiveChoices();
            updateScoreSummary();
        }

        objectiveSelector.addEventListener('change', renderObjectiveChoices);
        if (addSelectedObjectivesBtn) addSelectedObjectivesBtn.addEventListener('click', addSelectedObjectives);
        populateObjectiveSelector();
    }

    // ── Critères subjectifs ───────────────────────────────────────────────────
    if (addSubjectiveBtn) {
        addSubjectiveBtn.addEventListener('click', () => {
            subjectiveContainer.appendChild(renderCriterion('subjective_criteres', {
                titre: '', observation: '',
                subcriteria: [{ libelle: '', note: 1, observation: '' }],
            }, subjectiveIndexCounter));
            subjectiveIndexCounter++;
            updateScoreSummary();
        });
    }

    function renderSubjectiveCriteria(criteria) {
        if (!subjectiveContainer) return;
        subjectiveContainer.innerHTML = '';
        criteria.forEach((c, i) => {
            subjectiveContainer.appendChild(renderCriterion('subjective_criteres', c, i));
        });
        subjectiveIndexCounter = criteria.length;
    }

    // ── Formations ────────────────────────────────────────────────────────────
    function makeFormationRow(row, idx) {
        const tr = document.createElement('tr');
        tr.className = 'border-t border-slate-200';
        tr.innerHTML = `
            <td class="p-2"><input type="text" name="identification[formations][${idx}][periode]" value="${escapeHtml(row.periode ?? '')}" class="ent-input"></td>
            <td class="p-2"><input type="text" name="identification[formations][${idx}][libelle]" value="${escapeHtml(row.libelle ?? '')}" class="ent-input"></td>
            <td class="p-2"><input type="text" name="identification[formations][${idx}][domaine]" value="${escapeHtml(row.domaine ?? '')}" class="ent-input"></td>
            <td class="p-2"><button type="button" class="ent-btn ent-btn-soft" data-rm>Supprimer</button></td>
        `;
        tr.querySelector('[data-rm]').addEventListener('click', () => {
            tr.remove();
            if (!formationsRows.children.length) addFormationRow({});
        });
        return tr;
    }
    function addFormationRow(row) {
        if (!formationsRows) return;
        formationsRows.appendChild(makeFormationRow(row || {}, formationIndexCounter));
        formationIndexCounter++;
    }

    window.sgpFillFormations = function (agentId) {
        if (!agentId || !formationsRows) return;
        fetch('/formations/agent/' + agentId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
        .then(r => r.ok ? r.json() : [])
        .then(formations => {
            formationsRows.innerHTML = '';
            formationIndexCounter = 0;
            (formations.length ? formations : [{}]).forEach(f => addFormationRow(f));
        })
        .catch(() => {});
    };

    // ── Expériences ───────────────────────────────────────────────────────────
    function makeExperienceRow(row, idx) {
        const tr = document.createElement('tr');
        tr.className = 'border-t border-slate-200';
        tr.innerHTML = `
            <td class="p-2"><input type="text" name="identification[experiences][${idx}][periode]" value="${escapeHtml(row.periode ?? '')}" class="ent-input"></td>
            <td class="p-2"><input type="text" name="identification[experiences][${idx}][poste]" value="${escapeHtml(row.poste ?? '')}" class="ent-input"></td>
            <td class="p-2"><input type="text" name="identification[experiences][${idx}][observations]" value="${escapeHtml(row.observations ?? '')}" class="ent-input"></td>
            <td class="p-2"><button type="button" class="ent-btn ent-btn-soft" data-rm>Supprimer</button></td>
        `;
        tr.querySelector('[data-rm]').addEventListener('click', () => {
            tr.remove();
            if (!experiencesRows.children.length) addExperienceRow({});
        });
        return tr;
    }
    function addExperienceRow(row) {
        if (!experiencesRows) return;
        experiencesRows.appendChild(makeExperienceRow(row || {}, experienceIndexCounter));
        experienceIndexCounter++;
    }

    if (addFormationRowBtn) addFormationRowBtn.addEventListener('click', () => addFormationRow({}));
    if (addExperienceRowBtn) addExperienceRowBtn.addEventListener('click', () => addExperienceRow({}));

    // Recalcul live
    document.addEventListener('input', e => {
        if (e.target.matches('input[name^="objective_criteres"][name$="[note]"], input[name^="subjective_criteres"][name$="[note]"], .eval-note-directe-input')) {
            updateScoreSummary();
        }
    });

    // ── Initialisation ────────────────────────────────────────────────────────
    (function initFormations() {
        const hasOld = Array.isArray(oldFormations) && oldFormations.some(f => f?.libelle && String(f.libelle).trim());
        if (hasOld) { oldFormations.forEach(r => addFormationRow(r || {})); }
        else if (prefilledAgentId) { window.sgpFillFormations(prefilledAgentId); }
        else { addFormationRow({}); }
    })();
    (Array.isArray(oldExperiences) && oldExperiences.length ? oldExperiences : [{}]).forEach(r => addExperienceRow(r));
    renderSubjectiveCriteria(Array.isArray(subjectiveTemplates) ? subjectiveTemplates : []);
    if (Array.isArray(oldObjectiveCriteria) && oldObjectiveCriteria.length) {
        oldObjectiveCriteria.forEach((c, i) => {
            objectiveContainer.appendChild(renderCriterion('objective_criteres', c, i, { titleReadonly: true, allowRemoveCriterion: true }));
            objectiveIndexCounter++;
        });
        // Re-render les choix de la fiche pour marquer comme disabled les objectifs déjà ajoutés
        if (typeof renderObjectiveChoices === 'function') renderObjectiveChoices();
    }
    updateScoreSummary();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-remplissage depuis la liste d'agents (si présente)
    const agentsData = JSON.parse(document.getElementById('eval-agents-data')?.textContent || '[]');
    if (agentsData.length) {
        const fieldNomPrenom    = document.getElementById('identification_nom_prenom');
        const fieldEmploi       = document.getElementById('identification_emploi');
        const fieldDirection    = document.getElementById('identification_direction');
        const fieldDirectionSvc = document.getElementById('identification_direction_service');
        const fieldSignature    = document.getElementById('signature_evalue_nom');

        function fillIdent(data) {
            if (!data) return;
            if (fieldNomPrenom)    fieldNomPrenom.value    = data.nom_prenom        ?? '';
            if (fieldEmploi)       fieldEmploi.value       = data.emploi            ?? '';
            if (fieldDirection)    fieldDirection.value    = data.entite_nom        ?? '';
            if (fieldDirectionSvc) fieldDirectionSvc.value = data.direction_service ?? '';
            const fm = document.getElementById('identification_matricule');
            if (fm) fm.value = data.matricule ?? '';
            const fd = document.getElementById('identification_date_prise_fonction');
            if (fd) fd.value = data.date_prise_fonction ?? '';
            if (fieldSignature && (!fieldSignature.value || fieldSignature.dataset.autoFilled)) {
                fieldSignature.value = data.nom_prenom ?? '';
                fieldSignature.dataset.autoFilled = '1';
            }
        }

        const selAgent       = document.querySelector('select[name="agent_id"]');
        const objectivesUrl  = '{{ route("chef.evaluations.objectives-for-agent") }}';

        function fetchAndReloadObjectives(agentId) {
            if (!agentId || !window.sgpReloadObjectives) return;
            fetch(`${objectivesUrl}?agent_id=${agentId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => window.sgpReloadObjectives(data))
                .catch(() => {});
        }

        if (selAgent) {
            selAgent.addEventListener('change', function () {
                const id = parseInt(this.value, 10);
                fillIdent(agentsData.find(a => a.id === id) || null);
                if (id && window.sgpFillFormations) window.sgpFillFormations(id);
                fetchAndReloadObjectives(id);
            });

            // Remplissage immédiat si agent pré-sélectionné via ?agent_id=
            const prefilledId = JSON.parse(document.getElementById('eval-prefilled-agent')?.textContent || 'null');
            if (prefilledId) {
                const found = agentsData.find(a => a.id === parseInt(prefilledId, 10));
                if (found) fillIdent(found);
                // Recharger les fiches uniquement si objectiveOptions est vide (pas de pré-chargement serveur)
                const hasServerOptions = JSON.parse(document.getElementById('eval-objective-options')?.textContent || '[]').length > 0;
                if (!hasServerOptions) fetchAndReloadObjectives(parseInt(prefilledId, 10));
            }
        }
    }

    // Auto-remplissage depuis les listes service/agence/caisse (contexte directeur)
    const servicesData = JSON.parse(document.getElementById('eval-services-data')?.textContent || '[]');
    const agencesData  = JSON.parse(document.getElementById('eval-agences-data')?.textContent  || '[]');
    const caissesData  = JSON.parse(document.getElementById('eval-caisses-data')?.textContent  || '[]');

    if (servicesData.length || agencesData.length || caissesData.length) {
        function fillServiceIdent(data) {
            if (!data) return;
            const f = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
            f('identification_nom_prenom',             data.nom_prenom);
            f('identification_emploi',                 data.emploi);
            f('identification_direction',              data.entite_nom);
            f('identification_direction_service',      data.direction_service);
            f('identification_matricule',              data.matricule);
            f('identification_date_prise_fonction',    data.date_prise_fonction);
            const sig = document.getElementById('signature_evalue_nom');
            if (sig && (!sig.value || sig.dataset.autoFilled)) {
                sig.value = data.nom_prenom ?? '';
                sig.dataset.autoFilled = '1';
            }
            // Charger les formations du chef/directeur si agent_id disponible
            if (data.agent_id && window.sgpFillFormations) {
                window.sgpFillFormations(data.agent_id);
            }
        }
        const entityObjectivesUrl = JSON.parse(document.getElementById('eval-entity-objectives-url')?.textContent || 'null');

        function fetchEntityObjectives(paramName, id) {
            if (!id || !entityObjectivesUrl || !window.sgpReloadObjectives) return;
            fetch(entityObjectivesUrl + '?' + paramName + '=' + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => window.sgpReloadObjectives(data))
                .catch(() => {});
        }

        function bindSelect(name, dataset, paramName) {
            const sel = document.querySelector('select[name="' + name + '"]');
            if (!sel || !dataset.length) return;
            sel.addEventListener('change', function () {
                const id = parseInt(this.value, 10);
                fillServiceIdent(id ? (dataset.find(d => d.id === id) || null) : null);
                if (id) fetchEntityObjectives(paramName, id);
            });
        }
        bindSelect('service_id', servicesData, 'service_id');
        bindSelect('agence_id',  agencesData,  'agence_id');
        bindSelect('caisse_id',  caissesData,  'caisse_id');

        // Remplissage immédiat si entité pré-sélectionnée (venant d'une page de subordonné)
        const preCaisse  = JSON.parse(document.getElementById('eval-prefilled-caisse')?.textContent     || 'null');
        const preService = JSON.parse(document.getElementById('eval-prefilled-service-dt')?.textContent || 'null');
        const preAgence  = JSON.parse(document.getElementById('eval-prefilled-agence')?.textContent     || 'null');
        if (preCaisse)  fillServiceIdent(caissesData.find(d => d.id === parseInt(preCaisse, 10))  || null);
        if (preService) fillServiceIdent(servicesData.find(d => d.id === parseInt(preService, 10)) || null);
        if (preAgence)  fillServiceIdent(agencesData.find(d => d.id === parseInt(preAgence, 10))  || null);
    }

    // Dates de signature + date d'évaluation
    function todayISO() { return new Date().toISOString().slice(0, 10); }
    ['date_signature_evalue', 'date_signature_evaluateur'].forEach(id => {
        const el = document.getElementById(id);
        if (el && !el.value) el.value = todayISO();
    });
    const nomIdent = document.getElementById('identification_nom_prenom');
    const nomSig   = document.getElementById('signature_evalue_nom');
    if (nomIdent && nomSig && nomIdent.value && !nomSig.value) {
        nomSig.value = nomIdent.value;
        nomSig.dataset.autoFilled = '1';
    }
    const dateEval = document.getElementById('identification_date_evaluation');
    if (dateEval && !dateEval.value) {
        const d = new Date();
        dateEval.value = String(d.getDate()).padStart(2,'0') + '/'
            + String(d.getMonth() + 1).padStart(2,'0') + '/'
            + d.getFullYear();
    }
});
</script>
