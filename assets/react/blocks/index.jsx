/**
 * CampaignPress Gutenberg Blocks
 *
 * React components for custom Gutenberg blocks
 *
 * Note: Using WordPress's bundled React via wp.element instead of
 * importing React separately. This reduces bundle size and ensures
 * compatibility with WordPress core and plugins.
 */

// Use WordPress's React (wp.element) instead of separate React import
// import React from 'react'; // ❌ No longer needed
const { createElement } = wp.element; // ✓ Use WordPress React

import './DonationButton';
import './CampaignProgress';
import './IssueCard';
import './EventCountdown';
import './VolunteerCTA';
