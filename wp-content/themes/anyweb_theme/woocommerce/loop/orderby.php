<?php
/**
 * Show options for ordering
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/orderby.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="filters-box">
        <div class="filters-box__holder">
            <div class="filter-container">

                <div class="sorting-holder">
                    <div class="sorting">
                        <span class="sorting-title">Сортувати за:</span>
						<form class="woocommerce-ordering" method="get">
                        <ul class="sorting-list">
						<?php foreach ( $catalog_orderby_options as $id => $name ) :
                            if($id=='price-desc' || $id=='price'){
                                if($id=='price-desc'){
                                    echo '<li><a href="?orderby=' . esc_attr( $id ) . '" class="orderby-link"><span>' . esc_html( $name ) . '</span><i class="icon-price-down"></i></a></li>';
                                }
                                if($id=='price'){
                                    echo '<li><a href="?orderby=' . esc_attr( $id ) . '" class="orderby-link"><span>' . esc_html( $name ) . '</span><i class="icon-price-up"></i></a></li>';
                                }
                            }else{
							echo '<li><a href="?orderby=' . esc_attr( $id ) . '" class="orderby-link"><span>' . esc_html( $name ). '</span></a></li>';
                            }
						endforeach;
						?>

                        </ul>
						<?php wc_query_string_form_fields( null, array( 'orderby', 'submit' ) ); ?>
						</form>
                    </div>
                </div>
            </div>
        </div>
    </div>