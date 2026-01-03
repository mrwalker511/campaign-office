/**
 * CampaignPress CRM Interface
 *
 * Premium feature: Customer Relationship Management for campaign contacts
 *
 * Note: Using WordPress's bundled React via wp.element instead of
 * importing React separately. This reduces bundle size and ensures
 * compatibility with WordPress core and plugins.
 */

// Use WordPress's React (wp.element) instead of separate React import
// import React from 'react'; // ❌ No longer needed
const { createElement, Component } = wp.element; // ✓ Use WordPress React

// CRM components will be added here (Premium feature)
// Example: import ContactManager from './ContactManager';
// Example: import VolunteerTracker from './VolunteerTracker';

// Export CRM components
export default {};
