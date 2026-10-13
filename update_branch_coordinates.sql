-- =========================================================================================
-- SQL AMAN UNTUK VPS PRODUCTION PStore
-- Penambahan Kolom Geofencing & Update Koordinat Cabang
-- TIDAK MENGHAPUS / MERUSAK DATA YANG SUDAH ADA 1 TAHUN
-- =========================================================================================

-- 1. Tambah kolom latitude, longitude, dan radius jika belum ada (Safe DDL)
SET @dbname = DATABASE();
SET @tablename = "branches";

-- Latitude
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = "latitude") > 0,
  "SELECT 1",
  "ALTER TABLE branches ADD COLUMN latitude DECIMAL(10, 8) NULL AFTER address"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Longitude
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = "longitude") > 0,
  "SELECT 1",
  "ALTER TABLE branches ADD COLUMN longitude DECIMAL(11, 8) NULL AFTER latitude"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Radius
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = "radius") > 0,
  "SELECT 1",
  "ALTER TABLE branches ADD COLUMN radius INT UNSIGNED NULL DEFAULT 50 COMMENT 'Radius absensi dalam meter' AFTER longitude"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =========================================================================================
-- 2. UPDATE KOORDINAT CABANG RESMI PSTORE (RADIUS DEFAULT 50 METER)
-- =========================================================================================

-- DKI JAKARTA
UPDATE branches SET address = 'Jl. Bang Pitung (Rawabelong) No. 356, Sukabumi Utara, Kebon Jeruk, Jakarta Barat', latitude = -6.1952300, longitude = 106.7865200, radius = 50 WHERE name LIKE '%Slipi%';
UPDATE branches SET address = 'Jl. Raya Lenteng Agung (Lenteng Agung Pass, samping B-Quik Kuning / dekat AEON Mall Tanjung Barat), Jagakarsa, Jakarta Selatan', latitude = -6.3262100, longitude = 106.8378400, radius = 50 WHERE name LIKE '%Lenteng Agung%';
UPDATE branches SET address = 'Jl. Prof. DR. Soepomo No. 143, Tebet Barat, Jakarta Selatan (Seberang Hotel Amaris / Samping Honda AHASS)', latitude = -6.2372400, longitude = 106.8488100, radius = 50 WHERE name LIKE '%Tebet%';
UPDATE branches SET address = 'Jl. Raden Inten II No. 20, Klender, Duren Sawit, Jakarta Timur (Seberang SMKN 48 Jakarta)', latitude = -6.2285100, longitude = 106.9189200, radius = 50 WHERE name LIKE '%Buaran%';
UPDATE branches SET address = 'Jl. Boulevard Barat Raya Blok XC No. 5, Kelapa Gading Barat, Jakarta Utara (Samping Eiger)', latitude = -6.1524100, longitude = 106.8920300, radius = 50 WHERE name LIKE '%Kelapa Gading%';
UPDATE branches SET address = 'Jl. Danau Sunter Utara Blok F20 No. 23, Sunter Agung, Tanjung Priok, Jakarta Utara (Samping Bebek Kaleyo)', latitude = -6.1382400, longitude = 106.8647200, radius = 50 WHERE name LIKE '%Sunter%';
UPDATE branches SET address = 'Jl. Percetakan Negara Raya No. 45, Cempaka Putih Barat, Jakarta Pusat (Samping Kimia Farma)', latitude = -6.1884200, longitude = 106.8587100, radius = 50 WHERE name LIKE '%Salemba%';

