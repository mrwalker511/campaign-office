/**
 * Clean Optimized Files
 * ESM version for Node.js compatibility
 */

import fs from 'fs/promises';

async function clean() {
    console.log('🧹 Cleaning optimized files...\n');

    const dirsToClean = [
        'assets/css/critical'
    ];

    for (const dir of dirsToClean) {
        try {
            await fs.rm(dir, { recursive: true, force: true });
            console.log(`✅ Cleaned: ${dir}`);
        } catch (error) {
            if (error.code !== 'ENOENT') {
                console.error(`❌ Error cleaning ${dir}:`, error.message);
            }
        }
    }

    console.log('\n✨ Clean complete!\n');
}

clean().catch(console.error);
