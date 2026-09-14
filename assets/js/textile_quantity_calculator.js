(function () {
    'use strict';

    function isQuantityEditor(input) {
        if (!input) return false;
        var container = input.closest('.editable-container');
        if (!container) return false;

        var editable = container.previousElementSibling;
        if (editable && editable.classList && editable.classList.contains('edit-quantity')) {
            return true;
        }

        return !!document.querySelector('.edit-quantity.editable-open');
    }

    function hasMathOperators(value) {
        return /[+\-*/()]/.test(value);
    }

    function normalizeExpression(value) {
        return String(value || '')
            .replace(/,/g, '.')
            .replace(/\s+/g, '');
    }

    function calculateExpression(value) {
        var expression = normalizeExpression(value);

        if (!expression || !hasMathOperators(expression)) {
            return null;
        }

        if (!/^[0-9.+\-*/()]+$/.test(expression)) {
            throw new Error('Expresión no válida');
        }

        // Only numbers and basic arithmetic operators are allowed by the regex above.
        var result = Function('"use strict"; return (' + expression + ');')();

        if (typeof result !== 'number' || !isFinite(result)) {
            throw new Error('Resultado no válido');
        }

        return Math.round((result + Number.EPSILON) * 1000000) / 1000000;
    }

    function markInvalid(input) {
        input.style.borderColor = '#d9534f';
        input.setAttribute('title', 'Operación inválida. Usa números, +, -, *, / y paréntesis.');
        setTimeout(function () {
            input.style.borderColor = '';
        }, 1200);
    }

    document.addEventListener('keydown', function (event) {
        var input = event.target;

        if (!(input instanceof HTMLInputElement) || !isQuantityEditor(input)) {
            return;
        }

        if (event.key === 'Escape') {
            return;
        }

        if (event.key !== 'Enter') {
            return;
        }

        try {
            var result = calculateExpression(input.value);
            if (result !== null) {
                input.value = String(result);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        } catch (error) {
            event.preventDefault();
            event.stopImmediatePropagation();
            markInvalid(input);
        }
    }, true);

    document.addEventListener('input', function (event) {
        var input = event.target;
        if (!(input instanceof HTMLInputElement) || !isQuantityEditor(input)) {
            return;
        }

        input.setAttribute('inputmode', 'decimal');
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('title', 'Puedes calcular aquí: 12.5 + 8.3 + 4.7. Presiona Enter para guardar.');
    }, true);
})();
