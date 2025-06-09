<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!function_exists('formatRupiah')) {
            function formatRupiah($angka)
            {
                // Pastikan $angka adalah string
                $angka = (string) $angka;
        
                // Menghapus karakter yang tidak diinginkan (Rp, $, atau koma)
                $angka = str_replace(['Rp', '$', ','], '', $angka);
        
                // Memastikan angka dalam bentuk float dan memformatnya
                return 'Rp ' . number_format((float) $angka, 0, ',', '.');
            }
        }
    }
}
