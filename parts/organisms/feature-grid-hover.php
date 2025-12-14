<?php
/**
 * Layout Module: Feature Grid - Hover Enhanced Version
 * 
 * 3-column responsive grid with hover effects (lift + shadow).
 */

// Example data structure - replace with ACF fields or custom fields
$features = [
    [
        'icon' => 'education',
        'title' => 'Quality Education',
        'description' => 'Investing in our schools and teachers to ensure every child has access to world-class education and opportunities.',
        'link' => '#education',
    ],
    [
        'icon' => 'healthcare',
        'title' => 'Healthcare Access',
        'description' => 'Ensuring affordable, comprehensive healthcare coverage for all families in our community.',
        'link' => '#healthcare',
    ],
    [
        'icon' => 'economy',
        'title' => 'Economic Growth',
        'description' => 'Creating jobs and supporting local businesses through smart economic development policies.',
        'link' => '#economy',
    ],
    [
        'icon' => 'environment',
        'title' => 'Clean Environment',
        'description' => 'Protecting our natural resources and fighting climate change for future generations.',
        'link' => '#environment',
    ],
    [
        'icon' => 'safety',
        'title' => 'Public Safety',
        'description' => 'Building trust between communities and law enforcement while keeping neighborhoods safe.',
        'link' => '#safety',
    ],
    [
        'icon' => 'housing',
        'title' => 'Affordable Housing',
        'description' => 'Expanding access to quality, affordable housing for working families and seniors.',
        'link' => '#housing',
    ],
];

// Icon SVG mapping
function get_feature_icon_hover($icon_name) {
    $icons = [
        'education' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>',
        'healthcare' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>',
        'economy' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>',
        'environment' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'safety' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>',
        'housing' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>',
    ];
    return $icons[$icon_name] ?? $icons['education'];
}
?>

<section class="py-16 md:py-24 bg-neutral-50" aria-label="Our Priorities">
    <div class="container mx-auto px-4">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 md:mb-16">
            <h2 class="font-serif text-3xl md:text-4xl lg:text-5xl text-brand-900 font-bold mb-4">
                Our Key Priorities
            </h2>
            <div class="h-1 w-20 bg-accent-500 mx-auto rounded-full mb-6"></div>
            <p class="text-lg md:text-xl text-neutral-600 leading-relaxed">
                We're committed to addressing the issues that matter most to our community.
            </p>
        </div>

        <!-- Feature Grid with Hover Effects -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            <?php foreach ($features as $feature): ?>
                <!-- Card Molecule with Hover Enhancement -->
                <article class="group bg-white p-6 md:p-8 rounded-xl border border-neutral-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                    
                    <!-- Icon with Hover Scale -->
                    <div class="w-14 h-14 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-brand-600 group-hover:text-white transition-all duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <?php echo get_feature_icon_hover($feature['icon']); ?>
                        </svg>
                    </div>

                    <!-- Headline -->
                    <h3 class="text-xl md:text-2xl font-bold text-brand-900 mb-3 group-hover:text-brand-700 transition-colors">
                        <?php echo esc_html($feature['title']); ?>
                    </h3>

                    <!-- Body Text -->
                    <p class="text-neutral-600 leading-relaxed mb-5">
                        <?php echo esc_html($feature['description']); ?>
                    </p>

                    <!-- Learn More Link with Arrow Animation -->
                    <a 
                        href="<?php echo esc_url($feature['link']); ?>" 
                        class="inline-flex items-center text-accent-600 font-semibold group-hover:text-accent-700 transition-colors focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 rounded"
                        aria-label="Learn more about <?php echo esc_attr($feature['title']); ?>"
                    >
                        Learn More
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>

                </article>
            <?php endforeach; ?>
        </div>

    </div>
</section>
