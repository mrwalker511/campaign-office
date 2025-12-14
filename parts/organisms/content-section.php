<?php
/**
 * Layout Module: Content Section
 * 
 * Standard prose content area with sidebar support option.
 */
?>
<section class="py-16 md:py-24 bg-white" aria-label="Main Content">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-12">
            
            <!-- Main Content Area -->
            <div class="w-full lg:w-2/3">
                <article class="prose prose-lg md:prose-xl max-w-none text-neutral-700">
                    <h2 class="text-3xl md:text-4xl font-serif font-bold text-brand-900 mb-6">
                        A Vision for Transformative Change
                    </h2>
                    <p class="leading-relaxed mb-6">
                        Since the beginning of this campaign, our mission has been clear: to give a voice to the voiceless and ensure that our government works for everyone, not just the privileged few. We believe in the power of grassroots movements to reshape our political landscape.
                    </p>
                    <p class="leading-relaxed mb-6">
                        Through dedicated service and unwavering commitment to our core values, we have developed a comprehensive platform that addresses the urgent needs of our district while laying the groundwork for long-term prosperity.
                    </p>
                    
                    <blockquote class="border-l-4 border-accent-500 pl-6 italic text-xl text-neutral-800 my-8 bg-neutral-50 py-4 pr-4 rounded-r-lg">
                        "We are not just running for office; we are running to incite a movement of change that will echo through generations."
                    </blockquote>

                    <h3 class="text-2xl font-bold text-brand-800 mt-10 mb-4">Why This Matters Now</h3>
                    <p class="leading-relaxed mb-6">
                        The challenges we face today—economic inequality, climate change, and social injustice—require bold, innovative solutions. We cannot afford to wait. The time for action is now.
                    </p>
                    
                    <ul class="list-none pl-0 space-y-3 my-8">
                        <li class="flex items-start">
                            <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-accent-100 text-accent-600 font-bold text-sm mr-3 mt-1">✓</span>
                            <span>Commitment to 100% renewable energy by 2030.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-accent-100 text-accent-600 font-bold text-sm mr-3 mt-1">✓</span>
                            <span>Universal access to early childhood education.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center rounded-full bg-accent-100 text-accent-600 font-bold text-sm mr-3 mt-1">✓</span>
                            <span>Affordable housing initiatives for working families.</span>
                        </li>
                    </ul>
                </article>
            </div>

            <!-- Sidebar (Optional) -->
            <aside class="w-full lg:w-1/3 space-y-8">
                <!-- Sidebar Widget: Newsletter -->
                <div class="bg-brand-50 p-6 rounded-xl border border-brand-100">
                    <h3 class="text-xl font-bold text-brand-900 mb-4">Stay Updated</h3>
                    <p class="text-neutral-600 mb-4 text-sm">Join our newsletter to receive the latest campaign news and event invites.</p>
                    <form class="space-y-3">
                        <label for="email-signup" class="sr-only">Email Address</label>
                        <input type="email" id="email-signup" placeholder="email@example.com" class="w-full px-4 py-3 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all">
                        <button type="submit" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition-colors shadow-sm">
                            Subscribe
                        </button>
                    </form>
                </div>

                <!-- Sidebar Widget: Events -->
                <div class="bg-white p-6 rounded-xl border border-neutral-200 shadow-sm">
                    <h3 class="text-xl font-bold text-brand-900 mb-4">Upcoming Events</h3>
                    <ul class="space-y-4">
                        <li class="border-b border-neutral-100 pb-3 last:border-0 last:pb-0">
                            <a href="#" class="group">
                                <span class="block text-accent-600 text-sm font-bold uppercase mb-1">Oct 15 • 6:00 PM</span>
                                <span class="block text-neutral-800 font-semibold group-hover:text-brand-600 transition-colors">Town Hall Meeting</span>
                            </a>
                        </li>
                        <li class="border-b border-neutral-100 pb-3 last:border-0 last:pb-0">
                            <a href="#" class="group">
                                <span class="block text-accent-600 text-sm font-bold uppercase mb-1">Oct 22 • 10:00 AM</span>
                                <span class="block text-neutral-800 font-semibold group-hover:text-brand-600 transition-colors">Park Clean-up Day</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

        </div>
    </div>
</section>
