<?php
	function enqueue_custom_admin_scripts_and_styles_footer() 
	{

	$style_version = filemtime(MORKVA_UKRPOSHTA_PLUGIN_DIR . 'admin/css/style.css'); 
    $script_version = filemtime(MORKVA_UKRPOSHTA_PLUGIN_DIR . 'admin/js/script.js');

    wp_enqueue_style(
        'custom-admin-style',
        MORKVA_UKRPOSHTA_PLUGIN_URL . 'admin/css/style.css', 
        array(), 
        $style_version, 
        'all' 
    );

   
    wp_enqueue_script(
        'custom-admin-script', 
        MORKVA_UKRPOSHTA_PLUGIN_URL . 'admin/js/script.js', 
        array(), 
        $script_version,
        true
    );
}
add_action('admin_footer', 'enqueue_custom_admin_scripts_and_styles_footer');
	echo '<br>';
	require("api.php");
	require("functions.php");
	//getting ukrposhta credentials
	$bearer = get_option('production_bearer_ecom');
	$cptoken = get_option('production_cp_token');
	$tbearer = get_option('production_bearer_status_tracking');
	//set up new ukrposhta apiobject
	$ukrposhtaApi = new UkrposhtaApi($bearer ,$cptoken, $tbearer);
	//define ukrposhtaa DB table name
	$tdb = MUP_TABLEDB;

	mup_display_nav();

	if ( isset( $_GET['_wpnonce'] )) {
		$nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));
  		$order_data_main = isset($_GET['order']) ? sanitize_text_field(wp_unslash($_GET['order'])) : '';
		if(wp_verify_nonce( $nonce, 'morkvaup_invoice_action_' . $order_data_main ))
		{
			require __DIR__.'/edit.php';
		}
	} else {
		if(isset($_POST['delete'])){
			$id = '';
			if ( isset($_POST['idd']) ) {
			    $id .= sanitize_text_field(wp_unslash( $_POST['idd'] )); 
			}

			$ref = '';
			if ( isset($_POST['ref']) ) {
			    $ref = sanitize_text_field(wp_unslash( $_POST['ref'] )); 
			}

			$ukrposhtaApi->RequestDelShipping($ref);
			global $wpdb;
			$table = "{$wpdb->prefix}{$tdb}";
			$where = array( 'order_invoice' => $id );
			$wpdb->delete( $table, $where );
			the_deletediv($id);
		}


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Mrk_UP_Myttn_List_Table extends WP_List_Table {

	private $bearer;
	private $tbearer;
	private $cptoken;	
	private $ukrposhtaApi;
	private $tdb = MUP_TABLEDB;
	private $results = array();
	private $posts_per_page = 10;

    public function __construct() {
       parent::__construct( array(
	      'singular'=> 'mrkupinvoice', // singular name of the listed records
	      'plural' => 'mrkupinvoices', // plural name, also this well be one of the table css class
	      'ajax'   => false, // We won't support Ajax for this table
	      'screen'   => 'morkvaup_invoices',
       ) );

		global $wpdb;
		$this->bearer = get_option('production_bearer_ecom');
		$this->cptoken = get_option('production_cp_token');
		$this->tbearer = get_option('production_bearer_status_tracking');
		$this->ukrposhtaApi = new UkrposhtaApi($this->bearer ,$this->cptoken, $this->tbearer);
		$this->posts_per_page = intval( get_option( 'posts_per_page' ) );

		$upinvqty = '';

		if (isset($_GET['_wpnonce'])) {
            // Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));

            if(wp_verify_nonce($nonce, 'morkvaup_invoices_update_all_nonce'))
            {
         		$upinvqty = isset( $_GET['upinvqty'] ) ? sanitize_text_field( wp_unslash($_GET['upinvqty'] )) : '';       
            }
        }
		
		$cache_key = 'mrkv_up_query_results_' . $this->tdb . '_upinvqty_' . $upinvqty . '_posts_per_page_' . $this->posts_per_page;
		$results = wp_cache_get($cache_key, 'mrkv_up');

		if (false === $results) {
		    global $wpdb;

		    if ('all' == $upinvqty) {
		        $results = $wpdb->get_results("
		            SELECT * 
		            FROM {$wpdb->prefix}uposhta_invoices
		            ORDER BY id DESC
		        ", ARRAY_A);
		    } else {
		        $results = $wpdb->get_results($wpdb->prepare("
		            SELECT * 
		            FROM {$wpdb->prefix}uposhta_invoices
		            ORDER BY id DESC
		            LIMIT %d
		        ", $this->posts_per_page), ARRAY_A);
		    }

		    wp_cache_set($cache_key, $results, 'mrkv_up', 3600);
		}

		$this->results = $results;	
    }

    /**
     * Getter for bearer property
     * @return string
     */
    public function getBearer() {
        return $this->bearer;
    }

	public function prepare_items( $search='' ) {

		$per_page = $this->posts_per_page;

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();		

		// check if a search was performed.
		$search_key = '';

		if (isset( $_REQUEST['mrkv_search_nonce_action'] )) {
	     	// Remove escaping from input data
            $nonce = sanitize_text_field(wp_unslash($_REQUEST['mrkv_search_nonce_action']));
            if(wp_verify_nonce( $nonce, 'mrkv_search_nonce_field' ))
            {
            	if ( isset( $_REQUEST['s'] ) ) {
			        $search_key = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
			    } 
            }
	    }

		$this->_column_headers = array($columns, $hidden, $sortable);		

		$data = $this->table_data();

		// filter the data in case of a search
		if ( $search_key ) {
			$data = $this->filter_table_data( $data, $search_key );
		}			

		$current_page = $this->get_pagenum();
		$total_items = count($data);
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		// usort( $data, array( &$this, 'usort_reorder' ) );

		$this->items = $data;

		$this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            //'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
        ) );
	}

    /**
     * Defines the WP_List_Table table columns.
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
       return $columns = array(
          'cb' => '<input type="checkbox" />',
          'id' 				=> __('ID накладної', 'woo-ukrposhta'),
          'order_invoice' 	=> __('Номер накладної', 'woo-ukrposhta'),
          'order_id' 		=> __('ID Замовлення', 'woo-ukrposhta'),
          'shipping_cost'	=> __('Вартість доставки', 'woo-ukrposhta'),
          'posting_type'	=> __('Тип відправлення', 'woo-ukrposhta'),
          'delivery_type'	=> __('Тип доставки', 'woo-ukrposhta'),
          'not_delivery'	=> __('При не врученні', 'woo-ukrposhta'),
          'destination'		=> __('Напрямок', 'woo-ukrposhta'),
          'invoice_status'	=> __('Статус', 'woo-ukrposhta'),
       );
    }

    /**
     * Defines which columns are hidden.
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array(
        	'id',
			// 'posting_type',
			'not_delivery'
        );
    }

    /**
     * Defines which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    function get_sortable_columns() {
       return $sortable_columns = array(
          'order_id' 		=> array( 'order_id', false )
       );
    }

	/*function usort_reorder( $a, $b ) {
	  // If no sort, default to 'order_id'
	  $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'order_id';
	  // If no order, default to desc
	  $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
	  // Determine sort order
	  $result = strcmp( $a[$orderby], $b[$orderby] );
	  // Send final sort direction to usort
	  return ( $order === 'asc' ) ? $result : -$result;
	}*/

    // Chekboxes column
    public function column_cb($item){
        return sprintf( 
            '<input type="checkbox" name="invoice_up[]" value="%1$s" class="checkbup" 
            	id="cbup-select-%2$s" valuedup="%3$s"' . checked( get_option('invoice_up[]'), 1 ) . ' />',
            /*$1%s*/ $item['invoice_ref'],
            /*$2%s*/ $item['order_invoice'],
            /*$3%s*/ $item['invoice_ref']
        );
    }	

    // Order Invoice number
    public function column_order_invoice( $item ){

    	// $html2 = $this->ukrposhtaApi->GetInfo( $item[ 'order_invoice' ] );
    	$html2 = $this->ukrposhtaApi->GetInfoUuid( $item[ 'invoice_ref' ] );
    	$print_invoice = '';
    	$print_invoice .= '<form target="_blank" action="' . get_site_url() . '/wp-content/plugins/woo-ukrposhta-pro/admin/partials/pdf.php" method="POST"
					style="  display: inline;">';
		$print_invoice .= wp_nonce_field( 'generate_invoice_nonce_action', 'generate_invoice_nonce', true, false );
    	$print_invoice .=
					'<input type="text" name="type" value="' . get_option( 'proptype' ) . '" style="display:none;" />
					<input class="startcodeup" type="text" name="ttn" value="' . $item['invoice_ref'] . '" hidden />
					<input type="text" name="bearer" value="' . $this->bearer . '" hidden />
					<input tyoe="text" name="cp_token" value="' . $this->cptoken . '" hidden />';
		if(isset($html2['type']) && $html2['type'] != "INTERNATIONAL"){

		}
		else{
			$print_invoice .= '<a alert="У новій вкладці відкриється документ для друку" title="Друк адресного ярлика" class="formsubmitup" />Друк 📥 </a>';
		}

		$print_invoice .= '</form>';

		$delete_invoice = '<form action="admin.php?page=morkvaup_invoices" method="POST" style=" display: inline;">
							<input type="text" name="delete" value="p" hidden />
							<input tyoe="text" name="idd" value="' . $item['order_invoice'] . '" hidden />
							<input tyoe="text" name="ref" value="' . $item['invoice_ref'] . '" hidden />
							<input hidden type="submit" class="" value="Видалити ЕН 🗑" />
							<a alert="Видалено" title="Видалення адресного ярлика" class="formsubmitup">Видалити ЕН 🗑</a>
						</form>';

        //Build row actions
        $actions = array(
            'print_invoice' => sprintf( '%1$s', $print_invoice ),
            'trash' 		=> sprintf( '%1$s', $delete_invoice )
        );

        return sprintf('%1$s %2$s',
            /*$1%s*/ '<a  href="post.php?post=' . $item['order_id'] . '&action=edit" class="row-title">' . $item['order_invoice'] . '</a>',
            /*$2%s*/ $this->row_actions( $actions )
        );
    }

    // Invoice ID
    public function column_id($item){
        return sprintf(
		    '%1$s',
		    '<a title="id накладної в системі укрпошти: ' . esc_attr( $item['invoice_ref'] ) . '" ' .
		    'href="' . esc_url( wp_nonce_url( 
		        'admin.php?page=morkvaup_invoices&post=' . $item['order_invoice'] . '&order=' . $item['order_id'], 
		        'morkvaup_invoice_action_' . $item['order_id'] 
		    ) ) . '" ' .
		    'class="row-title">' . esc_html( $item['id'] ) . '</a>'
		);   	
    }    

    // Order ID
    public function column_order_id($item){
        return sprintf( '%1$s',
        	'<a  href="post.php?post=' . $item['order_id'] . '&action=edit" class="row-title">' . $item['order_id'] . '</a>' 
        );    	
    }
    
    // Shipping_cost
    public function column_shipping_cost($item){
        return sprintf( '%1$s',
        	'<span title="' . $item['calc_descr'] . '">' . $item['shipping_cost'] . '</span>'
        );    	
    }

    // Posting_type
    public function column_posting_type($item){
        return sprintf( '%1$s', 
        	'<span title="Тип відправлення">' . $item['posting_type'] . '</span>' 
        );    	
    }

    // Delivery_type
    public function column_delivery_type($item){
        return sprintf( '%1$s', $item['delivery_type'] );    	
    }

    // Not_delivery
    public function column_not_delivery($item){
        return sprintf( '%1$s', $item['not_delivery'] );    	
    }

    // Destination
    public function column_destination($item){
        return sprintf( '%1$s', 
        	'<span title="Відправник"' . '">✉ ' . $item['sender'] . '</span><br>' .
        	'<span title="Отримувач"' . '">📩 ' . $item['recipient'] . '</span><br>'
        );    	
    }

    // Invoice_status
    public function column_invoice_status($item){
	
        return sprintf( '%1$s', 
        	'<span class="startcodeup" codeup="' . $item['invoice_status'] . '" ttnup="' . $item['order_invoice'] . '" >' . $item['invoice_status'] . '</span>' 
        );   	
    }

    // Table data
	private function table_data() {    
      	global $wpdb;

        $data = array();

		// $this->results = array_reverse( $this->results );

		foreach( $this->results as $invoice ) {
			$invoice_number = $invoice['order_invoice'];
			// $html2 = $this->ukrposhtaApi->GetInfo($invoice['order_invoice']);
			$html2 = $this->ukrposhtaApi->GetInfoUuid($invoice['invoice_ref']);

			$calculationDescription = isset($html2['calculationDescription']) ? $html2['calculationDescription'] : '';
			$deliveryPrice = isset($html2['deliveryPrice']) ? $html2['deliveryPrice'] : '';
			$type = isset($html2['type']) ? strtolower( $html2['type'] ) : '';
			$deliveryType = isset($html2['deliveryType']) ? FunctionDecode( 'type', $html2['deliveryType'] ) : '';
			$onFailReceiveType = isset($html2['onFailReceiveType']) ? FunctionDecode( 'fail', $html2['onFailReceiveType'] ) : '';
			$postcode = isset($html2['recipient']['addresses'][0]['address']['postcode']) ? $html2['recipient']['addresses'][0]['address']['postcode'] : '';
			$detailedInfo = isset($html2['recipient']['addresses'][0]['address']['detailedInfo']) ? $html2['recipient']['addresses'][0]['address']['detailedInfo'] : '';
			$lifecycle = isset($html2['lifecycle']['status']) ? strtolower( $html2['lifecycle']['status'] ) : '';
			$sender = isset($html2['sender']['addresses'][0]['address']['detailedInfo']) ? $html2['sender']['addresses'][0]['address']['detailedInfo'] : '';

            $data[] = array(
            	'id'				=> $invoice['id'],
            	'order_id' 			=> $invoice['order_id'],
                'order_invoice'  	=> $invoice['order_invoice'], // barcode
                'invoice_ref'		=> $invoice['invoice_ref'], // uuid
                'calc_descr'		=> $calculationDescription,
                'shipping_cost'		=> $deliveryPrice,
                'posting_type'		=> $type,
                'delivery_type'		=> $deliveryType,
                'not_delivery'		=> $onFailReceiveType,
				'sender'			=> $sender,
				'recipient'			=> $postcode . ' ' .$detailedInfo,               					   
                'invoice_status'  	=> $lifecycle
            );
		}

        return $data;
    }

// filter the table data based on the search key
public function filter_table_data( $table_data, $search_key ) {
	$filtered_table_data = array_values( array_filter( $table_data, function( $row ) use ( $search_key ) {
		foreach( $row as $row_val ) {
			if( stripos( $row_val, $search_key ) !== false ) {
				return true;
			}				
		}			
	} ) );

	return $filtered_table_data;

}    

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		$two = '';
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		} elseif ( 'bottom' === $which) {
			$two = '2';
			// wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
	<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<?php if ( $this->has_items() ) : ?>
			<form class="bulk_actions_form<?php echo esc_attr($two); ?>" target="_blank" method="POST" action >
				<input type="text" name="type" value="<?php echo esc_attr(get_option( 'proptype' )); ?>" style="display: none;" />
				<!-- <input type="text" name="ttn" value="<?php //echo $invoice['invoice_ref']; ?>" style="display: none;" /> -->
				<input type="text" name="bearer" value="<?php echo esc_attr(get_option('production_bearer_ecom')); ?>" style="display: none;" />
				<input tyoe="text" name="cp_token" value="<?php echo esc_attr(get_option('production_cp_token')); ?>" style="display: none;" />

				<div class="alignleft actions bulkactions">

					<input type="hidden" name="bulklistup<?php echo esc_attr($two); ?>" id="bulklistup<?php echo esc_attr($two); ?>" value="">
					<input type="hidden" name="bulklistdeleteup<?php echo esc_attr($two); ?>" id="bulklistdeleteup<?php echo esc_attr($two); ?>" value="">
					<input type="hidden" name="bulklistnewup<?php echo esc_attr($two); ?>" id="bulklistnewup<?php echo esc_attr($two); ?>" value="">
					<input type="hidden" name="sendtype" id="sendtype" value="<?php echo esc_attr( get_option('sendtype') ); ?>">

					<?php $this->bulk_actions( $which ); ?>
				</div>
			</form>				
			<?php
		endif;
		$this->extra_tablenav( $which );
		$this->pagination( $which );
		?>

		<br class="clear" />
	</div>
		<?php
	}

	/**
	 * Displays the table.
	 *
	 * @since 3.1.0
	 */
	public function display() {
		$singular = $this->_args['singular'];		

		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
<table class="wp-list-table <?php echo esc_attr(implode( ' ', $this->get_table_classes() )); ?>">
	<thead>
	<tr>
		<?php $this->print_column_headers(); ?>
	</tr>
	</thead>

	<tbody id="the-list"
		<?php
		if ( $singular ) {
			echo esc_attr(" data-wp-lists='list:$singular'");
		}
		?>
		>
		<?php $this->display_rows_or_placeholder(); ?>
	</tbody>

	<tfoot>
	<tr>
		<?php $this->print_column_headers( false ); ?>
	</tr>
	</tfoot>

</table>
		<?php
		// $this->display_tablenav( 'bottom' );
	}	    

	public function get_bulk_actions() {
	  return $actions = array(
	    'bulk_delete'  	=> __('Delete', 'woo-ukrposhta'),
	    'bulk_print' 	=> __('Друкувати', 'woo-ukrposhta')
	  );
	}

	protected function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$this->_actions = $this->get_bulk_actions();
			/**
			 * Filters the list table Bulk Actions drop-down.
			 *
			 * The dynamic portion of the hook name, `$this->screen->id`, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * This filter can currently only be used to remove bulk actions.
			 *
			 * @since 3.5.0
			 *
			 * @param string[] $actions An array of the available bulk actions.
			 */
			$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			$two            = '';

		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) ) {
			return;
		}


		echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . esc_html__( 'Select bulk action', 'woo-ukrposhta' ) . '</label>';
		echo '<select name="action' . esc_attr($two) . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
		echo '<option value="-1">' . esc_html__( 'Bulk Actions', 'woo-ukrposhta' ) . "</option>\n";


		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

			echo "\t" . '<option value="' . esc_html($name) . '"' . esc_attr($class) . '>' . esc_html($title) . "</option>\n";
		}

		echo "</select>\n";

		$bulk_action = $this->current_action();

		submit_button( __( 'Apply', 'woo-ukrposhta'	 ), 'action', '', false, array( 
			'id' => "doaction$two"
			// 'onclick' => "window.open('" . get_site_url() . "/wp-content/plugins/woo-ukrposhta-pro/admin/partials/" . $bulk_file. "','_blank')" 
		) );
		echo "\n";
		
		// Clear cache 
		/*if ( function_exists( 'wp_cache_clean_cache' ) ) {
		  global $file_prefix;
		  wp_cache_clean_cache( $file_prefix, true );
		}*/
	}	

	public function process_bulk_actions() {  
        global $wpdb;

		$data = $this->table_data();
    }

	function custom_bulk_admin_notices() {
        echo 'Hello.';
    }    

    public function extra_tablenav( $which ) {
    	$nonce = wp_create_nonce('morkvaup_invoices_update_all_nonce');
    	$url = admin_url('admin.php?page=morkvaup_invoices&upinvqty=all&_wpnonce=' . $nonce);
    	print '<a href="' . esc_url($url) . '" class="button button-primary vam" onclick="window.location.reload();"> Оновити всі</a>';
    }
   

	public function no_items() {
	  esc_html_e( 'Відправлень Укрпошти для відображення не знайдено.', 'woo-ukrposhta' );
	}