-- BANTEN & TANGERANG
UPDATE branches SET address = 'Jl. KH. Hasyim Ashari, RT.003/RW.002, Poris Plawad Indah, Kec. Cipondoh, Kota Tangerang, Banten 15141', latitude = -6.1865200, longitude = 106.6668100, radius = 50 WHERE name LIKE '%Tangerang%';
UPDATE branches SET address = 'Talagasari, Cikupa, Tangerang Regency, Banten 15710 (Plus Code: QGCF+HM, Jl. Raya Serang / Pantura, Seberang Bundaran Citra Raya, Samping Kopi Calf To Go Cikupa / RSIA Tiara)', latitude = -6.2285359, longitude = 106.5216401, radius = 50 WHERE name LIKE '%Cikupa%';
UPDATE branches SET address = 'Depan Hotel Flamenggo Samping Lotte Grosir, Jl. Raya Cilegon Drangong Serang - Banten No.Km.2, RT.5/RW.6, Kepandean, Kec. Serang, Kota Serang, Banten 42182', latitude = -6.1158200, longitude = 106.1412300, radius = 50 WHERE name LIKE '%Serang%';
UPDATE branches SET address = 'Jl. Ahmad Yani, Sukmajaya, Kec. Jombang, Kota Cilegon, Banten 42411', latitude = -6.0125400, longitude = 106.0528100, radius = 50 WHERE name LIKE '%Cilegon%';
UPDATE branches SET address = 'Jl. Siliwangi No. 14D, Pamulang Barat, Kec. Pamulang, Tangerang Selatan (Seberang Living Plaza Pamulang)', latitude = -6.3424100, longitude = 106.7328200, radius = 50 WHERE name LIKE '%Pamulang%';
UPDATE branches SET address = 'Jl. Aria Putra No. 71, Serua Indah, Kec. Ciputat, Tangerang Selatan (Depan Alfamart Kavling)', latitude = -6.3155100, longitude = 106.7388400, radius = 50 WHERE name LIKE '%Ciputat%';

-- JAWA BARAT
UPDATE branches SET address = 'Jl. Pulo Sirih Utama No. 620, RT.001/RW.014, Jaka Setia, Kec. Bekasi Selatan, Kota Bekasi, Jawa Barat 17147 (Depan Mall Grand Galaxy Park / Maps: Pstore Bekasi)', latitude = -6.2628410, longitude = 106.9744120, radius = 50 WHERE name LIKE '%Bekasi%';
UPDATE branches SET address = 'Jl. Tarum Barat II Blok E2 No. 25, Jayamukti, Kec. Cikarang Pusat, Kabupaten Bekasi, Jawa Barat 17530 (Area Pasar Bersih Cikarang Baru)', latitude = -6.3188200, longitude = 107.1662400, radius = 50 WHERE name LIKE '%Cikarang%';
UPDATE branches SET address = 'Jl. Dr. Sumeru No. 56C, Kebon Kelapa, Bogor Barat, Kota Bogor (Samping Bank BRI / Seberang SPBU Shell Semeru)', latitude = -6.5835200, longitude = 106.7792100, radius = 50 WHERE name LIKE '%Bogor%';
UPDATE branches SET address = 'Ruko Nirwana Golden Park Blok C No. 5, Jl. Pakansari, Pakansari, Kec. Cibinong, Kab. Bogor (Dekat Stadion Pakansari)', latitude = -6.4855200, longitude = 106.8354100, radius = 50 WHERE name LIKE '%Cibinong%';
UPDATE branches SET address = 'Jl. Alternatif Cibubur No. 59A, Jatikarya, Kec. Jatisampurna, Kota Bekasi (±700 meter dari Mall Ciputra Cibubur)', latitude = -6.3775100, longitude = 106.9242300, radius = 50 WHERE name LIKE '%Cibubur%';
UPDATE branches SET address = 'Jl. Raya Sawangan No. 38, Mampang, Pancoran Mas, Kota Depok (Dekat Mall DTC Sawangan)', latitude = -6.3982100, longitude = 106.7972400, radius = 50 WHERE name = 'Pstore Depok';
UPDATE branches SET address = 'Jl. Raya Sawangan (Area Simpang Parung Bingung / Mampang), Pancoran Mas, Kota Depok', latitude = -6.3985000, longitude = 106.7950000, radius = 50 WHERE name LIKE '%Depok Gotik%';
UPDATE branches SET address = 'Jl. Dipati Ukur No. 72E, Lebakgede, Kec. Coblong, Kota Bandung, Jawa Barat 40132 (Samping Hotel Neo / Dekat Kampus Unikom)', latitude = -6.8885200, longitude = 107.6162300, radius = 50 WHERE name LIKE '%Bandung Neo%';
UPDATE branches SET address = 'Jl. A.H. Nasution No. 73, Pasanggrahan, Kec. Ujung Berung, Kota Bandung, Jawa Barat 40615', latitude = -6.9152400, longitude = 107.6975100, radius = 50 WHERE name LIKE '%Bandung Uber%';
UPDATE branches SET address = 'Ruko Mediterania Blok A No. 19, Jl. Galuh Mas Raya, Sukaharja, Telukjambe Timur, Kab. Karawang (Samping Bank BRI)', latitude = -6.3218100, longitude = 107.2942400, radius = 50 WHERE name LIKE '%Karawang%';
UPDATE branches SET address = 'Jl. Dr. Cipto Mangunkusumo No. 26, Pekiringan, Kec. Kesambi, Kota Cirebon (Samping Transmart Cirebon)', latitude = -6.7262100, longitude = 108.5532400, radius = 50 WHERE name LIKE '%Cirebon%';
UPDATE branches SET address = 'Jl. KH. Abdullah bin Nuh No. 1, Pamoyanan, Kec. Cianjur, Kabupaten Cianjur', latitude = -6.8155200, longitude = 107.1325100, radius = 50 WHERE name LIKE '%Cianjur%';
UPDATE branches SET address = 'Jl. R.A. Kosasih No. 115, Ciaul, Subangjaya, Kec. Cikole, Kota Sukabumi (Samping Klinik Permata Ciaul)', latitude = -6.9212100, longitude = 106.9425200, radius = 50 WHERE name LIKE '%Sukabumi%';

