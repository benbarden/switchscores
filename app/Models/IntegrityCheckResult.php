<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrityCheckResult extends Model
{
    /**
     * @var string
     */
    protected $table = 'integrity_check_results';

    /**
     * @var array
     */
    protected $fillable = [
        'check_id', 'is_passing', 'failing_count', 'id_list'
    ];

    public function integrityCheck()
    {
        return $this->hasOne('App\Models\IntegrityCheck', 'id', 'check_id');
    }
}
