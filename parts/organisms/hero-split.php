<?php
/**
 * Layout Module: Hero - Split Screen Variant
 * 
 * Text on left (50%), Image/Slider on right (50%), stacks vertically on mobile.
 */
?>
<section class="relative w-full min-h-screen bg-white" aria-label="Hero Split">
    <div class="container mx-auto px-4 h-full">
        <div class="flex flex-col lg:flex-row items-center min-h-screen gap-8 lg:gap-12">
            
            <!-- Left: Content -->
            <div class="w-full lg:w-1/2 py-12 lg:py-20 order-2 lg:order-1">
                <!-- Label -->
                <span class="inline-block py-2 px-4 rounded-full bg-brand-100 text-brand-700 text-sm font-bold mb-6 tracking-wider uppercase">
                    Campaign 2024
                </span>

                <!-- Headline -->
                <h1 class="font-serif text-4xl md:text-5xl lg:text-6xl font-bold text-brand-900 leading-tight mb-6">
                    A New Vision for
                    <span class="block text-accent-600 mt-2">Our Community</span>
                </h1>

                <!-- Subheadline -->
                <p class="text-lg md:text-xl text-neutral-600 mb-10 leading-relaxed max-w-xl">
                    Together, we can create meaningful change that benefits every family, every neighborhood, and every generation to come.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a 
                        href="#volunteer" 
                        class="inline-flex items-center justify-center py-4 px-8 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition-all transform hover:-translate-y-1 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-brand-500/50"
                        aria-label="Get involved"
                    >
                        Get Involved
                    </a>
                    <a 
                        href="#learn" 
                        class="inline-flex items-center justify-center py-4 px-8 bg-neutral-100 hover:bg-neutral-200 text-brand-900 font-bold rounded-lg transition-all focus:outline-none focus:ring-4 focus:ring-neutral-300"
                        aria-label="Learn more about our vision"
                    >
                        Learn More
                    </a>
                </div>

                <!-- Stats/Social Proof -->
                <div class="flex flex-wrap gap-8 mt-12 pt-8 border-t border-neutral-200">
                    <div>
                        <div class="text-3xl font-bold text-brand-600">1,200+</div>
                        <div class="text-sm text-neutral-500 uppercase tracking-wide">Volunteers</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-brand-600">45</div>
                        <div class="text-sm text-neutral-500 uppercase tracking-wide">Events Held</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-brand-600">$250K</div>
                        <div class="text-sm text-neutral-500 uppercase tracking-wide">Raised</div>
                    </div>
                </div>
            </div>

            <!-- Right: Image/Slider -->
            <div class="w-full lg:w-1/2 order-1 lg:order-2">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl aspect-[4/5] lg:aspect-[3/4]">
                    <!-- Primary Image -->
                    <img 
                        src="<?php echo get_template_directory_uri(); ?>/assets/images/hero-split.jpg" 
                        alt="Campaign leader speaking at community event" 
                        class="w-full h-full object-cover"
                    >
                    
                    <!-- Optional: Floating Badge -->
                    <div class="absolute bottom-6 left-6 right-6 bg-white/95 backdrop-blur-sm p-6 rounded-xl shadow-lg">
                        <p class="text-brand-900 font-bold text-lg mb-1">Join us at our next town hall</p>
                        <p class="text-neutral-600 text-sm">October 22 • 6:00 PM • Community Center</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
