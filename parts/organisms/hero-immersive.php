<?php
/**
 * Layout Module: Hero - Immersive Variant
 * 
 * Full-screen video background with glassmorphism card at bottom left.
 */
?>
<section class="relative w-full h-screen overflow-hidden" aria-label="Hero Immersive">
    <!-- Video Background -->
    <div class="absolute inset-0 z-0">
        <video 
            autoplay 
            muted 
            loop 
            playsinline 
            class="w-full h-full object-cover"
            aria-hidden="true"
        >
            <source src="<?php echo get_template_directory_uri(); ?>/assets/videos/hero-bg.mp4" type="video/mp4">
            <source src="<?php echo get_template_directory_uri(); ?>/assets/videos/hero-bg.webm" type="video/webm">
        </video>
        <!-- Dark Overlay for Readability -->
        <div class="absolute inset-0 bg-gradient-to-tr from-brand-900/60 via-brand-900/40 to-transparent"></div>
    </div>

    <!-- Content Container -->
    <div class="container mx-auto px-4 h-full relative z-10 flex items-end pb-12 lg:pb-20">
        
        <!-- Glassmorphism Card - Bottom Left -->
        <div class="w-full lg:w-2/5 bg-white/10 backdrop-blur-xl rounded-2xl p-8 lg:p-10 border border-white/20 shadow-2xl">
            <!-- Label -->
            <span class="inline-block py-2 px-4 rounded-full bg-accent-500/90 text-white text-xs font-bold mb-4 tracking-wider uppercase backdrop-blur-sm">
                Live Campaign
            </span>

            <!-- Headline -->
            <h1 class="font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight mb-4">
                Real Change
                <span class="block text-brand-200">Starts Here</span>
            </h1>

            <!-- Subheadline -->
            <p class="text-base md:text-lg text-white/90 mb-8 leading-relaxed">
                Experience a campaign built on authentic connection, grassroots power, and unwavering commitment to our shared values.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a 
                    href="#donate" 
                    class="inline-flex items-center justify-center py-4 px-7 bg-accent-600 hover:bg-accent-700 text-white font-bold rounded-lg transition-all transform hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-accent-400/50"
                    aria-label="Donate to the campaign"
                >
                    Donate Now
                </a>
                <a 
                    href="#watch" 
                    class="inline-flex items-center justify-center py-4 px-7 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white font-bold rounded-lg border border-white/30 hover:border-white/50 transition-all focus:outline-none focus:ring-4 focus:ring-white/30"
                    aria-label="Watch our story"
                >
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    Watch Story
                </a>
            </div>

            <!-- Live Indicator -->
            <div class="flex items-center gap-2 mt-6 pt-6 border-t border-white/20">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-accent-500"></span>
                </span>
                <span class="text-white/80 text-sm font-medium">Next event in 3 days</span>
            </div>
        </div>

    </div>

    <!-- Mute Toggle (Optional) -->
    <button 
        class="absolute top-6 right-6 z-20 w-12 h-12 bg-white/10 backdrop-blur-md rounded-full border border-white/20 flex items-center justify-center text-white hover:bg-white/20 transition-all focus:outline-none focus:ring-4 focus:ring-white/30"
        aria-label="Toggle video sound"
        onclick="this.closest('section').querySelector('video').muted = !this.closest('section').querySelector('video').muted; this.querySelector('svg').classList.toggle('hidden');"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path>
        </svg>
        <svg class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
        </svg>
    </button>
</section>
