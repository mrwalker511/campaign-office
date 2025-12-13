/**
 * Donation Form Interactive Logic
 */
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.cp-donation-form');

    forms.forEach(form => {
        const tierBtns = form.querySelectorAll('.cp-donation-tier-btn:not(.cp-custom-amount-btn)');
        const customBtn = form.querySelector('.cp-custom-amount-btn');
        const customInput = form.querySelector('.cp-custom-amount-input');
        const cryptoBtn = form.querySelector('.cp-crypto-btn');
        const cryptoArea = form.querySelector('.cp-crypto-address');
        const copyBtn = form.querySelector('.cp-copy-btn');

        // Tier Selection
        tierBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                form.querySelectorAll('.cp-donation-tier-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                if (customInput) customInput.style.display = 'none';
            });
        });

        // Custom Amount
        if (customBtn) {
            customBtn.addEventListener('click', () => {
                form.querySelectorAll('.cp-donation-tier-btn').forEach(b => b.classList.remove('selected'));
                customBtn.classList.add('selected');
                if (customInput) customInput.style.display = 'block';
            });
        }

        // Crypto Toggle
        if (cryptoBtn && cryptoArea) {
            cryptoBtn.addEventListener('click', () => {
                cryptoArea.style.display = cryptoArea.style.display === 'none' ? 'block' : 'none';
            });
        }

        // Copy Address
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                const code = form.querySelector('.cp-btc-addy');
                navigator.clipboard.writeText(code.innerText).then(() => {
                    const original = copyBtn.innerText;
                    copyBtn.innerText = 'Copied!';
                    setTimeout(() => copyBtn.innerText = original, 2000);
                });
            });
        }
    });
});
