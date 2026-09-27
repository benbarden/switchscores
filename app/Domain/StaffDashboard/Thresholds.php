<?php

namespace App\Domain\StaffDashboard;

/**
 * When a staff dashboard tile turns amber or red. Agreed with Ben 2026-09-27 (#163, decisions 6
 * and 13). Kept in one place so the rules can be read and changed without hunting through Twig.
 *
 * Ages are in days since the oldest waiting item arrived. A null means that tile has no such state.
 */
class Thresholds
{
    const REVIEW_DRAFTS_AMBER_DAYS = 3;
    const REVIEW_DRAFTS_RED_DAYS = 7;

    const QUICK_REVIEWS_AMBER_DAYS = 3;
    const QUICK_REVIEWS_RED_DAYS = 7;

    const FEATURED_GAMES_AMBER_DAYS = 7;
    const FEATURED_GAMES_RED_DAYS = null;

    const CONTACT_AMBER_DAYS = 3;
    const CONTACT_RED_DAYS = null;

    // A Live feed not imported for this long has missed a nightly run.
    const FEED_STALE_HOURS = 26;
    // A Live feed matching fewer titles than this (percent) needs a look at its match rule.
    const FEED_MATCH_RATE_AMBER = 50;

    // A nightly job whose latest trace is older than this has missed a run.
    const JOBS_STALE_HOURS = 26;
}
