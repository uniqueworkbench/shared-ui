<?php

namespace UniqueWorkbench\SharedUi\ErrorTracking;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $table = 'error_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'handled' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }
}
