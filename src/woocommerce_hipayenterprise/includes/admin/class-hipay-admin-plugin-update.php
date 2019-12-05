<?php

defined('ABSPATH') || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Admin_Plugin_Update_Handler
{

    static private $WC_HIPAYENTERPRISE_GITHUB_RELEASE_URL = "https://api.github.com/repos/hipay/hipay-enterprise-sdk-woocommerce/releases/latest";
    static private $WC_HIPAYENTERPRISE_GITHUB_CHANGELOG_URL = "https://api.github.com/repos/hipay/hipay-enterprise-sdk-woocommerce/contents/CHANGELOG.md";
    static private $WC_HIPAYENTERPRISE_GITHUB_README_URL = "https://api.github.com/repos/hipay/hipay-enterprise-sdk-woocommerce/readme";
    static private $WC_HIPAYENTERPRISE_DEV_HOMEPAGE = "https://developer.hipay.com/doc/hipay-enterprise-sdk-woocommerce";
    static private $WC_HIPAYENTERPRISE_GITHUB_RATE_URL = "https://api.github.com/rate_limit";

    static private $WC_HIPAYENTERPRISE_WORDPRESS_REQUIRES = '4.9';
    static private $WC_HIPAYENTERPRISE_PHP_REQUIRES = '7.0';
    static private $WC_HIPAYENTERPRISE_WORDPRESS_TESTED = '5.2.4';

    /**
     * The plugin current version
     * @var string
     */
    public $current_version;

    /**
     * Plugin Slug (plugin_directory/plugin_file.php)
     * @var string
     */
    public $plugin_slug;

    /**
     * Plugin name (plugin_file)
     * @var string
     */
    public $slug;

    /**
     * Plugin info
     * @var string
     */
    public $pluginUpdateInfo;


    public $confHelper;
    private $logs;
    public $id;

    /**
     * Initialize a new instance of the WordPress Auto-Update class
     * @param string $current_version
     * @param string $plugin_slug
     */
    function __construct($current_version, $plugin_slug)
    {
        $this->id = 'Hipay_Plugin_Update_Helper';
        $this->confHelper = new Hipay_Config();
        $this->logs = new Hipay_Log($this);

        // Set the class public variables
        $this->current_version = $current_version;
        $this->plugin_slug = $plugin_slug;
        list ($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);

        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));

        // Define the alternative response for information checking
        add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);
    }

    /**
     * Add our self-hosted autoupdate plugin to the filter transient
     *
     * @param $transient
     * @return object $ transient
     * @throws Exception
     */
    public function check_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $this->pluginUpdateInfo = $this->getRemoteInformation();

        // If a newer version is available, add the update
        if ($this->pluginUpdateInfo && version_compare($this->current_version,
                $this->pluginUpdateInfo->updateInfo->remoteVersion, '<')) {
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $this->pluginUpdateInfo->updateInfo->remoteVersion;
            $obj->url = $this->pluginUpdateInfo->updateInfo->remoteUrl;
            $obj->package = $this->pluginUpdateInfo->updateInfo->remotePackageUrl;
            $obj->tested = '5.2.3';
            $obj->icons = array(
                '1x' => WC_HIPAYENTERPRISE_URL_ASSETS . 'images/logo_128.png',
                '2x' => WC_HIPAYENTERPRISE_URL_ASSETS . 'images/logo_256.png'
            );

            $transient->response[$this->plugin_slug] = $obj;
        }

        return $transient;
    }

    /**
     * Add our self-hosted description to the filter
     *
     * @param boolean $false
     * @param array $action
     * @param object $arg
     * @return bool|object
     * @throws Exception
     */
    public function check_info($false, $action, $arg)
    {
        if ($arg->slug === $this->slug) {
            $this->pluginUpdateInfo = $this->getRemoteInformation();

            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->plugin_name = WC_HIPAYENTERPRISE_NAME;
            $obj->requires = static::$WC_HIPAYENTERPRISE_WORDPRESS_REQUIRES;
            $obj->requires_php = static::$WC_HIPAYENTERPRISE_PHP_REQUIRES;
            $obj->tested = static::$WC_HIPAYENTERPRISE_WORDPRESS_TESTED;
            $obj->author = '<a href="https://hipay.com">HiPay</a>';
            $obj->banners = array(
                'high' => WC_HIPAYENTERPRISE_URL_ASSETS . 'images/banner_high.png',
                'low' => WC_HIPAYENTERPRISE_URL_ASSETS . 'images/banner_low.png'
            );
            $obj->homepage = static::$WC_HIPAYENTERPRISE_DEV_HOMEPAGE;
            $obj->version = $this->pluginUpdateInfo->generalInfo->version;
            $obj->last_updated = $this->pluginUpdateInfo->generalInfo->last_updated;
            $obj->sections = $this->pluginUpdateInfo->generalInfo->sections;
            $obj->download_link = $this->pluginUpdateInfo->generalInfo->download_link;

            return $obj;
        }
        return false;
    }

    /**
     * Return the remote version
     * @return string $remote_version
     * @throws Exception
     */
    public function getRemoteInformation()
    {
        $remoteInfo = get_option("wc_hipay_update_info");
        $curdate = new DateTime();

        if(!$remoteInfo || $remoteInfo->updateDate->add(new DateInterval("PT1H")) < $curdate) {
            /*
             * Checking if we can poll github API safely (two polls needed : general info and changelog)
             */
            $requestRate = wp_remote_get(static::$WC_HIPAYENTERPRISE_GITHUB_RATE_URL);
            if (!is_wp_error($requestRate) || wp_remote_retrieve_response_code($requestRate) === 200) {
                $rateLimit = json_decode($requestRate['body']);
                $this->logs->logInfos("[UPGRADE CHECK] Got remaining rate from GitHub  : " . $rateLimit->rate->remaining);

                if ($rateLimit->rate->remaining > 1) {
                    $request = wp_remote_get(static::$WC_HIPAYENTERPRISE_GITHUB_RELEASE_URL);
                    if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
                        $body = json_decode($request['body']);

                        $this->logs->logInfos("[UPGRADE CHECK] Got latest version from GitHub : " . $body->tag_name);

                        $updateInfo = new \stdClass();
                        $updateInfo->remoteVersion = $body->tag_name;
                        $updateInfo->remoteUrl = $body->html_url;
                        $updateInfo->remotePackageUrl = $body->assets[0]->browser_download_url;

                        $generalInfo = new \stdClass();
                        $generalInfo->version = $body->tag_name;
                        $generalInfo->last_updated = $body->published_at;
                        $generalInfo->download_link = $body->assets[0]->browser_download_url;

                        $generalInfo->sections = array(
                            'description' => $this->getPluginDescription(),
                            'changelog' => $this->getPluginChangelog()
                        );

                        $remoteInfo = new \stdClass();
                        $remoteInfo->updateInfo = $updateInfo;
                        $remoteInfo->generalInfo = $generalInfo;
                        $remoteInfo->updateDate = $curdate;

                        update_option("wc_hipay_update_info", $remoteInfo);
                        return $remoteInfo;
                    } else {
                        $this->logs->logErrors("[UPGRADE CHECK] Error when getting new version from GitHub  : " . implode(', ', $request->get_error_messages()));
                    }
                }
            } else {
                $this->logs->logErrors("[UPGRADE CHECK] Error when getting remaining rate from GitHub  : " . implode(', ', $requestRate->get_error_messages()));
            }
        } else {
            $this->logs->logInfos("[UPGRADE CHECK] Last check is less than 1 hour ago, getting from local save : " . $remoteInfo->updateInfo->remoteVersion);
        }

        return $remoteInfo;
    }

    /**
     * @return bool|string Returns the plugin's description, or false if any error occurs
     */
    private function getPluginDescription()
    {
        $request = wp_remote_get(static::$WC_HIPAYENTERPRISE_GITHUB_README_URL);
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            $body = json_decode($request['body']);

            $readme = base64_decode($body->content);

            return \Michelf\Markdown::defaultTransform($readme);
        }
        return false;
    }

    /**
     * @return bool|string Returns the plugin's changelog, or false if any error occurs
     */
    private function getPluginChangelog()
    {
        $request = wp_remote_get(static::$WC_HIPAYENTERPRISE_GITHUB_CHANGELOG_URL);
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            $body = json_decode($request['body']);

            $changelog = base64_decode($body->content);

            return \Michelf\Markdown::defaultTransform($changelog);
        }
        return false;
    }


}