<?php

namespace Nucleus\Scripture\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Nucleus\Scripture\Models\Passage;

class PassageFactory extends Factory
{
    protected $model = Passage::class;

    public function definition(): array
    {
        $books = ['GEN', 'PSA', 'PRO', 'MAT', 'JHN', 'ROM', 'REV'];
        $translations = ['KJV', 'ESV', 'NIV', 'NKJV'];

        $book = $this->faker->randomElement($books);
        $chapter = $this->faker->numberBetween(1, 20);
        $translation = $this->faker->randomElement($translations);

        return [
            'book' => $book,
            'chapter' => $chapter,
            'translation' => $translation,
            'content' => [
                'book' => $book,
                'chapter' => $chapter,
                'translation' => $translation,
                'reference' => "{$book} {$chapter}",
                'totalChapters' => 30,
                'verses' => [
                    ['number' => 1, 'text' => $this->faker->sentence()],
                    ['number' => 2, 'text' => $this->faker->sentence()],
                ],
            ],
            'fetched_at' => now(),
        ];
    }
}
