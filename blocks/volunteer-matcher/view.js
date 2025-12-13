/**
 * Volunteer Matcher Interactive Logic
 */
document.addEventListener('DOMContentLoaded', function () {
    const selector = document.getElementById('cp-skill-select');
    const radiusRange = document.getElementById('cp-radius-range');
    const radiusVal = document.getElementById('cp-radius-value');
    const cards = document.querySelectorAll('.cp-role-card');

    if (selector) {
        selector.addEventListener('change', function () {
            const skill = this.value;
            cards.forEach(card => {
                const cardSkills = card.getAttribute('data-skills');
                if (skill === 'all' || cardSkills.includes(skill)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    if (radiusRange && radiusVal) {
        radiusRange.addEventListener('input', function () {
            radiusVal.innerText = this.value + ' mi';
            // In a real implementation with Geolocation, this would filter based on distance
        });
    }
});
