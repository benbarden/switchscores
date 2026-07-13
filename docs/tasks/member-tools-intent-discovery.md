# Member tools: Discovery Hub + Intent System

Shipped member-area features. Ported from Claude memory 2026-07-13 (memory system retired).
Verify details against current code before relying on file paths.

## Intent System (deferred actions) — shipped 2026-03-13

Lets public-page CTAs trigger member actions, handling auth + email verification gracefully.
Encourages signups by letting users start an action before having an account.

**Key files**
- `app/Enums/MemberIntent.php` — supported actions enum
- `app/Http/Controllers/Members/IntentController.php` — main controller
- `resources/views/members/intent/verify-prompt.twig` — verification prompt page
- `resources/views/public/games/page/member-collection.twig` — public page CTA partial

**Supported actions:** `wishlist-add` (adds immediately), `collection-add` (→ collection add form), `quick-review` (→ quick review form).

**URL pattern:** `/members/intent/{action}/{gameId}` (e.g. `/members/intent/wishlist-add/123`).
In public CTAs use `route('members.intent.handle', {'action': 'xxx', 'gameId': game.id})`.

**Flow:** public CTA → auth middleware redirects to login if needed (stores intended URL) →
after login/register user returns to intent URL → if unverified, verify-prompt page (intent
stored in session) → verification email embeds intent params in the signed URL (for reliability)
→ after verification the action executes.

## Discovery Hub + member tools — shipped 2026-03-08

**Discovery Hub Phase 1**
- Find me a game (`/members/find-game`, GameFinderController) — filters: category (hierarchical), console, rating, players, multiplayer, play modes; excludes owned/ignored/unranked (optional).
- Wishlist (`/members/wishlist`, WishlistController) — table `user_wishlist`; auto-removes when added to collection.
- Hidden games (`/members/ignored-games`, IgnoredGamesController) — table `user_ignored_games`; excluded from search results.

**Collection form redesign (#82/#83)**
- Add mode: play status + owned date (Skip/Today/Custom). Edit mode: all fields (play status, owned type, owned from, hours played).
- Play status as coloured button tiles (CSS `.play-status-btn` in `member-b5/custom.css`). Returns to referrer after adding from game finder.

**Open registration (#19)**
- Blocks new Twitter signups (keeps login for existing). Email verification is a manual trigger (user clicks to request). Unverified users are read-only. Email templates in `resources/views/emails/`.

**Member nav:** Dashboard | Find a game | Wishlist | Collection | Add to collection | Quick reviews | Settings.

**Next (Phase 2):** Saved searches — save named filter sets; dashboard shows new matches; future email notifications.
