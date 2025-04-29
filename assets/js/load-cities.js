import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.default.min.css';

document.addEventListener("DOMContentLoaded", () => {
    const stateElement = document.getElementById("state");
    const cityElement  = document.getElementById("city");
    const errorElement = document.getElementById("error-message");

    const stateSelect = new TomSelect(stateElement, {
        create: false,
        sortField: { field: 'text', direction: 'asc' },
        placeholder: stateElement.dataset.placeholder || "Selecione um estado",
        allowEmptyOption: false
    });

    const citySelect = new TomSelect(cityElement, {
        create: false,
        sortField: { field: 'text', direction: 'asc' },
        placeholder: cityElement.dataset.placeholder || "Selecione uma cidade",
        allowEmptyOption: false
    });

    const fetchData = url =>
        fetch(url)
            .then(res => res.ok ? res.json() : [])
            .catch(() => []);

    const showError = msg => {
        if (errorElement) {
            errorElement.textContent = msg;
            errorElement.classList.remove("d-none");
        }
    };
    const hideError = () => {
        if (errorElement) {
            errorElement.textContent = "";
            errorElement.classList.add("d-none");
        }
    };

    const clearCities = () => {
        citySelect.clearOptions();
        citySelect.clear(true);
    };

    stateSelect.on("change", async value => {
        clearCities();
        if (!value) return;
        const cities = await fetchData(`/api/states/${encodeURIComponent(value)}/cities`);
        cities.forEach(c => {
            citySelect.addOption({ value: c.id, text: c.name });
        });
        citySelect.refreshOptions(false);
    });

    citySelect.on("change", async value => {
        hideError();
        if (!value) return;
        const name = citySelect.options[value]?.text?.trim();
        if (!name) return;

        const { exists } = await fetchData(
            `/organizations/check-duplicate?name=${encodeURIComponent(name)}&cityId=${encodeURIComponent(value)}`
        );

        if (exists) {
            showError(
                `Este município já foi credenciado. Entre em contato com ${window.SNP_EMAIL} para mais informações.`
            );
            citySelect.clear(true);
        }
    });

    const initialState = stateSelect.getValue();
    if (initialState) {
        stateSelect.fire("change", initialState);
    }
});
