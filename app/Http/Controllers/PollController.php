<?php

namespace App\Http\Controllers;

use App\Choice;
use App\Poll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PollController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        return Poll::orderBy('created_at', 'DESC')->get();
    }

    public function store(Request $request)
    {
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

        $poll = Poll::create([
            'title' => $title,
            'description' => $description,
            'deadline' => $deadline,
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
        $poll = Poll::find($id);
        if($poll == null) {
            abort(422, "invalid poll");
        }

        return $poll->delete();
    }

    public function vote($id, $choice_id)
    {
        $user = Auth::user();
        $choice = Choice::find($choice_id);
        if($choice == null) {
            abort(422, "invalid choice");
        }

        $poll = Poll::find($id);

        if($poll == null || $poll->id != $choice->poll_id) {
            abort(422, 'invalid poll');
        }

        if($poll->deadline < Carbon::now()) {
            abort(422, 'voting deadline');
        }

        if($poll->isVoted()) {
            abort(422, 'already voted');
        }

        $choice->users()->attach($user->id);
        return response()->json(['message' => 'vote success']);
    }
}
