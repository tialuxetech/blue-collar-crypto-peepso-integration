document.addEventListener('click', function (e) {

    // Open modal
    if (e.target.matches('.bcc-open-modal')) {
        const modalId = e.target.dataset.modal;
        const modal = document.getElementById(modalId);

        if (modal) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        }
    }

    // Close modal
    if (
        e.target.matches('.bcc-modal-close') ||
        e.target.matches('.bcc-modal-overlay')
    ) {
        const modal = e.target.closest('.bcc-modal');

        if (modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        }
    }

});