-- JAWA TENGAH & DIY
UPDATE branches SET address = 'Ruko DTA Square, Kledokan, Caturtunggal, Kec. Depok, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55282', latitude = -7.7718200, longitude = 110.4082400, radius = 50 WHERE name LIKE '%Jogja%';
UPDATE branches SET address = 'Jl. Veteran No. 50, Randusari, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50249 (Area belakang Polda Jateng)', latitude = -6.9962100, longitude = 110.4132400, radius = 50 WHERE name LIKE '%Semarang%';
UPDATE branches SET address = 'Samping BPR/BSI, Nusukan, Jl. Kapten Piere Tendean Ruko Harlin, Nusukan, Kec. Banjarsari, Kota Surakarta, Jawa Tengah 57135', latitude = -7.5458200, longitude = 110.8252100, radius = 50 WHERE name LIKE '%Solo%';
UPDATE branches SET address = 'Jl. Profesor DR. HR Boenyamin No. 80, Pakembaran, Bancarkembar, Kec. Purwokerto Utara, Kabupaten Banyumas, Jawa Tengah 53121', latitude = -7.4125200, longitude = 109.2458400, radius = 50 WHERE name LIKE '%Purwokerto%';

-- JAWA TIMUR
UPDATE branches SET address = 'Jl. Raya Bratang Binangun No. 29, Baratajaya, Kec. Gubeng, Surabaya, Jawa Timur 60284', latitude = -7.2965200, longitude = 112.7578400, radius = 50 WHERE name LIKE '%Surabaya%';
UPDATE branches SET address = 'Jl. S. Supriadi No. 23, Sukun, Kec. Sukun, Kota Malang, Jawa Timur', latitude = -7.9985100, longitude = 112.6242300, radius = 50 WHERE name LIKE '%Malang%';

