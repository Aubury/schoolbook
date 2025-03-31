<?php
	global $wp_version;
	if ( version_compare( $wp_version, '5.6', '<' ) ) { ?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<?php }
?>
<script src="<?php echo MUP_PLUGIN_URL . 'admin/js/script.js'; ?>"></script>
<link rel="stylesheet" href="<?php echo MUP_PLUGIN_URL . 'admin/css/style.css'; ?>"/>

<?php
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

	if(isset($_GET['post'])) {
 		require __DIR__.'/edit.php';
	} else {
		if(isset($_POST['delete'])){
			$id ='';$id .= $_POST['idd'];$ref = $_POST['ref'];
			$ukrposhtaApi->RequestDelShipping($ref);
			global $wpdb;
			$query = "DELETE FROM `{$wpdb->prefix}{$tdb}`"." WHERE {$wpdb->prefix}{$tdb}.order_invoice='".$id."'";
			echo '<script>console.log("'.$query.'")</script>';
			$wpdb->query( $query );
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
		$upinvqty = isset( $_GET['upinvqty'] ) ? sanitize_text_field( $_GET['upinvqty'] ) : '';
		if ( 'all' == $upinvqty ) {
			$this->results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}{$this->tdb}" . " ORDER BY id DESC", ARRAY_A  );
		} else {
			$this->results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}{$this->tdb}" . " ORDER BY id DESC LIMIT " . $this->posts_per_page, ARRAY_A  );
		}	
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

// check and process any actions such as bulk actions.
//$this->process_bulk_actions(); // error_log(print_r($this->process_bulk_action(),true));		

		// check if a search was performed.
		$search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';


		$this->_column_headers = array($columns, $hidden, $sortable);

