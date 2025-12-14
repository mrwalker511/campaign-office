# Documentation Upgrade Plan

## Goal
To populate and update the `docs/` directory to facilitate efficient agentic programming. The goal is to provide any future AI agent (or human developer) with a clear, comprehensive, and structured context to immediately start working effectively.

## Proposed Changes

### 1. Create `docs/AGENTS.md`
This will be the "Master Instruction" file for agents.
- **Purpose**: High-level map and "System 2" thinking guide.
- **Content**:
  - Project Mission & Executive Summary.
  - Quick Map (Where to find what).
  - "The Golden Rules" (Coding standards, architectural constraints).
  - Common Workflows (How to build, test, deploy).

### 2. Enhance `docs/ARCHITECTURE.md`
- **Current State**: Very brief.
- **New Content**:
  - Integrate the excellent "High-Level Architecture" and "Deep Dive" sections from `CLAUDE.md`.
  - clearly define the Free vs Premium boundary.
  - Explain the Database Schema.

### 3. Enhance `docs/TECH_STACK.md`
- **Current State**: 6 lines.
- **New Content**:
  - Detailed version requirements (PHP, WP, Node).
  - Dependencies (Vite, Bootstrap, etc.).
  - Environment setup.

### 4. Review `docs/STYLEGUIDE.md`
- **Current State**: Excellent.
- **Action**: Keep as is, but ensure it is referenced in `AGENTS.md`.

## Verification Plan
### Manual Verification
- Review the generated Markdown files for clarity and accuracy.
- Ensure no information is lost from `CLAUDE.md` if we decide to eventually deprecate it (though we will keep it for now).
