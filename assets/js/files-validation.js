document.addEventListener('DOMContentLoaded', function () {
    const MAX_TOTAL_SIZE = 1024 * 1024;
    const form = document.getElementById('space-edit-form');
    const fileInputs = form.querySelectorAll('input[type="file"]');
    const submitButton = document.getElementById('submit-btn') || form.querySelector('input[type="submit"]');

    let errorMsg = document.createElement('div');
    errorMsg.className = 'alert alert-danger mt-3';
    errorMsg.style.display = 'none';
    form.appendChild(errorMsg);

    function isValidFileType(file, allowedTypes) {
        const ext = file.name.split('.').pop().toLowerCase();
        return allowedTypes.includes(ext);
    }

    function updateFileValidation() {
        let totalSize = 0;
        let typeError = false;
        let typeErrorMsg = '';

        fileInputs.forEach(input => {
            if (input.files.length > 0) {
                const allowed = input.dataset.allowed ? input.dataset.allowed.split(',').map(e => e.trim().toLowerCase()) : null;
                for (let file of input.files) {
                    totalSize += file.size;
                    if (allowed && !isValidFileType(file, allowed)) {
                        typeError = true;
                        typeErrorMsg = `Tipo de arquivo inválido em "${input.id}". Permitidos: ${allowed.join(', ').toUpperCase()}`;
                    }
                }
            }
        });

        if (totalSize > MAX_TOTAL_SIZE) {
            errorMsg.textContent = 'O tamanho total dos arquivos enviados não pode ultrapassar 1MB.';
            errorMsg.style.display = 'block';
            if (submitButton) submitButton.disabled = true;
        } else if (typeError) {
            errorMsg.textContent = typeErrorMsg;
            errorMsg.style.display = 'block';
            if (submitButton) submitButton.disabled = true;
        } else {
            errorMsg.style.display = 'none';
            if (submitButton) submitButton.disabled = false;
        }
    }

    fileInputs.forEach(input => {
        input.addEventListener('change', updateFileValidation);
    });

    form.addEventListener('submit', function (e) {
        let totalSize = 0;
        let typeError = false;

        fileInputs.forEach(input => {
            if (input.files.length > 0) {
                const allowed = input.dataset.allowed ? input.dataset.allowed.split(',').map(e => e.trim().toLowerCase()) : null;
                for (let file of input.files) {
                    totalSize += file.size;
                    if (allowed && !isValidFileType(file, allowed)) {
                        typeError = true;
                    }
                }
            }
        });

        if (totalSize > MAX_TOTAL_SIZE || typeError) {
            e.preventDefault();
            errorMsg.textContent = 'O envio foi bloqueado. Verifique o tamanho e o tipo dos arquivos.';
            errorMsg.style.display = 'block';
        }
    });
});