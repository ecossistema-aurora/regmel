document.addEventListener('DOMContentLoaded', () => {
    const registrationForm = document.querySelector('#registerCityForm, #registerCompanyForm');
    const submitButton = document.querySelector('#submitBtn, button[type="submit"].btn-primary');

    const policyCheckboxes = {
        terms: document.getElementById('acceptTerms'),
        privacy: document.getElementById('acceptPrivacy'),
        image: document.getElementById('acceptImage')
    };

    const updateSubmitButtonState = () => {
        const allPoliciesAccepted = Object.values(policyCheckboxes).every(checkbox => checkbox && checkbox.checked);
        if (submitButton) {
            submitButton.disabled = !allPoliciesAccepted;
        }
    };

    const handlePolicyButtonClick = (event, shouldAccept) => {
        const policyName = event.target.dataset.policy;
        const targetCheckbox = policyCheckboxes[policyName];

        if (targetCheckbox) {
            targetCheckbox.checked = shouldAccept;
            updateSubmitButtonState();
        }
    };

    Object.values(policyCheckboxes).forEach(checkbox => {
        if (checkbox) {
            checkbox.addEventListener('click', (event) => event.preventDefault());
        }
    });

    document.querySelectorAll('.accept-policy').forEach(button => {
        button.addEventListener('click', (event) => handlePolicyButtonClick(event, true));
    });

    document.querySelectorAll('.cancel-policy').forEach(button => {
        button.addEventListener('click', (event) => handlePolicyButtonClick(event, false));
    });

    updateSubmitButtonState();

    if (registrationForm) {
        registrationForm.addEventListener('submit', (event) => {
            const allPoliciesAccepted = Object.values(policyCheckboxes).every(checkbox => checkbox && checkbox.checked);
            if (!allPoliciesAccepted) {
                event.preventDefault();
            }
        });
    }
});
