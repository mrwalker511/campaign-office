const nextJest = require('next/jest');

const createJestConfig = nextJest({
  dir: './',
});

const customJestConfig = {
  moduleDirectories: ['node_modules', '<rootDir>/'],
  testEnvironment: 'jest-environment-jsdom',
  setupFilesAfterEnv: ['<rootDir>/tests/javascript/setup.js'],
  moduleNameMapping: {
    '^@/(.*)$': '<rootDir>/$1',
    '^@components/(.*)$': '<rootDir>/assets/react/components/$1',
    '^@blocks/(.*)$': '<rootDir>/blocks/$1',
  },
  transform: {
    '^.+\\.(js|jsx|ts|tsx)$': ['babel-jest', { presets: ['next/babel'] }],
  },
  testPathIgnorePatterns: ['<rootDir>/.next/', '<rootDir>/node_modules/'],
  collectCoverageFrom: [
    'assets/react/**/*.{js,jsx}',
    '!assets/react/**/*.stories.{js,jsx}',
    '!**/node_modules/**',
    '!**/coverage/**',
  ],
};

module.exports = createJestConfig(customJestConfig);
