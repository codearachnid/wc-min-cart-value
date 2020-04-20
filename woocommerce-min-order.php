<?php

   /*
   Plugin Name: WooCommerce Minimum Order Amount
   Description: Add the option for a WooCommerce minimum order amount, customized by cart amount or specific products. Includes customization of notification texts for the cart and checkout pages
   Version: 1.0
   Author: Timothy Wood @codearachnid
   Author URI: https://github.com/codearachnid
   License: GPLv3 or later License
   URI: http://www.gnu.org/licenses/gpl-3.0.html
   */

   if ( ! defined( 'ABSPATH' ) ) {
       exit; // Exit if accessed directly
   }
  /* Settings */

  add_filter( 'woocommerce_general_settings','wc_min_order_settings', 10, 2 );
  function wc_min_order_settings( $settings ) {

      $settings[] = array(
        'title' => __( 'Minimum order settings', 'wc_minimum_order_amount' ),
        'type' => 'title',
        'desc' => 'Set the minimum order amount and adjust notifications',
        'id' => 'wc_min_order_settings'
      );

        // Minimum order amount
        $settings[] = array(
          'title'             => __( 'Minimum order amount', 'woocommerce' ),
          'desc'              => __( 'Leave this empty if all orders are accepted, otherwise set the minimum order amount', 'wc_minimum_order_amount' ),
          'id'                => 'wc_min_order_value',
          'default'           => '',
          'type'              => 'number',
          'desc_tip'          => true,
          'css'      => 'width:70px;'
      );

      // Minimum order amount
        $settings[] = array(
          'title'             => __( 'Product override', 'woocommerce' ),
          'desc'              => __( 'If product ID is in this list then cart meets minimum order amount.', 'wc_minimum_order_amount' ),
          'id'                => 'wc_min_order_override_ids',
          'default'           => '',
          'type'              => 'text',
          'desc_tip'          => true
      );

      // Cart message
        $settings[] = array(
          'title'    => __( 'Cart message', 'woocommerce' ),
          'desc'     => __( 'Show this message if the current order total is less than the defined minimum - for example "50".', 'wc_minimum_order_amount' ),
          'id'       => 'wc_min_order_cart_notification',
          'default'  => 'Your current order total is %s — your order must be at least %s.',
          'type'     => 'text',
          'desc_tip' => true,
          'css'      => 'width:500px;'
      );

      // Checkout message
        $settings[] = array(
          'title'    => __( 'Checkout message', 'woocommerce' ),
          'desc'     => __( 'Show this message if the current order total is less than the defined minimum', 'wc_minimum_order_amount' ),
          'id'       => 'wc_min_order_checkout_notification',
          'default'  => 'Your current order total is %s — your order must be at least %s.',
          'type'     => 'text',
          'desc_tip' => true,
          'css'      => 'width:500px;'
        );

      $settings[] = array( 'type' => 'sectionend', 'id' => 'wc_min_order_settings' );
      return $settings;
  }

/* Notices and checks */

add_action( 'woocommerce_checkout_process', 'wc_min_order_amount_check' );
add_action( 'woocommerce_before_cart' , 'wc_min_order_amount_check' );

function wc_min_order_amount_check() {

      // Get the minimum value from settings
      $minimum = get_option( 'wc_min_order_value' );
      $override_product_ids = explode( ',', get_option( 'wc_min_order_override_ids' ) );
      $override_product_in_cart = false;

      foreach ( WC()->cart->get_cart() as $cart_item ) {
          $product = $cart_item['data'];
          if( ! empty($product) ){
              $product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;
              if ( in_array( $product_id, $override_product_ids ) && !$override_product_in_cart ) {
                $override_product_in_cart = true;
              }
          }
      }

      // check if the minimum value has even been set
      if (!$override_product_in_cart && $minimum && WC()->cart->total < $minimum ) {

          if( is_cart() ) {

              wc_print_notice(
                  sprintf( get_option( 'wc_min_order_cart_notification' ),
                      wc_price( WC()->cart->total ),
                      wc_price( $minimum )
                  ), 'error' );

          } else {

              wc_add_notice(
                  sprintf( get_option( 'wc_min_order_checkout_notification' ) ,
                      wc_price( WC()->cart->total ),
                      wc_price( $minimum )
                  ), 'error' );
          }
      }
    }
