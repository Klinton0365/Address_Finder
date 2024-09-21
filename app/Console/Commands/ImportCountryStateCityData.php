<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCountryStateCityData extends Command
{
    protected $signature = 'import:countries_states_cities';
    protected $description = 'Import countries, states, and cities from a JSON file';

    public function handle()
    {
        $jsonFilePath = storage_path('app/countries+states+cities.json');
        
        if (!file_exists($jsonFilePath)) {
            $this->error("File not found: $jsonFilePath");
            return;
        }

        $jsonData = json_decode(file_get_contents($jsonFilePath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Error decoding JSON: " . json_last_error_msg());
            return;
        }

        foreach ($jsonData as $country) {
            $this->info("Importing country: " . $country['name']);

            // Insert country
            $countryId = DB::table('countries')->insertGetId([
                'name' => $country['name'],
                'iso2' => $country['iso2'],
                'iso3' => $country['iso3'],
                'numeric_code' => $country['numeric_code'],
                'phone_code' => $country['phone_code'],
                'capital' => $country['capital'],
                'currency' => $country['currency'],
                'currency_name' => $country['currency_name'],
                'currency_symbol' => $country['currency_symbol'],
                'tld' => $country['tld'],
                'native' => $country['native'],
                'region' => $country['region'],
                'subregion' => $country['subregion'],
                'latitude' => $country['latitude'],
                'longitude' => $country['longitude'],
                'emoji' => $country['emoji'],
            ]);

            // Insert states if they exist
            foreach ($country['states'] as $state) {
                $stateId = DB::table('states')->insertGetId([
                    'name' => $state['name'],
                    'state_code' => $state['state_code'],
                    'country_id' => $countryId,
                    'latitude' => $state['latitude'],
                    'longitude' => $state['longitude'],
                ]);

                // Insert cities if they exist
                foreach ($state['cities'] as $city) {
                    DB::table('cities')->insert([
                        'name' => $city['name'],
                        'state_id' => $stateId,
                        'latitude' => $city['latitude'],
                        'longitude' => $city['longitude'],
                    ]);
                }
            }
        }

        $this->info("Import completed!");
    }
}
