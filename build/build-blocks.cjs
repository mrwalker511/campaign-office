#!/usr/bin/env node

/**
 * Build Script for CampaignPress Blocks
 *
 * Compiles React/JSX blocks using @wordpress/scripts
 *
 * @package CampaignPress
 */

const { exec } = require('child_process');
const path = require('path');
const fs = require('fs');

const BLOCKS_DIR = path.join(__dirname, '..', 'blocks');

// Blocks to build
const blocks = [
    'hero-commander',
    'donation-form',
    'event-organizer',
    'volunteer-matcher',
    'progress',
    'countdown',
    'policy-platform',
    'mission-control',
    'section-wrapper',
    'style-panel'
];

console.log('🔨 Building CampaignPress Blocks...\n');

/**
 * Build a single block
 */
function buildBlock(blockName) {
    return new Promise((resolve, reject) => {
        const blockPath = path.join(BLOCKS_DIR, blockName);
        const indexPath = path.join(blockPath, 'index.js');

        // Check if index.js exists
        if (!fs.existsSync(indexPath)) {
            console.log(`⚠️  Skipping ${blockName} - no index.js found`);
            resolve();
            return;
        }

        console.log(`📦 Building ${blockName}...`);

        // Use wp-scripts to build
        const command = `cd "${blockPath}" && npx wp-scripts build index.js --output-path=.`;

        exec(command, (error, stdout, stderr) => {
            if (error) {
                console.error(`❌ Error building ${blockName}:`, error.message);
                reject(error);
                return;
            }

            if (stderr && !stderr.includes('Compiled successfully')) {
                console.warn(`⚠️  ${blockName}:`, stderr);
            }

            console.log(`✅ ${blockName} built successfully`);
            resolve();
        });
    });
}

/**
 * Build all blocks sequentially
 */
async function buildAllBlocks() {
    for (const block of blocks) {
        try {
            await buildBlock(block);
        } catch (error) {
            console.error(`Failed to build ${block}`);
        }
    }

    console.log('\n🎉 Build complete!\n');
}

// Run the build
buildAllBlocks().catch((error) => {
    console.error('Build failed:', error);
    process.exit(1);
});
