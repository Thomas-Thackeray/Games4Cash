<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeoLocationService
{
    /**
     * Resolve a human-readable location string from an IP address.
     * Returns "Local" for loopback IPs, "Unknown" on any failure.
     */
    public static function lookup(string $ip): string
    {
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
            return 'Local';
        }

        // Private/reserved ranges — no point querying an API
        if (self::isPrivateIp($ip)) {
            return 'Private Network';
        }

        try {
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,city,regionName,country',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (($data['status'] ?? '') === 'success') {
                    $parts = array_filter([
                        $data['city']       ?? '',
                        $data['regionName'] ?? '',
                        $data['country']    ?? '',
                    ]);
                    return implode(', ', $parts) ?: 'Unknown';
                }
            }
        } catch (\Throwable) {
            // Silently fail — location is non-critical
        }

        return 'Unknown';
    }

    private static function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
