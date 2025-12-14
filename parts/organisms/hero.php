<?php
/**
 * Layout Module: Hero
 * 
 * Full width hero section with headline, subhead, and dual CTAs.
 */
?>
<section class="relative w-full bg-brand-900 text-white overflow-hidden py-20 lg:py-32" aria-label="Hero">
    <!-- Background Decor (Optional) -->
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <svg class="w-full h-full" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none">
            <path d="M0 100 C 20 0 50 0 100 100 Z"></path>
        </svg>
    </div>

    <div class="container mx-auto px-4 relative z-10 flex flex-col items-center text-center">
        <!-- Preheader / Label -->
        <span class="inline-block py-1 px-3 rounded-full bg-brand-800 text-brand-200 text-sm font-semibold mb-6 tracking-wider uppercase">
            Campaign 2024
        </span>

        <!-- Headline -->
        <h1 class="font-serif text-4xl md:text-5xl lg:text-7xl font-bold leading-tight mb-6 max-w-4xl opacity-0 animate-[fadeIn_1s_ease-out_forwards]">
            Building a Better Future,<br>
            <span class="text-brand-300">Together.</span>
        </h1>

        <!-- Subheadline -->
        <p class="text-lg md:text-xl text-brand-100 mb-10 max-w-2xl leading-relaxed opacity-0 animate-[fadeIn_1s_ease-out_0.3s_forwards]">
            Join the movement dedicated to transparency, community growth, and sustainable progress for our district.
        </p>

        <!-- CTA Group -->
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto opacity-0 animate-[fadeIn_1s_ease-out_0.6s_forwards]">
            <a href="#" class="inline-flex justify-center items-center py-4 px-8 bg-accent-600 hover:bg-accent-700 text-white font-bold rounded-lg transition-all transform hover:-translate-y-1 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-accent-500/50">
                Join the Campaign
            </a>
            <a href="#" class="inline-flex justify-center items-center py-4 px-8 bg-transparent border-2 border-white/30 hover:border-white text-white font-bold rounded-lg transition-all focus:outline-none focus:ring-4 focus:ring-white/30 backdrop-blur-sm hover:bg-white/10">
                Learn More
            </a>
        </div>
    </div>
</section>
