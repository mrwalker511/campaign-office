/**
 * Clean Optimized Files
 * Removes all generated/optimized assets
 * 
 * Usage:
 *   npm run clean
 */

import fs from 'fs/promises';
import { directories, logger } from './config.js';

async function clean() {
    logger.header('Clean Optimized Files');

    const dirsToClean = [
        directories.css.critical,
        directories.css.output,
        directories.js.output,
        directories.images.output,
        directories.reports
    ];

    let cleaned = 0;
    let skipped = 0;

    for (const dir of dirsToClean) {
        try {
            await fs.rm(dir, { recursive: true, force: true });
            logger.success(`Cleaned: ${dir}`);
            cleaned++;
        } catch (error) {
            if (error.code === 'ENOENT') {
                console.log(`ℹ️  Skipped (not found): ${dir}`);
                skipped++;
            } else {
                logger.error(`Error cleaning ${dir}: ${error.message}`);
            }
        }
    }

    console.log('\n' + '═'.repeat(60));
    console.log(`\n✨ Clean complete! Removed ${cleaned} directories, skipped ${skipped}.\n`);
}

clean().catch(error => {
    logger.error(`Fatal error: ${error.message}`);
    process.exit(1);
});
