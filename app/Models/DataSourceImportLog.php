<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Game;

class DataSourceImportLog extends Model
{
    const EVENT_ADDED    = 'added';
    const EVENT_UPDATED  = 'updated';
    const EVENT_DELISTED = 'delisted';
    const EVENT_CONFLICT = 'conflict';

    protected $table = 'data_source_import_log';

    public $timestamps = false;

    protected $fillable = [
        'run_id', 'source_id', 'link_id', 'game_id', 'event_type', 'changed_fields', 'created_at'
    ];

    protected $casts = [
        'changed_fields' => 'array',
        'created_at' => 'datetime',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
