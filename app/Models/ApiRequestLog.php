<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    // API version markers, recorded so V1 (legacy) traffic is clearly
    // distinguishable from later versions in the log.
    const VERSION_V1 = 'V1';

    protected $table = 'api_request_log';

    protected $fillable = [
        'api_version',
        'method',
        'path',
        'status_code',
        'token_id',
        'ip',
        'duration_ms',
    ];
}
