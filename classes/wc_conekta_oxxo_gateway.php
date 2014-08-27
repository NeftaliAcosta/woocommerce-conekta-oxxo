<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
  /**
* WC Conekta OXXO Gateway Class.
* 
*/
class WC_Conekta_Oxxo_Gateway extends WC_Payment_Gateway {
    
    
        public function __construct(){
        
            $this->id = 'conektaoxxo';
            $this->method_title = 'Pago Conekta OXXO';
            $this->has_fields = false;            
            $this->method_description ='Plugin para generar el cargo para un cliente con Conekta utilizando los OXXO';
            
            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option( 'title' );
            $this->description  = $this->get_option( 'description' );
            $this->instructions = $this->get_option( 'instructions', $this->description );
            $this->private_key = $this->get_option( 'private_key' );

                        
            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );         
            add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
             // Checking if private_key is not empty.
             $this->private_key == '' ? add_action( 'admin_notices', array( &$this, 'private_key_missing_message' ) ) : '';
             
             // Valid for use.
             $this->enabled = ( 'yes' == $this->settings['enabled'] ) && !empty( $this->private_key ) && $this->is_valid_for_use();
             
            
          
        }
           
       /**
       * Initialise Settings Form Fields
       */
       public function init_form_fields() {
           $this->form_fields = array(
               'enabled' => array(
                   'title' => __( 'Enable/Disable', 'woocommerce' ),
                   'type' => 'checkbox',
                   'label' => __( 'Pago Conekta OXXO', 'woocommerce' ),
                   'default' => 'yes'
               ),
               'title' => array(
                   'title' => __( 'Title', 'woocommerce' ),
                   'type' => 'text',
                   'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                   'default' => __( 'OXXO Payment', 'woocommerce' ),
                   'desc_tip' => true,
               ),
               'private_key' => array(
                    'title' => __( 'Conekta Private key', 'woocommerce' ),
                    'type' => 'text',
                    'default' => ''
                ),                
               'description' => array(
                   'title' => __( 'Description', 'woocommerce' ),
                   'type' => 'textarea',
                   'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
                   'default' =>__( 'Por favor realiza el pago en el OXXO más cercano utilizando la clave que mandaremos a tu e-mail.', 'woocommerce' ),
                   'desc_tip' => true,
               ),
               'instructions' => array(
                   'title' => __( 'Instructions', 'woocommerce' ),
                   'type' => 'textarea',
                   'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
                   'default' => '',
                   'desc_tip' => true,
               ),
           );
       }
       
       /**
     * Output for the order received page.
     */
    public function thankyou_page() {
        if ( $this->instructions )
            echo wpautop( wptexturize( $this->instructions ) );
    }

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        if ( $this->instructions && ! $sent_to_admin && 'conektaoxxo' === $order->payment_method && 'on-hold' === $order->status ) {
            echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
        }
    }
       
       
    /**
    * Checking if this gateway is enabled and available in the user's currency.
    *
    * @return bool
    */
    public function is_valid_for_use() {
        if ( ! in_array( get_woocommerce_currency(), array( 'MXN' ) ) ) {
            return false;
        }
        return true;
    }
    
    /**
    * Adds error message when not configured the private_key.
    *
    * @return string Error Mensage.
    */
    public function private_key_missing_message() {
        $message = '<div class="error">';
        $message .= '<p>' . sprintf( '<strong>Gateway Coneckta OXXO Disabled</strong> Por favor ingresa el valor de la private key de Conekta. ' ) . '</p>';
        $message .= '</div>';
        echo $message;
    }
    

   public function process_payment( $order_id ) {
       
           global $woocommerce;
           
           $order = new WC_Order( $order_id );
           
           $amount = $order->get_total() * 100;
           
           Conekta::setApiKey($this->private_key);
                      
           try{
               
                $charge = Conekta_Charge::create(array(
                    "amount"=> $amount,
                    "currency"=> "MXN",
                    "description"=> "Recibo de pago para orden #".$order_id,
                    'reference_id'=>$order_id,
                    "cash"=> array(
                        "type"=>"oxxo"                        
                    )
                ));
                
                if ($charge->status == 'pending_payment')
                {
                        // Mark as on-hold (we're awaiting the cheque)
                        $order->update_status('on-hold', __( 'Awaiting the conekta OXOO payment', 'woocommerce' ));

                        // Reduce stock levels
                        $order->reduce_order_stock();

                        // Remove cart
                        $woocommerce->cart->empty_cart();
                        
                         update_post_meta( $order_id, 'ckta-id', $charge->id );
                         update_post_meta( $order_id, 'ckta-creado', $charge->created_at );
                         update_post_meta( $order_id, 'ckta-expira', $charge->payment_method->expiry_date );
                         update_post_meta( $order_id, 'ckta-barcode', $charge->payment_method->barcode );
                         update_post_meta( $order_id, 'ckta-barcodeurl', $charge->payment_method->barcode_url );
                         

                        
                        return array(
                            'result'     => 'success',
                            'redirect'    => $this->get_return_url( $order )
                        );
                }
                else{
                    
                    $order->add_order_note('Error en cargo OXXO: '.$charge->status);
                    $order->update_status('Failed', __( 'Error conekta payment', 'woocommerce' ));
                    $woocommerce->add_error( "Existio un problema con el cargo, por favor reintenta." );
                    
                }              
                
           }catch (Conekta_Error $e){
                    
                    $woocommerce->add_error( "Existio un problema con el cargo, por favor reintenta." );
                    $order->add_order_note('Error:'.$e->getMessage());
           }           
           
       }
       
}
?>