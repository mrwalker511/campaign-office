export default {
  // Test environment
  testEnvironment: 'jsdom',

  // Setup files
  setupFilesAfterEnv: ['<rootDir>/tests/javascript/setup.js'],

  // Module paths
  roots: ['<rootDir>/assets', '<rootDir>/blocks', '<rootDir>/tests'],

  // Test match patterns
  testMatch: [
    '**/__tests__/**/*.{js,jsx}',
    '**/*.test.{js,jsx}',
    '**/*.spec.{js,jsx}',
  ],

  // Transform files with babel
  transform: {
    '^.+\\.(js|jsx)$': ['babel-jest', { configFile: './babel.config.cjs' }],
  },

  // Module name mapper for imports
  moduleNameMapper: {
    '\\.(css|less|scss|sass)$': '<rootDir>/tests/javascript/mocks/styleMock.js',
    '\\.(jpg|jpeg|png|gif|svg)$': '<rootDir>/tests/javascript/mocks/fileMock.js',
    '^@wordpress/(.*)$': '<rootDir>/node_modules/@wordpress/$1',
  },

  // Coverage configuration
  collectCoverageFrom: [
    'assets/js/**/*.{js,jsx}',
    'assets/react/**/*.{js,jsx}',
    'blocks/**/*.js',
    '!**/node_modules/**',
    '!**/vendor/**',
    '!**/build/**',
    '!**/*.config.js',
  ],

  coverageDirectory: 'coverage',

  coverageReporters: ['text', 'lcov', 'html'],

  coverageThresholds: {
    global: {
      branches: 50,
      functions: 50,
      lines: 50,
      statements: 50,
    },
  },

  // Ignore patterns
  testPathIgnorePatterns: ['/node_modules/', '/vendor/', '/build/'],

  // Module file extensions
  moduleFileExtensions: ['js', 'jsx', 'json'],

  // Verbose output
  verbose: true,

  // Clear mocks between tests
  clearMocks: true,

  // Automatically reset mock state
  resetMocks: true,
};
