/**
 * ShopBR — cadastro-vendedor.js
 * Handles:
 *   - CPF/CNPJ input mask
 *   - Phone input mask
 *   - Form validation
 *   - Success message + field clear
 */

(function () {
    'use strict';

    /* ────────────────────────────────────────────
       Mask helpers
    ──────────────────────────────────────────── */

    /**
     * Apply CPF (000.000.000-00) or CNPJ (00.000.000/0000-00) mask
     * based on the number of digits typed.
     */
    function applyCpfCnpjMask(value) {
        // Keep only digits
        var digits = value.replace(/\D/g, '').slice(0, 14);

        if (digits.length <= 11) {
            // CPF: 000.000.000-00
            return digits
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3}\.\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3}\.\d{3}\.\d{3})(\d)/, '$1-$2');
        } else {
            // CNPJ: 00.000.000/0000-00
            return digits
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2}\.\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{2}\.\d{3}\.\d{3})(\d)/, '$1/$2')
                .replace(/^(\d{2}\.\d{3}\.\d{3}\/\d{4})(\d)/, '$1-$2');
        }
    }

    /**
     * Apply phone mask: (00) 00000-0000 or (00) 0000-0000
     */
    function applyPhoneMask(value) {
        var digits = value.replace(/\D/g, '').slice(0, 11);

        if (digits.length <= 10) {
            // (00) 0000-0000
            return digits
                .replace(/^(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{4})(\d{1,4})$/, '$1-$2');
        } else {
            // (00) 00000-0000
            return digits
                .replace(/^(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{5})(\d{1,4})$/, '$1-$2');
        }
    }

    /* ────────────────────────────────────────────
       Validation helpers
    ──────────────────────────────────────────── */
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function showError(fieldId, errorId, message) {
        var field = document.getElementById(fieldId);
        var error = document.getElementById(errorId);
        if (field) field.classList.add('is-error');
        if (error) {
            error.textContent = message;
            error.classList.add('visible');
        }
    }

    function clearError(fieldId, errorId) {
        var field = document.getElementById(fieldId);
        var error = document.getElementById(errorId);
        if (field) field.classList.remove('is-error');
        if (error) {
            error.textContent = '';
            error.classList.remove('visible');
        }
    }

    function clearAllErrors() {
        var form = document.getElementById('seller-form');
        if (!form) return;
        form.querySelectorAll('.form-control').forEach(function (el) {
            el.classList.remove('is-error');
        });
        form.querySelectorAll('.form-error').forEach(function (el) {
            el.textContent = '';
            el.classList.remove('visible');
        });
    }

    /* ────────────────────────────────────────────
       Full form validation
    ──────────────────────────────────────────── */
    function validateForm() {
        clearAllErrors();

        var valid     = true;
        var nameEl    = document.getElementById('seller-name');
        var emailEl   = document.getElementById('seller-email');
        var storeEl   = document.getElementById('seller-store');
        var cpfEl     = document.getElementById('seller-cpf');
        var phoneEl   = document.getElementById('seller-phone');

        // Name
        if (!nameEl || !nameEl.value.trim()) {
            showError('seller-name', 'seller-name-error', 'O nome completo é obrigatório.');
            valid = false;
        }

        // Email
        if (!emailEl || !emailEl.value.trim()) {
            showError('seller-email', 'seller-email-error', 'O e-mail é obrigatório.');
            valid = false;
        } else if (!EMAIL_RE.test(emailEl.value.trim())) {
            showError('seller-email', 'seller-email-error', 'Digite um e-mail válido (ex: nome@dominio.com).');
            valid = false;
        }

        // Store name
        if (!storeEl || !storeEl.value.trim()) {
            showError('seller-store', 'seller-store-error', 'O nome da loja é obrigatório.');
            valid = false;
        }

        // CPF/CNPJ
        if (!cpfEl || !cpfEl.value.trim()) {
            showError('seller-cpf', 'seller-cpf-error', 'O CPF ou CNPJ é obrigatório.');
            valid = false;
        } else {
            var cpfDigits = cpfEl.value.replace(/\D/g, '');
            if (cpfDigits.length !== 11 && cpfDigits.length !== 14) {
                showError('seller-cpf', 'seller-cpf-error', 'CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos.');
                valid = false;
            }
        }

        // Phone
        if (!phoneEl || !phoneEl.value.trim()) {
            showError('seller-phone', 'seller-phone-error', 'O telefone é obrigatório.');
            valid = false;
        } else {
            var phoneDigits = phoneEl.value.replace(/\D/g, '');
            if (phoneDigits.length !== 10 && phoneDigits.length !== 11) {
                showError('seller-phone', 'seller-phone-error', 'Telefone deve ter 10 dígitos (fixo) ou 11 dígitos (celular).');
                valid = false;
            }
        }

        return valid;
    }

    /* ────────────────────────────────────────────
       Clear all fields
    ──────────────────────────────────────────── */
    function clearAllFields() {
        var form = document.getElementById('seller-form');
        if (!form) return;
        form.querySelectorAll('input').forEach(function (input) {
            input.value = '';
        });
    }

    /* ────────────────────────────────────────────
       Live clear on input
    ──────────────────────────────────────────── */
    function attachLiveClear(fieldId, errorId) {
        var field = document.getElementById(fieldId);
        if (!field) return;
        field.addEventListener('input', function () {
            if (field.value.trim()) {
                clearError(fieldId, errorId);
            }
        });
    }

    /* ────────────────────────────────────────────
       Init
    ──────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        var cpfInput   = document.getElementById('seller-cpf');
        var phoneInput = document.getElementById('seller-phone');
        var form       = document.getElementById('seller-form');
        var successEl  = document.getElementById('seller-success');

        // CPF/CNPJ mask
        if (cpfInput) {
            cpfInput.addEventListener('input', function (e) {
                var cursor = this.selectionStart;
                var masked = applyCpfCnpjMask(this.value);
                this.value = masked;
                // Restore cursor (best-effort)
                if (this.setSelectionRange) {
                    var newCursor = Math.min(cursor + (masked.length - e.target.value.length), masked.length);
                    this.setSelectionRange(newCursor, newCursor);
                }
            });

            cpfInput.addEventListener('keydown', function (e) {
                // Allow backspace, delete, arrows, tab
                var allowed = [8, 9, 27, 35, 36, 37, 38, 39, 40, 46];
                if (allowed.indexOf(e.keyCode) !== -1) return;
                // Block non-numeric keys (excluding Ctrl combos)
                if (!e.ctrlKey && !e.metaKey && (e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
        }

        // Phone mask
        if (phoneInput) {
            phoneInput.addEventListener('input', function () {
                this.value = applyPhoneMask(this.value);
            });

            phoneInput.addEventListener('keydown', function (e) {
                var allowed = [8, 9, 27, 35, 36, 37, 38, 39, 40, 46];
                if (allowed.indexOf(e.keyCode) !== -1) return;
                if (!e.ctrlKey && !e.metaKey && (e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
        }

        // Live clears
        attachLiveClear('seller-name',  'seller-name-error');
        attachLiveClear('seller-email', 'seller-email-error');
        attachLiveClear('seller-store', 'seller-store-error');
        attachLiveClear('seller-cpf',   'seller-cpf-error');
        attachLiveClear('seller-phone', 'seller-phone-error');

        // Form submit
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                if (successEl) successEl.classList.remove('visible');

                if (validateForm()) {
                    if (successEl) successEl.classList.add('visible');
                    clearAllFields();
                    // Scroll success into view
                    successEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    // Scroll to first error
                    var firstError = form.querySelector('.form-error.visible');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        }
    });

}());
