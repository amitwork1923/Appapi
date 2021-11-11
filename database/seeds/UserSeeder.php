<?php

use Illuminate\Database\Seeder;
use App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $users = [
            [
                'name' => 'Amit',
                'user_name' => 'amitmeet',
                'email' => 'amit@yopmail.com',
                'password' => Hash::make('admin786'), // secret
                'user_role' => 1,
                'status' => 1,
                'created_at' => time(),
                //'updated_at' => time(),
               // 'registered_at' => time(),
            ],
            // [
            //     'name' => $faker->name,
            //     'email' => $faker->unique()->safeEmail,
            //     'password' => Hash::make('admin786'), // secret
            //     'role_id' => Role::where('name', 'sales')->first()->id,
            //     'status' => 1,
            //     'created_at' => time(),
            // ],
        ];

        
        foreach ($users as $val) {
           $createduser =  User::firstOrCreate($val);
           		$userrole = [
           			'user_id'=>$createduser->id,
           			'role_id'=>$createduser->role_id,
           			'status'=>'1',
           		];
        }
    }
}
