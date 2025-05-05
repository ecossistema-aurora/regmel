import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.default.min.css';

document.addEventListener('DOMContentLoaded', () => {
    const regionElement = document.getElementById('region-filter');
    const stateElement = document.getElementById('state-filter');
    const typeElement = document.getElementById('type-filter');

    let stateSelect;

    if (regionElement) {
        regionElement.classList.remove('form-select');
        const regionSelect = new TomSelect(regionElement, {
            create: false,
            placeholder: regionElement.dataset.placeholder || 'Selecione',
            allowEmptyOption: true,
            sortField: { field: 'text', direction: 'asc' },
        });

        regionSelect.on('change', async value => {
            if (!stateElement) return;

            stateSelect.clearOptions();
            stateSelect.clear(true);

            if (!value) return;

            const states = await fetch(`/api/regions/${encodeURIComponent(value)}/states`)
                .then(res => res.ok ? res.json() : [])
                .catch(() => []);

            states.forEach(state => {
                stateSelect.addOption({ value: state.acronym, text: state.name });
            });
            stateSelect.refreshOptions(false);

            regionElement.form.submit();
        });
    }

    if (stateElement) {
        stateElement.classList.remove('form-select');
        stateSelect = new TomSelect(stateElement, {
            create: false,
            placeholder: stateElement.dataset.placeholder || 'Selecione',
            allowEmptyOption: true,
            sortField: { field: 'text', direction: 'asc' },
        });

        stateSelect.on('change', () => {
            stateElement.form.submit();
        });
    }

    if (typeElement) {
        typeElement.classList.remove('form-select');
        new TomSelect(typeElement, {
            create: false,
            placeholder: typeElement.dataset.placeholder || 'Selecione',
            allowEmptyOption: true,
            sortField: { field: 'text', direction: 'asc' },
        }).on('change', () => {
            typeElement.form.submit();
        });
    }
});
