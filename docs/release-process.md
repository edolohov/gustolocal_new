## WordPress Release Process (MVP)

Branching
- main: production
- develop: staging

Environments
- Staging: password-protected; auto-deploy from `develop`
- Production: manual approve/deploy from `main`

Workflow
1) Create feature branch from `develop`
2) PR → `develop`, preview on staging
3) QA checklist: pages render, forms work, no console errors, Core Web Vitals unchanged
4) Merge → `develop`; tag pre-release (e.g., v0.5.0-rc1)
5) Promote: PR `develop` → `main`, tag release (semver), changelog update

Coding standards
- No inline CSS; enqueue styles via theme
- Reusable blocks/ACF blocks for components
- Namespaced CSS classes (e.g., `site-` prefix + BEM)

Backups
- Automated daily + pre-release snapshot

Rollbacks
- Keep previous release tag; single-click revert via host or deploy tool




