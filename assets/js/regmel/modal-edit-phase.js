const editModal = document.getElementById('editPhaseModal');
editModal.addEventListener('show.bs.modal', event => {
    const btn = event.relatedTarget;
    const form = document.getElementById('editPhaseForm');

    form.action = btn.getAttribute('data-route');

    form.querySelector('#editPhaseName').value = btn.getAttribute('data-name');
    form.querySelector('#editPhaseDescription').value = btn.getAttribute('data-description');
    form.querySelector('#editStartDate').value = btn.getAttribute('data-start-date');
    form.querySelector('#editEndDate').value = btn.getAttribute('data-end-date');
    form.querySelector('#editStatus').value = btn.getAttribute('data-status');
});