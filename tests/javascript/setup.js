/**
 * Jest Test Setup
 *
 * This file runs before each test
 */

import '@testing-library/jest-dom';

// Mock WordPress globals
global.wp = {
  i18n: {
    __: (text) => text,
    _x: (text) => text,
    _n: (single, plural, number) => (number === 1 ? single : plural),
    sprintf: (format, ...args) => format,
  },
  element: {
    createElement: (...args) => args,
    Fragment: 'Fragment',
  },
  components: {},
  blockEditor: {},
  blocks: {
    registerBlockType: jest.fn(),
  },
  data: {
    select: jest.fn(),
    dispatch: jest.fn(),
    subscribe: jest.fn(),
  },
  hooks: {
    addFilter: jest.fn(),
    addAction: jest.fn(),
    doAction: jest.fn(),
    applyFilters: jest.fn(),
  },
};

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: jest.fn().mockImplementation((query) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: jest.fn(),
    removeListener: jest.fn(),
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    dispatchEvent: jest.fn(),
  })),
});

// Mock IntersectionObserver
global.IntersectionObserver = class IntersectionObserver {
  constructor() {}
  disconnect() {}
  observe() {}
  takeRecords() {
    return [];
  }
  unobserve() {}
};

// Suppress console errors in tests (optional)
// global.console.error = jest.fn();
// global.console.warn = jest.fn();
