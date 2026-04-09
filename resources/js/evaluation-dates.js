// Auto-fill and restrict evaluation dates for PCA evaluation creation

document.addEventListener('DOMContentLoaded', function () {
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const dateEvaluation = document.getElementById('identification_date_evaluation');

    if (!dateDebut || !dateFin || !dateEvaluation) return;

    // Set min date for dateDebut (6 months before today)
    const today = new Date();
    const minDebut = new Date(today.getFullYear(), today.getMonth() - 6, today.getDate());
    dateDebut.min = minDebut.toISOString().split('T')[0];
    dateDebut.max = today.toISOString().split('T')[0];

    // Set dateEvaluation to today and make it readonly
    dateEvaluation.value = today.toISOString().split('T')[0];
    dateEvaluation.readOnly = true;

    // When dateDebut changes, set dateFin to 6 months after dateDebut
    dateDebut.addEventListener('change', function () {
        if (dateDebut.value) {
            const debut = new Date(dateDebut.value);
            const fin = new Date(debut.getFullYear(), debut.getMonth() + 6, debut.getDate());
            dateFin.value = fin.toISOString().split('T')[0];
            dateFin.readOnly = true;
        } else {
            dateFin.value = '';
            dateFin.readOnly = false;
        }
    });

    // If dateDebut already has a value (edit/old), trigger change
    if (dateDebut.value) {
        dateDebut.dispatchEvent(new Event('change'));
    }
});
