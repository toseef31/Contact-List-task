<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use Faker\Factory as Faker;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 50; $i++) {
            $name = $faker->name; // Generate a random name
            $initials = $this->getInitials($name); // Generate initials from the name

            Client::create([
                'name' => $name,
                'phone_number' => $faker->phoneNumber,  // Generate a random phone number
                'initials' => $initials,  // Set initials
            ]);
        }
    }

    private function getInitials(string $name): string
    {
        $words = explode(' ', $name); // Split name into words
        $initials = '';

        foreach ($words as $word) {
            $initials .= strtoupper($word[0]); // Take the first letter of each word and make it uppercase
        }

        return $initials;
    }
}
