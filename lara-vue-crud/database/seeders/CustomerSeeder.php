<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Doe',
                'phone_number' => '+1-555-0123',
                'email' => 'john.doe@example.com',
                'address_line_1' => '123 Main Street',
                'address_line_2' => 'Apt 4B',
                'country' => 'United States',
                'state' => 'California',
                'city' => 'Los Angeles',
            ],
            [
                'name' => 'Jane Smith',
                'phone_number' => '+1-555-0456',
                'email' => 'jane.smith@example.com',
                'address_line_1' => '456 Oak Avenue',
                'address_line_2' => null,
                'country' => 'United States',
                'state' => 'New York',
                'city' => 'New York City',
            ],
            [
                'name' => 'Mike Johnson',
                'phone_number' => '+1-555-0789',
                'email' => 'mike.johnson@example.com',
                'address_line_1' => '789 Pine Road',
                'address_line_2' => 'Suite 200',
                'country' => 'Canada',
                'state' => 'Ontario',
                'city' => 'Toronto',
            ],
            [
                'name' => 'Sarah Wilson',
                'phone_number' => '+44-20-1234-5678',
                'email' => 'sarah.wilson@example.com',
                'address_line_1' => '321 High Street',
                'address_line_2' => 'Flat 5',
                'country' => 'United Kingdom',
                'state' => 'England',
                'city' => 'London',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
