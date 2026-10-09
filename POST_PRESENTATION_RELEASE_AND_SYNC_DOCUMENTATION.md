# Crystal Shipping Inc. - Seafarer Application Portal
## Post-Presentation Client Release, Repository Recovery & Git/SourceTree Synchronization Documentation

**Project**: Seafarer Application Portal (`Crystal-Application-Form` / `Crystal_App_Form`)  
**Client / Stakeholder**: Crystal Shipping Inc.  
**Repository**: `https://github.com/rvngbrl/Crystal-Application-Form.git`  
**Active Development Branch**: `update-dev-team-form`  
**Tracking Base**: `main` (commit `9ea599e`, PR #5)  
**Date**: October 9, 2026  
**Document Author / Lead Developer**: Bolante, Aaron B. (`bolante352@gmail.com` / `20261807-admin`)  

---

## 1. WHAT DID WE BUILD?

Following the successful final client output presentation for Crystal Shipping Inc., we completed and synchronized the end-to-end production codebase of the **Seafarer Application Portal**.

The system comprises:
1. **Pure PHP Application Portal (`index.php`)**:
   - Zero-dependency standalone application capable of running on XAMPP, WAMP, cPanel, or standard Apache/Nginx web servers without requiring a Node.js runtime in production.
   - Complete multi-step seafarer registration covering:
     - Position & Personal Information with real-time Date of Birth (DOB) and age calculation.
     - Interdependent Next-of-Kin validation (e.g., conditional spouse requirements based on civil status).
     - Statutory Government Identification (SSS, PhilHealth, Pag-IBIG, TIN).
     - Maritime Certification & Licensure with real-time validity checks and advisory alerts for expired credentials.
     - Sea Service and Vessel Experience recording.
     - Review summary and document attachment uploads.
2. **Centralized Database Integration Architecture (`config.php`, `db.php`, `submit_application.php`)**:
   - Production connection to remote MySQL instance (`192.185.23.171:3306`, database: `crystinc_crystaldb`).
   - Transactional persistence across applicant personal records, certificates, and sea-service logs with prepared statements preventing SQL injection.
   - Environment variable management using `.env` with a version-controlled `.env.example` template.
3. **TypeScript / React & Vite Production Frontend (`src/`, `dist/`)**:
   - Responsive UI components for interactive applicant guidance, terms and conditions verification, and submission confirmation.
   - Optimized production build transformed cleanly into `dist/`.
4. **SourceTree & GitHub Synchronization Pipeline**:
   - Full recovery and re-anchoring of the local Git metadata (`.git`).
   - Seamless compatibility bridge between the SourceTree repository path (`C:\xampp\htdocs\Crystal-Application-Form`) and the Antigravity workspace path (`C:\xampp\htdocs\Crystal_App_Form`) via an NTFS directory junction.
   - Direct synchronization to GitHub remote repository (`https://github.com/rvngbrl/Crystal-Application-Form.git`).

---

## 2. WHY DID WE BUILD IT?

1. **Client Milestone & Operational Mandate**:
   - Crystal Shipping Inc. required a digitized, secure, and user-friendly portal allowing seafarers worldwide to apply online and submit their documentation prior to deployment.
2. **Elimination of Localhost Hardcoding**:
   - The earlier codebase relied on local development mockups or local MySQL databases that prevented the client from testing or utilizing the application across corporate networks.
3. **Strict Maritime Regulatory Compliance**:
   - STCW 2010 Manila Amendments, POEA/DMW regulations, and MARINA licensing requirements dictate that expired documents must not cause silent rejections but must trigger structured review warnings so crewing officers can guide applicants through certificate renewal.
4. **Codebase Preservation & Team Synchronization**:
   - Development progressed intensely up to the final client presentation within the Antigravity workspace. Without pushing the accumulated changes to GitHub and SourceTree, the remote repository remained stuck at the pre-workspace state (September 11, 2026), risking divergence between local working code and the shared team codebase.

---

## 3. HOW DOES IT WORK?

### A. Application Lifecycle
1. **Candidate Onboarding**: The applicant visits `http://localhost/Crystal_App_Form/` (or the deployed company URL).
2. **Client-Side Real-Time Validation**:
   - Entering DOB immediately computes age and enforces minimum/maximum legal maritime working age constraints.
   - Civil status selection dynamically toggles dependent spouse information.
   - Certification issue and expiry dates are evaluated against current Philippine Standard Time (`Asia/Manila`, UTC+8). Expired documents display an amber advisory warning while allowing completion for crewing evaluation.
3. **Server-Side Persistence (`submit_application.php` / `index.php`)**:
   - Submissions are wrapped in an atomic database transaction.
   - If document uploads or row insertions fail, the transaction rolls back, preventing corrupt partial records.
4. **Reference Confirmation**:
   - Upon successful database commit, an application reference number is generated (`CSI-XXXXXX`), displayed to the seafarer, and stored in the crewing database.

### B. Git & SourceTree Dual-Path Architecture
- **Directory Junction**:
  ```
  C:\xampp\htdocs\Crystal-Application-Form  ==[NTFS Junction]==>  C:\xampp\htdocs\Crystal_App_Form
  ```
- Any commit or file operation made through SourceTree (which targets `Crystal-Application-Form`) or through the Antigravity workspace/terminal (which targets `Crystal_App_Form`) operates simultaneously on the exact same inodes and `.git` repository.
- The remote origin is configured to `https://github.com/rvngbrl/Crystal-Application-Form.git` with upstream branch tracking enabled for `update-dev-team-form`.

---

## 4. WHY DID WE IMPLEMENT IT THIS WAY?

1. **Dual PHP + React Hybrid**:
   - Implementing a standalone pure PHP portal in `index.php` allows Crystal Shipping Inc. to host the system on any low-cost shared hosting or internal XAMPP server with zero build tooling or Node.js runtime overhead.
   - Retaining the TypeScript/React structure in `src/` preserves the modern component architecture for future web application scaling.
2. **Directory Junction vs Renaming**:
   - Renaming `Crystal_App_Form` back to `Crystal-Application-Form` would have disrupted active web server configurations, IDE workspace roots, and local browser bookmarks.
   - Creating an NTFS junction gives both names full functionality without duplicating storage or creating desynchronized copies.
3. **Environment-Driven Configuration with Fallbacks**:
   - `config.php` prioritizes `.env` variables if present, falling back to verified company remote database parameters. This enables zero-configuration onboarding for authorized internal staff while allowing customized environments for staging and production.

---

## 5. WHAT ALTERNATIVES DID WE HAVE?

### Alternative 1: Full Git Re-initialization from Scratch (`git init`)
- **Description**: Running `git init` in `Crystal_App_Form`, adding a new remote, and force-pushing.
- **Why Rejected**: This would have completely obliterated the project's commit history on GitHub (commits by `Bolante, Aaron B.`, `EyRon`, pull requests #1 through #5, and historical diffs). Force-pushing would also break collaboration for all other developers on the team.

### Alternative 2: Renaming Workspace Folder to Match SourceTree Path
- **Description**: Renaming `c:\xampp\htdocs\Crystal_App_Form` to `c:\xampp\htdocs\Crystal-Application-Form`.
- **Why Rejected**: The active Antigravity workspace session was bound to `Crystal_App_Form`. Renaming the directory mid-flight could have severed IDE hooks, local Apache vhost aliases, and browser test sessions.

### Alternative 3: Exporting Code as a Zip File for Manual Upload
- **Description**: Generating an archive and manually uploading files via GitHub web interface.
- **Why Rejected**: Lacks commit granularity, does not update local SourceTree tracking, violates conventional version control best practices, and fails to establish an ongoing push/pull pipeline.

---

## 6. WHAT EVIDENCE SUPPORTS THE DECISION?

1. **Remote Repository Log Inspection**:
   - Querying `git ls-remote https://github.com/rvngbrl/Crystal-Application-Form.git` showed that `main` was at `9ea599e` (PR #5) and `update-dev-team-form` was at `d1d7c7e`.
   - The bare clone confirmed that the working tree in `Crystal_App_Form` was a direct evolution of commit `9ea599e`.
2. **SourceTree Configuration Evidence**:
   - Inspection of `C:\Users\bolan\AppData\Local\Atlassian\SourceTree\bookmarks.xml` and `settings.log` confirmed the last successful commit before workspace creation took place on September 11, 2026, targeting `C:\xampp\htdocs\Crystal-Application-Form`.
3. **Build & Typecheck Verification**:
   - `npx tsc --noEmit` passed with exit code 0.
   - `npm run build` compiled 1682 modules cleanly into `dist/` in 19.32s.
4. **Push Dry-Run & Upstream Push**:
   - Dry run push validated push permissions under cached credentials (`bolante352-maker` / `20261807-admin`).
   - The live push succeeded cleanly:
     `d1d7c7e..6172d8e update-dev-team-form -> update-dev-team-form`.

---

## 7. WHAT WENT WRONG & HOW IT WAS DIAGNOSED

### The Incident
When attempting to push changes to SourceTree and GitHub following the client presentation, `git status` in `c:\xampp\htdocs\Crystal_App_Form` returned:
```
fatal: not a git repository (or any of the parent directories): .git
```
Additionally, SourceTree still referenced the old path `C:\xampp\htdocs\Crystal-Application-Form`, which did not exist on disk.

### Root Cause Analysis
1. When the Antigravity workspace was established, the project folder was named with underscores (`Crystal_App_Form`) instead of hyphens (`Crystal-Application-Form`).
2. During directory provisioning, the hidden `.git` folder was not transferred into `Crystal_App_Form`.
3. As work progressed rapidly on client feature requests (remote DB integration, date validation, UI polish), changes accumulated in the unstaged workspace without local Git tracking.

### Resolution Steps
1. **Safety Backup**: Created a clean full backup at `C:\xampp\htdocs_backup_crystal_20261009` using `robocopy` before any Git operations.
2. **Repository Re-anchoring**: Cloned the official GitHub repository metadata into a temporary location and migrated `.git` directly into `C:\xampp\htdocs\Crystal_App_Form`.
3. **Path Compatibility**: Created an NTFS directory junction (`C:\xampp\htdocs\Crystal-Application-Form` -> `C:\xampp\htdocs\Crystal_App_Form`) and updated SourceTree `bookmarks.xml`.
4. **Staging & Commit**: Configured author credentials (`Bolante, Aaron B. <bolante352@gmail.com>`), verified `.env` exclusion via `.gitignore`, staged all updated components, and committed under commit `6172d8e`.
5. **Remote Push**: Executed `git push -u origin update-dev-team-form`, successfully updating the remote branch on GitHub.

---

## 8. WHAT WAS LEARNED?

1. **Workspace Provisioning Must Validate VCS Continuity**:
   - Whenever initializing or migrating a workspace directory in any IDE, verifying `git status` and remote tracking on Day 1 prevents late-stage push discrepancies.
2. **NTFS Junctions Bridge Tool Path Mismatches**:
   - Directory junctions provide an instant, non-destructive solution when different developer tools (e.g., SourceTree vs Antigravity vs Apache) expect differing directory naming conventions.
3. **Environment Security Discipline**:
   - Ensuring `.env` remains strictly ignored while maintaining `.env.example` protects production database credentials during bulk repository recovery.

---

## 9. WHAT TRADE-OFFS WERE ACCEPTED?

1. **Pushing to `update-dev-team-form` vs Direct Push to `main`**:
   - *Trade-off*: Pushing to `update-dev-team-form` preserves team pull request history and conforms to the repository's established workflow (PRs #1–#5 were all merged from this branch). However, updating `main` requires either opening PR #6 on GitHub or fast-forwarding `main` directly.
   - *Why Acceptable*: Preserves branch protection integrity and gives the lead reviewer visibility into the final release diff.
2. **Dual-Path Directory Junction**:
   - *Trade-off*: Having both `Crystal-Application-Form` and `Crystal_App_Form` in the filesystem requires developers to understand they are viewing the same physical directory.
   - *Why Acceptable*: Completely prevents broken bookmarks in SourceTree without altering active workspace paths.

---

## 10. WHAT LIMITATIONS REMAIN & NEXT STEPS?

1. **Pull Request #6 on GitHub**:
   - The branch `update-dev-team-form` is pushed and up to date on GitHub. A Pull Request can now be created and merged into `main` via:
     `https://github.com/rvngbrl/Crystal-Application-Form/pull/new/update-dev-team-form`
2. **Evaluation Portal (`questionnaire`)**:
   - The companion portal located in `c:\xampp\htdocs\questionnaire` currently has no Git tracking or remote repository configured. If the team desires, a new GitHub repository can be initialized for `questionnaire`.
3. **Database Migration Script**:
   - The remote MySQL database schema is live on `192.185.23.171`. A standalone SQL schema dump (`schema.sql`) should be added to the repository in the next sprint to assist automated CI/CD testing.
