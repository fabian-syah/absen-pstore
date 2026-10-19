<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddressResolverService
{
    /**
     * Cek apakah string input adalah Link (URL) atau format koordinat
     */
    public static function isLinkOrCoordinate(?string $input): bool
    {
        if (empty($input)) {
            return false;
        }

        $trimmed = trim($input);

        // Link HTTP / HTTPS atau Google Maps
        if (preg_match('/^https?:\/\//i', $trimmed) ||
            str_contains($trimmed, 'google.com/maps') ||
            str_contains($trimmed, 'maps.google') ||
            str_contains($trimmed, 'maps.app.goo.gl') ||
            str_contains($trimmed, 'goo.gl/maps')) {
            return true;
        }

        // Format Koordinat Desimal (-2.1234, 106.1234)
        if (preg_match('/^[-+]?[0-9]{1,2}(?:\.[0-9]+)?[,\s]+[-+]?[0-9]{1,3}(?:\.[0-9]+)?$/', $trimmed)) {
            return true;
        }

        // Format DMS (6°16'38.3"S 106°51'18.6"E)
        if (preg_match('/[\x{00B0}\°]/u', $trimmed) && preg_match('/[NSns]/i', $trimmed) && preg_match('/[EWew]/i', $trimmed)) {
            return true;
        }

        return false;
    }

    /**
     * Resolusi input (bisa berupa Google Maps link, koordinat, atau DMS) menjadi teks alamat resmi dan koordinat
     *
     * @param string|null $input
     * @param float|null $existingLat
     * @param float|null $existingLng
     * @return array [success => bool, address => string, lat => float|null, lng => float|null, is_converted => bool]
     */
    public static function resolve(?string $input, ?float $existingLat = null, ?float $existingLng = null): array
    {
        $raw = trim($input ?? '');
        if (empty($raw)) {
            return [
                'success' => false,
                'address' => '',
                'lat' => $existingLat,
                'lng' => $existingLng,
                'is_converted' => false,
                'message' => 'Input kosong.'
            ];
        }

        // Jika bukan link dan bukan koordinat, biarkan teks alamat apa adanya
        if (!self::isLinkOrCoordinate($raw)) {
            return [
                'success' => true,
                'address' => $raw,
                'lat' => $existingLat,
                'lng' => $existingLng,
                'is_converted' => false
            ];
        }

        $url = $raw;

        // 1. Expand shortlink jika format maps.app.goo.gl atau goo.gl/maps
        if (str_contains($url, 'maps.app.goo.gl') || str_contains($url, 'goo.gl/maps')) {
            $expanded = self::expandShortlink($url);
            if (!empty($expanded)) {
                $url = $expanded;
            }
        }

        // 2. Ekstrak koordinat dari URL / format input
        $coords = self::extractCoordinates($url);

        // Jika tidak ketemu koordinat dari URL tapi branch sudah punya existingLat & existingLng
        if (!$coords && !is_null($existingLat) && !is_null($existingLng)) {
            $coords = ['lat' => (float)$existingLat, 'lng' => (float)$existingLng];
        }

        if (!$coords) {
            // Cek apakah ada nama tempat dari URL Google Maps (misal /place/Pstore+Jakarta/...)
            $placeName = self::extractPlaceNameFromUrl($url);
            if (!empty($placeName)) {
                return [
                    'success' => true,
                    'address' => $placeName,
                    'lat' => $existingLat,
                    'lng' => $existingLng,
                    'is_converted' => true
                ];
            }

            return [
                'success' => false,
                'address' => $raw,
                'lat' => $existingLat,
                'lng' => $existingLng,
                'is_converted' => false,
                'message' => 'Tidak dapat menemukan koordinat dari link yang diberikan.'
            ];
        }

        $lat = $coords['lat'];
        $lng = $coords['lng'];

        // 3. Lakukan Reverse Geocoding untuk mendapatkan teks alamat manusia
        $resolvedAddress = self::reverseGeocode($lat, $lng);

        if (!empty($resolvedAddress)) {
            return [
                'success' => true,
                'address' => $resolvedAddress,
                'lat' => $lat,
                'lng' => $lng,
                'is_converted' => true
            ];
        }

        // Fallback jika reverse geocoding gagal: coba nama tempat dari URL atau format koordinat
        $placeName = self::extractPlaceNameFromUrl($url);
        $fallbackAddress = !empty($placeName) ? $placeName : "Titik Lokasi GPS ({$lat}, {$lng})";

        return [
            'success' => true,
            'address' => $fallbackAddress,
            'lat' => $lat,
            'lng' => $lng,
            'is_converted' => true
        ];
    }

    /**
     * Ekstrak koordinat latitude dan longitude dari string input atau URL Google Maps
     */
    public static function extractCoordinates(string $query): ?array
    {
        // Pola 1: !3d<lat>!4d<lng> (Pin spesifik Google Maps)
        if (preg_match('/!3d([-+]?[0-9]{1,2}\.[0-9]+)!4d([-+]?[0-9]{1,3}\.[0-9]+)/', $query, $matches)) {
            $lat = (float)$matches[1];
            $lng = (float)$matches[2];
            if (self::isValidCoordinates($lat, $lng)) {
                return ['lat' => $lat, 'lng' => $lng];
            }
        }

        // Pola 2: @<lat>,<lng> (Center view Google Maps)
        if (preg_match('/@([-+]?[0-9]{1,2}\.[0-9]+),([-+]?[0-9]{1,3}\.[0-9]+)/', $query, $matches)) {
            $lat = (float)$matches[1];
            $lng = (float)$matches[2];
            if (self::isValidCoordinates($lat, $lng)) {
                return ['lat' => $lat, 'lng' => $lng];
            }
        }

        // Pola 3: [?&](q|ll|destination)=<lat>,<lng>
        if (preg_match('/[?&](?:q|ll|destination)=([-+]?[0-9]{1,2}\.[0-9]+),([-+]?[0-9]{1,3}\.[0-9]+)/i', $query, $matches)) {
            $lat = (float)$matches[1];
            $lng = (float)$matches[2];
            if (self::isValidCoordinates($lat, $lng)) {
                return ['lat' => $lat, 'lng' => $lng];
            }
        }

        // Pola 4: /place/<lat>,<lng>
        if (preg_match('/\/place\/([-+]?[0-9]{1,2}\.[0-9]+),([-+]?[0-9]{1,3}\.[0-9]+)/i', $query, $matches)) {
            $lat = (float)$matches[1];
            $lng = (float)$matches[2];
            if (self::isValidCoordinates($lat, $lng)) {
                return ['lat' => $lat, 'lng' => $lng];
            }
        }

        // Pola 5: Koordinat DMS (Derajat Menit Detik, contoh: 2°07'37.7"S 106°06'50.3"E)
        $decoded = urldecode($query);
        if (preg_match('/([0-9]{1,2})\s*[\x{00B0}\°]\s*([0-9]{1,2})\s*[\'′]\s*([0-9]{1,2}(?:\.[0-9]+)?)\s*["″]?\s*([NSns])\s*,?\s*([0-9]{1,3})\s*[\x{00B0}\°]\s*([0-9]{1,2})\s*[\'′]\s*([0-9]{1,2}(?:\.[0-9]+)?)\s*["″]?\s*([EWew])/u', $decoded, $dms)) {
            $lat = (float)$dms[1] + ((float)$dms[2] / 60) + ((float)$dms[3] / 3600);
            if (strtoupper($dms[4]) === 'S') $lat = -$lat;

            $lng = (float)$dms[5] + ((float)$dms[6] / 60) + ((float)$dms[7] / 3600);
            if (strtoupper($dms[8]) === 'W') $lng = -$lng;

            if (self::isValidCoordinates($lat, $lng)) {
                return ['lat' => round($lat, 7), 'lng' => round($lng, 7)];
            }
        }

        // Pola 6: Format desimal langsung ("-6.2088, 106.8456")
        if (preg_match('/^[-+]?[0-9]{1,2}(?:\.[0-9]+)?[,\s]+[-+]?[0-9]{1,3}(?:\.[0-9]+)?$/', trim($query))) {
            $parts = preg_split('/[\s,]+/', trim($query));
            if (count($parts) >= 2) {
                $lat = (float)$parts[0];
                $lng = (float)$parts[1];
                if (self::isValidCoordinates($lat, $lng)) {
                    return ['lat' => $lat, 'lng' => $lng];
                }
            }
        }

        return null;
    }

    /**
     * Ekstrak nama tempat jika ada dalam link Google Maps /place/<name>/
     */
    public static function extractPlaceNameFromUrl(string $url): ?string
    {
        $decoded = urldecode($url);
        if (preg_match('/\/place\/([^/@?#]+)/', $decoded, $m)) {
            $name = trim(str_replace('+', ' ', $m[1]));
            // Abaikan jika hanya berupa koordinat atau DMS
            if (!empty($name) && 
                !preg_match('/^[-+]?[0-9]/', $name) && 
                !preg_match('/[\x{00B0}\°]/u', $name)) {
                return $name;
            }
        }
        return null;
    }

    /**
     * Follow redirects untuk shortlink maps.app.goo.gl atau goo.gl
     */
    public static function expandShortlink(string $url): string
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            curl_exec($ch);
            $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            return $effectiveUrl ?: $url;
        } catch (\Exception $e) {
            Log::warning('AddressResolverService expandShortlink error: ' . $e->getMessage());
            return $url;
        }
    }

    /**
     * Lakukan Reverse Geocode dari koordinat menjadi alamat teks bahasa Indonesia
     */
    public static function reverseGeocode(float $lat, float $lng): ?string
    {
        // Provider 1: OpenStreetMap Nominatim
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'AbsensiPStore/1.0 (' . config('app.url', 'https://absenps.com') . ')',
                'Accept-Language' => 'id,en',
            ])->timeout(6)->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data)) {
                    // Jika ada display_name, kita rapikan
                    if (!empty($data['display_name'])) {
                        return self::cleanDisplayName($data['display_name']);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('AddressResolverService Nominatim error: ' . $e->getMessage());
        }

        // Provider 2: BigDataCloud Fallback
        try {
            $bdcResponse = Http::timeout(5)->get('https://api.bigdatacloud.net/data/reverse-geocode-client', [
                'latitude' => $lat,
                'longitude' => $lng,
                'localityLanguage' => 'id',
            ]);

            if ($bdcResponse->successful()) {
                $bdc = $bdcResponse->json();
                $parts = [];
                if (!empty($bdc['locality'])) $parts[] = $bdc['locality'];
                if (!empty($bdc['city'])) $parts[] = $bdc['city'];
                if (!empty($bdc['principalSubdivision'])) $parts[] = $bdc['principalSubdivision'];
                if (!empty($bdc['countryName'])) $parts[] = $bdc['countryName'];

                if (!empty($parts)) {
                    return implode(', ', $parts);
                }
            }
        } catch (\Exception $e) {
            Log::warning('AddressResolverService BigDataCloud fallback error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Rapikan display_name dari OpenStreetMap jika ada bagian redundan
     */
    private static function cleanDisplayName(string $displayName): string
    {
        // Pisahkan per koma dan hapus duplikat berurutan
        $parts = array_map('trim', explode(',', $displayName));
        $unique = [];
        foreach ($parts as $p) {
            if (empty($p)) continue;
            // Lewati jika persis sama dengan bagian sebelumnya
            if (!empty($unique) && strcasecmp(end($unique), $p) === 0) continue;
            $unique[] = $p;
        }

        return implode(', ', $unique);
    }

    /**
     * Validasi range koordinat
     */
    private static function isValidCoordinates(float $lat, float $lng): bool
    {
        return ($lat >= -90.0 && $lat <= 90.0 && $lng >= -180.0 && $lng <= 180.0);
    }
}
