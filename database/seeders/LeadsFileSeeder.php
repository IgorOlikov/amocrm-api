<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use function PHPUnit\Framework\stringContains;

class LeadsFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $i = 0;
        while ($i < 50) {

            $priceRandomizer = fake()->boolean();
            $costPriceRandomizer = fake()->boolean();

            $leads[] = [
                'name' => Str::random(10),
                'price' => $priceRandomizer ? null : mt_rand(1,1000),
                'cost_price' => $costPriceRandomizer ? null : (string)mt_rand(1,1000),
            ];
            $i++;
        }



       $jsonLeads  = json_encode($leads);

        $file = fopen(base_path() . '/testleads.txt','w', true);
        fwrite($file, $jsonLeads);
        fclose($file);
    }
}
