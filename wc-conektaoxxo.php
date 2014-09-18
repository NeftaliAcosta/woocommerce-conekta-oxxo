<?php
  /**
 * Plugin Name: WooCommerce Conekta Oxxo
 * Plugin URI: https://github.com/ramelp/woocommerce-conekta-oxxo
 * Description: WooCommerce Conekta Oxxo te permite generar el cargo.
 * Author: ramelp
 * Author URI: http://sisnodo.com
 * Version: 0.1
 * License: GPLv2 or later 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Conekta_Oxxo_Payment' ) ) :

/**
 * WooCommerce Conekta Oxxo main class.
 */
class WC_Conekta_Oxxo_Payment {

    /**
     * Plugin version.
     *
     * @var string
     */
    const VERSION = '0.1';

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin public actions.
     */
    private function __construct() {
        
        // Checks PHP version >= 5.3.
        if (function_exists('get_called_class')){
            // Checks with WooCommerce is installed.
            if ( class_exists( 'WC_Payment_Gateway' )  ) {
                $this->includes();
                    
                add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
                
            } else {
                add_action( 'admin_notices', array( $this, 'woocommerce_error' ) );
            }
        }
        else {
                add_action( 'admin_notices', array( $this, 'woocommerce_error_php' ) );                
        }
    }

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function get_instance() {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }
   
    
    /**
     * Includes.
     *
     * @return void
     */
    private function includes() {
        
        if (!class_exists('Conekta'))
            include_once 'classes/lib/conekta/lib/Conekta.php';
        include_once 'classes/wc_conekta_oxxo_gateway.php';
        
    }

    /**
     * Add the gateway to WooCommerce.
     *
     * @param   array $methods WooCommerce payment methods.
     *
     * @return  array          Payment methods with PagSeguro.
     */
    public function add_gateway( $methods ) {
        $methods[] = 'WC_Conekta_Oxxo_Gateway';

        return $methods;
    }

    
    public function woocommerce_error() {
        echo '<div class="error"><p>Conekta Oxxo Gateway depends on the last version or 2.1 to work!</p></div>';
    }
    
    public function woocommerce_error_php() {
        echo '<div class="error"><p>Conekta OXXO needs to be run on PHP >= 5.3.0</p></div>';
    }
    
    
}

add_action( 'plugins_loaded', array( 'WC_Conekta_Oxxo_Payment', 'get_instance' ), 0 );

endif;
?>