<?php

namespace Tests\Feature;

use App\Models\HariLibur;
use App\Models\Pengguna;
use App\Services\HariLiburService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HariLiburTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'username' => 'admin_test',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_hari_libur_page(): void
    {
        $response = $this->get(route('admin.hari-libur.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_magang_user_cannot_access_hari_libur_page(): void
    {
        $magang = Pengguna::create([
            'username' => 'magang_test',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $response = $this->actingAs($magang)->get(route('admin.hari-libur.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_hari_libur_page_with_stats_and_list(): void
    {
        HariLibur::create([
            'tanggal' => '2026-01-01',
            'nama' => 'Tahun Baru 2026 Masehi',
            'jenis' => 'Hari Libur Nasional',
            'keterangan' => 'Tahun Baru 2026 Masehi',
            'sumber' => 'api',
        ]);

        HariLibur::create([
            'tanggal' => '2026-05-28',
            'nama' => 'Cuti Bersama Hari Raya',
            'jenis' => 'Cuti Bersama',
            'keterangan' => 'Cuti Bersama',
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.hari-libur.index', ['tahun' => 2026]));

        $response->assertStatus(200);
        $response->assertSee('Master Data Hari Libur &amp; Cuti Bersama', false);
        $response->assertSee('Tahun Baru 2026 Masehi');
        $response->assertSee('Cuti Bersama Hari Raya');
        $response->assertSee('Sinkronkan dari API');
    }

    public function test_admin_can_create_hari_libur_manually(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.hari-libur.store'), [
            'tanggal' => '2026-08-17',
            'nama' => 'Hari Kemerdekaan RI ke-81',
            'jenis' => 'Hari Libur Nasional',
            'keterangan' => 'Libur Nasional Kemerdekaan',
        ]);

        $response->assertRedirect(route('admin.hari-libur.index', ['tahun' => 2026]));
        $response->assertSessionHas('success');

        $libur = HariLibur::whereDate('tanggal', '2026-08-17')->first();
        $this->assertNotNull($libur);
        $this->assertEquals('Hari Kemerdekaan RI ke-81', $libur->nama);
        $this->assertEquals('Hari Libur Nasional', $libur->jenis);
        $this->assertEquals('manual', $libur->sumber);
    }

    public function test_create_hari_libur_fails_on_duplicate_date(): void
    {
        HariLibur::create([
            'tanggal' => '2026-08-17',
            'nama' => 'Hari Kemerdekaan RI',
            'jenis' => 'Hari Libur Nasional',
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.hari-libur.store'), [
            'tanggal' => '2026-08-17',
            'nama' => 'Kemerdekaan RI Duplikat',
            'jenis' => 'Hari Libur Nasional',
        ]);

        $response->assertSessionHasErrors('tanggal');
        $this->assertEquals(1, HariLibur::whereDate('tanggal', '2026-08-17')->count());
    }

    public function test_admin_can_update_hari_libur_and_sets_sumber_to_manual(): void
    {
        $libur = HariLibur::create([
            'tanggal' => '2026-05-01',
            'nama' => 'Hari Buruh',
            'jenis' => 'Hari Libur Nasional',
            'sumber' => 'api',
            'external_id' => 'api-buruh-123',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.hari-libur.update', $libur->id), [
            'tanggal' => '2026-05-01',
            'nama' => 'Hari Buruh Internasional (May Day)',
            'jenis' => 'Hari Libur Nasional',
            'keterangan' => 'May Day 2026',
        ]);

        $response->assertRedirect(route('admin.hari-libur.index', ['tahun' => 2026]));
        $response->assertSessionHas('success');

        $libur->refresh();
        $this->assertEquals('Hari Buruh Internasional (May Day)', $libur->nama);
        $this->assertEquals('manual', $libur->sumber);
    }

    public function test_admin_can_delete_hari_libur(): void
    {
        $libur = HariLibur::create([
            'tanggal' => '2026-12-31',
            'nama' => 'Libur Tambahan',
            'jenis' => 'Cuti Bersama',
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.hari-libur.destroy', $libur->id));

        $response->assertRedirect(route('admin.hari-libur.index', ['tahun' => 2026]));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('hari_libur', [
            'id' => $libur->id,
        ]);
    }

    public function test_hari_libur_service_syncs_data_from_api_correctly(): void
    {
        Config::set('services.hari_libur.key', 'dummy_api_key_123');

        Http::fake([
            'https://use.apiindonesia.id/api/v1/libur?tahun=2026' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 'libur-1',
                        'date' => '2026-01-01',
                        'name' => 'Tahun Baru 2026 Masehi',
                        'type' => 'national',
                        'is_joint_leave' => 0,
                        'description' => 'Tahun baru masehi',
                        'year' => 2026,
                    ],
                    [
                        'id' => 'libur-2',
                        'date' => '2026-03-23',
                        'name' => 'Cuti Bersama Idul Fitri',
                        'type' => 'cuti_bersama',
                        'is_joint_leave' => 1,
                        'description' => 'Cuti bersama idul fitri',
                        'year' => 2026,
                    ],
                ],
            ], 200),
        ]);

        $service = app(HariLiburService::class);
        $result = $service->syncByYear(2026);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['total_api']);
        $this->assertEquals(2, $result['inserted']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, $result['skipped_manual']);

        $libur1 = HariLibur::whereDate('tanggal', '2026-01-01')->first();
        $this->assertNotNull($libur1);
        $this->assertEquals('Tahun Baru 2026 Masehi', $libur1->nama);
        $this->assertEquals('Hari Libur Nasional', $libur1->jenis);
        $this->assertEquals('api', $libur1->sumber);
        $this->assertEquals('libur-1', $libur1->external_id);

        $libur2 = HariLibur::whereDate('tanggal', '2026-03-23')->first();
        $this->assertNotNull($libur2);
        $this->assertEquals('Cuti Bersama Idul Fitri', $libur2->nama);
        $this->assertEquals('Cuti Bersama', $libur2->jenis);
        $this->assertEquals('api', $libur2->sumber);
        $this->assertEquals('libur-2', $libur2->external_id);
    }

    public function test_api_sync_does_not_overwrite_manual_holidays(): void
    {
        Config::set('services.hari_libur.key', 'dummy_api_key_123');

        // Data manual admin pada 17 Agustus 2026
        HariLibur::create([
            'tanggal' => '2026-08-17',
            'nama' => 'HUT RI ke-81 (Acara Khusus)',
            'jenis' => 'Hari Libur Nasional',
            'keterangan' => 'Kustom admin',
            'sumber' => 'manual',
        ]);

        Http::fake([
            'https://use.apiindonesia.id/api/v1/libur?tahun=2026' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 'libur-ri',
                        'date' => '2026-08-17',
                        'name' => 'Hari Kemerdekaan RI Versi API',
                        'is_joint_leave' => 0,
                    ],
                ],
            ], 200),
        ]);

        $service = app(HariLiburService::class);
        $result = $service->syncByYear(2026);

        $this->assertEquals(1, $result['skipped_manual']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, $result['inserted']);

        // Data manual tetap utuh
        $libur = HariLibur::whereDate('tanggal', '2026-08-17')->first();
        $this->assertEquals('HUT RI ke-81 (Acara Khusus)', $libur->nama);
        $this->assertEquals('manual', $libur->sumber);
        $this->assertEquals('Kustom admin', $libur->keterangan);
    }

    public function test_api_sync_fails_gracefully_when_api_server_returns_500(): void
    {
        Config::set('services.hari_libur.key', 'dummy_api_key_123');

        // Buat data libur existing
        HariLibur::create([
            'tanggal' => '2026-01-01',
            'nama' => 'Tahun Baru Existing',
            'jenis' => 'Hari Libur Nasional',
            'sumber' => 'api',
        ]);

        Http::fake([
            'https://use.apiindonesia.id/api/v1/libur?tahun=2026' => Http::response(['error' => 'Internal Server Error'], 500),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.hari-libur.sync'), [
            'tahun' => 2026,
        ]);

        $response->assertRedirect(route('admin.hari-libur.index', ['tahun' => 2026]));
        $response->assertSessionHas('error');

        // Pastikan data existing tidak terhapus
        $this->assertDatabaseHas('hari_libur', [
            'tanggal' => '2026-01-01',
            'nama' => 'Tahun Baru Existing',
        ]);
    }

    public function test_sync_console_command_runs_successfully(): void
    {
        Config::set('services.hari_libur.key', 'dummy_api_key_123');

        Http::fake([
            'https://use.apiindonesia.id/api/v1/libur?tahun=2026' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 'libur-10',
                        'date' => '2026-01-01',
                        'name' => 'Tahun Baru 2026',
                        'is_joint_leave' => 0,
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('app:sync-hari-libur 2026')
            ->expectsOutputToContain('Memulai sinkronisasi hari libur dari API...')
            ->expectsOutputToContain('Sinkronisasi hari libur selesai dengan sukses.')
            ->assertExitCode(0);

        $libur = HariLibur::whereDate('tanggal', '2026-01-01')->first();
        $this->assertNotNull($libur);
        $this->assertEquals('Tahun Baru 2026', $libur->nama);
    }
}
