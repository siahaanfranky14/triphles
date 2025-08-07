<?php
namespace App\Helpers;

class FuzzySugeno
{
    private $rules = [];

    public function __construct()
    {
        $this->initializeRules();
    }

    // Fungsi keanggotaan untuk suhu
    private function suhuRendah($value)
    {
        if ($value <= 0) return 1;
        if ($value <= 27) return (27 - $value) / 27;
        return 0;
    }

    private function suhuSedang($value)
    {
        if ($value <= 25) return 0;
        if ($value <= 35) return ($value - 25) / 10;
        if ($value <= 40) return (40 - $value) / 5;
        return 0;
    }

    private function suhuTinggi($value)
    {
        if ($value <= 37) return 0;
        if ($value <= 50) return ($value - 37) / 13;
        return 1;
    }

    // Fungsi keanggotaan untuk pH tanah
    private function phAsam($value)
    {
        if ($value <= 0) return 1;
        if ($value <= 7) return (7 - $value) / 7;
        return 0;
    }

    private function phNetral($value)
    {
        if ($value <= 5) return 0;
        if ($value <= 7) return ($value - 5) / 2;
        if ($value <= 9) return (9 - $value) / 2;
        return 0;
    }

    private function phBasa($value)
    {
        if ($value <= 7) return 0;
        if ($value <= 14) return ($value - 7) / 7;
        return 1;
    }

    // Fungsi keanggotaan untuk kelembaban tanah
    private function kelembabanRendah($value)
    {
        if ($value >= 100) return 1;
        if ($value >= 50) return (100 - $value) / 50;
        return 0;
    }

    private function kelembabanSedang($value)
    {
        if ($value <= 25) return 0;
        if ($value <= 50) return ($value - 25) / 25;
        if ($value <= 75) return (75 - $value) / 25;
        return 0;
    }

    private function kelembabanTinggi($value)
    {
        if ($value >= 50) return 0;
        if ($value > 0) return (50 - $value) / 50;
        return 1;
    }

    // Inisialisasi aturan fuzzy
    private function initializeRules()
    {
        $rules = [
            ['rendah', 'asam', 'rendah', 'buruk'],
            ['rendah', 'asam', 'sedang', 'buruk'],
            ['rendah', 'asam', 'tinggi', 'buruk'],
            ['rendah', 'netral', 'rendah', 'buruk'],
            ['rendah', 'netral', 'sedang', 'baik'],
            ['rendah', 'netral', 'tinggi', 'baik'],
            ['rendah', 'basa', 'rendah', 'buruk'],
            ['rendah', 'basa', 'sedang', 'baik'],
            ['rendah', 'basa', 'tinggi', 'baik'],

            ['sedang', 'asam', 'rendah', 'buruk'],
            ['sedang', 'asam', 'sedang', 'buruk'],
            ['sedang', 'asam', 'tinggi', 'baik'],
            ['sedang', 'netral', 'rendah', 'buruk'],
            ['sedang', 'netral', 'sedang', 'baik'],
            ['sedang', 'netral', 'tinggi', 'sangat_baik'],
            ['sedang', 'basa', 'rendah', 'buruk'],
            ['sedang', 'basa', 'sedang', 'baik'],
            ['sedang', 'basa', 'tinggi', 'sangat_baik'],

            ['tinggi', 'asam', 'rendah', 'buruk'],
            ['tinggi', 'asam', 'sedang', 'baik'],
            ['tinggi', 'asam', 'tinggi', 'baik'],
            ['tinggi', 'netral', 'rendah', 'baik'],
            ['tinggi', 'netral', 'sedang', 'sangat_baik'],
            ['tinggi', 'netral', 'tinggi', 'sangat_baik'],
            ['tinggi', 'basa', 'rendah', 'baik'],
            ['tinggi', 'basa', 'sedang', 'sangat_baik'],
            ['tinggi', 'basa', 'tinggi', 'sangat_baik'],
        ];

        foreach ($rules as $rule) {
            $this->addRule(...$rule);
        }
    }

    // Tambahkan aturan fuzzy
    private function addRule($suhu, $ph_tanah, $kelembaban_tanah, $output)
    {
        $this->rules[] = compact('suhu', 'ph_tanah', 'kelembaban_tanah', 'output');
    }

    // Implementasi inferensi fuzzy
    public function evaluate($suhu, $ph_tanah, $kelembaban_tanah)
    {
        // Validasi input
        if ($suhu < 0 || $suhu > 100 || $ph_tanah < 0 || $ph_tanah > 14 || $kelembaban_tanah < 0 || $kelembaban_tanah > 100) {
            throw new \InvalidArgumentException("Input values out of range.");
        }

        $suhuRendah = $this->suhuRendah($suhu);
        $suhuSedang = $this->suhuSedang($suhu);
        $suhuTinggi = $this->suhuTinggi($suhu);

        $phAsam = $this->phAsam($ph_tanah);
        $phNetral = $this->phNetral($ph_tanah);
        $phBasa = $this->phBasa($ph_tanah);

        $kelembabanRendah = $this->kelembabanRendah($kelembaban_tanah);
        $kelembabanSedang = $this->kelembabanSedang($kelembaban_tanah);
        $kelembabanTinggi = $this->kelembabanTinggi($kelembaban_tanah);

        $results = [];

        foreach ($this->rules as $rule) {
            $suhuDegree = ${'suhu' . ucfirst($rule['suhu'])};
            $phDegree = ${'ph' . ucfirst($rule['ph_tanah'])};
            $kelembabanDegree = ${'kelembaban' . ucfirst($rule['kelembaban_tanah'])};

            $degree = min($suhuDegree, $phDegree, $kelembabanDegree);

            if ($degree > 0) {
                $results[] = ['output' => $rule['output'], 'degree' => $degree];
            }
        }

        $crispValue = $this->combineOutputs($results);
        return $this->categorizeOutput($crispValue);
    }

    // Kombinasi output aturan (menggunakan metode centroid)
    private function combineOutputs($results)
    {
        $numerator = 0;
        $denominator = 0;

        foreach ($results as $result) {
            $outputValue = $this->getOutputValue($result['output']);
            $degree = $result['degree'];

            $numerator += $degree * $outputValue;
            $denominator += $degree;
        }

        return ($denominator == 0) ? 0 : $numerator / $denominator;
    }

    // Dapatkan nilai output berdasarkan kategori output
    private function getOutputValue($output)
    {
        switch ($output) {
            case 'buruk':
                return 25;
            case 'baik':
                return 50;
            case 'sangat_baik':
                return 75;
            default:
                return 0;
        }
    }

    // Kategorikan output crisp menjadi label
    private function categorizeOutput($crispValue)
    {
        if ($crispValue < 35) {
            return 'buruk';
        } elseif ($crispValue < 65) {
            return 'baik';
        } else {
            return 'sangat_baik';
        }
    }
}
