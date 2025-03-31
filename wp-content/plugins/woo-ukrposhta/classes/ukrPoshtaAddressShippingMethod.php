<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

class ukrPoshtaAddressShippingMethod extends WC_Shipping_Method
{
  public function __construct($instance_id = 0)
  {
    parent::__construct($instance_id);
    $this->id = MORKVA_UKRPOSHTA_UP_ADDRESS_SHIPPING_NAME;
    $this->method_title = MORKVA_UKRPOSHTA_UP_ADDRESS_SHIPPING_TITLE;
    $this->method_description = '';

    $this->supports = array(
	    'shipping-zones',
	    'instance-settings',
	    'instance-settings-modal',
    );

    $this->init();
  }

	public function __get($name) {
		return $this->$name;
	}

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    public function init()
    {
      $this->init_form_fields();
      $this->init_settings();

      $translator = new \deliveryplugin\Ukrposhta\classes\UPTranslator();
      $translates = $translator->getTranslates();

	    // $this->title = $translates['method_title'];
        $this->title = $this->get_option( 'title' );
      // Save settings in admin if you have any defined
      add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

	/**
     * calculate_shipping function.
     *
     * @access public
     *
     * @param array $package
     */
    public function calculate_shipping($package = array())
    {
      $rate = array(
        'label' => $this->title,
        'cost' => intval(get_option('morkva_ukrposhta_default_price')),
        'package' => $package,
      );
	    $this->add_rate($rate);
    }

    /**
     * Is this method available?
     * @param array $package
     * @return bool
     */
    public function is_available($package)
    {
        return $this->is_enabled();
    }

    //Fields for the modal form on the tab Shipping of the Zones admin page
    public function init_form_fields() {

        $this->instance_form_fields = array(

            'title' => array(
                'title' => __( 'Title', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                'default' => __('Укрпошта на Адресу ', 'woocommerce' ),
                'desc_tip' => true,
            )
        );
    }

}