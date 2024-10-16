document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const loginForm = document.getElementById('loginForm');
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword')
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            alert('Registration successful!');
            registerForm.reset();
        }

    });

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            alert('Login successful!');
            loginForm.reset();
        }

    });

    

    function validateForm() {
        let isValid = true;

        if (firstNameInput.value.trim() === '' ) {
            firstNameError.textContent = 'First name is required.';
            isValid = false;
        } else {
            firstNameError.textContent = '';
        }

        if (!validateEmail(emailInput.value)) {
            emailError.textContent = 'Please enter a valid email address. ';
            isValid = false;
        } else {
            emailError.textContent = '';
        }

        if (!validatePassword(passwordInput.value)) {
            passwordError.textContent = 'Password must be at least 8 characters long, conatin at leat one uppercase letter, three digit, and one special character.';
            isValid = false;
        } else {
            passwordError.textCont = '';
        }

        if (passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordError.textContent = 'Passwords do not match.';
            isValid = false;
        } else {
            confirmPasswordError.textContent = '';
        }
        return isValid;
    }

    function validateEmail(email) {
        const re = /^[^\s@] +@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }

    function validatePassword(password) {
        const re = /^(?=.*[A-Z])(?=.*\d.*\d.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
        return re.test(password);
    }

});