<?php
/**
 * Class for add liqpay to wordpress menu
 * 
 * */
Class MorkvaLiqpayMenu
{
    /**
     * Slug for page in Woo Tab Sections
     * 
     * */
    public $slug = 'admin.php?page=wc-settings&tab=checkout&section=morkva-liqpay';

    /**
     * Constructor for create menu
     * 
     * */
    public function __construct()
    {
        # Add menu
        add_action('admin_menu', array($this, 'register_admin_menu'));
    }

    /**
     * Register menu page
     * 
     * */
    public function register_admin_menu()
    {
        # Add menu Liqpay
        add_menu_page(__('Morkva LiqPay', 'mrkv-liqpay-extended'), __('Morkva LiqPay', 'mrkv-liqpay-extended'), 'manage_options', $this->slug, false, plugin_dir_url(__DIR__) . 'img/morkva-liqpay-logo.svg', 26);
    }
}