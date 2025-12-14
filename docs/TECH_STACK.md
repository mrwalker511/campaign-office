# Tech Stack & Environment

## Core Requirements
- **PHP**: 8.1+ (Strict requirement for Premium modules).
- **WordPress**: 6.9+ (Required for design tokens).
- **Node**: 18.0+ (For build tools).
- **Composer**: 2.0+ (For PHP dependencies).

## Frontend Stack
- **JavaScript**: Vanilla JS for frontend (no jQuery dependency for new features).
- **React**: Used for **Gutenberg Blocks** and **Admin CRM UI** only.
- **CSS**: Modern CSS (Variables, Grid, Flex, Clamp). **No Sass/SCSS** (unless requested).
- **Build Tool**: Vite (Legacy Webpack support exists but deprecated).

## Backend Stack
- **Database**: MySQL 8.0 / MariaDB 10.5.
- **Server**: Apache/Nginx.

## Development Environment
- **Local**: Window/Mac/Linux.
- **Linting**: standard WordPress Coding Standards (WPCS).
- **Formatting**: Prettier + PHP-CS-Fixer.

## Dependencies (bundled)
- **Chart.js**: For Analytics dashboards.
- **Leaflet**: For Map blocks.
- *(Note: Bootstrap 5 is removed/deprecated in favor of `theme.json`)*