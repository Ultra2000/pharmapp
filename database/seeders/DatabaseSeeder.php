<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PharmacyInfo;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Création des rôles
        $adminRole = Role::create(['name' => 'admin']);
        $vendeurRole = Role::create(['name' => 'vendeur']);

        // Création de l'administrateur
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Création d'un vendeur
        $vendeur = User::create([
            'name' => 'Vendeur',
            'email' => 'vendeur@example.com',
            'password' => Hash::make('password'),
        ]);
        $vendeur->assignRole('vendeur');

        // Création des fournisseurs
        $suppliers = [
            [
                'name' => 'Pharmacie Plus',
                'contact_name' => 'Jean Martin',
                'phone' => '01 23 45 67 89',
                'email' => 'contact@pharmacieplus.fr',
                'address' => '1 rue de la Santé, 75000 Paris',
            ],
            [
                'name' => 'MediStock',
                'contact_name' => 'Marie Dubois',
                'phone' => '01 98 76 54 32',
                'email' => 'contact@medistock.fr',
                'address' => '42 avenue des Médicaments, 69000 Lyon',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            $supplier = Supplier::create($supplierData);
            
            // Création de produits pour chaque fournisseur
            $forms = ['comprimé', 'gélule', 'sirop', 'pommade'];
            for ($i = 1; $i <= 5; $i++) {
                Product::create([
                    'name' => "Médicament {$supplier->name} {$i}",
                    'dci' => "DCI Médicament {$i}",
                    'dosage' => rand(100, 1000) . "mg",
                    'form' => $forms[array_rand($forms)],
                    'barcode' => (string)rand(1000000000000, 9999999999999),
                    'purchase_price' => rand(500, 2000) / 100,
                    'sale_price' => rand(1000, 5000) / 100,
                    'stock' => rand(10, 100),
                    'expiry_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                    'lot_number' => 'LOT' . strtoupper(substr(md5(rand()), 0, 8)),
                    'supplier_id' => $supplier->id,
                ]);
            }
        }

        // Création des informations de la pharmacie
        PharmacyInfo::create([
            'name' => 'Pharmacie Example',
            'address' => '1 rue Example',
            'zip' => '75000',
            'city' => 'Paris',
            'phone' => '01 23 45 67 89',
            'email' => 'contact@pharmacie-example.fr',
            'siret' => '123 456 789 00012',
            'license_number' => 'LIC123456',
            'invoice_footer' => 'Merci de votre confiance',
        ]);
    }
}
