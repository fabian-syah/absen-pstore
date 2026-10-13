<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchCoordinateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi / memperbarui koordinat GPS (latitude, longitude, radius 50m) dan alamat terverifikasi
     * untuk seluruh cabang PStore tanpa mengubah ID cabang atau merusak data yang sudah ada di produksi.
     */
    public function run()
    {
        $branchCoordinates = [
            // 1. DKI JAKARTA (NON-PUSAT)
            'Pstore Slipi' => [
                'address' => 'Jl. Bang Pitung (Rawabelong) No. 356, Sukabumi Utara, Kebon Jeruk, Jakarta Barat',
                'lat' => -6.1952300,
                'lng' => 106.7865200,
                'radius' => 50,
            ],
            'Pstore Lenteng Agung' => [
                'address' => 'Jl. Raya Lenteng Agung (Lenteng Agung Pass, samping B-Quik Kuning / dekat AEON Mall Tanjung Barat), Jagakarsa, Jakarta Selatan',
                'lat' => -6.3262100,
                'lng' => 106.8378400,
                'radius' => 50,
            ],
            'Pstore Tebet' => [
                'address' => 'Jl. Prof. DR. Soepomo No. 143, Tebet Barat, Jakarta Selatan (Seberang Hotel Amaris / Samping Honda AHASS)',
                'lat' => -6.2372400,
                'lng' => 106.8488100,
                'radius' => 50,
            ],
            'Pstore Buaran' => [
                'address' => 'Jl. Raden Inten II No. 20, Klender, Duren Sawit, Jakarta Timur (Seberang SMKN 48 Jakarta)',
                'lat' => -6.2285100,
                'lng' => 106.9189200,
                'radius' => 50,
            ],
            'Pstore Kelapa Gading' => [
                'address' => 'Jl. Boulevard Barat Raya Blok XC No. 5, Kelapa Gading Barat, Jakarta Utara (Samping Eiger)',
                'lat' => -6.1524100,
                'lng' => 106.8920300,
                'radius' => 50,
            ],
            'Pstore Sunter' => [
                'address' => 'Jl. Danau Sunter Utara Blok F20 No. 23, Sunter Agung, Tanjung Priok, Jakarta Utara (Samping Bebek Kaleyo)',
                'lat' => -6.1382400,
                'lng' => 106.8647200,
                'radius' => 50,
            ],
            'Pstore Salemba' => [
                'address' => 'Jl. Percetakan Negara Raya No. 45, Cempaka Putih Barat, Jakarta Pusat (Samping Kimia Farma)',
                'lat' => -6.1884200,
                'lng' => 106.8587100,
                'radius' => 50,
            ],

            // 2. BANTEN & TANGERANG RAYA
            'Pstore Tangerang' => [
                'address' => 'Jl. KH. Hasyim Ashari, RT.003/RW.002, Poris Plawad Indah, Kec. Cipondoh, Kota Tangerang, Banten 15141',
                'lat' => -6.1865200,
                'lng' => 106.6668100,
                'radius' => 50,
            ],
            'Pstore Cikupa' => [
                'address' => 'Talagasari, Cikupa, Tangerang Regency, Banten 15710 (Plus Code: QGCF+HM, Jl. Raya Serang / Pantura, Seberang Bundaran Citra Raya, Samping Kopi Calf To Go Cikupa / RSIA Tiara)',
                'lat' => -6.2285359,
                'lng' => 106.5216401,
                'radius' => 50,
            ],
            'Pstore Serang Banten' => [
                'address' => 'Depan Hotel Flamenggo Samping Lotte Grosir, Jl. Raya Cilegon Drangong Serang - Banten No.Km.2, RT.5/RW.6, Kepandean, Kec. Serang, Kota Serang, Banten 42182',
                'lat' => -6.1158200,
                'lng' => 106.1412300,
                'radius' => 50,
            ],
            'Pstore Cilegon' => [
                'address' => 'Jl. Ahmad Yani, Sukmajaya, Kec. Jombang, Kota Cilegon, Banten 42411',
                'lat' => -6.0125400,
                'lng' => 106.0528100,
                'radius' => 50,
            ],
            'Pstore Pamulang' => [
                'address' => 'Jl. Siliwangi No. 14D, Pamulang Barat, Kec. Pamulang, Tangerang Selatan (Seberang Living Plaza Pamulang)',
                'lat' => -6.3424100,
                'lng' => 106.7328200,
                'radius' => 50,
            ],
            'Pstore Ciputat' => [
                'address' => 'Jl. Aria Putra No. 71, Serua Indah, Kec. Ciputat, Tangerang Selatan (Depan Alfamart Kavling)',
                'lat' => -6.3155100,
                'lng' => 106.7388400,
                'radius' => 50,
            ],

            // 3. JAWA BARAT
            'Pstore Bekasi' => [
                'address' => 'Jl. Pulo Sirih Utama No. 620, RT.001/RW.014, Jaka Setia, Kec. Bekasi Selatan, Kota Bekasi, Jawa Barat 17147 (Depan Mall Grand Galaxy Park / Maps: Pstore Bekasi)',
                'lat' => -6.2628410,
                'lng' => 106.9744120,
                'radius' => 50,
            ],
            'Pstore Cikarang' => [
                'address' => 'Jl. Tarum Barat II Blok E2 No. 25, Jayamukti, Kec. Cikarang Pusat, Kabupaten Bekasi, Jawa Barat 17530 (Area Pasar Bersih Cikarang Baru)',
                'lat' => -6.3188200,
                'lng' => 107.1662400,
                'radius' => 50,
            ],
            'Pstore Bogor' => [
                'address' => 'Jl. Dr. Sumeru No. 56C, Kebon Kelapa, Bogor Barat, Kota Bogor (Samping Bank BRI / Seberang SPBU Shell Semeru)',
                'lat' => -6.5835200,
                'lng' => 106.7792100,
                'radius' => 50,
            ],
            'Pstore Cibinong' => [
                'address' => 'Ruko Nirwana Golden Park Blok C No. 5, Jl. Pakansari, Pakansari, Kec. Cibinong, Kab. Bogor (Dekat Stadion Pakansari)',
                'lat' => -6.4855200,
                'lng' => 106.8354100,
                'radius' => 50,
            ],
            'Pstore Cibubur' => [
                'address' => 'Jl. Alternatif Cibubur No. 59A, Jatikarya, Kec. Jatisampurna, Kota Bekasi (±700 meter dari Mall Ciputra Cibubur)',
                'lat' => -6.3775100,
                'lng' => 106.9242300,
                'radius' => 50,
            ],
            'Pstore Depok' => [
                'address' => 'Jl. Raya Sawangan No. 38, Mampang, Pancoran Mas, Kota Depok (Dekat Mall DTC Sawangan)',
                'lat' => -6.3982100,
                'lng' => 106.7972400,
                'radius' => 50,
            ],
            'Pstore Depok Gotik' => [
                'address' => 'Jl. Raya Sawangan (Area Simpang Parung Bingung / Mampang), Pancoran Mas, Kota Depok',
                'lat' => -6.3985000,
                'lng' => 106.7950000,
                'radius' => 50,
            ],
            'Pstore Bandung Neo' => [
                'address' => 'Jl. Dipati Ukur No. 72E, Lebakgede, Kec. Coblong, Kota Bandung, Jawa Barat 40132 (Samping Hotel Neo / Dekat Kampus Unikom)',
                'lat' => -6.8885200,
                'lng' => 107.6162300,
                'radius' => 50,
            ],
            'Pstore Bandung Uber' => [
                'address' => 'Jl. A.H. Nasution No. 73, Pasanggrahan, Kec. Ujung Berung, Kota Bandung, Jawa Barat 40615',
                'lat' => -6.9152400,
                'lng' => 107.6975100,
                'radius' => 50,
            ],
            'Pstore Karawang' => [
                'address' => 'Ruko Mediterania Blok A No. 19, Jl. Galuh Mas Raya, Sukaharja, Telukjambe Timur, Kab. Karawang (Samping Bank BRI)',
                'lat' => -6.3218100,
                'lng' => 107.2942400,
                'radius' => 50,
            ],
            'Pstore Cirebon' => [
                'address' => 'Jl. Dr. Cipto Mangunkusumo No. 26, Pekiringan, Kec. Kesambi, Kota Cirebon (Samping Transmart Cirebon)',
                'lat' => -6.7262100,
                'lng' => 108.5532400,
                'radius' => 50,
            ],
            'Pstore Cianjur' => [
                'address' => 'Jl. KH. Abdullah bin Nuh No. 1, Pamoyanan, Kec. Cianjur, Kabupaten Cianjur',
                'lat' => -6.8155200,
                'lng' => 107.1325100,
                'radius' => 50,
            ],
            'Pstore Sukabumi' => [
                'address' => 'Jl. R.A. Kosasih No. 115, Ciaul, Subangjaya, Kec. Cikole, Kota Sukabumi (Samping Klinik Permata Ciaul)',
                'lat' => -6.9212100,
                'lng' => 106.9425200,
                'radius' => 50,
            ],

            // 4. JAWA TENGAH & D.I. YOGYAKARTA
            'Pstore Jogja' => [
                'address' => 'Ruko DTA Square, Kledokan, Caturtunggal, Kec. Depok, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55282',
                'lat' => -7.7718200,
                'lng' => 110.4082400,
                'radius' => 50,
            ],
            'Pstore Semarang' => [
                'address' => 'Jl. Veteran No. 50, Randusari, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50249 (Area belakang Polda Jateng)',
                'lat' => -6.9962100,
                'lng' => 110.4132400,
                'radius' => 50,
            ],
            'Pstore Solo' => [
                'address' => 'Samping BPR/BSI, Nusukan, Jl. Kapten Piere Tendean Ruko Harlin, Nusukan, Kec. Banjarsari, Kota Surakarta, Jawa Tengah 57135',
                'lat' => -7.5458200,
                'lng' => 110.8252100,
                'radius' => 50,
            ],
            'Pstore Purwokerto' => [
                'address' => 'Jl. Profesor DR. HR Boenyamin No. 80, Pakembaran, Bancarkembar, Kec. Purwokerto Utara, Kabupaten Banyumas, Jawa Tengah 53121',
                'lat' => -7.4125200,
                'lng' => 109.2458400,
                'radius' => 50,
            ],

            // 5. JAWA TIMUR
            'Pstore Surabaya' => [
                'address' => 'Jl. Raya Bratang Binangun No. 29, Baratajaya, Kec. Gubeng, Surabaya, Jawa Timur 60284',
                'lat' => -7.2965200,
                'lng' => 112.7578400,
                'radius' => 50,
            ],
            'Pstore Malang' => [
                'address' => 'Jl. S. Supriadi No. 23, Sukun, Kec. Sukun, Kota Malang, Jawa Timur',
                'lat' => -7.9985100,
                'lng' => 112.6242300,
                'radius' => 50,
            ],

            // 6. SUMATERA & KEPULAUAN RIAU
            'Pstore Medan' => [
                'address' => 'Sebrang kampus UISU, Jl. Sisingamangaraja I No. 160-B, Siti Rejo I, Kec. Medan Kota, Kota Medan, Sumatera Utara 20217',
                'lat' => 3.5685200,
                'lng' => 98.6942100,
                'radius' => 50,
            ],
            'Pstore Batam Center' => [
                'address' => 'Deretan SPBU, Komplek Palm Regency, Batam Center, Gelael, Jl. Laksamana Bintan Ruko Blok B13A, Taman Baloi, Kec. Batam Kota, Kota Batam, Kepulauan Riau 29464',
                'lat' => 1.1275200,
                'lng' => 104.0458200,
                'radius' => 50,
            ],
            'Pstore Batam batu aji' => [
                'address' => 'Ruko SP Plaza Blok Jati No. 1–2, Sagulung / Batu Aji, Kota Batam, Kepulauan Riau',
                'lat' => 1.0458200,
                'lng' => 103.9852400,
                'radius' => 50,
            ],
            'Pstore Pekanbaru' => [
                'address' => 'Jl. Suka Karya No. 3, Kelurahan Sialang Munggu, Kec. Tampan, Kota Pekanbaru, Riau 28293',
                'lat' => 0.4585200,
                'lng' => 101.3852100,
                'radius' => 50,
            ],
            'Pstore Padang' => [
                'address' => 'Jl. Dr. Moh. Hatta No. 42, Anduring, Kec. Kuranji, Kota Padang, Sumatera Barat 25176',
                'lat' => -0.9412100,
                'lng' => 100.3952400,
                'radius' => 50,
            ],
            'Pstore Palembang' => [
                'address' => 'Jl. Mayor Ruslan No. 8, 9 Ilir, Kec. Ilir Tim. II, Kota Palembang, Sumatera Selatan 30114',
                'lat' => -2.9685200,
                'lng' => 104.7652100,
                'radius' => 50,
            ],
            'Pstore Jambi' => [
                'address' => 'Jl. Sumantri Brojonegoro, Payo Lebar, Kec. Jelutung, Kota Jambi, Jambi 36124',
                'lat' => -1.6125200,
                'lng' => 103.6025100,
                'radius' => 50,
            ],
            'Pstore Bengkulu' => [
                'address' => '674G+6QF, Jl. Meranti, Sawah Lebar, Kec. Ratu Agung, Kota Bengkulu, Bengkulu 38222 (Samping Indomaret Meranti)',
                'lat' => -3.8012100,
                'lng' => 102.2752400,
                'radius' => 50,
            ],
            'Pstore Lampung' => [
                'address' => 'Jl. P. Antasari No. 40, Tj. Baru, Kec. Kedamaian, Kota Bandar Lampung, Lampung 35122',
                'lat' => -5.4085200,
                'lng' => 105.2752100,
                'radius' => 50,
            ],
            'Pstore Aceh' => [
                'address' => 'Jl. Dr. Mr. Mohd Hasan No. 96a, Batoh, Kec. Lueng Bata, Kota Banda Aceh, Aceh 23245',
                'lat' => 5.5352100,
                'lng' => 95.3352400,
                'radius' => 50,
            ],
            'Pstore Bangkabelitung' => [
                'address' => 'Jl. KH. Abdurahman Siddik, Gedung Nasional, Kec. Taman Sari, Kota Pangkal Pinang, Kepulauan Bangka Belitung 33684 (Depan Puncak Mall)',
                'lat' => -2.1258200,
                'lng' => 106.1125100,
                'radius' => 50,
            ],

            // 7. KALIMANTAN
            'Pstore Balikpapan' => [
                'address' => 'Jln. Soekarno-Hatta Komplek Bumi Nirwana Indah, Blk. B No. 06, Batu Ampar, Kec. Balikpapan Utara, Kota Balikpapan, Kalimantan Timur 76129 (Samping Hotel Platinum)',
                'lat' => -1.2185200,
                'lng' => 116.8652100,
                'radius' => 50,
            ],
            'Pstore Samarinda' => [
                'address' => 'Jl. Gatot Subroto No. 22a, Bandara, Kec. Sungai Pinang, Kota Samarinda, Kalimantan Timur 75117',
                'lat' => -0.4852100,
                'lng' => 117.1552400,
                'radius' => 50,
            ],
            'Pstore Banjarmasin' => [
                'address' => 'Jl. Kp. Melayu Darat, Melayu, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan 70121 (Samping BNI Kampung Melayu)',
                'lat' => -3.3215200,
                'lng' => 114.5952100,
                'radius' => 50,
            ],
            'Pstore Pontianak' => [
                'address' => 'Jl. Jendral Urip, Tengah, Kec. Pontianak Kota, Kota Pontianak, Kalimantan Barat 78243 (Depan Matahari Mall)',
                'lat' => -0.0245200,
                'lng' => 109.3385200,
                'radius' => 50,
            ],
            'Pstore Palangkaraya' => [
                'address' => 'Jl. Seth Adji No. 113, Langkai, Kec. Pahandut, Kota Palangka Raya, Kalimantan Tengah 74874',
                'lat' => -2.2285200,
                'lng' => 113.9252100,
                'radius' => 50,
            ],
            'Pstore Tarakan' => [
                'address' => 'Jl. Cendrawasih, Karang Anyar Pantai, Kec. Tarakan Bar., Kota Tarakan, Kalimantan Utara 77111',
                'lat' => 3.3258200,
                'lng' => 117.5852100,
                'radius' => 50,
            ],
            'Pstore Sangatta' => [
                'address' => 'Jl. Wolter Monginsidi, Sangatta Utara, Kec. Sangatta Utara, Kabupaten Kutai Timur, Kalimantan Timur 75683',
                'lat' => 0.4952100,
                'lng' => 117.5452400,
                'radius' => 50,
            ],

            // 8. BALI, NUSA TENGGARA, & SULAWESI
            'Pstore Bali' => [
                'address' => 'Jl. Teuku Umar No. 102, Dauh Puri Klod, Kec. Denpasar Bar., Kota Denpasar, Bali 80113',
                'lat' => -8.6752100,
                'lng' => 115.2052400,
                'radius' => 50,
            ],
            'Pstore Lombok' => [
                'address' => 'Samping We Drink, Jl. Pejanggik No. 28, Cakranegara Bar., Mataram Barat, Kota Mataram, Nusa Tenggara Bar. 83239',
                'lat' => -8.5885200,
                'lng' => 116.1285100,
                'radius' => 50,
            ],
            'Pstore Makassar' => [
                'address' => 'Jl. Toddopuli 7 No. 7, Borong, Kec. Manggala, Kota Makassar, Sulawesi Selatan 90222',
                'lat' => -5.1612100,
                'lng' => 119.4525100,
                'radius' => 100,
            ],
            'Pstore Makassar Toddopuli' => [
                'address' => 'Jl. Toddopuli 7 No. 7, Borong, Kec. Manggala, Kota Makassar, Sulawesi Selatan 90222',
                'lat' => -5.1612100,
                'lng' => 119.4525100,
                'radius' => 100,
            ],
            'Pstore Makassar New' => [
                'address' => 'Jl. Perintis Kemerdekaan KM 12, Pai, Kec. Tamalanrea, Kota Makassar, Sulawesi Selatan 90245 (Samping Swalayan Bintang / Satu Sama)',
                'lat' => -5.1297200,
                'lng' => 119.4990200,
                'radius' => 100,
            ],
            'Pstore Makassar Perintis' => [
                'address' => 'Jl. Perintis Kemerdekaan KM 12, Pai, Kec. Tamalanrea, Kota Makassar, Sulawesi Selatan 90245 (Samping Swalayan Bintang / Satu Sama)',
                'lat' => -5.1297200,
                'lng' => 119.4990200,
                'radius' => 100,
            ],
            'Pstore Manado' => [
                'address' => 'Jl. Piere Tendean No. 23, Sario Utara, Kec. Sario, Kota Manado, Sulawesi Utara 95111 (Kawasan Megamas)',
                'lat' => 1.4812100,
                'lng' => 124.8352100,
                'radius' => 50,
            ],
            'Pstore Palu' => [
                'address' => 'Jl. Emmy Saelan No. 4, Lolu Sel., Kec. Palu Sel., Kota Palu, Sulawesi Tengah 94111 (Seberang Pizza Hut / Zona Coffee)',
                'lat' => -0.9085200,
                'lng' => 119.8812400,
                'radius' => 50,
            ],
            'Pstore Gorontalo' => [
                'address' => 'Jl. Prof. Dr. H.B. Jassin No. 3, Limba B, Kota Sel., Kota Gorontalo, Gorontalo 96136 (Perempatan Lampu Merah Gelael)',
                'lat' => 0.5412100,
                'lng' => 123.0612100,
                'radius' => 50,
            ],
        ];

        foreach ($branchCoordinates as $branchName => $data) {
            DB::table('branches')
                ->where('name', $branchName)
                ->update([
                    'address' => $data['address'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'radius' => $data['radius'],
                    'updated_at' => now(),
                ]);
        }

        // Penanganan Khusus Cabang Makassar (Toddopuli & Perintis)
        DB::table('branches')
            ->where(function ($q) {
                $q->where('name', 'LIKE', '%Toddopuli%')
                    ->orWhere('name', 'Pstore Makassar');
            })
            ->where('name', 'NOT LIKE', '%Perintis%')
            ->where('name', 'NOT LIKE', '%Makassar New%')
            ->update([
                'address' => 'Jl. Toddopuli 7 No. 7, Borong, Kec. Manggala, Kota Makassar, Sulawesi Selatan 90222',
                'latitude' => -5.1612100,
                'longitude' => 119.4525100,
                'radius' => 100,
                'updated_at' => now(),
            ]);

        DB::table('branches')
            ->where(function ($q) {
                $q->where('name', 'LIKE', '%Perintis%')
                    ->orWhere('name', 'LIKE', '%Makassar New%');
            })
            ->update([
                'address' => 'Jl. Perintis Kemerdekaan KM 12, Pai, Kec. Tamalanrea, Kota Makassar, Sulawesi Selatan 90245 (Samping Swalayan Bintang / Satu Sama)',
                'latitude' => -5.1297200,
                'longitude' => 119.4990200,
                'radius' => 100,
                'updated_at' => now(),
            ]);
    }
}
