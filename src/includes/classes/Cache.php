<?php
/**
 * Simple in-memory query cache for repeated DB queries
 * Uses static arrays for request-scoped caching
 * Can be extended with APCu/Redis later for persistent caching
 */

namespace WebPA\includes\classes;

class Cache
{
    // Request-scoped cache (cleared at end of request)
    private static array $data = [];
    
    // Default TTL in seconds (300 = 5 minutes)
    private const DEFAULT_TTL = 300;
    
    /**
     * Get a cached value
     */
    public static function get(string $key)
    {
        if (isset(self::$data[$key])) {
            $entry = self::$data[$key];
            // Check if expired
            if (time() < $entry['expires']) {
                return $entry['value'];
            }
            // Expired, remove it
            unset(self::$data[$key]);
        }
        return null;
    }
    
    /**
     * Set a cached value with optional TTL
     */
    public static function set(string $key, mixed $value, int $ttl = self::DEFAULT_TTL): void
    {
        self::$data[$key] = [
            'value' => $value,
            'expires' => time() + $ttl,
        ];
    }
    
    /**
     * Check if a key exists and is not expired
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }
    
    /**
     * Delete a cached value
     */
    public static function delete(string $key): void
    {
        unset(self::$data[$key]);
    }
    
    /**
     * Clear all cached values
     */
    public static function clear(): void
    {
        self::$data = [];
    }
    
    /**
     * Get cache stats (for debugging)
     */
    public static function stats(): array
    {
        $valid = 0;
        foreach (self::$data as $entry) {
            if (time() < $entry['expires']) {
                $valid++;
            }
        }
        return [
            'total' => count(self::$data),
            'valid' => $valid,
        ];
    }
}
