document.addEventListener('DOMContentLoaded', () => {
    const MAX_MB = window.MAX_FILE_SIZE_MB || 5;
    const MAX_SIZE = MAX_MB * 1024 * 1024;

    document.querySelectorAll('form').forEach(form => {
        const fileInputs = form.querySelectorAll('input[type="file"]');
        if (!fileInputs.length) return;

        let errorMsg = form.querySelector('.file-validation-error');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'alert alert-danger mt-3 file-validation-error';
            errorMsg.style.display = 'none';
            form.appendChild(errorMsg);
        }
        const submitBtn = form.querySelector('button[type="submit"],input[type="submit"]');

        function validate() {
            let total = 0, error = '', allowed, ext;
            fileInputs.forEach(input => {
                allowed = (input.dataset.allowed || '').split(',').map(e => e.trim().toLowerCase());
                Array.from(input.files).forEach(file => {
                    total += file.size;
                    ext = file.name.split('.').pop().toLowerCase();
                    if (allowed[0] && !allowed.includes(ext)) {
                        error = `Tipo de arquivo inválido em "${input.dataset.label}". Permitidos: ${allowed.join(', ').toUpperCase()}`;
                    }
                });
            });
            if (!error && total > MAX_SIZE) {
                error = `O tamanho total dos arquivos enviados não pode ultrapassar ${MAX_MB}MB.`;
            }
            errorMsg.textContent = error;
            errorMsg.style.display = error ? 'block' : 'none';
            if (submitBtn) submitBtn.disabled = !!error;
            return !error;
        }

        fileInputs.forEach(input => input.addEventListener('change', validate));
        form.addEventListener('submit', e => { if (!validate()) e.preventDefault(); });
    });
});