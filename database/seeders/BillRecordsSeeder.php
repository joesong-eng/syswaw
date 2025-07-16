<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillRecordsSeeder extends Seeder
{
    public function run(): void
    {
        $records = DB::table('machine_data_records')->where('machine_type', 'bill')->get();
        $machines = DB::table('machines')->where('machine_type', 'bill')->get()->keyBy('auth_key_id');

        foreach ($records as $record) {
            $machine = $machines[$record->auth_key_id] ?? null;
            $denominations = $machine && $machine->accepted_denominations ? json_decode($machine->accepted_denominations, true) : [100, 500, 1000];

            $denomination = $denominations[array_rand($denominations)];
            $count = rand(1, 5);
            $total = $denomination * $count;

            DB::table('bill_records')->insert([
                'record_id' => $record->id,
                'bill_denomination' => $denomination,
                'bill_count' => $count,
                'timestamp' => $record->timestamp,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('machine_data_records')->where('id', $record->id)->update(['credit_in' => $total]);
        }
    }
}
