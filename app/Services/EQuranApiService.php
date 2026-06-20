<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * EQuranApiService
 * ==================
 * Wrapper untuk berkomunikasi dengan API EQuran.id v2.
 * Base URL resmi: https://equran.id/api/v2
 *
 * Endpoint yang dipakai:
 * - GET /surat            -> daftar 114 surah (untuk tahu jumlah ayat per surah)
 * - GET /surat/{nomor}    -> detail surah lengkap beserta semua ayatnya
 *
 * Catatan format response v2: dibungkus status wrapper
 * { "code": 200, "message": "...", "data": { ... } }
 * Service ini sudah menangani unwrapping tersebut, jadi method publiknya
 * langsung mengembalikan array data bersih (tanpa wrapper).
 *
 * Data daftar surah (114 surah) di-cache 24 jam karena datanya statis,
 * supaya tidak perlu fetch berkali-kali setiap generate soal.
 */
class EQuranApiService
{
    private const BASE_URL = 'https://equran.id/api/v2';

    private const CACHE_TTL_SECONDS = 60 * 60 * 24; // 24 jam

    /**
     * Ambil daftar seluruh 114 surah (nomor, nama, namaLatin, jumlahAyat, dst).
     * Dipakai untuk mengetahui rentang nomor ayat yang valid per surah
     * sebelum memilih nomor ayat acak.
     *
     * @return array<int, array{
     *     nomor:int, nama:string, namaLatin:string, jumlahAyat:int,
     *     tempatTurun:string, arti:string, deskripsi:string
     * }>
     */
    public function getAllSurat(): array
    {
        return Cache::remember('equran:all-surat', self::CACHE_TTL_SECONDS, function () {
            $response = Http::timeout(15)->get(self::BASE_URL . '/surat');

            if (!$response->successful()) {
                throw new RuntimeException(
                    'Gagal mengambil daftar surat dari EQuran.id API: HTTP ' . $response->status()
                );
            }

            $body = $response->json();

            // Response v2 dibungkus { code, message, data }
            return $body['data'] ?? [];
        });
    }

    /**
     * Ambil detail 1 surah lengkap dengan seluruh ayatnya.
     *
     * @return array{
     *     nomor:int, nama:string, namaLatin:string, jumlahAyat:int,
     *     tempatTurun:string, arti:string, deskripsi:string,
     *     ayat: array<int, array{nomorAyat:int, teksArab:string, teksLatin:string, teksIndonesia:string}>
     * }
     */
    public function getSurat(int $nomor): array
    {
        if ($nomor < 1 || $nomor > 114) {
            throw new RuntimeException("Nomor surat tidak valid: {$nomor}. Harus 1-114.");
        }

        return Cache::remember("equran:surat:{$nomor}", self::CACHE_TTL_SECONDS, function () use ($nomor) {
            $response = Http::timeout(15)->get(self::BASE_URL . "/surat/{$nomor}");

            if (!$response->successful()) {
                throw new RuntimeException(
                    "Gagal mengambil surat nomor {$nomor} dari EQuran.id API: HTTP " . $response->status()
                );
            }

            $body = $response->json();

            return $body['data'] ?? [];
        });
    }

    /**
     * Ambil satu ayat spesifik dari sebuah surah.
     *
     * @return array{nomorAyat:int, teksArab:string, teksLatin:string, teksIndonesia:string}
     */
    public function getAyat(int $suratNomor, int $ayatNomor): array
    {
        $surat = $this->getSurat($suratNomor);

        $ayat = collect($surat['ayat'] ?? [])
            ->firstWhere('nomorAyat', $ayatNomor);

        if (!$ayat) {
            throw new RuntimeException("Ayat {$ayatNomor} tidak ditemukan di surat {$suratNomor}.");
        }

        return $ayat;
    }

    /**
     * Ambil 1 ayat sepenuhnya acak dari seluruh Al-Qur'an (1-114, ayat 1 s.d. jumlahAyat).
     *
     * @return array{
     *     suratNomor:int, suratNama:string, suratNamaLatin:string,
     *     ayat: array{nomorAyat:int, teksArab:string, teksLatin:string, teksIndonesia:string}
     * }
     */
    public function getRandomAyat(): array
    {
        $allSurat = $this->getAllSurat();

        if (empty($allSurat)) {
            throw new RuntimeException('Daftar surat kosong, tidak bisa mengambil ayat acak.');
        }

        $randomSurat = $allSurat[array_rand($allSurat)];
        $randomAyatNumber = random_int(1, (int) $randomSurat['jumlahAyat']);

        $ayat = $this->getAyat((int) $randomSurat['nomor'], $randomAyatNumber);

        return [
            'suratNomor' => (int) $randomSurat['nomor'],
            'suratNama' => $randomSurat['nama'],
            'suratNamaLatin' => $randomSurat['namaLatin'],
            'jumlahAyat' => (int) $randomSurat['jumlahAyat'],
            'ayat' => $ayat,
        ];
    }
}
