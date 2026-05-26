<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['multiple_choice', 'essay']);
        $isMultipleChoice = $type === 'multiple_choice';

        $options = $isMultipleChoice ? [
            'A' => fake()->sentence(3),
            'B' => fake()->sentence(3),
            'C' => fake()->sentence(3),
            'D' => fake()->sentence(3),
        ] : null;

        $correctAnswer = $isMultipleChoice 
            ? fake()->randomElement(['A', 'B', 'C', 'D']) 
            : fake()->paragraph();

        return [
            'type' => $type,
            'question' => fake()->sentence() . '?',
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'student_answer' => null,
            'is_answered' => false,
        ];
    }

    public function unanswered(): static
    {
        return $this->state(fn (array $attributes) => [
            'student_answer' => null,
            'is_answered' => false,
        ]);
    }

    public function answered(): static
    {
        return $this->state(function (array $attributes) {
            $isMultipleChoice = $attributes['type'] === 'multiple_choice';
            $isCorrect = fake()->boolean(40); // 40% chance correct

            if ($isMultipleChoice) {
                if ($isCorrect) {
                    $studentAnswer = $attributes['correct_answer'];
                } else {
                    $choices = ['A', 'B', 'C', 'D'];
                    $choices = array_filter($choices, fn($c) => $c !== $attributes['correct_answer']);
                    $studentAnswer = fake()->randomElement($choices);
                }
            } else {
                $studentAnswer = $isCorrect ? $attributes['correct_answer'] : fake()->paragraph();
            }

            return [
                'student_answer' => $studentAnswer,
                'is_answered' => true,
            ];
        });
    }
}
