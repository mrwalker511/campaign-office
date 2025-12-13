# CampaignPress Architecture & Logic

## 🏗 System Overview
CampaignPress is a **Hybrid WordPress Theme** that functions as a SaaS platform. It bridges the gap between a standard display theme (Classic/Block hybrid) and a Campaign Management System (CRM, Field Ops, Analytics).

## 📂 Directory Structure Strategy
The codebase is strictly divided into **Configuration**, **Free Features**, and **Premium Modules**.

```text
campaign-office/
├── theme.json                  <-- DESIGN TRUTH. Edit this for global styles.
├── functions.php               <-- Bootstrapper. Loads Includes.
├── style.css                   <-- Metadata & Critical CSS variables only.
├── assets/
│   ├── css/design-system-wp69.css <-- The "Engine" (Animations, Utils).
│   └── js/                     <-- Vite-compiled assets.
├── includes/
│   ├── free/                   <-- Core Theme Features (GPL)
│   │   ├── volunteer-management.php
│   │   ├── event-management.php
│   │   └── gutenberg-blocks.php
│   └── premium/                <-- SaaS Features (License Gated)
│       ├── crm/                <-- Custom DB Tables Logic
│       ├── field-operations/   <-- Canvassing/Phonebanking
│       ├── compliance/         <-- FEC Reporting
│       └── api/                <-- REST API Endpoints
└── templates/
    └── custom-post-types/      <-- Custom Template Loader Target
        ├── single/             <-- single-cp_event.php, etc.
        └── archive/            <-- archive-cp_issue.php, etc.