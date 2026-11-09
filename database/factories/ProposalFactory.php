<?php

namespace Database\Factories;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => 'Agent '.fake()->words(3, true),
            'description' => fake()->paragraph(),
            'grade' => fake()->boolean() ? fake()->randomFloat(2, 60, 100) : null,
        ];
    }

    /**
     * Proposal yang belum dinilai.
     */
    public function ungraded(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade' => null,
        ]);
    }

    /**
     * Proposal yang sudah dinilai.
     */
    public function graded(float $grade = 85): static
    {
        return $this->state(fn (array $attributes) => [
            'grade' => $grade,
        ]);
    }
}
