import { trans } from '@symfony/ux-translator';

let actionToConfirm = '';

export function openDocumentModal(organizationName, organizationId, filePath) {
    const title = trans('document');
    const displayDocument = trans('display_document');
    const clickPdf = trans('click_pdf');

    document.getElementById('modalDocumentTitle').innerText = `${title} - ${organizationName}`;
    document.getElementById('modalDocumentBody').innerHTML = `
        <object style="min-height: 600px;" data="${filePath}" type="application/pdf" width="100%" height="100%">
            <p>${displayDocument} <a href="${filePath}" target="_blank">${clickPdf}</a></p>
        </object>
    `;
    document.getElementById('organizationId').value = organizationId;
}

export function openConfirmAction(action) {
    actionToConfirm = action;

    const confirmBody = document.getElementById('confirmActionModalBody');

    if (action === 'aprovar') {
        confirmBody.innerText = trans('confirm_approve');
    } else if (action === 'recusar') {
        confirmBody.innerText = trans('confirm_reject');
    }

    const confirmModal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
    confirmModal.show();

    document.getElementById('confirmActionButton').onclick = function() {
        executeAction();
        confirmModal.hide();
    };
}

function executeAction() {
    if (actionToConfirm === 'aprovar') {
        console.log(trans('document_acepted'));
    } else if (actionToConfirm === 'recusar') {
        console.log(trans('document_recused'));
    }
}
