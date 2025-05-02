import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.default.min.css';

document.addEventListener('DOMContentLoaded', () => {
    const regionElement = document.getElementById('region-filter');
    const stateElement  = document.getElementById('state-filter');
    if (!regionElement || !stateElement) return;

    regionElement.classList.remove('form-select');
    stateElement .classList.remove('form-select');

    const regionSelect = new TomSelect(regionElement, {
        create: false,
        placeholder: regionElement.dataset.placeholder || 'Selecione',
        allowEmptyOption: true,
        sortField: { field: 'text', direction: 'asc' },
    });

    const stateSelect = new TomSelect(stateElement, {
        create: false,
        placeholder: stateElement.dataset.placeholder || 'Selecione',
        allowEmptyOption: true,
        sortField: { field: 'text', direction: 'asc' },
    });

    const fetchStatesByRegion = regionValue => {
        return fetch(`/api/regions/${encodeURIComponent(regionValue)}/states`)
            .then(res => res.ok ? res.json() : [])
            .catch(() => []);
    };

    regionSelect.on('change', async value => {
        stateSelect.clearOptions();
        stateSelect.clear(true);

        if (!value) return;

        const states = await fetchStatesByRegion(value);
        states.forEach(state => {
            stateSelect.addOption({ value: state.acronym, text: state.name });
        });
        stateSelect.refreshOptions(false);

        regionElement.form.submit();
    });

    stateSelect.on('change', () => {
        regionElement.form.submit();
    });

    const initialRegion = regionSelect.getValue();
    if (initialRegion) {
        regionSelect.fire('change', initialRegion);
    }
});
