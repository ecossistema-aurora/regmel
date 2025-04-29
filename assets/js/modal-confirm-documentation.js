function confirmDecision(event, approved = true) {
    const modal = new bootstrap.Modal(event.getAttribute('data-target'), {
        backdrop: 'static',
        keyboard: false
    });

    document.querySelector('#confirmDecisionTitle').innerHTML = approved ? 'Aprovar' : 'Recusar';
    document.querySelector('#confirmDecisionQuestion').innerHTML = approved ? 'Você tem certeza que deseja aprovar o documento?' : 'Você tem certeza que deseja recusar o documento?';
    document.querySelector('#confirmDecisionButton').innerHTML = approved ? 'Aprovar' : 'Recusar';

    modal.show();
}
