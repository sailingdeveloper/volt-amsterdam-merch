<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allProduct = [
            [
                'name' => 'Volt Amsterdam T-Shirt',
                'name_nl' => 'Volt Amsterdam T-Shirt',
                'slug' => 't-shirt',
                'description' => 'Premium cotton t-shirt with the iconic Volt Amsterdam logo. Comfortable fit, perfect for showing your support for progressive European politics.',
                'description_nl' => 'Premium katoenen t-shirt met het iconische Volt Amsterdam logo. Comfortabele pasvorm, perfect om je steun voor progressieve Europese politiek te tonen.',
                'price' => 2500,
                'image' => 'products/t-shirt.jpg',
                'stock' => 100,
            ],
            [
                'name' => 'Volt Amsterdam Hoodie',
                'name_nl' => 'Volt Amsterdam Hoodie',
                'slug' => 'hoodie',
                'description' => 'Stay warm while making a statement. This cozy hoodie features the Volt logo and is made from sustainable materials.',
                'description_nl' => 'Blijf warm terwijl je een statement maakt. Deze gezellige hoodie heeft het Volt logo en is gemaakt van duurzame materialen.',
                'price' => 5500,
                'image' => 'products/hoodie.jpg',
                'stock' => 50,
            ],
            [
                'name' => 'Volt Amsterdam Cap',
                'name_nl' => 'Volt Amsterdam Pet',
                'slug' => 'cap',
                'description' => 'Classic cap with embroidered Volt logo. Adjustable strap for the perfect fit.',
                'description_nl' => 'Klassieke pet met geborduurd Volt logo. Verstelbare band voor de perfecte pasvorm.',
                'price' => 1800,
                'image' => 'products/cap.jpg',
                'stock' => 75,
            ],
            [
                'name' => 'Volt Amsterdam Tote Bag',
                'name_nl' => 'Volt Amsterdam Draagtas',
                'slug' => 'tote-bag',
                'description' => 'Eco-friendly cotton tote bag. Perfect for groceries, books, or everyday use. Features the Volt Amsterdam design.',
                'description_nl' => 'Milieuvriendelijke katoenen draagtas. Perfect voor boodschappen, boeken of dagelijks gebruik. Met het Volt Amsterdam ontwerp.',
                'price' => 1200,
                'image' => 'products/tote-bag.jpg',
                'stock' => 200,
            ],
            [
                'name' => 'Volt Sticker Pack',
                'name_nl' => 'Volt Stickerpakket',
                'slug' => 'sticker-pack',
                'description' => 'Set of 10 high-quality vinyl stickers. Weather-resistant and perfect for laptops, water bottles, and more.',
                'description_nl' => 'Set van 10 hoogwaardige vinyl stickers. Weerbestendig en perfect voor laptops, waterflessen en meer.',
                'price' => 500,
                'image' => 'products/stickers.jpg',
                'stock' => 500,
            ],
            [
                'name' => 'Volt Amsterdam Mug',
                'name_nl' => 'Volt Amsterdam Mok',
                'slug' => 'mug',
                'description' => 'Start your day with a cup of European unity. Ceramic mug with Volt branding, dishwasher safe.',
                'description_nl' => 'Begin je dag met een kopje Europese eenheid. Keramische mok met Volt branding, vaatwasserbestendig.',
                'price' => 1500,
                'image' => 'products/mug.jpg',
                'stock' => 150,
            ],
        ];

        foreach ($allProduct as $product) {
            Product::create($product);
        }
    }
}
