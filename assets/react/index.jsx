import React from 'react';
import { createRoot } from 'react-dom/client';
import ClassicStatesmanHomepage from './ClassicStatesmanHomepage';

/**
 * Initialize Classic Statesman Homepage
 *
 * This renders the homepage when the #classic-statesman-root element is present
 */
document.addEventListener('DOMContentLoaded', () => {
  const rootElement = document.getElementById('classic-statesman-root');

  if (rootElement) {
    const root = createRoot(rootElement);
    root.render(<ClassicStatesmanHomepage />);
  }
});
