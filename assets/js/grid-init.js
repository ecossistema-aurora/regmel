import {
    trans,
    PREVIOUS,
    NEXT,
    SHOWING,
    RESULTS,
    OF,
    TO,
    TABLE_TYPE_A_KEYWORD,
    TABLE_NO_RECORDS_FOUND,
    TABLE_ERROR
} from "../translator.js";

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table.js-grid').forEach(table => {
        const headers = Array.from(table.querySelectorAll('thead th'))
            .map(th => th.textContent.trim())
            .filter(name => name);

        const data = Array.from(table.querySelectorAll('tbody tr'))
            .map(tr =>
                Array.from(tr.querySelectorAll('td'))
                    .map(td => td.innerHTML.trim())
            );

        const wrapper = document.createElement('div');

        const selectContainer = document.createElement('div');
        selectContainer.className = 'd-flex flex-wrap align-items-center gap-3 mb-3';

        const labelWrapper = document.createElement('div');
        labelWrapper.className = 'col-auto';

        const selectLabel = document.createElement('label');
        selectLabel.textContent = 'Itens por página:';
        selectLabel.className = 'col-form-label';
        selectLabel.setAttribute('for', 'gridLimitSelect');

        labelWrapper.appendChild(selectLabel);

        const selectWrapper = document.createElement('div');
        selectWrapper.className = 'col-auto';

        const selectLimit = document.createElement('select');
        selectLimit.id = 'gridLimitSelect';
        selectLimit.className = 'form-select form-select-sm w-auto';

        [10, 20, 30, 50, 100, 'all'].forEach(option => {
            const opt = document.createElement('option');
            opt.value = option === 'all' ? -1 : option;
            opt.textContent = option === 'all' ? 'Mostrar todos' : option;
            if (option === 50) opt.selected = true;
            selectLimit.appendChild(opt);
        });

        selectWrapper.appendChild(selectLimit);

        selectContainer.appendChild(labelWrapper);
        selectContainer.appendChild(selectWrapper);

        wrapper.appendChild(selectContainer);

        const gridContainer = document.createElement('div');
        wrapper.appendChild(gridContainer);

        table.parentNode.insertBefore(wrapper, table);

        const grid = new gridjs.Grid({
            columns: headers.map(name => ({
                name,
                sort: !['foto', 'imagem', 'ações'].includes(name.toLowerCase()),
                formatter: cell => gridjs.html(cell),
            })),
            data,
            search: true,
            pagination: Number(selectLimit.value) === -1 ? false : {
                enabled: true,
                limit: Number(selectLimit.value),
                page: 1,
                resetPageOnUpdate: true
            },
            className: {
                table: 'table table-striped table-hover',
                th: 'bg-dark text-white',
                td: 'bg-light'
            },
            language: {
                search: { placeholder: trans(TABLE_TYPE_A_KEYWORD) },
                pagination: {
                    previous: trans(PREVIOUS),
                    next: trans(NEXT),
                    showing: trans(SHOWING),
                    results: trans(RESULTS),
                    of: trans(OF),
                    to: trans(TO),
                },
                noRecordsFound: trans(TABLE_NO_RECORDS_FOUND),
                error: trans(TABLE_ERROR),
            }
        });

        grid.render(gridContainer);
        table.remove();

        selectLimit.addEventListener('change', () => {
            const newLimit = Number(selectLimit.value);

            const newPagination = newLimit === -1 ? false : {
                enabled: true,
                limit: newLimit,
                page: 1,
                resetPageOnUpdate: true
            };

            grid
                .updateConfig({
                    data,
                    pagination: newPagination
                })
                .forceRender();
        });
    });
});
