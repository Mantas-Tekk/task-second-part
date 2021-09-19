<?php

defined( 'ABSPATH' ) || exit;

require_once dirname(__FILE__) . '/../../spell-woocommerce.php';

add_action('woocommerce_after_cart_totals', 'add_direct_payment_button_in_cart');
function add_direct_payment_button_in_cart()
{
    class Spell_Cart
    {
        public function __construct()
        {
            $this->spellPayment = new WC_Spell_Gateway();
        }

        public function init_button()
        {
            $enabled = $this->spellPayment->get_option('enabled') === 'yes' ? true : false;
            $direct_payment = $this->spellPayment->get_option('direct_payment_enabled') === 'yes' ? true : false;
            $direct_payment_text = $this->spellPayment->get_option('direct_payment_text');
            $direct_payment_styles = $this->spellPayment->get_option('direct_payment_styles_cart');
            $is_direct_payment_enabled = $enabled && $direct_payment;
            $image_url = $this->spellPayment->get_button_image_url();
            $url = home_url() . '/?wc-api=wc_spell_pay';

            if ($is_direct_payment_enabled) {
                $button_html = '<div class="direct-payment-wrapper"><a href="'.$url.'" class="direct-payment-button button alt"><img src="'. $image_url . '"></a></div>';

                if ($direct_payment_styles !== '') {
                    $button_html = '<style>'.$direct_payment_styles.'</style>'.$button_html;
                }

                return $button_html;
            }

            return '';
        }
    }

    $GLOBALS['wc_spell_cart'] = new Spell_Cart();
    echo $GLOBALS['wc_spell_cart']->init_button();
}
