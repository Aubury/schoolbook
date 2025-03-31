<?php
/*
  Register all actions and filters for the plugin
*/

use deliveryplugin\Ukrposhta\classes\invoice\UkrposhtaApiClass;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Utilities\OrderUtil;

require ("class-morkvaup-plugin-callbacks.php");



class MUP_Plugin_Loader
{

    public $tdb = MUP_TABLEDB;
    /**
     * The array of pages for plugin menu
     *
     * @since 1.0.0
     * @access protected
     * @var array $pages    Pages for plugin menu
     */
    protected $pages;

    /**
     * The array of subpages for plugin menu
     *
     * @since 1.0.0
     * @access protected
     * @var array $subpages     Subpages for plugin menu
     */
    protected $subpages;

    /**
     * Array of settings groups fields for plugin
     *
     * @since 1.0.0
     * @access protected
     * @var array $settings
     */
    protected $settings;

    /**
     * Array of sections for settings fields for plugin
     *
     * @since 1.0.0
     * @access protected
     * @var array $sections
     */
    protected $sections;

    /**
     * Array of fields for settings fields for plugin
     *
     * @since 1.0.0
     * @access protected
     * @var array $fields
     */
    protected $fields;

    /**
     * The array of actions registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;
    /**
     * The array of filters registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;

    /**
     * Object of callbacks class
     *
     * @since   1.0.0
     * @access  protected
     * @var     string $callbacks       Class of callbacks
     */
    protected $callbacks;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        global $wp_settings_sections;
        $this->actions = array();
        $this->filters = array();
        $this->pages = array();
        $this->subpages = array();
        $this->settings = array();
        $this->sections = array();
        $this->fields = array();

        $this->callbacks = new MUP_Plugin_Callbacks();

        $this->add_settings_fields();
        $this->register_fields_sections();
        $this->register_settings_fields();

        $this->register_menu_pages();
        $this->register_menu_subpages();