-- SUMATERA & KEPULAUAN RIAU
UPDATE branches SET address = 'Sebrang kampus UISU, Jl. Sisingamangaraja I No. 160-B, Siti Rejo I, Kec. Medan Kota, Kota Medan, Sumatera Utara 20217', latitude = 3.5685200, longitude = 98.6942100, radius = 50 WHERE name LIKE '%Medan%';
UPDATE branches SET address = 'Deretan SPBU, Komplek Palm Regency, Batam Center, Gelael, Jl. Laksamana Bintan Ruko Blok B13A, Taman Baloi, Kec. Batam Kota, Kota Batam, Kepulauan Riau 29464', latitude = 1.1275200, longitude = 104.0458200, radius = 50 WHERE name LIKE '%Batam Center%';
UPDATE branches SET address = 'Ruko SP Plaza Blok Jati No. 1–2, Sagulung / Batu Aji, Kota Batam, Kepulauan Riau', latitude = 1.0458200, longitude = 103.9852400, radius = 50 WHERE name LIKE '%Batam batu aji%';
UPDATE branches SET address = 'Jl. Suka Karya No. 3, Kelurahan Sialang Munggu, Kec. Tampan, Kota Pekanbaru, Riau 28293', latitude = 0.4585200, longitude = 101.3852100, radius = 50 WHERE name LIKE '%Pekanbaru%';
UPDATE branches SET address = 'Jl. Dr. Moh. Hatta No. 42, Anduring, Kec. Kuranji, Kota Padang, Sumatera Barat 25176', latitude = -0.9412100, longitude = 100.3952400, radius = 50 WHERE name LIKE '%Padang%';
UPDATE branches SET address = 'Jl. Mayor Ruslan No. 8, 9 Ilir, Kec. Ilir Tim. II, Kota Palembang, Sumatera Selatan 30114', latitude = -2.9685200, longitude = 104.7652100, radius = 50 WHERE name LIKE '%Palembang%';
UPDATE branches SET address = 'Jl. Sumantri Brojonegoro, Payo Lebar, Kec. Jelutung, Kota Jambi, Jambi 36124', latitude = -1.6125200, longitude = 103.6025100, radius = 50 WHERE name LIKE '%Jambi%';
UPDATE branches SET address = '674G+6QF, Jl. Meranti, Sawah Lebar, Kec. Ratu Agung, Kota Bengkulu, Bengkulu 38222 (Samping Indomaret Meranti)', latitude = -3.8012100, longitude = 102.2752400, radius = 50 WHERE name LIKE '%Bengkulu%';
UPDATE branches SET address = 'Jl. P. Antasari No. 40, Tj. Baru, Kec. Kedamaian, Kota Bandar Lampung, Lampung 35122', latitude = -5.4085200, longitude = 105.2752100, radius = 50 WHERE name LIKE '%Lampung%';
UPDATE branches SET address = 'Jl. Dr. Mr. Mohd Hasan No. 96a, Batoh, Kec. Lueng Bata, Kota Banda Aceh, Aceh 23245', latitude = 5.5352100, longitude = 95.3352400, radius = 50 WHERE name LIKE '%Aceh%';
UPDATE branches SET address = 'Jl. KH. Abdurahman Siddik, Gedung Nasional, Kec. Taman Sari, Kota Pangkal Pinang, Kepulauan Bangka Belitung 33684 (Depan Puncak Mall)', latitude = -2.1258200, longitude = 106.1125100, radius = 50 WHERE name LIKE '%Bangkabelitung%';

