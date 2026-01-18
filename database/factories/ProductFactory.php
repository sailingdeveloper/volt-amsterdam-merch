<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'name_nl' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'description_nl' => fake()->paragraph(),
            'price' => fake()->numberBetween(500, 10000),
            'image' => null,
            'stock' => fake()->numberBetween(0, 100),
            'active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
            'sizes' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function withSizes(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => null,
            'sizes' => [
                'XS' => 5,
                'S' => 10,
                'M' => 15,
                'L' => 10,
                'XL' => 5,
            ],
        ]);
    }

    public function withSizesOutOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => null,
            'sizes' => [
                'XS' => 0,
                'S' => 0,
                'M' => 0,
                'L' => 0,
                'XL' => 0,
            ],
        ]);
    }
}
