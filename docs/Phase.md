# Current Phase

## Phase
Phase 3: Integration Hardening

## Status
completed

## Phase Summary
- Phase 1: Persistence Foundation — completed
- Phase 2: Drag-and-Drop UX — completed
- Phase 3: Integration Hardening — completed

## Notes
- Phase 1 completed:
  - Added `sort_order` migration for `transaction_sell_lines`.
  - Persisted `sort_order` during sell line create/update.
  - Applied ordered reads in edit and receipt line retrieval paths.
- Phase 2 completed:
  - Added draggable row handle in quotation sell row template.
  - Enabled sortable row behavior in `#pos_table` for quotation/draft sell forms.
  - Added row reindexing for input names and modal targets after reorder/remove/add.
- Phase 3 completed:
  - Ordered sell line load in edit flow for consistent ordering.
  - Drag handle icon updated to `fa fa-bars` to display in current Font Awesome build.
  - Confirmed drag-and-drop works for quotation rows.
  - Resolved production incident `ISS-2026-03-03-01` (`sort_order` missing in DB schema during edit/print PDF flow).
  - Added deploy migration execution in `.cpanel.yml` (`php artisan migrate --force`) to prevent repeat incidents.
