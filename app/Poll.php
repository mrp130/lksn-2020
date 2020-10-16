<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poll extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['updated_at', 'deleted_at'];
    protected $appends = ['creator', 'result'];
    protected $with = ['choices'];

    public function choices()
    {
        return $this->hasMany('App\Choice');
    }

    public function getCreatorAttribute()
    {
        return User::find($this->created_by)->username;
    }

    public function getResultAttribute()
    {
        if (!$this->canViewResult()) {
            return null;
        }

        return $this->pollResult();
    }

    public function pollResult()
    {
        $choices = $this->choices()->get()->keyBy('id')->all();
        foreach($choices as &$choice) {
            $choice['point'] = 0;
        }

        $divisions = Division::all();
        foreach ($divisions as $division) {
            $winners = $this->divisionResult($division);
            if(count($winners) == 0) continue;

            $point = 1/count($winners);
            foreach($winners as $id) {
                $choices[$id]['point'] += $point;
            }
        }

        return collect($choices)->flatten()->all();
    }

    public function divisionResult(Division $division)
    {
        $votes = Vote::where('poll_id', $this->id)
            ->groupBy('choice_id')
            ->where('division_id', $division->id)
            ->select('choice_id', DB::raw('count(1) as total'))
            ->orderBy(DB::raw('count(1)', 'DESC'))
            ->get();

        if(count($votes) == 0) return [];

        $max = $votes[0]['total'];
        $ids = [$votes[0]['choice_id']];

        for($i = 1; $i < count($votes); $i++) {
            $vote = $votes[$i];
            if($vote['total'] != $max) break;
            array_push($ids, $vote['choice_id']);
        }

        return $ids;
    }

    public function canViewResult()
    {
        return $this->isVoted() || $this->isDeadline() || auth()->user()->isAdmin();
    }

    public function isVoted()
    {
        $user_id = auth()->user()->id;

        $count = Vote::where('user_id', $user_id)
            ->where('poll_id', $this->id)
            ->count();

        return $count > 0;
    }

    public function isDeadline()
    {
        return $this->deadline < Carbon::now();
    }
}
