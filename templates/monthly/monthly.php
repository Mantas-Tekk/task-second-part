<?php

defined( 'ABSPATH' ) || exit;

require_once dirname(__FILE__) . '/../../spell-woocommerce.php';

add_action('woocommerce_after_add_to_cart_button', 'add_direct_payment_button_in_product_test');
function add_direct_payment_button_in_product_test()
{
    class Spell_Product_Monthly
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

            if ($is_in_stock && $product_id && $enabled && $product->price > 50 &&  $product->price < 1400) {
                $form_html = '<div class="direct-payment-wrapper">';


                $form_html .= '<div class="monthly-pay-container">

                <!-- loan slider -->
                <div class="slidecontainer">
                  <input type="range" min="50" max="1400" value="'.$product->price.'" class="slider" id="payment-slider">
                  <p>Selected amount: <span id="payment-slider-text"></span> EUR</p>
                </div>
                <!--loan period slider  -->
                <div class="slidecontainer">
                  <input type="range" min="3" max="24" value="12" class="slider" id="payment-slider1">
                  <p>Period: <span id="payment-slider-text1"></span> months</p>
                </div>
                 <!--  monthly cost to orepay the loan -->
                <div class="slidecontainer">
                   <p>Monthly cost: <span id="payment-slider-text2"></span> EUR</p>
                </div>
                
                <button onclick="getOutput()" type="button"">Calculate</button>
                </div>
                </div>
                <script>
                let slider = document.getElementById("payment-slider");
                let output = document.getElementById("payment-slider-text");
                output.innerHTML = slider.value;
                
                slider.oninput = function() {
                  output.innerHTML = this.value;
                }
                
                let slider1 = document.getElementById("payment-slider1");
                let output1 = document.getElementById("payment-slider-text1");
                output1.innerHTML = slider1.value;
                
                slider1.oninput = function() {
                output1.innerHTML = this.value;
                }
                
                <!-- Mocked api -->
                let output2 = document.getElementById("payment-slider-text2");
                function getOutput() {
                    const xhttp = new XMLHttpRequest();
                      xhttp.onload = function() {
                        let res = this.responseText;
                        document.getElementById("payment-slider-text2").innerHTML = parseFloat(res.replace("\"",""));
                        }
                      xhttp.open("POST", "'.plugins_url( 'mocked-api.php', __FILE__ ).'", true);
                      <!-- Sends loan value and period -->
                      xhttp.send(JSON.stringify({ "loan": slider.value, "months": slider1.value }));

                }
                </script>';
                return $form_html;
            }
            return '';
        }
    }

    $GLOBALS['wc_spell_product_montlhy'] = new Spell_Product_Monthly();
    echo $GLOBALS['wc_spell_product_montlhy']->init_button();
}
