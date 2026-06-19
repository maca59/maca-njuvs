/**
 * Clear masked secret placeholders when the admin field receives focus.
 */
(function () {
    'use strict';

    document.addEventListener('focusin', function (event) {
        var input = event.target;

        if (!input || !input.matches || !input.matches('.maca-admin-secret-field[data-maca-secret-mask]')) {
            return;
        }

        input.value = '';
        input.removeAttribute('data-maca-secret-mask');
    });
})();
