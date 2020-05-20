<?php
/*
Plugin Name: Edit Checkout Page
Plugin URI: https://github.com/jannisbrandt/Modify-the-cart-details-o-WooCommerce-Checkout-Page
description: Fügt in der Tabelle auf der Checkout-Seite von WooCommerce die Funktionalität des Ändern der Anzahl der Produkte hinzu.
Version: 1.2
Author: Jannis Brandt
Author URI: https://jannisbrandt.de
License: GPL2
*/
?>

<?php

    /**
    * Check if WooCommerce is active
    **/
    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        
        /*
        * remove the quantity count from checkout page table
        */

        function remove_quantity_text( $cart_item, $cart_item_key ) {
            $product_quantity= '';
            return $product_quantity;
        }

        add_filter ('woocommerce_checkout_cart_item_quantity', 'remove_quantity_text', 10, 2 );


        /*
         * will add delete button, quanitity field on the checkout page table
         */

        function add_quantity( $product_title, $cart_item, $cart_item_key ) {

            /* Checkout page check */
            if (  is_checkout() ) {
                /* Get Cart of the user */
                $cart     = WC()->cart->get_cart();
                foreach ( $cart as $cart_key => $cart_value ){
                    if ( $cart_key == $cart_item_key ){
                        $product_id = $cart_item['product_id'];
                        $_product   = $cart_item['data'] ;

                        /* Step 1 : Add delete icon */
                        $return_value = sprintf(
                            '<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                            esc_url( WC()->cart->get_remove_url( $cart_key ) ),
                            __( 'Remove this item', 'woocommerce' ),
                            esc_attr( $product_id ),
                            esc_attr( $_product->get_sku() )
                        );

                        /* Step 2 : Add product name */
                        $return_value .= '&nbsp; <span class = "product_name" >' . $product_title . '</span>' ;

                        /* Step 3 : Add quantity selector */
                        if ( $_product->is_sold_individually() ) {
                            $return_value .= sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_key );
                        } else {
                            $return_value .= woocommerce_quantity_input( array(
                                'input_name'  => "cart[{$cart_key}][qty]",
                                'input_value' => $cart_item['quantity'],
                                'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
                                'min_value'   => '1'
                            ), $_product, false );
                        }
                        return $return_value;
                    }
                }
            }else{
                /*
                 * It will return the product name on the cart page.
                 * As the filter used on checkout and cart are same.
                 */
                $_product   = $cart_item['data'] ;
                $product_permalink = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';
                if ( ! $product_permalink ) {
                    $return_value = $_product->get_title() . '&nbsp;';
                } else {
                    $return_value = sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_title());
                }
                return $return_value;
            }
        }

        add_filter ('woocommerce_cart_item_name', 'add_quantity' , 10, 3 );


        /* Add Javascript at the Footer */

        function add_quanity_js(){
            if ( is_checkout() ) {
                wp_enqueue_script( 'checkout_script', plugins_url( 'assets/js/add_quantity.js', __FILE__ ), '', '', false );
                $localize_script = array(
                    'ajax_url' => admin_url( 'admin-ajax.php' )
                );
                wp_localize_script( 'checkout_script', 'add_quantity', $localize_script );
            }
        }

        add_action( 'wp_footer', 'add_quanity_js', 10 );


        function load_ajax()
        {
            if (!is_user_logged_in()) {
                add_action('wp_ajax_nopriv_update_order_review', 'update_order_review');
            } else {
                add_action('wp_ajax_update_order_review', 'update_order_review');
            }
        }

        add_action('init', 'load_ajax');


        function update_order_review()
        {
            $values = array();
            parse_str($_POST['post_data'], $values);
            $cart = $values['cart'];
            foreach ($cart as $cart_key => $cart_value) {
                WC()->cart->set_quantity($cart_key, $cart_value['qty'], false);
                WC()->cart->calculate_totals();
                woocommerce_cart_totals();
            }
            wp_die();
        }

        function add_css(){
            if (  is_checkout() ) {
                wp_enqueue_style( 'checkout_style', plugins_url( 'assets/css/edit-checkout.css', __FILE__ ), '', '', false );
            }
        }

        add_action( 'wp_footer', 'add_css', 10 );

}