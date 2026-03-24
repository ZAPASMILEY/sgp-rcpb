<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annees', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('annee')->unique();
            $table->timestamps();
        });

        $timestamp = now();
        $years = collect([(int) $timestamp->year]);

        foreach ([
            ['table' => 'entites', 'column' => 'created_at'],
            ['table' => 'directions', 'column' => 'created_at'],
            ['table' => 'services', 'column' => 'created_at'],
            ['table' => 'agents', 'column' => 'created_at'],
            ['table' => 'objectifs', 'column' => 'date'],
            ['table' => 'evaluations', 'column' => 'date_debut'],
        ] as $source) {
            if (! Schema::hasTable($source['table']) || ! Schema::hasColumn($source['table'], $source['column'])) {
                continue;
            }

            $values = DB::table($source['table'])
                ->whereNotNull($source['column'])
                ->pluck($source['column']);

            foreach ($values as $value) {
                $years->push((int) Carbon::parse($value)->year);
            }
        }

        $payload = $years
            ->filter(fn (mixed $year): bool => is_numeric($year))
            ->map(fn (mixed $year): int => (int) $year)
            ->unique()
            ->sort()
            ->values()
            ->map(fn (int $year): array => [
                'annee' => $year,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])
            ->all();

        if ($payload !== []) {
            DB::table('annees')->insert($payload);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('annees');
    }
};