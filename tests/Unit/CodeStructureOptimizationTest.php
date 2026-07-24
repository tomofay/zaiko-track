<?php

namespace Tests\Unit;

use Tests\TestCase;

class CodeStructureOptimizationTest extends TestCase
{
    /** @test */
    public function peminjaman_controller_does_not_have_detail_peminjaman_all_call()
    {
        $content = file_get_contents(app_path('Http/Controllers/PeminjamanController.php'));
        $this->assertStringNotContainsString('DetailPeminjaman::all()', $content);
        $this->assertStringContainsString('DetailPeminjaman::exists()', $content);
    }

    /** @test */
    public function home_controller_has_limit_on_jadwal_kembali()
    {
        $content = file_get_contents(app_path('Http/Controllers/HomeController.php'));
        $this->assertStringContainsString('->limit(15)', $content);
    }

    /** @test */
    public function pembelian_controller_uses_withsum()
    {
        $content = file_get_contents(app_path('Http/Controllers/PembelianController.php'));
        $this->assertStringContainsString('withSum', $content);
    }

    /** @test */
    public function inventaris_controller_uses_bulk_query()
    {
        $content = file_get_contents(app_path('Http/Controllers/InventarisController.php'));
        $this->assertStringContainsString("whereIn('id_inventaris'", $content);
        $this->assertStringContainsString('pluck', $content);
    }

    /** @test */
    public function barang_controller_uses_wherein()
    {
        $content = file_get_contents(app_path('Http/Controllers/BarangController.php'));
        $this->assertStringContainsString('whereIn', $content);
    }

    /** @test */
    public function layout_uses_argon_minified_css()
    {
        $content = file_get_contents(resource_path('views/layouts/demo.blade.php'));
        $this->assertStringContainsString('argon-dashboard.min.css', $content);
    }

    /** @test */
    public function layout_removed_duplicate_bootstrap()
    {
        $content = file_get_contents(resource_path('views/layouts/demo.blade.php'));
        $this->assertStringNotContainsString('maxcdn.bootstrapcdn.com', $content);
        $this->assertStringNotContainsString('cdn.jsdelivr.net/npm/@popperjs', $content);
    }

    /** @test */
    public function layout_removed_fontawesome_kit_js()
    {
        $content = file_get_contents(resource_path('views/layouts/demo.blade.php'));
        $this->assertStringNotContainsString('kit.fontawesome.com', $content);
    }

    /** @test */
    public function layout_removed_github_buttons()
    {
        $content = file_get_contents(resource_path('views/layouts/demo.blade.php'));
        $this->assertStringNotContainsString('buttons.github.io', $content);
    }

    /** @test */
    public function layout_has_font_display_swap()
    {
        $content = file_get_contents(resource_path('views/layouts/demo.blade.php'));
        $this->assertStringContainsString('display=swap', $content);
    }

    /** @test */
    public function layout_still_has_datatables()
    {
        $content = file_get_contents(resource_path('views/layouts/demo.blade.php'));
        $this->assertStringContainsString('datatables.js', $content);
        $this->assertStringContainsString('datatables.css', $content);
    }

    /** @test */
    public function peminjaman_controller_imports_carbon()
    {
        $content = file_get_contents(app_path('Http/Controllers/PeminjamanController.php'));
        $this->assertStringContainsString('use Carbon\Carbon;', $content);
        $this->assertStringNotContainsString('Carbon\Carbon::', $content);
    }

    /** @test */
    public function rate_limiter_is_configured_for_web()
    {
        $content = file_get_contents(app_path('Providers/RouteServiceProvider.php'));
        $this->assertStringContainsString("RateLimiter::for('web'", $content);
    }

    /** @test */
    public function kernel_has_throttle_middleware_for_web()
    {
        $content = file_get_contents(app_path('Http/Kernel.php'));
        $this->assertStringContainsString("ThrottleRequests::class.':web'", $content);
    }

    /** @test */
    public function migration_indexes_file_exists()
    {
        $path = database_path('migrations/2026_07_21_000001_add_performance_indexes.php');
        $this->assertFileExists($path);
        $content = file_get_contents($path);
        $this->assertStringContainsString("index('status')", $content);
        $this->assertStringContainsString("index('id_barang')", $content);
        $this->assertStringContainsString("index('id_ruangan')", $content);
        $this->assertStringContainsString("index('id_jenis_barang')", $content);
        $this->assertStringContainsString("index('tgl_kembali')", $content);
    }

    /** @test */
    public function home_controller_uses_inventaris_barang_not_inventarisis()
    {
        $content = file_get_contents(app_path('Http/Controllers/HomeController.php'));
        $this->assertStringNotContainsString('inventarisis', $content);
        $this->assertStringContainsString('inventaris.barang', $content);
    }
}