-- KALIMANTAN
UPDATE branches SET address = 'Jln. Soekarno-Hatta Komplek Bumi Nirwana Indah, Blk. B No. 06, Batu Ampar, Kec. Balikpapan Utara, Kota Balikpapan, Kalimantan Timur 76129 (Samping Hotel Platinum)', latitude = -1.2185200, longitude = 116.8652100, radius = 50 WHERE name LIKE '%Balikpapan%';
UPDATE branches SET address = 'Jl. Gatot Subroto No. 22a, Bandara, Kec. Sungai Pinang, Kota Samarinda, Kalimantan Timur 75117', latitude = -0.4852100, longitude = 117.1552400, radius = 50 WHERE name LIKE '%Samarinda%';
UPDATE branches SET address = 'Jl. Kp. Melayu Darat, Melayu, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan 70121 (Samping BNI Kampung Melayu)', latitude = -3.3215200, longitude = 114.5952100, radius = 50 WHERE name LIKE '%Banjarmasin%';
UPDATE branches SET address = 'Jl. Jendral Urip, Tengah, Kec. Pontianak Kota, Kota Pontianak, Kalimantan Barat 78243 (Depan Matahari Mall)', latitude = -0.0245200, longitude = 109.3385200, radius = 50 WHERE name LIKE '%Pontianak%';
UPDATE branches SET address = 'Jl. Seth Adji No. 113, Langkai, Kec. Pahandut, Kota Palangka Raya, Kalimantan Tengah 74874', latitude = -2.2285200, longitude = 113.9252100, radius = 50 WHERE name LIKE '%Palangkaraya%';
UPDATE branches SET address = 'Jl. Cendrawasih, Karang Anyar Pantai, Kec. Tarakan Bar., Kota Tarakan, Kalimantan Utara 77111', latitude = 3.3258200, longitude = 117.5852100, radius = 50 WHERE name LIKE '%Tarakan%';
UPDATE branches SET address = 'Jl. Wolter Monginsidi, Sangatta Utara, Kec. Sangatta Utara, Kabupaten Kutai Timur, Kalimantan Timur 75683', latitude = 0.4952100, longitude = 117.5452400, radius = 50 WHERE name LIKE '%Sangatta%';

-- BALI, NTB, & SULAWESI
UPDATE branches SET address = 'Jl. Teuku Umar No. 102, Dauh Puri Klod, Kec. Denpasar Bar., Kota Denpasar, Bali 80113', latitude = -8.6752100, longitude = 115.2052400, radius = 50 WHERE name LIKE '%Bali%';
UPDATE branches SET address = 'Samping We Drink, Jl. Pejanggik No. 28, Cakranegara Bar., Mataram Barat, Kota Mataram, Nusa Tenggara Bar. 83239', latitude = -8.5885200, longitude = 116.1285100, radius = 50 WHERE name LIKE '%Lombok%';

-- Makassar Toddopuli
UPDATE branches SET address = 'Jl. Toddopuli 7 No. 7, Borong, Kec. Manggala, Kota Makassar, Sulawesi Selatan 90222', latitude = -5.1612100, longitude = 119.4525100, radius = 100 WHERE (name = 'Pstore Makassar' OR name LIKE '%Toddopuli%') AND name NOT LIKE '%Perintis%' AND name NOT LIKE '%Makassar New%';

-- Makassar Perintis (Sesuai titik fisik aktual dari HP karyawan)
UPDATE branches SET address = 'Jl. Perintis Kemerdekaan KM 12, Pai, Kec. Tamalanrea, Kota Makassar, Sulawesi Selatan 90245 (Samping Swalayan Bintang / Satu Sama)', latitude = -5.1297200, longitude = 119.4990200, radius = 100 WHERE name LIKE '%Perintis%' OR name LIKE '%Makassar New%';

UPDATE branches SET address = 'Jl. Piere Tendean No. 23, Sario Utara, Kec. Sario, Kota Manado, Sulawesi Utara 95111 (Kawasan Megamas)', latitude = 1.4812100, longitude = 124.8352100, radius = 50 WHERE name LIKE '%Manado%';
UPDATE branches SET address = 'Jl. Emmy Saelan No. 4, Lolu Sel., Kec. Palu Sel., Kota Palu, Sulawesi Tengah 94111 (Seberang Pizza Hut / Zona Coffee)', latitude = -0.9085200, longitude = 119.8812400, radius = 50 WHERE name LIKE '%Palu%';
UPDATE branches SET address = 'Jl. Prof. Dr. H.B. Jassin No. 3, Limba B, Kota Sel., Kota Gorontalo, Gorontalo 96136 (Perempatan Lampu Merah Gelael)', latitude = 0.5412100, longitude = 123.0612100, radius = 50 WHERE name LIKE '%Gorontalo%';
