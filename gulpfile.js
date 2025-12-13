/**
 * CampaignPress Speed Optimization Gulpfile
 * Achieves 95+ Core Web Vitals scores
 */

const gulp = require('gulp');
const imagemin = require('gulp-imagemin');
const imageminAvif = require('imagemin-avif');
const imageminWebp = require('imagemin-webp');
const cleanCSS = require('gulp-clean-css');
const terser = require('gulp-terser');
const purgecss = require('gulp-purgecss');
const { deleteAsync } = require('del');

// Paths
const paths = {
    images: {
        src: 'assets/images/**/*.{jpg,jpeg,png}',
        dest: 'assets/images/optimized'
    },
    css: {
        src: 'assets/css/**/*.css',
        dest: 'assets/css/min'
    },
    js: {
        src: 'assets/js/**/*.js',
        dest: 'assets/js/min'
    },
    blocks: {
        css: 'blocks/**/*.css',
        js: 'blocks/**/*.js'
    }
};

/**
 * TASK 1: Optimize Images (AVIF + WebP + Compressed JPG)
 * Target: 80% compression, sub-100KB images
 */
function optimizeImages() {
    return gulp.src(paths.images.src)
        // Generate AVIF (best compression)
        .pipe(imagemin([
            imageminAvif({
                quality: 50,
                speed: 0
            })
        ]))
        .pipe(gulp.dest(paths.images.dest))
        // Generate WebP (fallback)
        .pipe(gulp.src(paths.images.src))
        .pipe(imagemin([
            imageminWebp({
                quality: 75
            })
        ]))
        .pipe(gulp.dest(paths.images.dest))
        // Optimize original JPG/PNG
        .pipe(gulp.src(paths.images.src))
        .pipe(imagemin([
            imagemin.mozjpeg({ quality: 80, progressive: true }),
            imagemin.optipng({ optimizationLevel: 5 })
        ]))
        .pipe(gulp.dest(paths.images.dest));
}

/**
 * TASK 2: Optimize CSS (PurgeCSS + Minify)
 * Target: Remove unused CSS, minify to <20KB
 */
function optimizeCSS() {
    return gulp.src([paths.css.src, paths.blocks.css])
        // Remove unused CSS
        .pipe(purgecss({
            content: [
                '**/*.php',
                'blocks/**/*.js',
                'assets/js/**/*.js'
            ],
            safelist: {
                standard: [
                    /^wp-/,
                    /^has-/,
                    /^is-/,
                    /^campaignpress-/,
                    /^cp-/,
                    /^dashicons/,
                    /^screen-reader-text/
                ],
                deep: [
                    /modal/,
                    /popover/,
                    /tooltip/,
                    /dropdown/
                ]
            }
        }))
        // Minify
        .pipe(cleanCSS({
            level: 2,
            compatibility: 'ie11'
        }))
        .pipe(gulp.dest(paths.css.dest));
}

/**
 * TASK 3: Optimize JavaScript (Terser minification)
 * Target: Tree-shake, minify, remove console.logs
 */
function optimizeJS() {
    return gulp.src([paths.js.src, paths.blocks.js])
        .pipe(terser({
            compress: {
                drop_console: true,
                drop_debugger: true,
                passes: 2
            },
            mangle: {
                toplevel: true
            },
            format: {
                comments: false
            }
        }))
        .pipe(gulp.dest(paths.js.dest));
}

/**
 * TASK 4: Clean optimized files
 */
async function clean() {
    await deleteAsync([
        'assets/images/optimized/**',
        'assets/css/min/**',
        'assets/js/min/**',
        'assets/css/critical/**'
    ]);
}

/**
 * TASK 5: Watch for changes
 */
function watch() {
    gulp.watch(paths.images.src, optimizeImages);
    gulp.watch(paths.css.src, optimizeCSS);
    gulp.watch(paths.js.src, optimizeJS);
}

// Export tasks
exports.optimizeImages = optimizeImages;
exports.optimizeCSS = optimizeCSS;
exports.optimizeJS = optimizeJS;
exports.clean = clean;
exports.watch = watch;

// Default task
exports.default = gulp.series(
    clean,
    gulp.parallel(optimizeImages, optimizeCSS, optimizeJS)
);
