/**
 * Frontend View Script for Progress Bar Animation
 */
document.addEventListener('DOMContentLoaded', function () {
    const bars = document.querySelectorAll('.campaignpress-progress-bar');

    // Intersection Observer to animate when in view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const bar = entry.target;
                const percentage = bar.getAttribute('data-percentage');
                const label = bar.querySelector('.campaignpress-progress-percentage');

                // Animate width
                bar.style.width = percentage + '%';

                // Animate number
                if (label) {
                    let start = 0;
                    const end = parseInt(percentage);
                    if (start === end) return;

                    const duration = 1000;
                    const increment = end / (duration / 16);

                    const timer = setInterval(() => {
                        start += increment;
                        if (start >= end) {
                            label.innerText = end + '%';
                            clearInterval(timer);
                        } else {
                            label.innerText = Math.floor(start) + '%';
                        }
                    }, 16);
                }

                observer.unobserve(bar);
            }
        });
    }, { threshold: 0.1 });

    bars.forEach(bar => observer.observe(bar));
});
