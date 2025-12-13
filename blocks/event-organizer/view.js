/**
 * Event Organizer Interactive Logic
 */
document.addEventListener('DOMContentLoaded', function () {
    const rsvpBtns = document.querySelectorAll('.cp-rsvp-btn');

    rsvpBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const originalText = this.innerText;
            this.innerText = '✓ Registered';
            this.classList.add('registered');
            this.disabled = true;
            this.style.background = 'var(--wp--preset--color--success, #28a745)';
            this.style.color = '#fff';
            this.style.borderColor = 'transparent';

            // In a real app, this would send an AJAX request to format-rsvp.php
            console.log('RSVP logged for event ID:', this.dataset.eventId);
        });
    });
});
