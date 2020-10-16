<?php

use App\User;
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
            $division = $divisions[$i];
            for($j = 0; $j < $totals[$i]; $j++) {
                $username = strtolower($division) . '_' . ($j+1);
                User::create([
                    'username' => $username,
                    'password' => bcrypt($username),
                    'division' => $division,
                    'role' => 'user',
                ]);
            }
        }

        for($i = 0; $i < 3; $i++) {
            $username = 'hr_' . ($i+1);
            User::create([
                'username' => $username,
                'password' => bcrypt($username),
                'division' => 'HR',
                'role' => 'admin',
            ]);
        }
    }
}
