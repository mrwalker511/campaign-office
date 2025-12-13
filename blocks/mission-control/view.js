/**
 * Mission Control Logic
 */
document.addEventListener('DOMContentLoaded', function () {
    // Re-using simplified countdown logic from basic block for robustness
    const dash = document.querySelector('.cp-mission-control');
    if (!dash) return;

    const targetDateStr = dash.getAttribute('data-date');
    if (!targetDateStr) return;

    const targetDate = new Date(targetDateStr).getTime();

    function updateDash() {
        const now = new Date().getTime();
        const distance = targetDate - now;

        if (distance < 0) return; // Expired

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const mins = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

        dash.querySelector('[data-unit="days"]').innerText = days;
        dash.querySelector('[data-unit="hours"]').innerText = hours;
        dash.querySelector('[data-unit="mins"]').innerText = mins;
    }

    setInterval(updateDash, 1000);
    updateDash();
});
