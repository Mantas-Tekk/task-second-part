<?php

defined('ABSPATH') || exit;

require_once dirname(__FILE__) . '/../../spell-woocommerce.php';

add_action('woocommerce_api_wc_spell_pay', 'redirect_to_spell_checkout_from_cart');
function redirect_to_spell_checkout_from_cart()
{
    class Spell_Cart_Controller
    {
        public function __construct()
        {
            $this->spellPayment = new WC_Spell_Gateway();
            $this->redirect();
        }

        public function redirect()
        {
            $cart = WC()->cart->get_cart();
            $products = array();
            $productObjects = array();

            foreach ($cart as $key => $cart_item) {
                $cart_product = $cart_item['data'];
                $name = method_exists( $cart_product, 'get_name' ) === true ? $cart_product->get_name() : $cart_product->name;
                $products[] = array(
                    'product_id' => $cart_item['product_id'],
                    'name' => $name,
                    'price' => round($cart_item['line_total'] * 100) / $cart_item['quantity'],
                    'quantity' => $cart_item['quantity']
                );
                $productObjects[] = [
                    'product' => $cart_product,
                    'quantity' => $cart_item['quantity']
                ];
            }

            WC()->session->set('spell_direct_items', $products);
            $result = $this->spellPayment->process_direct_payment($products);

            if ($result['status'] != 'failure' && $result['data']['checkout_url']) {
                WC()->session->set('spell_direct_payment_id', $result['data']['id']);
                wp_redirect($result['data']['checkout_url']);
            } else {
                wp_redirect($_SERVER['HTTP_REFERER']);
            }

            exit;
        }
    }

    new Spell_Cart_Controller();
}
