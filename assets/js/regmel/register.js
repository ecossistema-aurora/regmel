import "../../app.js";
import "../mask.js";

import {
    trans,
    VIEW_AUTHENTICATION_ERROR_FIRST_NAME_LENGTH,
    VIEW_AUTHENTICATION_ERROR_INVALID_EMAIL,
    VIEW_AUTHENTICATION_ERROR_INVALID_PASSWORD,
    VIEW_AUTHENTICATION_ERROR_LAST_NAME_LENGTH,
    VIEW_AUTHENTICATION_ERROR_PASSWORD_MISMATCH,
    VIEW_AUTHENTICATION_ERROR_CPF_INVALID,
    VIEW_AUTHENTICATION_ERROR_PHONE_INVALID,
    VIEW_AUTHENTICATION_ERROR_EMAIL_IN_USE
} from "../../translator.js";

document.addEventListener('DOMContentLoaded', function () {
    const FORM = document.querySelector('form');
    const ERROR_MESSAGE_ELEMENT = document.getElementById('error-message');

    const inputs = {
        firstName: document.querySelector('input[name="firstname"]'),
        lastName: document.querySelector('input[name="lastname"]'),
        cpf: document.querySelector('input[name="cpf"]'),
        phone: document.querySelector('input[name="phone"]'),
        email: document.querySelector('input[name="email"]'),
        password: document.querySelector('input[name="password"]'),
        confirmPassword: document.querySelector('input[name="confirm_password"]'),
    };

    const userEmail = document.querySelector('input[name="userEmail"]');
    const userPhone = document.querySelector('input[name="userPhone"]');

    if (userEmail) {
        inputs.userEmail = userEmail;
    }
    if (userPhone) {
        inputs.userPhone = userPhone;
    }

    const progressBar = document.querySelector('#passwordStrength .progress-bar');

    async function checkEmailExists(email) {
        try {
            const response = await fetch(`/api/users/exists?email=${encodeURIComponent(email)}`);
            const data = await response.json();
            return data.exists;
        } catch (error) {
            console.error('erro ao verificar o email:', error);
            return false;
        }
    }

    async function validateFields() {
        const firstName = inputs.firstName.value.trim();
        const lastName = inputs.lastName.value.trim();
        const cpf = inputs.cpf.value.trim();
        const phone = inputs.phone?.value.trim() || '';
        const email = inputs.email.value.trim();
        const password = inputs.password.value.trim();
        const confirmPassword = inputs.confirmPassword.value.trim();
        const userEmail = inputs.userEmail ? inputs.userEmail.value.trim() : '';
        const userPhone = inputs.userPhone ? inputs.userPhone.value.trim() : '';
        let errorMessage = '';

        Object.values(inputs).forEach(input => {
            if (input) {
                input.classList.remove('border-danger');
            }
        });

        const validations = [
            {
                valid: () => validateName(firstName),
                input: inputs.firstName,
                message: trans(VIEW_AUTHENTICATION_ERROR_FIRST_NAME_LENGTH)
            },
            {
                valid: () => validateName(lastName),
                input: inputs.lastName,
                message: trans(VIEW_AUTHENTICATION_ERROR_LAST_NAME_LENGTH)
            },
            {
                valid: () => validateCpf(cpf),
                input: inputs.cpf,
                message: trans(VIEW_AUTHENTICATION_ERROR_CPF_INVALID)
            },
            ...(inputs.phone ? [{
                valid: () => validatePhone(phone),
                input: inputs.phone,
                message: trans(VIEW_AUTHENTICATION_ERROR_PHONE_INVALID)
            }] : []),
            {
                valid: () => validateEmail(email),
                input: inputs.email,
                message: trans(VIEW_AUTHENTICATION_ERROR_INVALID_EMAIL)
            },
            {
                valid: () => validatePassword(password),
                input: inputs.password,
                message: trans(VIEW_AUTHENTICATION_ERROR_INVALID_PASSWORD)
            },
            {
                valid: () => validateConfirmPassword(password, confirmPassword),
                input: inputs.confirmPassword,
                message: trans(VIEW_AUTHENTICATION_ERROR_PASSWORD_MISMATCH)
            },
            {
                valid: () => calculatePasswordStrength(password) === 5,
                input: inputs.password,
                message: trans(VIEW_AUTHENTICATION_ERROR_INVALID_PASSWORD)
            },
        ];

        if (inputs.userPhone) {
            validations.push({
                valid: () => validatePhone(userPhone),
                input: inputs.userPhone,
                message: trans(VIEW_AUTHENTICATION_ERROR_PHONE_INVALID)
            });
        }

        for (const rule of validations) {
            const isValid = await rule.valid();
            if (!isValid) {
                errorMessage = rule.message;
                if (rule.input) {
                    rule.input.classList.add('border-danger');
                }
                break;
            }
        }

        if (errorMessage) {
            ERROR_MESSAGE_ELEMENT.textContent = errorMessage;
            ERROR_MESSAGE_ELEMENT.classList.remove('d-none');
            return false;
        } else {
            ERROR_MESSAGE_ELEMENT.classList.add('d-none');
            return true;
        }
    }

    async function validateEmailAvailability() {
        const email = inputs.userEmail.value.trim();
        if (!validateEmail(email)) {
            inputs.userEmail.classList.add('border-danger');
            ERROR_MESSAGE_ELEMENT.textContent = trans(VIEW_AUTHENTICATION_ERROR_INVALID_EMAIL);
            ERROR_MESSAGE_ELEMENT.classList.remove('d-none');
            return false;
        }

        const exists = await checkEmailExists(email);
        if (exists) {
            inputs.userEmail.classList.add('border-danger');
            ERROR_MESSAGE_ELEMENT.textContent = trans(VIEW_AUTHENTICATION_ERROR_EMAIL_IN_USE);
            ERROR_MESSAGE_ELEMENT.classList.remove('d-none');
            return false;
        }

        inputs.userEmail.classList.remove('border-danger');
        ERROR_MESSAGE_ELEMENT.classList.add('d-none');
        return true;
    }

    Object.entries(inputs).forEach(([key, input]) => {
        if (!input) return;

        if (key === 'userEmail') {
            input.addEventListener('blur', validateEmailAvailability);
        } else {
            input.addEventListener('input', validateFields);
        }
    });

    inputs.password.addEventListener('input', function() {
        updatePasswordStrength(inputs.password.value);
    });

    FORM.addEventListener('submit', async function (event) {
        const isFieldsValid = await validateFields();
        const isEmailAvailable = inputs.userEmail ? await validateEmailAvailability() : true;
        const isValid = isFieldsValid && isEmailAvailable;
        if (!isValid) {
            event.preventDefault();
        }
    });

    function updatePasswordStrength(password) {
        const progressBar = document.getElementById('progressBar');
        const strengthMessage = document.getElementById('strengthMessage');

        if (password.length === 0) {
            progressBar.classList.add('d-none');
            strengthMessage.textContent = '';
            return;
        }

        progressBar.classList.remove('d-none');

        const strength = calculatePasswordStrength(password);

        const strengthPercentage = (strength / 5) * 100;

        progressBar.style.width = strengthPercentage + '%';

        if (strengthPercentage <= 40) {
            progressBar.classList.add('bg-danger');
            progressBar.classList.remove('bg-warning', 'bg-success');
            strengthMessage.textContent = 'Senha fraca';
        } else if (strengthPercentage <= 80) {
            progressBar.classList.add('bg-warning');
            progressBar.classList.remove('bg-danger', 'bg-success');
            strengthMessage.textContent = 'Senha média';
        } else {
            progressBar.classList.add('bg-success');
            progressBar.classList.remove('bg-danger', 'bg-warning');
            strengthMessage.textContent = 'Senha forte';
        }
    }
});

function calculatePasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/\d/.test(password)) strength += 1;
    if (/[\W_]/.test(password)) strength += 1;
    return strength;
}

function validateName(name) {
    return name.length >= 2 && name.length <= 50;
}

function validateEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email) && email.length <= 100;
}

function validateCpf(cpf) {
    const cpfPattern = /^\d{3}\.\d{3}\.\d{3}-\d{2}$/;
    return cpfPattern.test(cpf) && cpf.length === 14;
}

function validatePhone(phone) {
    const phonePattern = /^\(\d{2}\)\s\d{1}\s\d{4}-\d{4}$/;
    return phonePattern.test(phone) && phone.length <= 16;
}

function validatePassword(password) {
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
    return passwordPattern.test(password) && password.length <= 255;
}

function validateConfirmPassword(password, confirmPassword) {
    return password === confirmPassword;
}

function validateCnpj(cnpj) {
    const cnpjPattern = /^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/;
    return cnpjPattern.test(cnpj) && cnpj.length === 18;
}
