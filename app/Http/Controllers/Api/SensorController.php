<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FuzzySugeno;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SensorController extends Controller
{
    public function getLatestValueSensor()
    {
        try {
            $value = Sensor::orderBy('id', 'desc')->latest()->first();
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $value
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function getAllLocation()
    {
        try {
            $locations = Location::all();
            $data = [];

            foreach ($locations as $key => $location) {
                $data[] = [
                    'id' => $location->id,
                    'id_alat' => $location->sensor->id_alat,
                    'suhu' => $location->sensor->suhu,
                    'kelembapan' => $location->sensor->kelembapan,
                    'ph' => $location->sensor->ph,
                    'kondisi_tanah' => $location->sensor->kondisi_tanah,
                    'id_sensor' => $location->id_sensor,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 200);
        }
    }

    public function load_fuzzy()
    {
        $fuzzy = new FuzzySugeno();
        $res = $fuzzy->evaluate(25, 6, 75); // Menggunakan nilai pH yang valid (misalnya 7)

        return response()->json([
            'data' => $res
        ], 200);
    }

    public function savedataSensor(Request $request)
    {
        $input = $request->all();

        $fuzzy = new FuzzySugeno();
        $res = $fuzzy->evaluate($input['suhu'], $input['ph_tanah'], $input['sensor_soil']);

        // Tentukan rekomendasi berdasarkan hasil evaluasi
        switch ($res) {
            case 'buruk':
                $rekomendasi = "Periksa kembali suhu, pH, dan kelembaban; perbaiki pH tanah, lakukan penyiraman jika diperlukan, serta tambahkan bahan organik atau kompos untuk meningkatkan kesuburan.";
                break;

            case 'baik':
                $rekomendasi = "Pertahankan kondisi saat ini dengan melakukan pemantauan rutin terhadap kelembaban dan pH, serta berikan pupuk sesuai kebutuhan tanaman.";
                break;

            case 'sangat_baik':
                $rekomendasi = "Tanah dalam kondisi sangat baik dan siap ditanami; lanjutkan perawatan ringan, lakukan pemupukan berkala, dan pantau secara rutin untuk menjaga kualitas.";
                break;

            default:
                $rekomendasi = "Tidak ada rekomendasi yang tersedia.";
                break;
        }

        // Simpan data sensor dan rekomendasi
        $sensor = Sensor::create([
            'id_alat' => $input['id_alat'],
            'suhu' => $input['suhu'],
            'kelembapan' => $input['sensor_soil'],
            'ph' => $input['ph_tanah'],
            'kondisi_tanah' => $res,
            'rekomendasi' => $rekomendasi
        ]);

        // Simpan lokasi
        Location::create([
            'id_sensor' => $sensor->id,
            'longitude' => $input['longitude'],
            'latitude' => $input['latitude']
        ]);

        return response()->json([
            'message' => 'success'
        ], 200);
    }


    public function detaildatalocation(Request $request)
    {
        try {
            $id = $request->input('id');
        } catch (\Exception $e) {
        }
    }
}
