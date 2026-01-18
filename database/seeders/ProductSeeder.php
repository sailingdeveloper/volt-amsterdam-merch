<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * Standard clothing sizes.
     */
    protected const SIZE_CLOTHING = ['XS' => 10, 'S' => 15, 'M' => 20, 'L' => 15, 'XL' => 10, 'XXL' => 5];

    /**
     * One-size for scarves.
     */
    protected const SIZE_ONE = ['1' => 25];

    /**
     * Clothing description in English.
     */
    protected const DESCRIPTION_CLOTHING_EN = "Specially for our campaign in Amsterdam, we've designed hoodies, long-sleeves, and a scarf.

Not standard Volt merchandise, but exclusive to this campaign. Limited edition. Extra hip.

There are hoodies, long-sleeves, and scarves. Not only in purple, because that's not everyone's colour. Volt's values, fortunately, are. 💜";

    /**
     * Clothing description in Dutch.
     */
    protected const DESCRIPTION_CLOTHING_NL = "Speciaal voor onze campagne in Amsterdam hebben we hoodies, long-sleeves en een sjaal ontworpen.

Geen standaard Volt-merch, maar exclusief voor deze campagne. Limited edition. Dus extra hip.

Er zijn hoodies, long-sleeves en sjalen. Niet alleen in het paars, want dat is niet ieders kleur. Het gedachtegoed van Volt gelukkig wél. 💜";

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Copy seed images to storage.
        $this->copyImageToStorage();
        $products = [
            // Beer.
            [
                'name' => 'Voltreffer Sixpack',
                'name_nl' => 'Voltreffer Sixpack',
                'slug' => 'voltreffer-sixpack',
                'description' => "Voltreffer is the only beer that not only tastes great, but also contributes to a progressive, purple Amsterdam. Brewed with love together with our friends at Poesiat & Kater. Fancy a good beer and a good cause? This is your moment.

For every €30 donation, you receive one six-pack of Voltreffer.

€30 = 1 six-pack
€60 = 2 six-packs
…and so on 🍻",
                'description_nl' => "Voltreffer, het enige bier dat niet alleen lekker smaakt, maar ook bijdraagt aan een progressief, paars Amsterdam. Gebrouwen met liefde samen met onze vrienden van Poesiat & Kater.
Zin in een goed biertje én een goede daad? Dan is dit jouw moment.

Voor elke €30 donatie ontvang je één sixpack Voltreffer.

Dus:
€30 = 1 sixpack
€60 = 2 sixpacks
...enzovoort 🍻",
                'price' => 3000,
                'stock' => 100,
                'image' => 'products/voltreffer.jpg',
                'active' => true,
            ],
            // Bike bell.
            [
                'name' => 'Volt Bike Bell',
                'name_nl' => 'Volt-fietsbel',
                'slug' => 'volt-fietsbel',
                'description' => "Sometimes you just need to make yourself heard. On the street and figuratively.

With the purple Volt bike bell, you move forward, past left and right, always with an eye on common sense.

The Volt bike bells (€20) must be paid via a separate Tikkie. This is because the bells were personally purchased and pre-financed by Marijn (💜).

To get this bike bell, send an email to Eerke (eerke.steller@volteuropa.org) with the subject line \"Geef mij een belletje\". She will then send you a Tikkie, after which you can collect the bike bell from her.",
                'description_nl' => "Soms moet je gewoon even van je laten horen. Op straat én figuurlijk.

Met de paarse Volt-fietsbel ga je vooruit, langs links en rechts, maar altijd met het oog op gezond verstand.

De Volt-fietsbellen (€20) moeten met een aparte Tikkie worden betaald. Dit komt doordat de fietsbellen persoonlijk zijn ingekocht en voorgeschoten door Marijn (💜).

Om deze fietsbel te bemachtigen, stuur je een mailtje naar Eerke (eerke.steller@volteuropa.org) met als onderwerp \"Geef mij een belletje\".

Zij stuurt je vervolgens een Tikkie, waarna je de fietsbel bij haar kunt ophalen.",
                'price' => 2000,
                'stock' => 100,
                'image' => 'products/bell.jpg',
                'active' => true,
                'orderable' => false,
            ],
            // Hoodie Purple.
            [
                'name' => 'Hoodie - Purple',
                'name_nl' => 'Hoodie - Paars',
                'slug' => 'hoodie-paars',
                'description' => self::DESCRIPTION_CLOTHING_EN,
                'description_nl' => self::DESCRIPTION_CLOTHING_NL,
                'price' => 4900,
                'stock' => null,
                'sizes' => self::SIZE_CLOTHING,
                'image' => 'products/hoodie-purple-front.png',
                'images' => ['products/hoodie-purple-back.png'],
                'active' => true,
            ],
            // Hoodie White.
            [
                'name' => 'Hoodie - White',
                'name_nl' => 'Hoodie - Wit',
                'slug' => 'hoodie-wit',
                'description' => self::DESCRIPTION_CLOTHING_EN,
                'description_nl' => self::DESCRIPTION_CLOTHING_NL,
                'price' => 4900,
                'stock' => null,
                'sizes' => self::SIZE_CLOTHING,
                'image' => 'products/hoodie-white-front.jpg',
                'images' => ['products/hoodie-white-back.png'],
                'active' => true,
            ],
            // Long-sleeve shirt Purple.
            [
                'name' => 'Long-Sleeve Shirt - Purple',
                'name_nl' => 'Long-Sleeve Shirt - Paars',
                'slug' => 'long-sleeve-paars',
                'description' => self::DESCRIPTION_CLOTHING_EN,
                'description_nl' => self::DESCRIPTION_CLOTHING_NL,
                'price' => 2800,
                'stock' => null,
                'sizes' => self::SIZE_CLOTHING,
                'image' => 'products/shirt-purple-front.png',
                'images' => ['products/shirt-purple-back.png'],
                'active' => true,
            ],
            // Long-sleeve shirt White.
            [
                'name' => 'Long-Sleeve Shirt - White',
                'name_nl' => 'Long-Sleeve Shirt - Wit',
                'slug' => 'long-sleeve-wit',
                'description' => self::DESCRIPTION_CLOTHING_EN,
                'description_nl' => self::DESCRIPTION_CLOTHING_NL,
                'price' => 2800,
                'stock' => null,
                'sizes' => self::SIZE_CLOTHING,
                'image' => 'products/shirt-white-front.png',
                'images' => ['products/shirt-white-back.png'],
                'active' => true,
            ],
            // Scarf White.
            [
                'name' => 'Scarf - White',
                'name_nl' => 'Sjaal - Wit',
                'slug' => 'sjaal-wit',
                'description' => self::DESCRIPTION_CLOTHING_EN,
                'description_nl' => self::DESCRIPTION_CLOTHING_NL,
                'price' => 2500,
                'stock' => null,
                'sizes' => self::SIZE_ONE,
                'image' => 'products/scarf-white.png',
                'active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }

    /**
     * Copy seed images from database/seeders/images to storage.
     */
    protected function copyImageToStorage(): void
    {
        $seedImagePath = database_path('seeders/images');

        if (is_dir($seedImagePath) === false) {
            return;
        }

        // Ensure the products directory exists in storage.
        Storage::disk('public')->makeDirectory('products');

        // Copy all images from seed directory to storage.
        $files = glob($seedImagePath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        foreach ($files as $file) {
            $filename = basename($file);
            $destination = 'products/' . $filename;

            if (Storage::disk('public')->exists($destination) === false) {
                Storage::disk('public')->put($destination, file_get_contents($file));
            }
        }
    }
}
