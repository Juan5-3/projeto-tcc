/**
 * ShopBR — login.js
 * Handles:
 *   - Panel switching (Login ↔ Register) without page reload
 *   - Field clearing on panel switch
 *   - Client-side validation
 *   - Success messages
 */

(function () {
    'use strict';

    /* ────────────────────────────────────────────
       Helpers
    ──────────────────────────────────────────── */
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function isValidEmail(value) {
        return EMAIL_RE.test(value.trim());
    }

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

    function clearAllErrors(formId) {
        var form = document.getElementById(formId);
        if (!form) return;
        form.querySelectorAll('.form-control').forEach(function (el) {
            el.classList.remove('is-error');
        });
        form.querySelectorAll('.form-error').forEach(function (el) {
            el.textContent = '';
            el.classList.remove('visible');
        });
    }

    function showSuccess(successId) {
        var el = document.getElementById(successId);
        if (el) el.classList.add('visible');
    }

    function hideSuccess(successId) {
        var el = document.getElementById(successId);
        if (el) el.classList.remove('visible');
    }

    function clearForm(formId) {
        var form = document.getElementById(formId);
        if (!form) return;
        form.querySelectorAll('input').forEach(function (input) {
            input.value = '';
        });
    }

    /* ────────────────────────────────────────────
       Panel switching
    ──────────────────────────────────────────── */
    window.switchPanel = function (panelName) {
        var loginPanel    = document.getElementById('panel-login');
        var registerPanel = document.getElementById('panel-register');
        var tabLogin      = document.getElementById('tab-login');
        var tabRegister   = document.getElementById('tab-register');

        if (panelName === 'login') {
            // Activate login
            loginPanel.classList.add('active');
            registerPanel.classList.remove('active');
            tabLogin.classList.add('active');
            tabRegister.classList.remove('active');
            tabLogin.setAttribute('aria-selected', 'true');
            tabRegister.setAttribute('aria-selected', 'false');
            // Clear register form and errors
            clearForm('register-form');
            clearAllErrors('register-form');
            hideSuccess('register-success');
        } else {
            // Activate register
            registerPanel.classList.add('active');
            loginPanel.classList.remove('active');
            tabRegister.classList.add('active');
            tabLogin.classList.remove('active');
            tabRegister.setAttribute('aria-selected', 'true');
            tabLogin.setAttribute('aria-selected', 'false');
            // Clear login form and errors
            clearForm('login-form');
            clearAllErrors('login-form');
            hideSuccess('login-success');
        }
    };

    /* ────────────────────────────────────────────
       Login form validation
    ──────────────────────────────────────────── */
    function validateLoginForm() {
        var valid    = true;
        var email    = document.getElementById('login-email');
        var password = document.getElementById('login-password');

        // Clear previous errors
        clearError('login-email', 'login-email-error');
        clearError('login-password', 'login-password-error');
        hideSuccess('login-success');

        // Email: required
        if (!email || !email.value.trim()) {
            showError('login-email', 'login-email-error', 'O e-mail é obrigatório.');
            valid = false;
        } else if (!isValidEmail(email.value)) {
            showError('login-email', 'login-email-error', 'Digite um e-mail válido (ex: nome@dominio.com).');
            valid = false;
        }

        // Password: required
        if (!password || !password.value) {
            showError('login-password', 'login-password-error', 'A senha é obrigatória.');
            valid = false;
        }

        return valid;
    }

    /* ────────────────────────────────────────────
       Register form validation
    ──────────────────────────────────────────── */
    function validateRegisterForm() {
        var valid    = true;
        var name     = document.getElementById('reg-name');
        var email    = document.getElementById('reg-email');
        var password = document.getElementById('reg-password');
        var confirm  = document.getElementById('reg-confirm');

        // Clear previous errors
        clearError('reg-name',     'reg-name-error');
        clearError('reg-email',    'reg-email-error');
        clearError('reg-password', 'reg-password-error');
        clearError('reg-confirm',  'reg-confirm-error');
        hideSuccess('register-success');

        // Full name: required
        if (!name || !name.value.trim()) {
            showError('reg-name', 'reg-name-error', 'O nome completo é obrigatório.');
            valid = false;
        }

        // Email: required + format
        if (!email || !email.value.trim()) {
            showError('reg-email', 'reg-email-error', 'O e-mail é obrigatório.');
            valid = false;
        } else if (!isValidEmail(email.value)) {
            showError('reg-email', 'reg-email-error', 'Digite um e-mail válido (ex: nome@dominio.com).');
            valid = false;
        }

        // Password: required
        if (!password || !password.value) {
            showError('reg-password', 'reg-password-error', 'A senha é obrigatória.');
            valid = false;
        }

        // Confirm password: required + must match
        if (!confirm || !confirm.value) {
            showError('reg-confirm', 'reg-confirm-error', 'Confirme sua senha.');
            valid = false;
        } else if (password && confirm.value !== password.value) {
            showError('reg-confirm', 'reg-confirm-error', 'As senhas não coincidem.');
            valid = false;
        }

        return valid;
    }

    /* ────────────────────────────────────────────
       Live validation — clear error on input
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
       Tab keyboard support
    ──────────────────────────────────────────── */
    function initTabKeyboard() {
        var tabs = document.querySelectorAll('.auth-tab');
        tabs.forEach(function (tab) {
            tab.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    tab.click();
                }
            });
        });
    }

    /* ────────────────────────────────────────────
       Init
    ──────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        initTabKeyboard();

        // Live clears
        attachLiveClear('login-email',    'login-email-error');
        attachLiveClear('login-password', 'login-password-error');
        attachLiveClear('reg-name',       'reg-name-error');
        attachLiveClear('reg-email',      'reg-email-error');
        attachLiveClear('reg-password',   'reg-password-error');
        attachLiveClear('reg-confirm',    'reg-confirm-error');

        // Login form submit
        var loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (validateLoginForm()) {
                    showSuccess('login-success');
                    clearForm('login-form');
                }
            });
        }

        // Register form submit
        var registerForm = document.getElementById('register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (validateRegisterForm()) {
                    showSuccess('register-success');
                    clearForm('register-form');
                }
            });
        }

        // Toggle link keyboard accessibility (anchor tags used as buttons)
        document.querySelectorAll('.auth-toggle a[role="button"]').forEach(function (el) {
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    el.click();
                }
            });
        });
    });

}());
