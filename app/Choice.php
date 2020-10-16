<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at', 'poll_id'];

    public function poll()
    {
        return $this->belongsTo('App\Poll');
    }
}
