<?php

use App\Division;
use App\Poll;
use App\User;
use App\Vote;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->simpleScenario();
        $this->floatScenario();
    }

    public function simpleScenario()
    {
        $hr = User::where('role', 'admin')->first();
        $poll = Poll::create([
            'title' => 'WFO/WFH',
            'description' => 'lebih produktif WFO atau WFH?',
            'deadline' => Carbon::now()->addMonth(),
            'created_by' => $hr->id,
        ]);

        $poll->choices()->createMany([
            [
                'choice' => 'WFO',
            ],
            [
                'choice' => 'WFH',
            ],
        ]);

        $choices = $poll->choices;
        $i = 0;
        foreach(Division::all() as $division)
        {
            if($division->name == 'HR') continue;

            foreach($division->users as $user)
            {
                Vote::create([
                    'user_id' => $user->id,
                    'poll_id' => $poll->id,
                    'choice_id' => $choices[$i % 2]->id,
                    'division_id' => $division->id,
                ]);

                $i++;
            }
        }
    }

    public function floatScenario()
    {
        $hr = User::where('role', 'admin')->skip(1)->first();

        $poll = Poll::create([
            'title' => 'makan gratis tiap hari apa?',
            'description' => 'akan diadakan makan siang gratis tiap minggunya di hari tertentu antara selasa/rabu/jumat',
            'deadline' => Carbon::now()->addMonth(),
            'created_by' => $hr->id,
        ]);

        $poll->choices()->createMany([
            [
                'choice' => 'selasa',
            ],
            [
                'choice' => 'rabu',
            ],
            [
                'choice' => 'jumat',
            ],
        ]);

        $div1 = Division::first();
        $div2 = Division::skip(1)->first();
        $div3 = Division::skip(2)->first();

        $users = $div1->users;
        $choices = $poll->choices;
        for($i = 0; $i < 4; $i++) {
            Vote::create([
                'user_id' => $users[$i]->id,
                'poll_id' => $poll->id,
                'choice_id' => $choices[1]->id,
                'division_id' => $div1->id,
            ]);
        }

        $users = $div2->users;
        $choices = $poll->choices;
        for($i = 0; $i < 3; $i++) {
            Vote::create([
                'user_id' => $users[$i]->id,
                'poll_id' => $poll->id,
                'choice_id' => $choices[$i]->id,
                'division_id' => $div2->id,
            ]);
        }

        $users = $div3->users;
        $choices = $poll->choices;
        for($i = 0; $i < 2; $i++) {
            Vote::create([
                'user_id' => $users[$i]->id,
                'poll_id' => $poll->id,
                'choice_id' => $choices[$i]->id,
                'division_id' => $div3->id,
            ]);
        }
    }
}
