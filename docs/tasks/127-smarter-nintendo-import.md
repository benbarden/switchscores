# 127: Smarter Nintendo.co.uk Import

## Overview

Replace the current wipe-and-replace import strategy with a sync/upsert approach. Detect new, changed, and removed games. Log changes as an audit trail. Mark removed games as delisted rather than deleting their data.

**Closes:** #113 (API vs manual status conflicts surfaced via audit log)
**Lays groundwork for:** #116 (publisher change monitoring — change detection is in place)

---

## Scope

### Migrations
- [ ] Add `content_hash` (varchar 32, nullable) to `data_source_raw` — MD5 of `source_data_json`
- [ ] Add `last_seen_at` (timestamp, nullable) to `data_source_raw`
- [ ] Add `is_delisted` (boolean, default 0) to `data_source_raw`
- [ ] Add `is_delisted` (boolean, default 0) to `data_source_parsed`
- [ ] Create `data_source_import_log` table: `id`, `source_id`, `link_id`, `game_id` (nullable), `event_type` (enum: added/updated/delisted/conflict), `changed_fields` (JSON, nullable), `created_at`

### Importer (`Importer::importToDb`)
- [ ] Upsert by `source_id + link_id` instead of always inserting
- [ ] Compute MD5 hash of incoming JSON; compare against stored `content_hash`
- [ ] If hash unchanged: update `last_seen_at` only, skip re-parse
- [ ] If hash changed or new: update all fields including `content_hash` and `last_seen_at`
- [ ] After all batches: mark records with `last_seen_at` older than import start as `is_delisted = 1`

### Parser (`Parser::setDataSourceRaw`)
- [ ] Look up existing `DataSourceParsed` by `source_id + link_id` instead of always creating new
- [ ] Update parsed fields but preserve `game_id`
- [ ] Only called for new or changed raw records

### Import command (`ImportParseLink`)
- [ ] Remove `deleteBySourceId` calls for both raw and parsed
- [ ] Only pass new/changed raw records to the parser
- [ ] After parsing: for newly delisted raw records with a `game_id`, set `game_status = DELISTED` on the linked game; also set `format_digital = FORMAT_DELISTED` if currently set to `FORMAT_AVAILABLE`
- [ ] Re-listing conflict: if a record reappears in API but linked game has `game_status = DELISTED`, log a `conflict` event rather than auto-activating
- [ ] Write audit log entries: `added` (new link_id), `updated` (hash changed, log which fields changed), `delisted` (not seen this run), `conflict` (reappeared but game is delisted)

### Staff UI
- [ ] Show `is_delisted` badge on parsed item detail page
- [ ] Consider a staff page to view recent import log entries (could be a follow-up)

---

## Key Decisions

- **Re-listing**: log as conflict, leave re-activation to staff. Too risky to auto-activate (game may have been manually delisted for other reasons).
- **`format_digital`**: update to `FORMAT_DELISTED` only if currently `FORMAT_AVAILABLE` — don't overwrite manual overrides. Field may be deprecated in future.
- **Audit log granularity**: one row per change event per item per run. `changed_fields` stores a JSON array of field names that changed (for `updated` events).

---

## Out of Scope

- Staff UI for browsing import log (can be a follow-up task)
- Full publisher change monitoring (#116) — groundwork laid but not implemented here
- Auto re-activation of delisted games
