# Plan

## Phase 1: Persistence Foundation
- Status: completed
- Add `sort_order` to `transaction_sell_lines`.
- Persist `sort_order` on create/update sell lines.
- Read sell lines ordered by `sort_order`, then `id`.

## Phase 2: Drag-and-Drop UX
- Status: completed
- Add drag handle column in quotation row table.
- Enable sortable rows in `#pos_table tbody`.
- Reindex row input names and modal IDs after reorder.

## Phase 3: Integration Hardening
- Status: completed
- Validate create/edit/save/reload order consistency.
- Validate print/download quotation order consistency.
- Validate no regressions in row calculations and row removal.