        add_action( 'admin_menu', array( $this, 'register_plugin_menu' ) );
        add_action('add_meta_boxes', array( $this, 'mv_add_meta_boxes' ) );
        add_action('admin_init', array( $this, 'register_plugin_settings' ) );


        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled()){
            add_filter('manage_woocommerce_page_wc-orders_columns', array( $this, 'woo_custom_column' ), 20 );
            add_action('manage_woocommerce_page_wc-orders_custom_column', array( $this, 'woo_column_get_data_hpos' ), 20, 2  );
        }
        else{
            add_filter('manage_edit-shop_order_columns', array( $this, 'woo_custom_column' ) );
            add_action('manage_shop_order_posts_custom_column', array( $this, 'woo_column_get_data' ) );
        }

        add_filter('wp_mail_from_name', array( $this, 'my_mail_from_name' ) );
    }
    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             The name of the WordPress action that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the action is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */

    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }
    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }
    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    1.0.0
     * @access   private
     * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         The priority at which the function should be fired.
     * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
     * @return   array                                  The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
    {
        $hooks[] = array(
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        );
        return $hooks;
    }
    /**
     * Register the filters and actions with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        foreach ($this->filters as $hook)
        {
            add_filter($hook['hook'], array(
                $hook['component'],
                $hook['callback']
            ) , $hook['priority'], $hook['accepted_args']);
        }
        foreach ($this->actions as $hook)
        {
            add_action($hook['hook'], array(
                $hook['component'],
                $hook['callback']
            ) , $hook['priority'], $hook['accepted_args']);
        }
    }

    /**
     * Registering plugin pages to menu
     *
     * @since   1.0.0
     */
    public function register_menu_pages()
    {
        $this->pages = array(
            array(
                'page_title' => MUP_PLUGIN_NAME,
                'menu_title' => 'UkrPoshta ',
                'capability' => 'manage_woocommerce',
                'menu_slug' => 'morkvaup_plugin',
                'callback' => array( $this, 'add_settings_page' ),
                'icon_url' => MUP_PLUGIN_URL . "/image/menu-icon.png",
                'position' => 60
            )
        );
        return $this;
    }

    /**
     *  Add Plugin Settings page
     *
     *  @since  1.0.0
     */
    public function add_settings_page()
    {

        require_once (MUP_PLUGIN_PATH . '/admin/partials/morkvaup-plugin-settings.php');
    }

    /**
     * Registering subpages for menu of plugin
     *
     * @since   1.0.0
     */
    public function register_menu_subpages()
    {
        $title = "Налаштування";

        $this->subpages = array(
            array(
                'parent_slug' => 'morkvaup_plugin',
                'page_title' => 'Налаштування',
                'menu_title' => 'Налаштування',
                'capability' => 'manage_woocommerce',
                'menu_slug' => 'morkvaup_plugin',
                'callback' => array( $this, 'add_settings_page' )
            ),
            array(
                'parent_slug' => 'morkvaup_plugin',
                'page_title' => 'Відправлення',
                'menu_title' => 'Відправлення',
                'capability' => 'manage_woocommerce',
                'menu_slug' => 'morkvaup_invoice',
                'callback' => array( $this, 'add_invoice_page' )
            ),
            array(
                'parent_slug' => 'morkvaup_plugin',
                'page_title' => 'Мої відправлення',
                'menu_title' => 'Мої відправлення',
                'capability' => 'manage_woocommerce',
                'menu_slug' => 'morkvaup_invoices',
                'callback' => array( $this, 'invoices_page' )
            )
        );

        return $this;
    }

    /**
     * Adding subpage of plugin
     *
     * @since 1.0.0
     */
    public function add_invoice_page()
    {
        require_once (MUP_PLUGIN_PATH . 'admin/partials/morkvaup-plugin-form.php');
    }

    public function add_invoice_page_inter()
    {
        require_once (MUP_PLUGIN_PATH . 'admin/partials/morkvaup-plugin-form-inter.php');
    }

    /**
     * Add invoices subpage of plugin
     *
     * @since 1.0.0
     */
    public function invoices_page()
    {
        $path = MUP_PLUGIN_PATH . 'admin/partials/morkvaup-plugin-invoices-page.php';
        if (file_exists($path))
        {
            require_once ($path);
        }
        else
        {
            $path = MUP_PLUGIN_PATH . 'admin/partials/morkvaup-plugin-invoices-page-demo.php';
            require_once ($path);

        }
    }

    /**
     * Add about page of plugin
     *
     * @since 1.0.0
     */
    public function about_page()
    {
        //echo file_get_contents( MUP_PLUGIN_PATH . 'admin/partials/morkvaup-plugin-about-page.php');
        require_once (MUP_PLUGIN_PATH . 'admin/partials/morkvaup-plugin-about-page.php');
    }

    /**
     * Register plugin menu
     *
     * @since   1.0.0
     */
    public function register_plugin_menu()
    {
        foreach ($this->pages as $page)
        {
            add_menu_page($page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'], $page['icon_url'], $page['position']);
        }

        foreach ($this->subpages as $subpage)
        {
            add_submenu_page($subpage['parent_slug'], $subpage['page_title'], $subpage['menu_title'], $subpage['capability'], $subpage['menu_slug'], $subpage['callback']);
        }
    }

    /**
     * Add setting fields for plugin
     *
     * @since   1.0.0
     */
    public function add_settings_fields()
    {
        $args = array(

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'production_bearer_ecom'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'production_bearer_status_tracking'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'production_cp_token'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'proptype'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'other_settings'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'sendtype'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'senduptype'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'ukrposhta_international_tracked'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'title_sender'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'up_company_name'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'title_international'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'up_sender_type'
            ),

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'names1'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'nameslatin'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'streetlatin'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'numlatin'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'citylatin'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'mrkvup_invoice_name'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'names2'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'names3'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'activate_plugin_en'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'ukrposhta_calculate_rates_currency'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'ukrposhta_calculate_bulky'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'ukrposhta_calculate_avia'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'ukrposhta_calculate_delivery_courier'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'ukrposhta_calculate_delivery_notification'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'ukrposhta_calculate_rates'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'morkvaup_citywh_autocreate_ttn'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'morkvaup_citywh_autosearch' // Автопошук міста та відділення
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'zone_ukrposhta' // Працювати із зонами доставки?
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'mrkvup_default_order_weight' // Вага відправлення, г
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'mrkvup_default_order_length' // Довжина відправлення, см
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'morkva_ukrposhta_default_price'
            ) ,

            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'phone'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'mrkvup_sender_iban'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'edrpou'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'up_tin'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'flat'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'warehouse'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'up_invoice_description'
            ) ,
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'morkva_ukrposhta_default_payer'
            ),
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'morkva_ukrposhta_transfer_postpay_to_sender_bank_account'
            ),
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'mrkvup_parcelitems_global_hscode'
            ),
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'mrkvup_parcelitems_attr_hscode'
            ),
            array(
                'option_group' => 'morkvaup_options_group',
                'option_name' => 'mrkvup_checkout_fields_position'
            )
        );

        $this->settings = $args;

        return $this;
    }

    /**
     *  Register all sections for settings fields
     *
     *  @since   1.0.0
     */
    public function register_fields_sections()
    {
        $args = array(
            array(
                'id' => 'morkvaup_admin_index',
                'title' => 'Налаштування',
                'callback' => function ()
                {
                    echo "";
                }
                ,
                'page' => 'morkvaup_plugin'
            )
        );

        $this->sections = $args;

        return $this;
    }

    /**
     * Register settings callbacks fields
     *
     * @since   1.0.0
     */
    public function register_settings_fields()
    {
        $args = array(

            // Налаштування
            array(
                'id' => 'production_bearer_ecom',
                'title' => 'PROD BEARER eCom',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupAuthBearer'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'production_bearer_ecom'
                )
            ) ,
            array(
                'id' => 'production_bearer_status_tracking',
                'title' => 'PROD BEARER Status Tracking',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupProdBearer'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'production_bearer_status_tracking'
                )
            ) ,
            array(
                'id' => 'production_cp_token',
                'title' => 'PROD COUNTERPARTY TOKEN',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupCpToken'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'production_cp_token'
                )
            ) ,
            array(
                'id' => 'sendtype',
                'title' => 'Тип відправлення',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupsendtype'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'sendtype'
                )
            ) ,
            array(
                'id' => 'proptype',
                'title' => 'Формат наклейки',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupprinttype'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'proptype'
                )
            ) ,
            array(
                'id' => 'up_invoice_description',
                'title' => 'Опис відправлення по замовчуванню по Україні',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupInvoiceDescription'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'up_invoice_description',
                    'class' => 'up_invoice_description'
                )
            ) ,
            array(
                'id' => 'shipping_costing_announcement',
                'title' => '<span>Розрахунок вартості доставки</span>',
                'callback' => function () {
                    echo '<p>Вартість доставки розраховується автоматично з даних про вагу і розмір товару.</p>';
                },
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
            ) ,
            array(
                'id' => 'morkva_ukrposhta_default_price',
                'title' => 'Ціна доставки',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupmorkva_ukrposhta_default_price'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'morkva_ukrposhta_default_price'
                )
            ) ,

            // Інші налаштування
            array(
                'id' => 'other_settings',
                'title' => '<h3 style="margin-bottom:0;">Інші налаштування</h3>',
                'callback' => function () {},
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
            ) ,
            array(
                'id' => 'morkvaup_citywh_autocreate_ttn',
                'title' => 'Автостворення накладної<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'cityWhAutocreatettn'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'morkvaup_citywh_autocreate_ttn',
                    'class' => 'basesettings allsettings show only-pro-version'
                )
            ) ,
            array(
                'id' => 'morkvaup_citywh_autosearch',
                'title' => 'Автопошук міста та відділення<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'cityWhAutosearch'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'morkvaup_citywh_autosearch',
                    'class' => 'basesettings allsettings show only-pro-version'
                )
            ) ,
            array(
                'id' => 'ukrposhta_calculate_rates',
                'title' => 'Додавати вартість доставки до суми замовлення?<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupukrposhta_calculate_rates'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'ukrposhta_calculate_rates',
                    'class' => 'only-pro-version'
                )
            ) ,
            array(
                'id' => 'zone_ukrposhta',
                'title' => 'Працювати із зонами доставки?',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupzone'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'zone_ukrposhta',
                    'class' => 'basesettings allsettings show'
                )
            ) ,
            array(
                'id' => 'mrkvup_default_order_weight',
                'title' => __( 'Вага відправлення, г', 'woo-ukrposhta-pro' ),
                'callback' => array(
                    $this->callbacks,
                    'mrkvup_default_order_weight_cb'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'mrkvup_default_order_weight'
                )
            ) ,
            array(
                'id' => 'mrkvup_default_order_length',
                'title' => __( 'Довжина відправлення, см', 'woo-ukrposhta-pro' ),
                'callback' => array(
                    $this->callbacks,
                    'mrkvup_default_order_length_cb'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'mrkvup_default_order_length'
                )
            ) ,
            array(
                'id' => 'activate_plugin_en',
                'title' => 'Зробити створення ЕН доступним для замовлень з іншими методами доставки?<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupActivateen'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'activate_plugin_en',
                    'class' => 'only-pro-version'
                )
            ) ,
            array(
                'id' => 'morkva_ukrposhta_default_payer',
                'title' => 'Платник за замовчуванням',
                'callback' => array( $this->callbacks, 'morkvaupDefaultPayer' ),
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'morkva_ukrposhta_default_payer'
                )
            ),

            // Відправник
            array(
                'id' => 'title_sender',
                'title' => '<h3 style="margin-bottom:0;">Відправник</h3>',
                'callback' => function () {},
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
            ) ,
            array(
                'id' => 'up_sender_type',
                'title' => __( 'Відправник представляє', 'woo-ukrposhta-pro' ),
                'callback' => array( $this->callbacks, 'morkvaup_sender_type' ),
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'up_sender_type'
                )
            ),
            array(
                'id' => 'up_company_name',
                'title' => 'Назва компанії / ФОП',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupCompanyName'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'up_company_name',
                    'class' => 'up_company_name display_none'
                )
            ) ,
            array(
                'id' => 'mrkvup_sender_iban',
                'title' => 'Рахунок IBAN',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupSenderIBAN'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'mrkvup_sender_iban',
                    'class' => 'mrkvup_sender_iban display_none'
                )
            ) ,
            array(
                'id' => 'morkva_ukrposhta_transfer_postpay_to_sender_bank_account',
                'title' => 'Отримувати післяплату на р/р',
                'callback' => array( $this->callbacks, 'getPayOnDeliveryOnSenderAccount' ),
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'morkva_ukrposhta_transfer_postpay_to_sender_bank_account',
                    'class' => 'transfer_postpay display_none'
                )
            ) ,
            array(
                'id' => 'edrpou',
                'title' => 'ЄДРПОУ',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupEdrpou'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'edrpou',
                    'class' => 'edrpou display_none'
                )
            ) ,
            array(
                'id' => 'up_tin',
                'title' => 'Індивідуальний податковий номер (ІПН)',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupTin'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'up_tin',
                    'class' => 'up_tin display_none'
                )
            ) ,
            array(
                'id' => 'names1',
                'title' => 'Прізвище',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupNames'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'names1',
                    'class' => 'names1 display_none'
                )
            ) ,
            array(
                'id' => 'names2',
                'title' => 'Ім\'я',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupNames2'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'names2',
                    'class' => 'names2 display_none'
                )
            ) ,

            array(
                'id' => 'names3',
                'title' => 'По-батькові',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupNames3'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'names3',
                    'class' => 'names3 display_none'
                )
            ) ,
            array(
                'id' => 'phone',
                'title' => 'Номер телефону',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupPhone'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'phone',
                    'class' => 'phone display_none'
                )
            ) ,
            array(
                'id' => 'warehouse',
                'title' => 'Поштовий індекс',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupWarehouseAddress'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'warehouse',
                    'class' => 'warehouse'
                )
            ) ,

            // Міжнародні відправлення
            array(
                'id' => 'title_international',
                'title' => '<h3 style="margin-bottom:0;">Міжнародні відправлення</h3>',
                'callback' => function () {},
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
            ) ,
            array(
                'id' => 'nameslatin',
                'title' => 'ПІБ латиницею<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupNamesLatin'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'nameslatin',
                    'class' => 'nameslatin only-pro-version'
                )
            ) ,
            array(
                'id' => 'numlatin',
                'title' => 'Номер будинку<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupNumLatin'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'nameslatin',
                    'class' => 'nameslatin only-pro-version'
                )
            ) ,
            array(
                'id' => 'streetlatin',
                'title' => 'Вулиця латиницею<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupStreetLatin'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'nameslatin',
                    'class' => 'nameslatin only-pro-version'
                )
            ) ,
            array(
                'id' => 'citylatin',
                'title' => 'Місто латиницею<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupCityLatin'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'citylatin',
                    'class' => 'citylatin only-pro-version'
                )
            ) ,
            array(
                'id' => 'mrkvup_invoice_name',
                'title' => 'Опис міжнародного відправлення латиницею<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'mrkvupInvoiceName'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'mrkvup_invoice_name',
                    'class' => 'mrkvup_invoice_name only-pro-version'
                )
            ) ,
            array(
                'id' => 'senduptype',
                'title' => 'Тип паковання',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupsenduptype'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'senduptype'
                )
            ) ,
            array(
                'id' => 'ukrposhta_international_tracked',
                'title' => 'Відстежувати бандероль?<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupukrposhta_international_tracked'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'ukrposhta_international_tracked',
                    'class' => 'only-pro-version'
                )
            ) ,
            array(
                'id' => 'ukrposhta_calculate_rates_currency_val',
                'title' => 'Параметри відправлення<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupukrposhta_calculate_rates_currency_val'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'ukrposhta_calculate_rates_currency_val',
                    'class' => 'only-pro-version'
                )
            ) ,
            array(
                'id'        => 'mrkvup_parcelitems_global_hscode',
                'title'     => __( 'Глобальний код ТН ЗЕД', 'woo-ukrposhta-pro' ) . '<br><b>Лише у pro-версії</b>',
                'callback'  => array(
                    $this->callbacks,
                    'morkvaupParcelItemsGlobalHsCode'
                ),
                'page'      => 'morkvaup_plugin',
                'section'   => 'morkvaup_admin_index',
                'args'      => array(
                    'label_for' => 'mrkvup_parcelitems_global_hscode',
                    'class' => 'only-pro-version'
                )
            ),
            array(
                'id' => 'mrkvup_parcelitems_attr_hscode',
                'title' => __( 'Коди ТН ЗЕД в атрибутах', 'woo-ukrposhta-pro' ) . '<br><b>Лише у pro-версії</b>',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupParcelItemsAttrHsCode'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'mrkvup_parcelitems_attr_hscode',
                    'class' => 'only-pro-version'
                )
            ) ,

            // Різне
            array(
                'id' => 'mrkvup_title_miscellaneous',
                'title' => '<h3 style="margin-bottom:0;">Різне</h3>',
                'callback' => function () {},
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
            ) ,
            array(
                'id' => 'mrkvup_checkout_fields_position',
                'title' => 'Позиція полів плагіну в Checkout',
                'callback' => array(
                    $this->callbacks,
                    'morkvaupCheckoutFieldsPosition'
                ) ,
                'page' => 'morkvaup_plugin',
                'section' => 'morkvaup_admin_index',
                'args' => array(
                    'label_for' => 'mrkvup_checkout_fields_position'
                )
            )
        );

        $this->fields = $args;

        return $this;
    }

    /**
     *  Registering all settings fields for plugin
     *
     *  @since   1.0.0
     */
    public function register_plugin_settings()
    {
        foreach ($this->settings as $setting)
        {
            register_setting($setting["option_group"], $setting["option_name"], (isset($setting["callback"]) ? $setting["callback"] : ''));
        }

        foreach ($this->sections as $section)
        {
            add_settings_section($section["id"], $section["title"], (isset($section["callback"]) ? $section["callback"] : '') , $section["page"]);
        }

        foreach ($this->fields as $field)
        {
            add_settings_field($field["id"], $field["title"], (isset($field["callback"]) ? $field["callback"] : '') , $field["page"], $field["section"], (isset($field["args"]) ? $field["args"] : ''));
        }
    }

    /**
     * Add meta box to WooCommerce order's page
     *
     * @since 1.0.0
     */
    public function add_plugin_meta_box()
    {
        
        if (!isset($_SESSION))
        {
            session_start();
        }

        if (isset($_GET["post"]) || isset($_GET["id"]))
        {
            $order_id = '';
            if(isset($_GET["post"])){
                $order_id = $_GET["post"];    
            }
            else{
                $order_id = $_GET["id"];
            }
            

            $order_data0 = wc_get_order($order_id);
            $order_data = $order_data0->get_data();

            $methodid = '';

            foreach ($order_data0->get_items('shipping') as $item_id => $shipping_item_obj)
            {
                $shipping_item_data = $shipping_item_obj->get_data();
                $methodid = $shipping_item_data['method_id'];
            }
            //echo $methodid;
            if ((strpos($methodid, 'u_poshta_shipping_method') !== false) || (strpos($methodid, 'ukrposhta_shippping') !== false) || (strpos($methodid, 'ukrposhta_address_shippping') !== false))
            {
                echo '<style>#mvup_other_fields{display:block;}</style>';
            }
            else
            {
                echo '<style>#mvup_other_fields{display:none;}</style>';
            }

            if (isset($order_id))
            {
                $order_data = wc_get_order($order_id);
                $order = $order_data->get_data();
                $_SESSION['order_data'] = $order;
                $_SESSION['order_id'] = $order_id;
            }

            echo "<img src='" . MUP_PLUGIN_URL . "/includes/icon.svg' style='width: 20px;margin-right: 20px;'/>";
            echo "<a class='button button-primary send' href='admin.php?page=morkvaup_invoice'>По Україні</a> ";
            echo "<a class='button button-primary send disebled'>Міжнародне</a>";
            echo "<script src=" . MUP_PLUGIN_URL . 'admin/js/script.js' . "></script>";
            echo "<link href=" . MUP_PLUGIN_URL . 'admin/css/style.css' . "/>";
            $this->invoice_meta_box_info();
        }
        else
        {
            echo '<style>#mvup_other_fields{display:none;}</style>';
        }
    }

    /**
     * Generating meta box
     *
     * @since 1.0.0
     */
    public function mv_add_meta_boxes()
    {   
        if(class_exists( CustomOrdersTableController::class )){
            $screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
            ? wc_get_page_screen_id( 'shop-order' )
            : 'shop_order';
        }
        else{
            $screen = 'shop_order';
        }

        add_meta_box('mvup_other_fields', __('Укрпошта', 'woocommerce') , array(
            $this,
            'add_plugin_meta_box'
        ) , $screen, 'side', 'core');
    }

    /**
     * Creating custom column at woocommerce order page
     *
     * @since 1.1.0
     */
    public function woo_custom_column($columns)
    {
        $columns['created_invoice'] = 'Відправлення';
        $columns['invoice_number'] = 'Номер Відправлення';
        return $columns;
    }

    /**
     * Getting data of order column at order page
     *
     * @since 1.1.0
     */
    public function woo_column_get_data($column )
    {
        global $post;
        $tdb = MUP_TABLEDB;

        if ($column == 'created_invoice')
        {
            global $wpdb;

            $order_id = $post->ID;
            $results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}{$tdb} WHERE order_id = '$order_id'", ARRAY_A);

            if ( !empty( $results ) ) {
                $img = "/logo1.svg";
                echo '<img height=25 src="' . plugin_dir_url( __FILE__ ) . $img . '" />';
            } else {
                $img = '/logo2.svg';
                echo '<img height=25 src="' . plugin_dir_url( __FILE__ ) . $img . '" />';
            }
        }

        if ($column == 'invoice_number')
        {
            global $wpdb;

            $order_id = $post->ID;
            $query = "SELECT * FROM {$wpdb->prefix}" . $tdb . " WHERE order_id = '$order_id'";
            $number_result = $wpdb->get_row($query, ARRAY_A);

            if ($number_result)
            {
                echo $number_result["order_invoice"];
            }
            else
            {
                echo "";
            }
        }
    }

    /**
     * Getting data of order column at order page
     *
     * @since 1.1.0
     */
    public function woo_column_get_data_hpos($column, $order )
    {
        $tdb = MUP_TABLEDB;

        $order_id = $order->get_id();

        if ($column == 'created_invoice')
        {
            global $wpdb;

            $results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}{$tdb} WHERE order_id = '$order_id'", ARRAY_A);

            if ( !empty( $results ) ) {
                $img = "/logo1.svg";
                echo '<img height=25 src="' . plugin_dir_url( __FILE__ ) . $img . '" />';
            } else {
                $img = '/logo2.svg';
                echo '<img height=25 src="' . plugin_dir_url( __FILE__ ) . $img . '" />';
            }
        }

        if ($column == 'invoice_number')
        {
            global $wpdb;
            $query = "SELECT * FROM {$wpdb->prefix}" . $tdb . " WHERE order_id = '$order_id'";
            $number_result = $wpdb->get_row($query, ARRAY_A);

            if ($number_result)
            {
                echo $number_result["order_invoice"];
            }
            else
            {
                echo "";
            }
        }
    }

    /**
     * Add info of invoice meta box
     *
     * @since 1.1.0
     */
    public function invoice_meta_box_info()
    {
        $tdb = MUP_TABLEDB;

        if (isset($_GET["post"]))
        {
            $order_id = $_GET["post"];
        }
        elseif(isset($_GET["id"])){
            $order_id = $_GET["id"];
        }
        else{
            return;
        }


        $selected_order = wc_get_order($order_id);

        $order = $selected_order->get_data();

        if(class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled())
        {
            $meta_ttn = $selected_order->get_meta('ukrposhta_ttn');
        }
        else
        {
            $meta_ttn = get_post_meta($order_id, 'ukrposhta_ttn', true);    
        }
        
        if (empty($meta_ttn)) {
            global $wpdb;
            $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tdb} WHERE order_id = '$order_id'", ARRAY_A);
            if (isset($result[0]['order_invoice'])) {
                $meta_ttn = $result[0]['order_invoice'];
            }
        }
        $invoice_email = $order['billing']['email'];

        if ( ! empty( $meta_ttn ) ) {
            $invoice_number = $meta_ttn;
            echo '<div style="margin-top:10px;">Номер Відправлення: ' . $meta_ttn . '</div>';
            // getting ukrposhta credentials
            $bearer = get_option( 'production_bearer_ecom' );
            $cptoken = get_option('production_cp_token');
            $tbearer = get_option('production_bearer_status_tracking');

            $ukrposhtaApi = new UkrposhtaApiClass($bearer ,$cptoken, $tbearer);

            $invoiceType = $ukrposhtaApi->GetInfo( $meta_ttn );
            $invoiceRef = $invoiceType['uuid'];
            if ( $invoiceType['type'] != "INTERNATIONAL" ) {
                // create button in meta-box 'mvup_other_fields' to print ukrposhta invoice sticker
                echo '<div></form><form target="_blank" action="' . dirname( plugin_dir_url( __FILE__ ) ) . '/admin/partials/pdf.php' . '" method="POST" />';
                echo '<input type="text" name="type" value="' . get_option( 'proptype' ) . '" style="display:none;" />
                        <input class="startcodeup" type="text" name="ttn" value="' . $invoiceRef . '" hidden />
                        <input type="text" name="bearer" value="' . $bearer . '" hidden />
                        <input tyoe="text" name="cp_token" value="' . $cptoken . '" hidden />';
                echo '<a style="margin: 5px;" alert="У новій вкладці відкриється документ для друку" title="Друк адресного ярлика" class="formsubmitup button" />' . ' <img src="' . plugins_url('img/003-barcode.png', __FILE__) . '" style="vertical-align:text-bottom;margin-right:5px;" /> Друк стікера </a></div>';
                echo '</form><form>';
            }

            if ( $invoiceType['type'] == "INTERNATIONAL" ) {
                // create button in meta-box 'mvup_other_fields' to print international ukrposhta invoice sticker
                // echo '<input type="button" name="fs1" class="fs1" title="форма митної декларації"  value="cn22" />';
                echo '<div></form><form target="_blank" action="' . dirname( plugin_dir_url( __FILE__ ) ) . '/admin/partials/pdf.php' . '" method="POST" />';
                echo '<select style="margin-top:10px;margin-bottom: 10px;" name="fs1">
                    <option value="cp71">Форма бланку супровідної адреси (cp71)</option>
                    <option value="cn22">Форма митної декларації (cn22)</option>
                    <option value="c6">Форма на конверт у форматі (c6)</option>
                    <option value="dl">Форма на конверт у форматі (dl)</option>
                    <option value="forms">Стікер (100мм х 100мм)</option>
                    <option value="tfp3">Форма на пересилання післяплати (tfp3)</option>
                </select>';
                echo '<input type="text" name="type" value="' . get_option( 'proptype' ) . '" style="display:none;" />
                        <input class="startcodeup" type="text" name="ttn" value="' . $invoiceRef . '" hidden />
                        <input type="text" name="bearer" value="' . $bearer . '" hidden />
                        <input tyoe="text" name="cp_token" value="' . $cptoken . '" hidden />';
                echo '<a style="margin: 5px;" alert="У новій вкладці відкриється документ для друку" title="Друк адресного ярлика" class="formsubmitup button" />' . ' <img src="' . plugins_url('img/003-barcode.png', __FILE__) . '" style="vertical-align:text-bottom;margin-right:5px;" /> Друк стікера </a></div>';
                echo '</form><form>';
            }

            $methodProperties = array(
                "Documents" => array(
                    array(
                        "DocumentNumber" => $invoice_number
                    ) ,
                )
            );
        }
        else
        {
            echo '<div style="margin-top:10px;">Номер відправлення не встановлено: -</div>';
        }

    }

    /**
     * From name email
     *
     * @since 1.1.3
     */
    public function my_mail_from_name($name)
    {
        return get_option('blogname');
    }


    public function morkvaup_update_plugin($transient)
    {
    }

}
