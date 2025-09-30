<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductAndSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [];
        for ($i = 1; $i <= 5; $i++) {
            $suppliers[$i] = \App\Models\Supplier::create([
                'name' => "Fournisseur $i",
                'contact_name' => "Contact $i",
                'phone' => "060000000$i",
                'email' => "fournisseur$i@pharma.com",
                'address' => "Adresse $i"
            ]);
        }

        for ($i = 1; $i <= 20; $i++) {
            \App\Models\Product::create([
                'name' => "Produit $i",
                'dci' => "DCI $i",
                'dosage' => "${i}00mg",
                'form' => 'Comprimé',
                'barcode' => str_pad($i, 13, '0', STR_PAD_LEFT),
                'purchase_price' => rand(5, 20),
                'sale_price' => rand(21, 40),
                'stock' => rand(0, 50),
                'expiry_date' => now()->addMonths(rand(1, 24)),
                'lot_number' => "LOT$i",
                'supplier_id' => $suppliers[rand(1, 5)]->id,
                'image' => null
            ]);
        }
    }
}
