<?php

namespace App\Http\Controllers;

use App\Choice;
use App\Poll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class PollController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        return Poll::all()->sortByDesc('created_at');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if(!$user->isAdmin()) {
            abort(401, 'admin only');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'choices' => 'required|array|min:2',
            'choices.*' => 'required|string|distinct'
        ]);

        $title = $request->input('title');
        $description = $request->input('description');
        $deadline = $request->input('deadline');
        $choices = $request->input('choices');

        $d = Carbon::parse($deadline);
        $deadline = $d->toRfc3339String();

        $poll = Poll::create([
            'title' => $title,
            'description' => $description,
            'deadline' => $deadline,
            'created_by' => $user->id,
        ]);

        foreach ($choices as $choice) {
            $poll->choices()->create([
                'choice' => $choice,
            ]);
        }

        return $poll;
    }

    public function show($id)
    {
        return Poll::find($id);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if(!$user->isAdmin()) {
            abort(401, 'admin only');
        }

        $poll = Poll::find($id);
        if($poll == null) {
            abort(422, "invalid poll");
        }

        return $poll->delete();
    }

    public function vote($id, $choice_id)
    {
        $user = Auth::user();

        if($user->isAdmin()) {
            abort(401, 'admin cannot vote');
        }

        $choice = Choice::find($choice_id);
        if($choice == null) {
            abort(422, "invalid choice");
        }

        $poll = Poll::find($id);

        if($poll == null || $poll->id != $choice->poll_id) {
            abort(422, 'invalid poll');
        }

        if($poll->isDeadline()) {
            abort(422, 'voting deadline');
        }

        if($poll->isVoted()) {
            abort(422, 'already voted');
        }

        $choice->users()->attach($user->id, ['division_id', $user->division_id]);
        return response()->json(['message' => 'vote success']);
    }
}
