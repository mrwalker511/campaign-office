<?php
/**
 * Layout Module: Feature Grid
 * 
 * 3-Column grid for displaying key policy points or features.
 */
?>
<section class="py-20 bg-neutral-50" aria-label="Key Priorities">
    <div class="container mx-auto px-4">
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="font-serif text-3xl md:text-4xl text-brand-900 font-bold mb-4">
                Our Key Priorities
            </h2>
            <div class="h-1 w-20 bg-accent-500 mx-auto rounded-full mb-6"></div>
            <p class="text-neutral-600 text-lg">
                We are focused on the issues that matter most to our community. Here is where we stand.
            </p>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <article class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-neutral-100 group">
                <div class="w-14 h-14 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-brand-900 mb-3">Education First</h3>
                <p class="text-neutral-600 leading-relaxed mb-4">
                    Investing in our schools and teachers to ensure every child has access to quality education.
                </p>
                <a href="#" class="inline-flex items-center text-accent-600 font-semibold hover:text-accent-700 transition-colors">
                    Read Policy <span aria-hidden="true" class="ml-2">&rarr;</span>
                </a>
            </article>

            <!-- Feature 2 -->
            <article class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-neutral-100 group">
                <div class="w-14 h-14 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-brand-900 mb-3">Sustainable Growth</h3>
                <p class="text-neutral-600 leading-relaxed mb-4">
                    Promoting economic development that respects our environment and local community values.
                </p>
                <a href="#" class="inline-flex items-center text-accent-600 font-semibold hover:text-accent-700 transition-colors">
                    Read Policy <span aria-hidden="true" class="ml-2">&rarr;</span>
                </a>
            </article>

            <!-- Feature 3 -->
            <article class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-neutral-100 group">
                <div class="w-14 h-14 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-brand-900 mb-3">Community Safety</h3>
                <p class="text-neutral-600 leading-relaxed mb-4">
                    Working together with law enforcement and community leaders to keep our neighborhoods safe.
                </p>
                <a href="#" class="inline-flex items-center text-accent-600 font-semibold hover:text-accent-700 transition-colors">
                    Read Policy <span aria-hidden="true" class="ml-2">&rarr;</span>
                </a>
            </article>
        </div>
    </div>
</section>
