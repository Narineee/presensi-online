<?php

namespace Tests\Unit;

use App\Models\Penilaian;
use PHPUnit\Framework\TestCase;

class PenilaianTest extends TestCase
{
    public function test_predikat_scale_and_keterangan(): void
    {
        $testCases = [
            100 => ['A', 'Sangat Baik', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            95 => ['A', 'Sangat Baik', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            90 => ['A', 'Sangat Baik', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            89 => ['B', 'Baik', 'bg-blue-50 text-blue-700 border-blue-200'],
            85 => ['B', 'Baik', 'bg-blue-50 text-blue-700 border-blue-200'],
            80 => ['B', 'Baik', 'bg-blue-50 text-blue-700 border-blue-200'],
            79 => ['C', 'Cukup Baik', 'bg-amber-50 text-amber-700 border-amber-200'],
            75 => ['C', 'Cukup Baik', 'bg-amber-50 text-amber-700 border-amber-200'],
            70 => ['C', 'Cukup Baik', 'bg-amber-50 text-amber-700 border-amber-200'],
            69 => ['D', 'Kurang Baik', 'bg-orange-50 text-orange-700 border-orange-200'],
            65 => ['D', 'Kurang Baik', 'bg-orange-50 text-orange-700 border-orange-200'],
            60 => ['D', 'Kurang Baik', 'bg-orange-50 text-orange-700 border-orange-200'],
            59 => ['E', 'Tidak Baik', 'bg-rose-50 text-rose-700 border-rose-200'],
            50 => ['E', 'Tidak Baik', 'bg-rose-50 text-rose-700 border-rose-200'],
            0 => ['E', 'Tidak Baik', 'bg-rose-50 text-rose-700 border-rose-200'],
        ];

        foreach ($testCases as $score => [$expectedPredikat, $expectedKeterangan, $expectedBadge]) {
            $penilaian = new Penilaian(['total_nilai' => $score]);

            $this->assertEquals($expectedPredikat, $penilaian->predikat, "Failed asserting predikat for score {$score}");
            $this->assertEquals($expectedKeterangan, $penilaian->keterangan_predikat, "Failed asserting keterangan for score {$score}");
            $this->assertEquals($expectedBadge, $penilaian->badge_class, "Failed asserting badge for score {$score}");
        }
    }
}
