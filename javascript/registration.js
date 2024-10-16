document.addEventListener("DOMContentLoaded", function () {
  const registerForm = document.getElementById("registerForm");
  const loginForm = document.getElementById("loginForm");

  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      e.preventDefault();
      if (validateRegistrationForm()) {
        window.location.href = "journal.html";
      }
    });
  }

  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();
      if (validateLoginForm()) {
        window.location.href = "journal.html";
      }
    });
  }

  function validateRegistrationForm() {
    const firstNameInput = document.getElementById("firstName");
    const lastNameInput = document.getElementById("lastName");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirmPassword");
    const firstNameError = document.getElementById("firstNameError");
    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");
    const confirmPasswordError = document.getElementById(
      "confirmPasswordError"
    );

    let isValid = true;

    if (firstNameInput.value.trim() === "") {
      firstNameError.textContent = "First name is required.";
      isValid = false;
    } else {
      firstNameError.textContent = "";
    }

    if (!validateEmail(emailInput.value)) {
      emailError.textContent = "Please enter a valid email address.";
      isValid = false;
    } else {
      emailError.textContent = "";
    }

    if (!validatePassword(passwordInput.value)) {
      passwordError.textContent =
        "Password must be at least 8 characters long, contain at least one uppercase letter, three digits, and one special character.";
      isValid = false;
    } else {
      passwordError.textContent = "";
    }

    if (passwordInput.value !== confirmPasswordInput.value) {
      confirmPasswordError.textContent = "Passwords do not match.";
      isValid = false;
    } else {
      confirmPasswordError.textContent = "";
    }

    return isValid;
  }

  function validateLoginForm() {
    const usernameInput = document.getElementById("username");
    const passwordInput = document.getElementById("password");
    const usernameError = document.getElementById("usernameError");
    const passwordError = document.getElementById("passwordError");

    let isValid = true;

    if (usernameInput.value.trim() === "") {
      usernameError.textContent = "Username is required.";
      isValid = false;
    } else {
      usernameError.textContent = "";
    }

    if (!validatePassword(passwordInput.value)) {
      passwordError.textContent = "Invalid password.";
      isValid = false;
    } else {
      passwordError.textContent = "";
    }

    return isValid;
  }

  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
  }

  function validatePassword(password) {
    const re =
      /^(?=.*[A-Z])(?=.*\d.*\d.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
    return re.test(password);
  }
});
