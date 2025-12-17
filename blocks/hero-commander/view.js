/**
 * Hero Commander Block - Frontend JavaScript
 *
 * Handles typewriter effect, parallax scrolling, and animations
 *
 * @package CampaignPress
 */

(function () {
    'use strict';

    /**
     * Typewriter Effect Class
     */
    class Typewriter {
        constructor(element, texts, speed) {
            this.element = element;
            this.texts = texts;
            this.speed = speed;
            this.textIndex = 0;
            this.charIndex = 0;
            this.isDeleting = false;
            this.isPaused = false;

            this.type();
        }

        type() {
            const currentText = this.texts[this.textIndex];
            const displayText = this.isDeleting
                ? currentText.substring(0, this.charIndex - 1)
                : currentText.substring(0, this.charIndex + 1);

            this.element.textContent = displayText;

            let typeSpeed = this.speed;

            if (this.isDeleting) {
                typeSpeed /= 2;
            }

            if (!this.isDeleting && this.charIndex === currentText.length) {
                // Pause at end of word
                typeSpeed = 2000;
                this.isDeleting = true;
            } else if (this.isDeleting && this.charIndex === 0) {
                // Move to next word
                this.isDeleting = false;
                this.textIndex = (this.textIndex + 1) % this.texts.length;
                typeSpeed = 500;
            }

            if (this.isDeleting) {
                this.charIndex--;
            } else {
                this.charIndex++;
            }

            setTimeout(() => this.type(), typeSpeed);
        }
    }

    /**
     * Parallax Effect Class
     */
    class ParallaxEffect {
        constructor(element) {
            this.element = element;
            this.background = element.querySelector('.hero-commander__background');
            this.lastScrollY = window.scrollY;
            this.ticking = false;

            this.init();
        }

        init() {
            window.addEventListener('scroll', () => this.requestTick());
            this.update();
        }

        requestTick() {
            if (!this.ticking) {
                window.requestAnimationFrame(() => this.update());
                this.ticking = true;
            }
        }

        update() {
            this.ticking = false;
            const scrollY = window.scrollY;
            const rect = this.element.getBoundingClientRect();

            // Only apply parallax if element is in viewport
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                const yPos = -(scrollY * 0.5);
                this.background.style.transform = `translate3d(0, ${yPos}px, 0)`;
            }
        }
    }

    /**
     * Initialize all Hero Commander blocks on the page
     */
    function initHeroCommander() {
        const heroBlocks = document.querySelectorAll('.hero-commander');

        heroBlocks.forEach((block) => {
            // Initialize Typewriter Effect
            const typewriterEnabled = block.dataset.typewriterEnabled === 'true';
            if (typewriterEnabled) {
                const container = block.querySelector('[data-typewriter-container]');
                const textElement = container?.querySelector('.typewriter-text');

                if (textElement && block.dataset.typewriterTexts) {
                    try {
                        const texts = JSON.parse(block.dataset.typewriterTexts);
                        const speed = parseInt(block.dataset.typewriterSpeed) || 100;
                        new Typewriter(textElement, texts, speed);
                    } catch (e) {
                        console.error('Hero Commander: Invalid typewriter texts', e);
                    }
                }
            }

            // Initialize Parallax Effect
            const parallaxEnabled = block.dataset.parallax === 'true';
            if (parallaxEnabled && window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
                // Only enable parallax on devices that don't prefer reduced motion
                // and on screens wider than 768px
                if (window.innerWidth > 768) {
                    new ParallaxEffect(block);
                }
            }

            // Intersection Observer for animations
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(
                    (entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('is-visible');
                                observer.unobserve(entry.target);
                            }
                        });
                    },
                    {
                        threshold: 0.1,
                        rootMargin: '0px 0px -100px 0px'
                    }
                );

                observer.observe(block);
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroCommander);
    } else {
        initHeroCommander();
    }

    // Re-initialize on dynamic content load (for SPAs)
    if (typeof window.wp !== 'undefined' && window.wp.domReady) {
        window.wp.domReady(initHeroCommander);
    }
})();
