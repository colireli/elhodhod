<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class BaseCountryCode extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            'group' =>  'general',
            'name' => 'base_country_code',
            'locked' => 0,
            'payload' => '1'
        ]);

    }
}