/**
* Screen options for the List Table
*
* Callback for the load-($page_hook_suffix)
* Called when the plugin page is loaded
* 
* @since    1.0.0
*/
/*public function load_invoice_list_table_screen_options() {
	$arguments = array(
		'label'		=>	__( 'Users Per Page' ),
		'default'	=>	5,
		'option'	=>	'users_per_page'
	);
	add_screen_option( 'per_page', $arguments );
	
	 // * Instantiate the User List Table. Creating an instance here will allow the core WP_List_Table class to automatically
	 // * load the table columns in the screen options panel		 
	 	 
	$this->invoice_list_table = new Mrk_UP_Myttn_List_Table();		
}*/

	/*
	 * Display the User List Table
	 * Callback for the add_users_page() in the add_plugin_admin_menu() method of this class.
	 */
	public function load_invoice_list_table(){
		// query, filter, and sort the data
		$this->prepare_items();
		?>
		<div class="wrap" id="mrkvup-list-table" style="margin-right:0;">    
		    <h2><?php esc_html_e( 'Мої відправлення Укрпошти ', 'woo-ukrposhta'); ?></h2><hr>
		        <div id="mrkv-wp-list-table-demo">			
		            <div id="mrkv-post-body">		
						<?php 
							echo '<form id="posts-filter" method="post">';
								wp_nonce_field( 'mrkv_search_nonce_action', 'mrkv_search_nonce_field' );
								$this->search_box( __( 'Search', 'woo-ukrposhta' ), 'search_id');
							echo '</form>';
							$this->display();					
						?>					
		            </div>			
		        </div>
		</div>
		<?php
	}	

} // End of Mrk_UP_Myttn_List_Table()


?>


<?php } // End of if(isset($_GET['post'])):20 ?>


<?php 
if ( isset( $_GET['page'] ) && 'morkvaup_invoices' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) && 
     ( ! isset( $_GET['post'] ) || empty( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) ) ) : ?>
    
    <?php
        $upTtnListTable = new Mrk_UP_Myttn_List_Table();
        $upTtnListTable->load_invoice_list_table(); 
    ?>

<?php endif; ?>


