<?php

/**
 * Class Wc_Hipay_Admin_Assets
 *
 *
 *
 */
class Wc_Hipay_Admin_Assets {

    /**
     * The single instance of the class.
     *
     * @var Wc_Hipay_Admin_Assets|null
     */
    protected static $instance = null;

    /**
     *
     */
    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();

            add_action('admin_enqueue_scripts', array( self::$instance,'enqueue_scripts'));
        }
        return self::$instance;
    }

    /**
     *
     */
    public function enqueue_scripts() {
        wp_register_style( 'wc_hipay_admin_multi_css', plugins_url( 'assets/css/admin/multi.min.css', WC_HIPAYENTERPRISE_BASE_FILE ), array(), WC_HIPAYENTERPRISE_VERSION );
        wp_enqueue_style( 'wc_hipay_admin_multi_css' );

        //Todo voir pour la minification des JS
        wp_enqueue_script( 'wc_hipay_admin_multi', plugins_url( 'assets/js/admin/multi.min.js', WC_HIPAYENTERPRISE_BASE_FILE ), array(), WC_HIPAYENTERPRISE_VERSION, true );
        wp_enqueue_script( 'wc_hipay_admin', plugins_url( 'assets/js/admin/hipay-admin.js', WC_HIPAYENTERPRISE_BASE_FILE ), array(), WC_HIPAYENTERPRISE_VERSION, true );
    }



}

Wc_Hipay_Admin_Assets::get_instance();