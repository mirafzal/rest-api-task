<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'photo' => $this->faker->filePath(),
            'description' => $this->faker->text(),
            'price' => $this->faker->numberBetween(1, 10000) * 1000,
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
        ];
    }


}
