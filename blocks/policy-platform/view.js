/**
 * Policy Platform Interactive Logic
 */
document.addEventListener('DOMContentLoaded', function () {
    // Accordions
    const headers = document.querySelectorAll('.cp-policy-header');

    headers.forEach(header => {
        header.addEventListener('click', () => {
            const isExpanded = header.getAttribute('aria-expanded') === 'true';
            const contentId = header.getAttribute('aria-controls');
            const content = document.getElementById(contentId);

            header.setAttribute('aria-expanded', !isExpanded);
            content.hidden = isExpanded;
        });
    });

    // Vote Buttons
    const voteBtns = document.querySelectorAll('.cp-vote-btn');
    voteBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            if (this.disabled) return;

            const countSpan = this.querySelector('.cp-vote-count');
            let count = parseInt(countSpan.innerText.replace(/,/g, ''));
            count++;
            countSpan.innerText = count.toLocaleString();

            this.classList.add('voted');
            this.style.color = 'var(--wp--preset--color--primary)';
            this.style.borderColor = 'var(--wp--preset--color--primary)';
            this.disabled = true;

            // In real app: AJAX call to save vote
        });
    });
});
