/**
 * Frontend View Script for Countdown
 */
document.addEventListener('DOMContentLoaded', function () {
    const countdowns = document.querySelectorAll('.campaignpress-countdown');

    countdowns.forEach(container => {
        const targetDateStr = container.getAttribute('data-date');
        if (!targetDateStr) return;

        const targetDate = new Date(targetDateStr).getTime();
        const daysEl = container.querySelector('[data-unit="days"]');
        const hoursEl = container.querySelector('[data-unit="hours"]');
        const minutesEl = container.querySelector('[data-unit="minutes"]');
        const secondsEl = container.querySelector('[data-unit="seconds"]');

        function updateTimer() {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                // Expired
                clearInterval(interval);
                container.innerHTML = '<p class="campaignpress-countdown-expired">Election Day has arrived!</p>';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            if (daysEl) daysEl.innerText = days;
            if (hoursEl) hoursEl.innerText = hours.toString().padStart(2, '0');
            if (minutesEl) minutesEl.innerText = minutes.toString().padStart(2, '0');
            if (secondsEl) secondsEl.innerText = seconds.toString().padStart(2, '0');
        }

        const interval = setInterval(updateTimer, 1000);
        updateTimer(); // Initial call
    });
});
