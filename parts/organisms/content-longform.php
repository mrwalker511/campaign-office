<?php
/**
 * Layout Module: Long-Form Content
 * 
 * Narrow, centered layout optimized for reading with beautiful typography.
 * Includes styled blockquotes and callout boxes.
 */
?>
<section class="py-16 md:py-24 bg-white" aria-label="Article Content">
    <div class="container mx-auto px-4">
        
        <!-- Content Container - Narrow & Centered -->
        <article class="max-w-3xl mx-auto">
            
            <!-- Article Header -->
            <header class="mb-12 text-center">
                <span class="inline-block py-2 px-4 rounded-full bg-brand-100 text-brand-700 text-sm font-bold mb-4 tracking-wider uppercase">
                    Policy Brief
                </span>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-brand-900 mb-6 leading-tight">
                    Building a Sustainable Future for Our Community
                </h1>
                <div class="flex items-center justify-center gap-4 text-sm text-neutral-500">
                    <time datetime="2024-10-15">October 15, 2024</time>
                    <span aria-hidden="true">•</span>
                    <span>8 min read</span>
                </div>
            </header>

            <!-- Long-Form Content with Typography Styles -->
            <div class="prose-content">
                
                <!-- Intro Paragraph -->
                <p class="text-xl md:text-2xl text-neutral-700 leading-relaxed mb-8 font-light">
                    Our community stands at a crossroads. The decisions we make today will shape the lives of generations to come. This is why we must act with both urgency and wisdom.
                </p>

                <!-- Standard Paragraphs -->
                <p class="text-lg text-neutral-700 leading-relaxed mb-6">
                    Over the past decade, we've witnessed unprecedented challenges—from economic inequality to climate change, from healthcare access to educational disparities. These aren't abstract policy debates; they're real issues affecting real families in our district every single day.
                </p>

                <p class="text-lg text-neutral-700 leading-relaxed mb-6">
                    Through extensive community engagement, town halls, and listening sessions, we've developed a comprehensive platform that addresses these challenges head-on. Our approach is grounded in three core principles: transparency, accountability, and bold progressive action.
                </p>

                <!-- Callout Box - Important Note -->
                <aside class="my-10 p-6 md:p-8 bg-accent-50 border-l-4 border-accent-500 rounded-r-xl">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-accent-500 text-white rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-accent-900 mb-2">Important Note</h3>
                            <p class="text-base text-accent-800 leading-relaxed">
                                All policy proposals outlined in this document have been vetted by community leaders, policy experts, and fiscal analysts to ensure both effectiveness and sustainability.
                            </p>
                        </div>
                    </div>
                </aside>

                <h2 class="font-serif text-3xl md:text-4xl font-bold text-brand-900 mt-12 mb-6">
                    Economic Development & Jobs
                </h2>

                <p class="text-lg text-neutral-700 leading-relaxed mb-6">
                    Creating sustainable, well-paying jobs is the foundation of a thriving community. Our economic development plan focuses on supporting local businesses, attracting green industries, and investing in workforce development programs that prepare our residents for the jobs of tomorrow.
                </p>

                <!-- Blockquote -->
                <blockquote class="my-10 pl-6 md:pl-8 border-l-4 border-accent-500 italic text-xl md:text-2xl text-neutral-800 leading-relaxed">
                    "We don't just need jobs—we need careers that provide dignity, security, and a pathway to the middle class for every family willing to work hard."
                </blockquote>

                <p class="text-lg text-neutral-700 leading-relaxed mb-6">
                    This means investing in apprenticeship programs, partnering with community colleges to align curriculum with industry needs, and ensuring that economic growth benefits everyone, not just those at the top.
                </p>

                <!-- Callout Box - Success Story -->
                <aside class="my-10 p-6 md:p-8 bg-brand-50 border-l-4 border-brand-500 rounded-r-xl">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-brand-500 text-white rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-brand-900 mb-2">Success Story</h3>
                            <p class="text-base text-brand-800 leading-relaxed">
                                In neighboring districts that implemented similar workforce development programs, unemployment dropped by 23% and median household income increased by $12,000 within three years.
                            </p>
                        </div>
                    </div>
                </aside>

                <h2 class="font-serif text-3xl md:text-4xl font-bold text-brand-900 mt-12 mb-6">
                    Education & Opportunity
                </h2>

                <p class="text-lg text-neutral-700 leading-relaxed mb-6">
                    Every child deserves access to world-class education, regardless of their zip code or family income. We're proposing a comprehensive education initiative that includes universal pre-K, smaller class sizes, competitive teacher salaries, and modern facilities equipped with 21st-century learning tools.
                </p>

                <!-- Unordered List -->
                <ul class="my-8 space-y-3">
                    <li class="flex items-start text-lg text-neutral-700">
                        <span class="flex-shrink-0 w-6 h-6 bg-accent-100 text-accent-600 rounded-full flex items-center justify-center text-sm font-bold mr-3 mt-1">✓</span>
                        <span>Increase per-student funding by 15% over the next two years</span>
                    </li>
                    <li class="flex items-start text-lg text-neutral-700">
                        <span class="flex-shrink-0 w-6 h-6 bg-accent-100 text-accent-600 rounded-full flex items-center justify-center text-sm font-bold mr-3 mt-1">✓</span>
                        <span>Expand after-school programs and summer learning opportunities</span>
                    </li>
                    <li class="flex items-start text-lg text-neutral-700">
                        <span class="flex-shrink-0 w-6 h-6 bg-accent-100 text-accent-600 rounded-full flex items-center justify-center text-sm font-bold mr-3 mt-1">✓</span>
                        <span>Provide free breakfast and lunch for all students</span>
                    </li>
                    <li class="flex items-start text-lg text-neutral-700">
                        <span class="flex-shrink-0 w-6 h-6 bg-accent-100 text-accent-600 rounded-full flex items-center justify-center text-sm font-bold mr-3 mt-1">✓</span>
                        <span>Invest in mental health resources and counselors</span>
                    </li>
                </ul>

                <p class="text-lg text-neutral-700 leading-relaxed mb-6">
                    Education is the great equalizer, and we must ensure that every student has the support they need to reach their full potential.
                </p>

                <!-- Callout Box - Warning/Caution -->
                <aside class="my-10 p-6 md:p-8 bg-orange-50 border-l-4 border-orange-500 rounded-r-xl">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-500 text-white rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-orange-900 mb-2">Urgent Action Needed</h3>
                            <p class="text-base text-orange-800 leading-relaxed">
                                Our schools are currently operating with a $4.2 million budget deficit. Without immediate action, we risk teacher layoffs and program cuts that will harm our children's futures.
                            </p>
                        </div>
                    </div>
                </aside>

                <h2 class="font-serif text-3xl md:text-4xl font-bold text-brand-900 mt-12 mb-6">
                    Moving Forward Together
                </h2>

                <p class="text-lg text-neutral-700 leading-relaxed mb-6">
                    The challenges we face are significant, but so is our collective power to overcome them. This campaign isn't about one person—it's about a movement of neighbors, families, and community members coming together to build the future we deserve.
                </p>

                <p class="text-lg text-neutral-700 leading-relaxed mb-6">
                    I invite you to join us. Attend a town hall, volunteer for a weekend, or simply share your story. Together, we can create lasting, meaningful change.
                </p>

                <!-- Final CTA -->
                <div class="mt-12 pt-8 border-t border-neutral-200 text-center">
                    <a 
                        href="#join" 
                        class="inline-flex items-center justify-center py-4 px-8 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition-all transform hover:-translate-y-1 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-brand-500/50"
                    >
                        Join Our Movement
                    </a>
                </div>

            </div>

        </article>

    </div>
</section>
