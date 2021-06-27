<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IntegrityCheck extends Model
{
    const GAME_WRONG_RELEASE_YEAR = 'GameWrongReleaseYear';
    const GAME_NO_RELEASE_YEAR = 'GameNoReleaseYear';
    const GAME_MISSING_RANK = 'GameMissingRank';
    const GAME_NO_TITLE_HASHES = 'GameNoTitleHashes';
    const GAME_TITLE_HASH_MISMATCH = 'GameTitleHashMismatch';
    const TITLE_HASH_NO_GAME_MATCH = 'TitleHashNoGameMatch';
    const REVIEW_LINK_DUPLICATE = 'ReviewLinkDuplicate';

    const DESC_GAME_WRONG_RELEASE_YEAR = 'Games with the wrong ReleaseYear.';
    const DESC_GAME_NO_RELEASE_YEAR = 'Games with no ReleaseYear.';
    const DESC_GAME_MISSING_RANK = 'Games with >2 reviews but no rank.';
    const DESC_GAME_NO_TITLE_HASHES = 'Games with no title hashes.';
    const DESC_GAME_TITLE_HASH_MISMATCH = 'Games with a title that doesn\'t match a hash.';
    const DESC_TITLE_HASH_NO_GAME_MATCH = 'Title hashes that don\'t match a game.';
    const DESC_REVIEW_LINK_DUPLICATE = 'Review links that are duplicated (same game from the same review site).';

    /**
     * @var string
     */
    protected $table = 'integrity_checks';

    /**
     * @var array
     */
    protected $fillable = [
        'check_name', 'description', 'entity_name', 'is_passing', 'failing_count'
    ];

    public function results()
    {
        return $this->hasMany('App\IntegrityCheckResult', 'check_id', 'id');
    }
}
