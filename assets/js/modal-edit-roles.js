function modalEditRoles(id, roles) {
    const modal = new bootstrap.Modal('#modalEditRoles');
    const form = document.querySelector('#formEditRoles');
    const link = '{id}/edit-user-roles';

    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = roles.includes(checkbox.dataset.role);
    });

    form.action = link.replace("{id}", id);

    modal.show();
}
