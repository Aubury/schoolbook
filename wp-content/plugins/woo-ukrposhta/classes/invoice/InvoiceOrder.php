<?php

namespace deliveryplugin\Ukrposhta\classes\invoice;

use deliveryplugin\Ukrposhta\classes\invoice\Sender;

// If this file is called directly, abort.
defined( 'ABSPATH' ) or die();

class InvoiceOrder
{
	public $order_id;

	public $order_data;

	public $weightMsg = '';

	public $isNotDimensions = false;

    public function __construct()
    {
        $this->order_id = $this->getOrderId();
        $this->order_data = $this->getOrderData();
        $this->isNotDimensions = $this->isNotOrderDimensions();
    }

	public function getOrderId()
	{
		if ( isset($_SESSION['order_id'] ) ) {
			$order_id = $_SESSION['order_id'];
			return $order_id;
		}
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$referer_url = parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_QUERY );
			parse_str( $referer_url, $query_url_arr );
			if ( ! empty( $query_url_arr['post'] ) ) {
				$_SESSION['order_id'] = $query_url_arr['post'];
				$order_id = $query_url_arr['post'];
				return $order_id;
			}
		}
		return null;
	}

	public function getOrderData()
	{
		$this->order_data = \wc_get_order($this->order_id);
		return  \wc_get_order($this->order_id);
	}

	public function getUnitWeight()
	{
		$wc_unit_weihgt = \get_option( 'woocommerce_weight_unit' );
		switch ( $wc_unit_weihgt ) {
		  case 'g':
			  return $weight_coef = 1;
			  break;
		  case 'kg':
			  return $weight_coef = 1000;
			  break;
		  case 'lbs':
			  return $weight_coef = 453.59;
			  break;
		  case 'oz':
			  return $weight_coef = 28.34;
			  break;
		}
		return false;
	}

	public function translateUnitWeightName() {
		return __( get_option( 'woocommerce_weight_unit' ), 'woocommerce' );
	}

	public function getWeight()
	{
		$wp_option_weight = $this->getWPOptionWeight();
		$invoiceCargoMass = isset( $_POST['invoice_cargo_mass'] ) ? \sanitize_text_field( $_POST['invoice_cargo_mass'] ) : 0;
		$order_weight = $this->getOrderWeight();
		$weight = 0;
		if ( $invoiceCargoMass > 0 ) {
		    $weight = $invoiceCargoMass;
		} elseif ( $wp_option_weight > 0 ) {
		    $weight = $wp_option_weight;
		} elseif ( $order_weight > 0 ) {
		    $weight = $this->getUnitWeight() * $order_weight;
		} else {
		    $weight = 0;
		}
		return $weight;
	}

	public function getWPOptionWeight()
	{
		return ! empty(get_option( 'mrkvup_default_order_weight ') )
			?  \sanitize_text_field( esc_attr( get_option( 'mrkvup_default_order_weight ') ) ) : 0;
	}

	public function getLength()
	{
		$dimension_unit = \get_option( 'woocommerce_dimension_unit' ); // Одиниця виміру розмірів встановлена на сайті (м, см, мм, ...)
		$wp_option_lenght = $this->getWPOptionLength();
		$input_length = isset( $_POST['invoice_volume'] ) ? intval( ceil( floatval( \sanitize_text_field( $_POST['invoice_volume'] ) ) ) ) : 0;
		$length_max = $this->getOrderMaxSizeCm( $this->order_data );
		if ( $input_length > 0 ) {
		    $length = $input_length;
		} elseif ( $wp_option_lenght > 0 ) {
		    $length = $wp_option_lenght;
		} elseif ( \wc_get_dimension( $length_max, 'cm', $dimension_unit ) > 0 ) {
		    $length = \wc_get_dimension( $length_max, 'cm', $dimension_unit );
		} else {
		    $length = 0;
		}
		return $length;
	}

	public function getWPOptionLength()
	{
		return ! empty(get_option( 'mrkvup_default_order_length ') )
			?  \sanitize_text_field( esc_attr( get_option( 'mrkvup_default_order_length ') ) ) : 0;
	}

	public function getOrderWeight()
	{
		if ( ! isset( $this->order_id ) ) return;
		$order_weight = 0;
		foreach( $this->order_data->get_items() as $item => $value ) {
	        if ( $value->get_product_id() > 0 ) {
	            $_product = $value->get_product();
                if ( ! $_product->is_virtual() ) {
                    $order_weight += floatval( $_product->get_weight() ) * intval( $value->get_quantity() );
                }
				if (  $_product->get_parent_id() ) {
					if ( $value->get_variation_id() > 0) {
						$variation_id = $value->get_variation_id();
						$variation = wc_get_product($variation_id);
						$order_weight += floatval( $variation->get_weight() ) * intval( $value->get_quantity() );
					}
                }
	        }
        }
		return $order_weight;
	}

	public function getOrderDimensions()
	{
		if ( ! isset( $this->order_id ) ) return;
		$dimensions = array();
		$length = array();
		$width = array();
		$height = array();
		foreach( $this->order_data->get_items() as $item ) {
	        if ( $item->get_product_id() > 0 ) {
	            $_product = $item->get_product();
                if ( ! $_product->is_virtual() ) {
					array_push( $dimensions, floatval( $_product->get_length() ), floatval( $_product->get_width() ), floatval( $_product->get_height() ) );
					if ( ! floatval( $_product->get_length() ) > 0 || ! floatval( $_product->get_width() ) > 0 || ! floatval( $_product->get_height() ) > 0 ) {
						$this->weightMsg = 'Маса вказана не  для всіх товарів в кошику. Радимо уточнити цифри.';
					}
                }
	        }
        }
		return $dimensions;
	}

	public function isNotOrderDimensions()
	{
		if ( $this->getOrderDimensions() > 0 ) {
			return false;
		}
		return true;
	}

	public function getOrderVolumeWeight()
	{
		if ( ! isset( $this->order_id ) ) return;
		$volumeWeight = 0;
		foreach( $this->order_data->get_items() as $item ) {
			if ( $item->get_product_id() > 0 ) {
				$_product = $item->get_product();
				if ( ! $_product->is_virtual() ) {
					$volumeWeightProduct = floatval( $_product->get_length() ) * floatval( $_product->get_width() ) * floatval( $_product->get_height() );
					$volumeWeight += $volumeWeightProduct;
				}
			}
		}
		return $volumeWeight / 5000;
	}

	public function getOrderItems()
	{
		return $this->order_data->get_items();
	}

	public function getOrderMaxSizeCm($order_data)
	{
	    $dimension_unit = \get_option( 'woocommerce_dimension_unit' );
	    $order_data = $this->getOrderData();
	    if ( isset( $order_data ) && is_object( $order_data ) ) {
	        $items = $order_data->get_items();

	        if ( ! empty( $items ) ) {
				$product_sizes = array();
	            foreach ( $items as $item_id => $item ) {
	                $_product = $item->get_product();
	                $product_sizes = array(
	                    wc_get_dimension( $_product->get_length(), 'cm', $dimension_unit )  ,
	                    wc_get_dimension( $_product->get_width(), 'cm', $dimension_unit )  ,
	                    wc_get_dimension( $_product->get_height(), 'cm', $dimension_unit )
	                );
					return max( $product_sizes );
	            }

				$length_max = max( $product_sizes );
				$length_max = ! empty($lenght_max) ? $length_max : 0;
				return $length_max;
	        }
	    } else {
	        return wp_die('<h3>Для створення накладної перейдіть на <a href="edit.php?post_type=shop_order">сторінку Замовлення</a></h3>');
	    }
	}

	public function getDeliveryType() // 'W2W' or 'W2D'?
	{
		$shipping_methods = $this->order_data->get_shipping_methods();
		$shipping_method = @array_shift( $shipping_methods );
		$shipping_method_id = $shipping_method->get_method_id();
		 // W2W: Відділення - Відділення, W2D: Відділення - Двері
		$deliveryType = ( 'ukrposhta_shippping' == $shipping_method_id ) ? 'W2W' : 'W2D';
		return $deliveryType;
	}

	public function getShipmentType() // // 'EXPRESS' or 'STANDART'?
	{
		return isset( $_POST['sendtype'] ) ? $_POST['sendtype'] : \sanitize_text_field( get_option( ' sendtype' ) );
	}

	public function isPaidByRecipient()
	{
		$paidByRecipient = false;
		$paidByRecipient = ( 'mrkvup_recipient' == \sanitize_text_field( get_option( 'morkva_ukrposhta_default_payer' ) ) )
			? true : false;
		if ( isset( $_POST['mrkvup_default_payer'] ) ) {
		    $paidByRecipient = ( 'mrkvup_recipient' == \sanitize_text_field( $_POST['mrkvup_default_payer'] ) ) ? true : false;
		}
		return $paidByRecipient;
	}

	public function getDescription()
	{
		return isset( $_POST['up_invoice_description'] )
			? \sanitize_textarea_field( $_POST['up_invoice_description'] )
			: \sanitize_textarea_field( get_option( 'up_invoice_description' ) );
	}

	public function getOnFailReceveType()
	{
		return isset( $_POST['onFailReceiveType'] ) ? $_POST['onFailReceiveType'] : 'RETURN';
	}

	public function getPostPay()
	{
		return isset( $_POST['invoice_places'] ) ? \sanitize_text_field( $_POST['invoice_places'] ) : 0;
	}

	public function getDeclaredPrice()
	{
		return isset( $_POST['declaredPrice'] ) ? floatval( \sanitize_text_field( $_POST['declaredPrice'] ) ) : 0;
	}

	public function isShowSenderBankAccountOnSticker()
	{
	    $sender = new Sender();
	    $sender_type = $sender->getType();
	    $shipment_type = $this->getShipmentType();
	    if ( ('STANDARD' == $shipment_type || 'EXPRESS' == $shipment_type) &&
	        ( 'COMPANY' == $sender_type || 'PRIVATE_ENTREPRENEUR' == $sender_type ) &&
	        $_POST['declaredPrice'] && $this->order_data->get_payment_method() == 'cod' ) {
	       return $transferPostPayToBankAccount = get_option( 'morkva_ukrposhta_transfer_postpay_to_sender_bank_account' ) ? true : false;
	   } else {
	       return $transferPostPayToBankAccount = false;
	   }
	}

	// Function group for get shipping filds values if billing fields values are empty
	public function getShippingFirstName()
	{
		return $shipping_first_name = $this->order_data->get_billing_first_name()
			??  $this->order_data->get_shipping_first_name() ?? '';
	}

	public function getShippingLastName()
	{
		return $shipping_last_name = $this->order_data->get_billing_last_name()
			??  $this->order_data->get_shipping_last_name() ?? '';
	}

	public function getShippingMiddleName()
	{
		return $shipping_surname = $this->order_data->get_billing_company()
			?? $this->order_data->get_shipping_company() ?? '';
	}

	public function getShippingPostcode()
	{
		return $shipping_postcode = $this->order_data->get_billing_postcode()
			??  $this->order_data->get_shipping_postcode() ?? '';
	}

	public function getShippingAddress_1() // Apartment, office, block etc.
	{
		return $shipping_address_1 = $this->order_data->get_billing_address_1()
			??  $this->order_data->get_shipping_address_1() ?? '';
	}

	public function getShippingAddress_2() // Apartment, office, block etc.
	{
		return $shipping_address_2 = $this->order_data->get_billing_address_2()
			??  $this->order_data->get_shipping_address_2() ?? '';
	}

	public function getShippingCity()
	{
		return $shipping_city = $this->order_data->get_billing_city()
			??  $this->order_data->get_shipping_city() ?? '';
	}

	public function getShippingState() // Region
	{
		return $shipping_state = $this->order_data->get_billing_state()
			??  $this->order_data->get_shipping_state() ?? '';
	}

	public function getShippingPhone() // Phone number validation
	{
		$shipping_phone = ( $this->order_data->get_billing_phone() ??  $this->order_data->get_shipping_phone() ) ?? '';
		if ( $this->stringStartsWith( '38', $shipping_phone ) ) return substr( $shipping_phone, 2 );
		if ( $this->stringStartsWith( '+38', $shipping_phone ) ) return substr( $shipping_phone, 3 );
		if ( $this->stringStartsWith( '8', $shipping_phone ) ) return substr( $shipping_phone, 1 );
		return $shipping_phone;
	}

	public function stringStartsWith($startString, $string)
	{
	    $len = strlen( $startString );
	    return ( substr( $string, 0, $len ) === $startString );
	}

	public function displayDetailedAddress()
	{
		echo $this->getShippingAddress_1() . ' ' . $this->getShippingAddress_2() . ', ' . $this->getShippingPostcode();
	}

}
