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
                'active' => true,
            ],
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
                'active' => true,
                'orderable' => false,
            ],
            [
                'name' => 'Volt AMS Hoodie (White)',
                'name_nl' => 'Volt AMS Hoodie (Wit)',
                'slug' => 'volt-ams-hoodie-wit',
                'description' => "Specially for our campaign in Amsterdam, we've designed hoodies, long-sleeves, and a scarf.

Not standard Volt merchandise, but exclusive to this campaign. Limited edition. Extra hip.

There are hoodies, long-sleeves, and scarves. Not only in purple, because that's not everyone's colour. Volt's values, fortunately, are. 💜",
                'description_nl' => "Speciaal voor onze campagne in Amsterdam hebben we hoodies, long-sleeves en een sjaal ontworpen.

Geen standaard Volt-merch, maar exclusief voor deze campagne. Limited edition. Dus extra hip.

Er zijn hoodies, long-sleeves en sjalen. Niet alleen in het paars, want dat is niet ieders kleur. Het gedachtegoed van Volt gelukkig wél. 💜",
                'price' => 2500,
                'stock' => 50,
                'active' => true,
            ],
        ];

        foreach ($allProduct as $product) {
            Product::create($product);
        }
    }
}
