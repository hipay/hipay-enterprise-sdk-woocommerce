<?php
/**
 * HiPay Enterprise SDK WooCommerce
 *
 * 2018 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2018 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_SRI_Helper_Curl
{
    /**
     * Cache key for integrity hashes
     */
    const CACHE_KEY_PREFIX = 'hipay_sri_hash_';
    
    /**
     * Cache expiration time (5 minutes)
     */
    const CACHE_EXPIRATION = 300;
    
    /**
     * Store integrity hashes for scripts
     */
    private static $script_integrity_hashes = array();
    
    /**
     * Flag to track if the filter has been added
     */
    private static $filter_added = false;

    /**
     * Load HiPay SDK with Subresource Integrity using cURL
     * 
     * @param string $sdk_url The SDK URL from configuration
     * @param string $script_handle WordPress script handle
     * @param array $dependencies Script dependencies
     * @param string $version Script version
     * @param bool $in_footer Whether to enqueue in footer
     */
    public static function enqueue_sdk_with_sri($sdk_url, $script_handle = 'hipay-sdk', $dependencies = array(), $version = '1.0.0', $in_footer = true)
    {
        // Check if cURL is available
        if (!function_exists('curl_init')) {
            // Fallback: load without SRI if cURL is not available
            wp_register_script($script_handle, $sdk_url, $dependencies, $version, $in_footer);
            wp_enqueue_script($script_handle);
            return;
        }
        
        // Generate the integrity URL by replacing .js with .integrity
        $integrity_url = str_replace('.js', '.integrity', $sdk_url);
        
        // Fetch integrity hash server-side
        $integrity_hash = self::get_integrity_hash($integrity_url);
        
        if ($integrity_hash) {
            // Store the integrity hash for this script
            self::$script_integrity_hashes[$script_handle] = $integrity_hash;
            
            // Load script with SRI
            wp_register_script($script_handle, $sdk_url, $dependencies, $version, $in_footer);
            wp_enqueue_script($script_handle);
            
            // Add the filter only once
            if (!self::$filter_added) {
                add_filter('script_loader_tag', array(__CLASS__, 'add_integrity_attributes'), 10, 2);
                self::$filter_added = true;
            }
            
        } else {
            // Fallback: load without SRI if integrity hash cannot be fetched
            wp_register_script($script_handle, $sdk_url, $dependencies, $version, $in_footer);
            wp_enqueue_script($script_handle);
        }
    }
    
    /**
     * Fetch integrity hash using cURL with caching
     * 
     * @param string $integrity_url The integrity file URL
     * @return string|false The integrity hash or false on failure
     */
    private static function get_integrity_hash($integrity_url)
    {
        // Check cache first
        $cache_key = self::CACHE_KEY_PREFIX . md5($integrity_url);
        $cached_hash = get_transient($cache_key);
        
        if ($cached_hash !== false) {
            return $cached_hash;
        }
        
        // Fetch using cURL
        $integrity_hash = self::fetch_integrity_hash_curl($integrity_url);
        
        if ($integrity_hash) {
            // Cache the hash
            set_transient($cache_key, $integrity_hash, self::CACHE_EXPIRATION);
            return $integrity_hash;
        }
        
        return false;
    }
    
    /**
     * Fetch integrity hash using cURL
     * 
     * @param string $integrity_url The integrity file URL
     * @return string|false The integrity hash or false on failure
     */
    private static function fetch_integrity_hash_curl($integrity_url)
    {
        if (!function_exists('curl_init')) {
            return false;
        }
        
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $integrity_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'HiPay-WooCommerce-SRI/1.0',
            CURLOPT_HTTPHEADER => array(
                'Accept: text/plain, */*',
                'Cache-Control: no-cache'
            )
        ));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Check for errors
        if ($error || $http_code !== 200 || empty($response)) {
            error_log("HiPay SRI: Failed to fetch integrity hash from {$integrity_url}. HTTP Code: {$http_code}, Error: {$error}");
            return false;
        }
        
        // Clean and validate the hash
        $hash = trim($response);
        
        // Basic validation - should be a SHA hash
        if (!preg_match('/^sha[0-9]+-[a-zA-Z0-9+\/=]+$/', $hash)) {
            error_log("HiPay SRI: Invalid integrity hash format: {$hash}");
            return false;
        }
        
        return $hash;
    }
    
    /**
     * Clear cached integrity hashes
     * 
     * @param string $sdk_url Optional SDK URL to clear specific cache
     */
    public static function clear_cache($sdk_url = null)
    {
        if ($sdk_url) {
            $integrity_url = str_replace('.js', '.integrity', $sdk_url);
            $cache_key = self::CACHE_KEY_PREFIX . md5($integrity_url);
            delete_transient($cache_key);
        } else {
            // Clear all SRI caches
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    '_transient_' . self::CACHE_KEY_PREFIX . '%'
                )
            );
        }
    }
    
    /**
     * Add integrity attributes to script tags
     * 
     * @param string $tag The script tag
     * @param string $handle The script handle
     * @return string The modified script tag
     */
    public static function add_integrity_attributes($tag, $handle)
    {
        // Check if this script has an integrity hash
        if (isset(self::$script_integrity_hashes[$handle])) {
            $integrity_hash = self::$script_integrity_hashes[$handle];
            
            // Check if integrity attribute already exists to prevent duplication
            if (strpos($tag, 'integrity=') === false) {
                return str_replace('<script ', '<script integrity="' . esc_attr($integrity_hash) . '" crossorigin="anonymous" ', $tag);
            }
        }
        
        return $tag;
    }
    
    /**
     * Get cache status for debugging
     * 
     * @param string $sdk_url The SDK URL
     * @return array Cache status information
     */
    public static function get_cache_status($sdk_url)
    {
        $integrity_url = str_replace('.js', '.integrity', $sdk_url);
        $cache_key = self::CACHE_KEY_PREFIX . md5($integrity_url);
        $cached_hash = get_transient($cache_key);
        
        return array(
            'integrity_url' => $integrity_url,
            'cache_key' => $cache_key,
            'cached' => $cached_hash !== false,
            'hash' => $cached_hash ?: 'not cached',
            'expires_in' => $cached_hash ? get_option('_transient_timeout_' . $cache_key) - time() : 0
        );
    }
} 