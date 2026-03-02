# Quotation Row Drag-and-Drop: Analysis and Phase Plan

## Request summary
Client request: enable drag-and-drop row reordering for quotation items.

## Project structure snapshot
- Backend: Laravel (`app/`, `routes/`, `database/`).
- Quotation entry UI: Blade templates in `resources/views/sell/` and `resources/views/sale_pos/`.
- Quotation row behavior: `public/js/pos.js` (not `public/js/purchase.js`).

## Relevant files
- `resources/views/sell/create.blade.php`
- `resources/views/sell/edit.blade.php`
- `resources/views/sale_pos/product_row.blade.php`
- `public/js/pos.js`
- `app/Http/Controllers/SellPosController.php`
- `app/Utils/TransactionUtil.php`
- `database/migrations/2017_11_20_063603_create_transaction_sell_lines.php`

## Findings (current issues)
1. No drag-and-drop handler exists on quotation rows (`#pos_table tbody`).
2. Row inputs are index-based (`products[row_index][...]`), so reorder must reindex input names to keep payload consistent.
3. Existing quotation edit flow does not persist custom row order:
- `transaction_sell_lines` has no dedicated order column.
- update logic edits rows by `transaction_sell_lines_id` and does not store visual position.
- edit query for sell lines has no explicit `orderBy` for a user-defined position.
4. Without explicit persistence, order behavior is ID/database-order driven and can mismatch user drag order.

## Recommended fix approach
1. Add persistent ordering in backend:
- Create migration to add `sort_order` integer to `transaction_sell_lines`.
- Save/update `sort_order` for main sell lines during create and update.
- Keep combo/modifier child lines excluded from user drag order logic.
2. Apply order consistently on read:
- In edit/loading queries, sort by `sort_order` then `id`.
- In quotation receipt/detail pipelines, ensure lines are emitted in the same order.
3. Add frontend drag-and-drop on quotation table:
- Use jQuery UI `sortable` on `#pos_table tbody`.
- Add a drag handle cell/icon in each row.
- On drop, reindex all `products[...]` field names and row modal IDs/targets.
- Recompute row serial/display state and keep existing calculations intact.

## Phased implementation plan
### Phase 1: Persistence foundation
- Add `sort_order` schema migration.
- Update create/update sell line utilities to write `sort_order`.
- Update queries/read paths to honor `sort_order`.

### Phase 2: Drag-and-drop UX
- Add drag handle UI in row template.
- Enable sortable behavior in `pos.js` for quotation/sell entry pages.
- Reindex row inputs and modal bindings after drag.

### Phase 3: Integration hardening
- Validate create, edit, save, reload, and print/download quotation order consistency.
- Validate row add/remove still works with reordered lists.
- Validate no regression for non-quotation direct sell flow.

## Acceptance checklist
- User can drag quotation rows up/down in entry screen.
- Saved quotation keeps same order after reopen.
- Printed/downloaded quotation shows same order.
- Existing row edit/remove/quantity/price updates still work.
- No functional dependency on `public/js/purchase.js` for this feature.

## Notes before development
- This feature should be implemented in the sell/quotation flow (`pos.js` + sell views), not purchase flow.
- Reindexing after drag is critical to avoid payload/index mismatch.
- `vendor.js` already includes sortable support, so no additional frontend package is required.
