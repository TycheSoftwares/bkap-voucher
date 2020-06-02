<?php
function bkapef_coupon_code( $product_id, $amount ) {

    $coupon_data = array( 'id' => '', 'coupon' => '' );
    
    $ten_random_string = bkapef_random_string();
    $first_two_digit   = rand( 0, 99 );
    $final_string      = $first_two_digit.$ten_random_string;
    $coupon_code       = $final_string;

    // Add meta
    $coupon_meta = array(   'discount_type'                 => 'fixed_product',
                            'coupon_amount'                 => $amount,
                            'minimum_amount'                => '',
                            'maximum_amount'                => '',
                            'individual_use'                => 'no',
                            'free_shipping'                 => 'no',
                            'product_ids'                   => $product_id,
                            'exclude_product_ids'           => '',
                            'usage_limit'                   => 1,
                            'usage_limit_per_user'          => 1,
                            'date_expires'                  => strtotime( '+ 7 days' ),
                            'apply_before_tax'              => 'yes',
                            'product_ids'                   => '',
                            'exclude_sale_items'            => 'no',
                            'exclude_product_ids'           => '',
                            'product_categories'            => array(),
                            'exclude_product_categories'    => array(),
                        );

    $coupon         = apply_filters( 'bkapef_before_shop_coupon_create',
                            array(
                                'post_title'   => $coupon_code,
                                'post_content' => 'This coupon provides 100% discount as voucher.',
                                'post_status'  => 'publish',
                                'post_author'  => 1,
                                'post_type'    => 'shop_coupon',
                                'post_expiry_date' => strtotime( '+ 7 days' ),
                                'meta_input'    => $coupon_meta,
                            )
                        );
    $new_coupon_id  = wp_insert_post( $coupon );

    $coupon_data[ 'id' ] = $new_coupon_id;
    $coupon_data[ 'coupon' ] = $final_string;
    
    return $coupon_data;
}

function bkapef_random_string() {
    $character_set_array   = array();
    $character_set_array[] = array( 'count' => 5, 'characters' => 'abcdefghijklmnopqrstuvwxyz' );
    $character_set_array[] = array( 'count' => 5, 'characters' => '0123456789' );
    $temp_array            = array();
    foreach ( $character_set_array as $character_set ) {
        for ( $i = 0; $i < $character_set['count']; $i++ ) {
                $temp_array[] = $character_set['characters'][ rand( 0, strlen( $character_set['characters'] ) - 1 ) ];
            }
        }
    shuffle( $temp_array );
    return implode( '', $temp_array );
}