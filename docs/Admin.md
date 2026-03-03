# Admin

This file manages the admin process for this documentation space. It is the working admin record for
documentation, review, and release coordination, while `SystemAdmin.md` is the authoritative project-level source.

## Responsibilities
- Maintain documentation governance and review notes.
- Ensure `docs/Features.md` and `docs/Issues.md` are updated.
- Coordinate phase transitions and releases.

## Date
2026-03-03

## Request / Instruction
- Track issue in `docs/Issues.md` and sync related docs.
- Commit solved issue documentation and deployment fix traceability.

## Scope
- Document incident `ISS-2026-03-03-01` and resolution details.
- Sync `docs/Project.md`, `docs/Plan.md`, and `docs/Phase.md` with resolved status.

## Acceptance Criteria
- Issue log includes root cause, repro, resolution, and commit reference.
- Related docs reflect deployment migration safeguard.

## Notes
- Resolved issue: `Unknown column 'sort_order' in 'ORDER BY'` in quotation/sales edit + print PDF flow.
- Deployment safeguard commit: `10a26b0` (`Run migrations during cPanel deploy`).
