<?php
/**
 * Layout Module: Hero - Center Aligned Variant
 * 
 * Large H1, subheadline, two buttons (primary/secondary), background image overlay.
 */
?>
<section class="relative w-full min-h-screen flex items-center justify-center overflow-hidden" aria-label="Hero Center">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <img 
            src="<?php echo get_template_directory_uri(); ?>/assets/images/hero-bg.jpg" 
            alt="" 
            class="w-full h-full object-cover"
        >
        <div class="absolute inset-0 bg-gradient-to-b from-brand-900/80 via-brand-900/70 to-brand-900/90"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        <!-- Main Headline -->
        <h1 class="font-serif text-5xl md:text-6xl lg:text-8xl font-bold text-white leading-tight mb-6 max-w-5xl mx-auto">
            Leadership That Puts
            <span class="block text-brand-300 mt-2">People First</span>
        </h1>

        <!-- Subheadline -->
        <p class="text-xl md:text-2xl lg:text-3xl text-brand-100 mb-12 max-w-3xl mx-auto leading-relaxed font-light">
            Building stronger communities through transparency, accountability, and bold progressive action.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a 
                href="#join" 
                class="inline-flex items-center justify-center py-5 px-10 bg-accent-600 hover:bg-accent-700 text-white text-lg font-bold rounded-lg transition-all transform hover:-translate-y-1 hover:shadow-2xl focus:outline-none focus:ring-4 focus:ring-accent-500/50 w-full sm:w-auto"
                aria-label="Join our campaign"
            >
                Join the Movement
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
            <a 
                href="#platform" 
                class="inline-flex items-center justify-center py-5 px-10 bg-transparent border-2 border-white/40 hover:border-white hover:bg-white/10 text-white text-lg font-bold rounded-lg backdrop-blur-sm transition-all focus:outline-none focus:ring-4 focus:ring-white/30 w-full sm:w-auto"
                aria-label="View our platform"
            >
                Our Platform
            </a>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </div>
</section>
