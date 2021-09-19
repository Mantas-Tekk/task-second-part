<?php

defined('ABSPATH') || exit;

require_once dirname(__FILE__) . '/../../spell-woocommerce.php';

add_action('woocommerce_api_wc_spell_callback', 'handle_spell_callback');
function handle_spell_callback()
{
    class Spell_Callback_Controller
    {
        public function __construct()
        {
            $this->spellPayment = new WC_Spell_Gateway();
            $this->create_order_from_return_data();
        }

        public function create_order_from_return_data()
        {
            // If order was paid for, then create order
            if ($_GET['action'] == 'paid') {
                $order = wc_create_order();
                $orderProducts = WC()->session->get('spell_direct_items');
                $paymentId = WC()->session->get('spell_direct_payment_id');
                $paymentData = $this->spellPayment->get_payment_data($paymentId);
                $orderData = $paymentData['client'];

                foreach ($orderProducts as $orderProduct) {
                    $order->add_product($orderProduct['product'], (int)$orderProduct['quantity']);
                }

                $order->set_address($this->get_address_from_order_data($orderData),'billing');
                $order->set_address($this->get_address_from_order_data($orderData),'shipping');
                $order->set_payment_method($this->spellPayment);
                $order->set_transaction_id($paymentId);
                $order->set_date_paid(time());
                $order->calculate_totals();
                $order->update_status('completed', $paymentData['purchase']['notes'], TRUE);
                WC()->cart->empty_cart();
                wp_redirect(site_url());
            } else {
                wp_redirect(site_url('cart'));
            }
        }

        public function get_address_from_order_data($orderData)
        {
            return array(
                'first_name' => $orderData['full_name'], // @todo: Split into 'first_name' and 'last_name' if necessary
                'last_name'  => '',
                'company'    => '',
                'email'      => $orderData['email'],
                'phone'      => $orderData['phone'],
                'address_1'  => $orderData['shipping_street_address'],
                'address_2'  => '',
                'city'       => $orderData['shipping_city'],
                'state'      => '',
                'postcode'   => $orderData['shipping_zip_code'],
                'country'    => $orderData['shipping_country']
            );
        }
    }

    new Spell_Callback_Controller();
}
