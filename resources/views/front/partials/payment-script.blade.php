{{--
    Tient à jour l'équivalent en gourdes annoncé sous MonCash.
    Attend un champ de montant dont l'identifiant est passé en paramètre.
--}}
<script>
(function () {
    var field = document.getElementById(@json($amountFieldId ?? 'amount'));
    if (!field) { return; }

    var targets = document.querySelectorAll('[data-htg]');
    if (!targets.length) { return; }

    function refresh() {
        var value = parseFloat(field.value || '0');

        targets.forEach(function (el) {
            var htg = value * parseFloat(el.dataset.rate || '0');
            el.textContent = 'faktire ' + htg.toLocaleString('fr-FR', {
                minimumFractionDigits: 0, maximumFractionDigits: 0
            }) + ' HTG';
        });
    }

    field.addEventListener('input', refresh);
    field.addEventListener('change', refresh);
    refresh();
})();
</script>
