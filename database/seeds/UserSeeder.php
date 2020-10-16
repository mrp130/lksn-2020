<?php

use App\Division;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $divisions = ['Payment', 'Procurement', 'IT', 'Finance'];
        $totals = [5, 3, 7, 4];

        for($i = 0; $i < 4; $i++) {
            $division = Division::create([
                'name' => $divisions[$i],
            ]);

            for($j = 0; $j < $totals[$i]; $j++) {
                $username = strtolower($division->name) . '_' . ($j+1);
                $division->users()->create([
                    'username' => $username,
                    'password' => bcrypt($username),
                    'role' => 'user',
                ]);
            }
        }

        $division = Division::create([
            'name' => "HR",
        ]);

        for($i = 0; $i < 3; $i++) {
            $username = 'hr_' . ($i+1);
            $division->users()->create([
                'username' => $username,
                'password' => bcrypt($username),
                'role' => 'admin',
            ]);
        }
    }
}
