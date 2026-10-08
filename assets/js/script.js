/**
 * Skillbridg — Client-side JavaScript Utilities
 * Form validation, DOM interactions, and UX enhancements.
 */

document.addEventListener('DOMContentLoaded', function () {

    // --- 1. Form Validation Handlers ---
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            let isValid = true;
            let errorMessage = '';

            if (name && name.value.trim() === '') {
                isValid = false;
                errorMessage = 'Please enter your full name.';
            } else if (email && !validateEmail(email.value.trim())) {
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            } else if (password && password.value.length < 6) {
                isValid = false;
                errorMessage = 'Password must be at least 6 characters long.';
            } else if (confirmPassword && password.value !== confirmPassword.value) {
                isValid = false;
                errorMessage = 'Passwords do not match.';
            }

            if (!isValid) {
                e.preventDefault();
                showClientAlert(errorMessage);
            }
        });
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');

            if (!email || !validateEmail(email.value.trim())) {
                e.preventDefault();
                showClientAlert('Please enter a valid email address.');
            } else if (!password || password.value.trim() === '') {
                e.preventDefault();
                showClientAlert('Please enter your password.');
            }
        });
    }

    // --- 2. Helper Functions ---
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function showClientAlert(msg) {
        let alertContainer = document.getElementById('client-alert-container');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'client-alert-container';
            const mainContent = document.querySelector('.main-content') || document.body;
            mainContent.insertBefore(alertContainer, mainContent.firstChild);
        }

        alertContainer.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <span>${msg}</span>
                <button class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
        `;
    }

    // --- 3. Confirmation Dialog for Destructive Actions ---
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            const confirmMsg = this.getAttribute('data-confirm') || 'Are you sure you want to perform this action?';
            if (!confirm(confirmMsg)) {
                e.preventDefault();
            }
        });
    });

});
