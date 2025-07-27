<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::unprepared(file_get_contents(database_path('sql/countries.sql')));
        $cities_sql_seeder_query = file_get_contents(database_path('sql/cities.sql'));
        $cities_sql_seeder_query = str_replace("'0000-00-00 00:00:00'", "'2014-01-01 12:01:01'", $cities_sql_seeder_query);
        DB::unprepared($cities_sql_seeder_query);
    }
}
