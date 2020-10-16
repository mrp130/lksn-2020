<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poll extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['updated_at', 'deleted_at'];
    protected $appends = ['result', 'is_voted'];
    protected $with = ['choices'];

    public function choices()
    {
        return $this->hasMany('App\Choice');
    }

    public function getResultAttribute()
    {
        if(!$this->isVoted()) {
            return null;
        }

        $choice_ids = $this->choices->pluck('id')->all();

        $result = DB::table('choice_user')
            ->select('choice_id', DB::raw('count(1) as total'))
            ->groupBy('choice_id')
            ->whereIn('choice_id', $choice_ids)
            ->get();

        $result_id = $result->pluck('choice_id')->all();

        foreach($choice_ids as $id) {
            if(in_array($id, $result_id)) continue;
            $result->push([
                'choice_id' => $id,
                'total' => 0,
            ]);
        }

        return $result->sortBy('choice_id')->values()->all();
    }

    public function isVoted()
    {
        $user_id = auth()->user()->id;

        $choice_ids = $this->choices->pluck('id')->all();
        $count = DB::table('choice_user')
            ->where('user_id', $user_id)
            ->whereIn('choice_id', $choice_ids)
            ->count();

        return $count > 0;
    }

    public function getIsVotedAttribute()
    {
        return $this->isVoted();
    }
}
