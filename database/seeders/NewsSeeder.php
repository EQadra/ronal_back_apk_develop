<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\Company;
use Carbon\Carbon;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('slug', 'demo-company')->first();

        $news = [
            [
                'titulo' => 'Sube el precio del oro',
                'descripcion' => 'El oro alcanza máximos históricos.',
                'fecha_publicacion' => Carbon::now()->subDays(2),
            ],
            [
                'titulo' => 'Mercado latino en recuperación',
                'descripcion' => 'Metales con tendencia positiva.',
                'fecha_publicacion' => Carbon::now()->subDay(),
            ],
        ];

        foreach ($news as $n) {
            News::create(array_merge($n, [
                'company_id' => $company->id,
            ]));
        }
    }
}