// check and process any actions such as bulk actions.
// $this->process_bulk_actions(); // error_log(print_r($this->process_bulk_action(),true));		

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
          'id' 				=> __('ID накладної'),
          'order_invoice' 	=> __('Номер накладної'),
          'order_id' 		=> __('ID Замовлення'),
          'shipping_cost'	=> __('Вартість доставки'),
          'posting_type'	=> __('Тип відправлення'),
          'delivery_type'	=> __('Тип доставки'),
          'not_delivery'	=> __('При не врученні'),
          'destination'		=> __('Напрямок'),
          'invoice_status'	=> __('Статус'),
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
        return sprintf( '%1$s',
        	'<a  title="id накладної в системі укрпошти: ' . $item['invoice_ref'] . '" ' .
        	'href="admin.php?page=morkvaup_invoices&post=' . $item['order_invoice'] . 
        	'&order=' . $item['order_id'] . '" ' . 
        	'class="row-title">' . $item['id'] . '</a>' 
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

/*public static function delete_records($id)
{
	global $wpdb;
	$wpdb->delete("database_table_name", ['id' => $id], ['%d']);
}*/	  

/*public static function bulk_print_action( $id )
{
	global $wpdb;
	// $wpdb->delete("database_table_name", ['id' => $id], ['%d']);
	$res = $wpdb->get_results( "SELECT * FROM {$this->tdb} WHERE id = {$id}" );
	// error_log($res);
}	 */  


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
			<form class="bulk_actions_form<?php echo $two; ?>" target="_blank" method="POST" action >
				<input type="text" name="type" value="<?php echo get_option( 'proptype' ); ?>" style="display: none;" />
				<!-- <input type="text" name="ttn" value="<?php //echo $invoice['invoice_ref']; ?>" style="display: none;" /> -->
				<input type="text" name="bearer" value="<?php echo get_option('production_bearer_ecom'); ?>" style="display: none;" />
				<input tyoe="text" name="cp_token" value="<?php echo get_option('production_cp_token'); ?>" style="display: none;" />

				<div class="alignleft actions bulkactions">

					<input type="hidden" name="bulklistup<?php echo $two; ?>" id="bulklistup<?php echo $two; ?>" value="">
					<input type="hidden" name="bulklistdeleteup<?php echo $two; ?>" id="bulklistdeleteup<?php echo $two; ?>" value="">
					<input type="hidden" name="bulklistnewup<?php echo $two; ?>" id="bulklistnewup<?php echo $two; ?>" value="">
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
<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
	<thead>
	<tr>
		<?php $this->print_column_headers(); ?>
	</tr>
	</thead>

	<tbody id="the-list"
		<?php
		if ( $singular ) {
			echo " data-wp-lists='list:$singular'";
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
	    'bulk_delete'  	=> __('Delete'),
	    'bulk_print' 	=> __('Друкувати')
	    // 'bulk_new_invoice_print' => __('Друкувати новостворені накладні')
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


		echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . __( 'Select bulk action' ) . '</label>';
		echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
		echo '<option value="-1">' . __( 'Bulk Actions' ) . "</option>\n";


		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

			echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
		}

		echo "</select>\n";

		$bulk_action = $this->current_action();

		submit_button( __( 'Apply' ), 'action', '', false, array( 
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

		$data = $this->table_data(); // error_log(print_r($data,true));

		/*if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'bulk_print' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] === 'bulk_print' ) ) {

			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );*/  // error_log(wp_verify_nonce( $nonce, 'bulk-mrkupinvoices' ));
			/*
			 * Note: the nonce field is set by the parent class
			 * wp_nonce_field( 'bulk-' . $this->_args['plural'] );	 
			 */
			/*if ( ! wp_verify_nonce( $nonce, 'bulk-mrkupinvoices' ) ) { // verify the nonce.
				$this->invalid_nonce_redirect();
			} else {
				// $cb_arr = array(); //error_log(print_r($this->results,true));
				if( ! empty( $_POST['bulklistup'] ) ) {
					$cb_arr = explode(",", $_POST['bulklistup']); //error_log(print_r($cb_arr,true));
					foreach ($data as $item) {
					$html2 = $this->ukrposhtaApi->GetInfo( $item[ 'order_invoice' ] );  //error_log(print_r($html2,true));
			    		foreach ($cb_arr as $val) {  //error_log(print_r($val,true));		        		
				        	if ( $val == $html2['barcode'] ) {
			        			if( $_POST['action'] == 'bulk_print' ) {
			        				// error_log('Hello! Be back!');
								}
				        	}	
			        	}	        		
				    }
				}
			}
		}*/
		if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'bulk_delete' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] === 'bulk_delete' ) ) {
			// Працює, якщо підключити в методі 'prepare_items()' поточний метод `$this->process_bulk_actions();
		}
    }

	function custom_bulk_admin_notices() {
        echo 'Hello.';
    }    

    public function extra_tablenav( $which ) {
    	print '<a href="?page=morkvaup_invoices&upinvqty=all" class="button button-primary vam" onclick="window.location.reload();"> Оновити всі</a>';
    }

public function getAddrSticker() {
    // $url = 'https://www.ukrposhta.ua/forms/ecom/0.0.1/shipments/'. $uuid .'/sticker?token=' . $this->token;

    $authorization = "Authorization: Bearer " . $this->bearer;

    $cur = curl_init($url);
    curl_setopt( $cur, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cur, CURLOPT_HTTPHEADER, array('Content-Type: application/pdf' , $authorization )); // Inject the token into the header
    $html = curl_exec( $cur );
    curl_close ( $cur );
    // return  json_decode($html, true);
    /*if ( ! empty( $url ) ) {
		// header('Location:' . $url);
		echo '<script>window.open(' . $url . ', "_blank");</script>';
		// exit;
	}*/
	return;
}    

	public function no_items() {
	  _e( 'Відправлень Укрпошти для відображення не знайдено.' );
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
		    <h2><?php _e( 'Мої відправлення Укрпошти '); ?></h2><hr>
		        <div id="mrkv-wp-list-table-demo">			
		            <div id="mrkv-post-body">		
						<?php 
							echo '<form id="posts-filter" method="post">';
								$this->search_box( __( 'Search' ), 'search_id');
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


<?php if ( 'morkvaup_invoices' == $_GET['page'] && (null == (isset($_GET['post']) ? $_GET['post'] : '') ) ) : ?>
		
	<?php
	 	$upTtnListTable = new Mrk_UP_Myttn_List_Table();
		$upTtnListTable->load_invoice_list_table(); 
	?>	

<?php endif; ?>


