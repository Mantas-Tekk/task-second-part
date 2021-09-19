<?php

defined( 'ABSPATH' ) || exit;

require_once dirname(__FILE__) . '/../../spell-woocommerce.php';

add_action('woocommerce_after_add_to_cart_button', 'add_direct_payment_button_in_product');
function add_direct_payment_button_in_product()
{
    class Spell_Product
    {
        public function __construct()
        {
            $this->spellPayment = new WC_Spell_Gateway();
        }

        public function init_button()
        {
            global $product;

            $is_in_stock = method_exists( $product, 'is_in_stock' ) === true ? $product->is_in_stock() : $product->is_in_stock;
            $product_id = method_exists( $product, 'get_id' ) === true ? $product->get_id() : $product->id;
            $enabled = $this->spellPayment->get_option('enabled') === 'yes' ? true : false;
            $direct_payment = $this->spellPayment->get_option('direct_payment_enabled') === 'yes' ? true : false;
            $direct_payment_text = $this->spellPayment->get_option('direct_payment_text');
            $direct_payment_styles = $this->spellPayment->get_option('direct_payment_styles_pdp');
            $is_direct_payment_enabled = $enabled && $direct_payment;
            $image_url = $this->spellPayment->get_button_image_url();

            if ($is_in_stock && $product_id && $is_direct_payment_enabled) {
                $button_html = '<div class="direct-payment-wrapper"><button type="submit" name="add-to-cart-direct" value="1" class="direct-payment-button button alt"><img src="'. $image_url . '"></button></div>';

                if ($direct_payment_styles !== '') {
                    $button_html = '<style>'.$direct_payment_styles.'</style>'.$button_html;
                }

                // Add hidden input for simple and external products for the direct payment button to function correctly
                if (!in_array($product->get_type(), array('variable', 'grouped'))) {
                    $button_html .= '<input type="hidden" name="add-to-cart" value="'.$product_id.'"/>';
                }

                return $button_html;
            }

            return '';
        }
    }

    $GLOBALS['wc_spell_product'] = new Spell_Product();
    echo $GLOBALS['wc_spell_product']->init_button();
}
