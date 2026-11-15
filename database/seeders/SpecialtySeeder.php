<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Терапевт' => 'Первичный приём взрослых пациентов',
            'Хирург' => 'Хирургические заболевания',
            'Кардиолог' => 'Заболевания сердца и сосудов',
            'Невролог' => 'Заболевания нервной системы',
            'Офтальмолог' => 'Заболевания глаз',
            'Оториноларинголог' => 'Заболевания уха, горла и носа',
            'Эндокринолог' => 'Заболевания эндокринной системы',
            'Дерматолог' => 'Заболевания кожи',
        ];

        foreach ($items as $name => $description) {
            Specialty::create(compact('name', 'description'));
        }
    }
}
