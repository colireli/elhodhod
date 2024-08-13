<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;


use Illuminate\Database\Seeder;

class whatsappseed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            'group' =>  'notifications',
            'name' => 'whatsapp',
            'locked' => 0,
            'payload' => "false"
        ]);
    }
}
