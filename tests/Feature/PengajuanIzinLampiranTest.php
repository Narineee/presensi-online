<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengajuanIzinLampiranTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $userMagang;

    protected function setUp(): void
    {
        parent::setUp();

        $divisi = Divisi::create([
            'nama_divisi' => 'Bidang Komunikasi Publik',
            'nama_pimpinan' => 'Drs. Pimpinan',
            'nip_pimpinan' => '197001011995031001',
            'jabatan_pimpinan' => 'Kepala Bidang',
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing_1',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198001012005011001',
            'nama_lengkap' => 'Pembimbing Lapangan',
            'jabatan' => 'Pranata Komputer',
            'no_hp' => '08123456789',
        ]);

        $this->userMagang = Pengguna::create([
            'username' => 'hasnia_magang',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        Magang::create([
            'pengguna_id' => $this->userMagang->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'nama_lengkap' => 'Hasnia Putri',
            'no_induk' => '2026001',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
            'face_registered_at' => now(),
        ]);
    }

    public function test_form_create_menampilkan_lampiran_bukti_sebagai_field_wajib(): void
    {
        $response = $this->actingAs($this->userMagang)->get(route('izin.create'));

        $response->assertStatus(200);
        $response->assertSee('Lampiran Bukti (Surat Dokter / Dokumen Pendukung)', false);
        $response->assertSee('Wajib dilampirkan');
        $response->assertSee('name="bukti_file"', false);
        $response->assertSee('required', false);
    }

    public function test_pengajuan_izin_gagal_jika_bukti_lampiran_tidak_diisi(): void
    {
        $response = $this->actingAs($this->userMagang)->post(route('izin.store'), [
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-10-05',
            'tanggal_selesai' => '2026-10-06',
            'alasan' => 'Demam tinggi dan flu',
        ]);

        $response->assertSessionHasErrors(['bukti_file']);
        $this->assertEquals(
            'Bukti lampiran wajib diunggah.',
            session('errors')->first('bukti_file')
        );
        $this->assertDatabaseCount('pengajuan_izin', 0);
    }

    public function test_pengajuan_izin_berhasil_dengan_bukti_lampiran_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('surat_dokter.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->userMagang)->post(route('izin.store'), [
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-10-05',
            'tanggal_selesai' => '2026-10-06',
            'alasan' => 'Demam tinggi dan istirahat dokter',
            'bukti_file' => $file,
        ]);

        $response->assertRedirect(route('izin.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengajuan_izin', [
            'pengguna_id' => $this->userMagang->id,
            'jenis_izin' => 'sakit',
            'status_approval' => 'pending',
        ]);

        $izin = PengajuanIzin::first();
        $this->assertNotNull($izin->bukti_file);
        Storage::disk('public')->assertExists($izin->bukti_file);
    }

    public function test_pengajuan_izin_gagal_jika_format_lampiran_bukan_gambar_atau_pdf(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('dokumen.txt', 100, 'text/plain');

        $response = $this->actingAs($this->userMagang)->post(route('izin.store'), [
            'jenis_izin' => 'izin',
            'tanggal_mulai' => '2026-10-05',
            'tanggal_selesai' => '2026-10-05',
            'alasan' => 'Mengurus administrasi kampus',
            'bukti_file' => $file,
        ]);

        $response->assertSessionHasErrors(['bukti_file']);
        $this->assertEquals(
            'File bukti harus berformat JPG, PNG, atau PDF.',
            session('errors')->first('bukti_file')
        );
    }

    public function test_edit_permohonan_izin_bisa_mempertahankan_lampiran_lama(): void
    {
        $izin = PengajuanIzin::create([
            'pengguna_id' => $this->userMagang->id,
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-10-05',
            'tanggal_selesai' => '2026-10-06',
            'alasan' => 'Alasan sebelumnya yang cukup panjang',
            'bukti_file' => 'bukti-izin/sample.pdf',
            'status_approval' => 'pending',
        ]);

        $response = $this->actingAs($this->userMagang)->put(route('izin.update', $izin->id), [
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-10-05',
            'tanggal_selesai' => '2026-10-07',
            'alasan' => 'Alasan diperbarui menjadi lebih lama',
        ]);

        $response->assertRedirect(route('izin.index'));
        $this->assertDatabaseHas('pengajuan_izin', [
            'id' => $izin->id,
            'bukti_file' => 'bukti-izin/sample.pdf',
        ]);
    }

    public function test_form_create_menampilkan_struktur_stepper_dan_kartu_langkah_bertahap(): void
    {
        $response = $this->actingAs($this->userMagang)->get(route('izin.create'));

        $response->assertStatus(200);
        $response->assertSee('id="step-card-1"', false);
        $response->assertSee('id="step-card-2"', false);
        $response->assertSee('id="step-card-3"', false);
        $response->assertSee('id="step-card-4"', false);
        $response->assertSee('id="btn-submit-izin"', false);
        $response->assertSee('Lengkapi Semua Langkah');
        $response->assertSee('tracker-step-1', false);
        $response->assertSee('tracker-step-4', false);
    }
}
