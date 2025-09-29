<?php

// Selected filters

add_action('admin_menu', 'register_my_custom_menu_page');

function register_my_custom_menu_page() {
    add_submenu_page(
        'edit.php?post_type=product',
        'Атрибути для фільтрів',
        'Атрибути для фільтрів',
        'manage_options',
        'my-custom-submenu-page',
        'my_custom_submenu_page_callback'
    );
}

function my_custom_submenu_page_callback() {
    global $wpdb;

    // Збереження вибраних атрибутів
    if (isset($_POST['submit'])) {
        if (isset($_POST['selected_attributes'])) {
            $selected_attributes = $_POST['selected_attributes'];

            $saved_attributes = implode(',', $selected_attributes);
            update_option('selected_attributes', $saved_attributes);
        } else {
            // Якщо жоден атрибут не вибрано, видаліть збережені атрибути
            delete_option('selected_attributes');
        }
    }

    // Отримання всіх існуючих атрибутів
    $attributes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies");

    // Отримання збережених атрибутів
    $saved_attributes = get_option('selected_attributes', '');

    ?>
    <div class="wrap">
        <h2>Виберіть атрибути для фільтрів</h2>
        <form method="post" action="">
            <?php if ($attributes) : ?>
                <p>Існуючі атрибути:</p>
                <ul>
                    <?php foreach ($attributes as $attribute) : ?>
                        <li>
                            <input type="checkbox"
                                   name="selected_attributes[]"
                                   id="attribute_<?php echo esc_attr($attribute->attribute_name); ?>"
                                   value="<?php echo esc_attr($attribute->attribute_name); ?>"
                                <?php if (in_array($attribute->attribute_name, explode(',', $saved_attributes))) echo 'checked'; ?>>
                            <label for="attribute_<?php echo esc_attr($attribute->attribute_name); ?>">
                                <?php echo esc_html($attribute->attribute_label); ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>Немає існуючих атрибутів.</p>
            <?php endif; ?>

            <!-- Додайте ваші елементи форми тут для вибору атрибутів -->

            <input type="submit" name="submit" class="button button-primary" value="Зберегти">
        </form>
    </div>
    <?php
}


add_filter( 'woocommerce_default_catalog_orderby_options', 'truemisha_remove_orderby_options' );
add_filter( 'woocommerce_catalog_orderby', 'truemisha_remove_orderby_options' );
 
function truemisha_remove_orderby_options( $sortby ) {
 
	unset( $sortby[ 'menu_order' ] ); // Сортировка по умолчанию
	    // unset( $sortby[ 'popularity' ] ); // по популярности
        $orderby[ 'popularity' ] = 'популярністю';
	unset( $sortby[ 'rating' ] ); // по рейтингу
        $sortby[ 'popularity' ] = 'популярністю';
        $sortby[ 'price' ] = 'ціною';
	    // unset( $sortby[ 'price' ] ); // Цены: по возрастанию
        $sortby[ 'price-desc' ] = 'ціною';
        // unset( $sortby[ 'price-desc' ] ); // Цены: по убыванию
	    // unset( $sortby[ 'date' ] ); // Сортировка по более позднему
         $sortby[ 'date' ] = 'новинками';
 
	return $sortby;
 
}

// Убрать сортировку товаров
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );


function so_render_catalog_product($product_id){
							
    $product = wc_get_product($product_id);
    if ($product) {
      if( wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' )){
         
        $img_first =  wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );

      }else{
             $img_first[0]='/wp-content/uploads/woocommerce-placeholder.png';
            }

        $data = (object) [
        "permalink" => esc_url($product->get_permalink()),
        "image" => $img_first[0],
        "second_image" => $product->get_gallery_image_ids(),
        "price" => $product->get_price_html(),
        "title" => $product->get_name(),
        "is_new" => get_post_meta($product_id, '_is_new', true)
        ];

        if(!empty($data->second_image[0])) {
            $second_image = wp_get_attachment_image($data->second_image[0], 'post-thumbnail', 'true');
        } else {
          $second_image = '';
        }
        $isnew = '';

        if($data->is_new){
            $isnew = '<span class="new_book">new</span>';
        }

        $issale = '';

        if ($product->is_on_sale()) {
          $regular_price = $product->get_regular_price();
          $sale_price = $product->get_sale_price();

          $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);

          $issale = '<span class="sale_book">-' . $discount_percentage . '<span class="percent">%</span></span>';

        }

        return 
		"<li class=\"row product-item-list-col-1\"><span class=\"pr-info\">{$isnew}{$issale}</span>
		<div class=\"col-xs-12 product-item-small-card\">
		   <div class=\"row\">
			  <div class=\"col-xs-12 product-item-big-card\">
				 <div class=\"row\">
					<div class=\"col-md-12\">
					   <div class=\"product-item-container\" style=\"height: auto;\">
						  <div class=\"product-item\">
							 <a class=\"product-item-image-wrapper\" href=\"{$data->permalink}\" title=\"$data->title\" >
								<span class=\"product-item-image-slider-slide-container slide\" style=\"display: none;\" >
								</span>
								<span class=\"product-item-image-original\" style=\"background-image:url({$data->image})\"></span>
								
								<div class=\"product-item-image-slider-control-container\"  style=\"display: none;\">
								</div>
							 </a>
							 <div class=\"product-item-title\">
								<a href=\"{$data->permalink}\" title=\"$data->title\">
								<span>$data->title</span>
								</a>
							 </div>
							 <div>
								<div class=\"product-item-info-container product-item-price-container\" >
								   <span class=\"product-item-price-current\"> {$data->price}</span>								   
								</div>
								<div class=\"product-item-info-container product-item-hidden links_row\" >
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </div>
			  </div>
		   </div>
		</div>
		</li>";
    } else {

  }
}

function get_cookie_products_filters () {
    $raw = $_COOKIE['products_filters'] ?? null;

    if ($raw === null || $raw === '') {
        // Для отладки можно посмотреть, что реально приходит в PHP
        error_log('products_filters cookie is missing. $_COOKIE keys: ' . implode(', ', array_keys($_COOKIE)));
        $products_filters = null;
    } else {
        // 2) Разкодируем URL-процентную запись (%7B...%7D -> {...})
        $decoded = rawurldecode($raw);

        // 3) Если ставили через JS со строковыми кавычками — иногда полезно убрать лишние слеши
        // (безопасно: сработает только если они есть)
        $decoded = stripslashes($decoded);

        // 4) Превращаем в массив PHP
        $products_filters = json_decode($decoded, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('products_filters JSON error: ' . json_last_error_msg() . ' | value: ' . $decoded);
            // fallback: можно оставить как строку, если нужно
            // $products_filters = $decoded;
            $products_filters = null;
        }
    }

    return $products_filters;
}

function query_products_filters ( $data, $category ) {
    $query_request =  array(
            'product_cat' => $category,
            'post_type' => array('product'),
            'posts_per_page' => -1,
            'tax_query' => array(
                    'relation' => 'AND',
            ),
            'post_status' => 'publish'
    );

    if ($data['sort'][0] == "price_down") {

        $query_request['meta_key'] = '_price';
        $query_request['orderby'] = 'meta_value_num';
        $query_request['order'] = 'DESC';

    } elseif ($data['sort'][0] == "popularity") {

        $query_request['meta_key'] = 'popularity';
        $query_request['orderby'] = 'meta_value_num';
        $query_request['order'] = 'DESC';

    } elseif ($data['sort'][0] == "price_up") {

        $query_request['meta_key'] = '_price';
        $query_request['orderby'] = 'meta_value_num';
        $query_request['order'] = 'ASC';

    } elseif ($data['sort'][0] == "news") {

        $query_request['orderby'] = 'date';
        $query_request['order'] = 'DESC';

    }

    array_shift($data);
    foreach ($data as $key => $taxonomy) {
        if( isset($taxonomy)) {
            $terms = array();

            foreach ($taxonomy as $sub_key => $variaable) {
                array_push($terms , $variaable);
            }

            if(!empty($terms)){
                $arr = array(
                        'taxonomy' => 'pa_'.$key,
                        'field' => 'slug',
                );

                $arr['terms'] = $terms;
                $arr['operator'] = 'IN';
                array_push($query_request['tax_query'], $arr);
            }
        }
    }

    return $query_request;
}

remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);
	
add_action( 'woocommerce_before_shop_loop', 'ss_woocommerce_wrapper_before' );

function ss_woocommerce_wrapper_before(){
   $queried_object = get_queried_object();

   $slug = $queried_object->slug;

    $products_filters = get_cookie_products_filters();

    if ($products_filters !== null) {

        $args = query_products_filters($products_filters, $slug);

    } elseif ( $slug === '' ) {

        $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'ASC',
        );

    } else {

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => $slug,
                ),
            ),
            'orderby' => 'date',
            'order'   => 'ASC',
        );
   }

    $categories_terms = [];
    $allProducts = [];
    $products = new WP_Query( $args );

    foreach ($products->posts as $post) {
        array_push($allProducts, $post->ID);
    }

    $cookie_value = json_encode($allProducts, JSON_NUMERIC_CHECK);

    foreach ($products->posts as $prod) {
        $product = wc_get_product($prod);
        $attr = $product->get_attributes();

        foreach ($attr as $key => $item ) {
            foreach ( $item->get_terms() as $pa ) {
                array_push($categories_terms, $pa->name );
            }
        }
    }

    $unique_categories_terms = array_unique($categories_terms);

    echo '<script> // +1 день от текущей даты
            let date = new Date(Date.now() + 86400e3);
            date = date.toUTCString();
            document.cookie = "products=' . $cookie_value .'; expires=" + date;
          </script>';


    echo '
        <div>
            <div class="catalog-section-holder catalog-2-items js-catalog">';

    if($queried_object->taxonomy != "pa_avtori"){
  
            echo '<div class="filter-holder">
            <span class="close-filter"></span>
            <div class="bx-sidebar-block">
              <div class="sections-block">
                <span> ' . $queried_object->name . ' </span>
                <ul class="sections-list">';
                $args = array(
                    'parent'    => 0,
                    'child_of' => $queried_object->term_id,
                    'taxonomy'     => 'product_cat',
                    'type'         => 'product',
                );

                $misc_term = get_term_by('name', 'misc', 'product_cat');
                  if ($misc_term) {
                      $args['exclude'] = $misc_term->term_id;
                  }

                $categories = get_categories( $args );
                foreach($categories as $category) {
                    echo '<li><a href="' . get_category_link( $category->term_id ) . '">'
                        . $category->name
                        .'</a></li>';
                }

                echo '</ul>
              </div>
              <div class="bx-filter  ">
                <div class="bx-filter-section container-fluid-filter">
                  <form name="_form" method="get" >

                  <input class="filter-chbx" type="radio" data-attribute_label="sort" id="popularity" name="sorting" value="popularity">
                  <input class="filter-chbx" type="radio" data-attribute_label="sort" id="price_down" name="sorting" value="price_down">
                  <input class="filter-chbx" type="radio" data-attribute_label="sort" id="price_up" name="sorting"  value="price_up">
                  <input class="filter-chbx" type="radio" data-attribute_label="sort" id="news" name="sorting" value="news" checked>


                    <div class="row">';
            

            $saved_attributes = get_option('selected_attributes');
            $array_saved_attributes = explode(',', $saved_attributes);
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            $taxonomy_terms = array();
            $flag;
            if ($attribute_taxonomies) :
                foreach ($attribute_taxonomies as $tax) :
                  $flag = false;

                  foreach($array_saved_attributes as $attr_name){
                        if($attr_name == $tax->attribute_name){
                         $flag = true;
                        }
                  }

                    if (taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name)) && $flag) :
                        $taxonomy_terms = get_terms(wc_attribute_taxonomy_name($tax->attribute_name), 'orderby=name&hide_empty=0');
                        if( !empty($taxonomy_terms )){
                            echo '<div class="col-lg-12 bx-filter-parameters-box bx-active">
                            <span class="bx-filter-container-modef"></span>
                            <div class="bx-filter-parameters-box-title"">
                              <span class="bx-filter-parameters-box-hint">'
                                . $tax->attribute_label . '
                              </span>
                            </div>
                            <div class="bx-filter-block" data-role="bx_filter_block">
                              <div class="row bx-filter-parameters-box-container">
                                <div class="col-xl-12">';

                                foreach ($taxonomy_terms as $value) {
                                    if ($slug === '') {
                                        echo '<div class="checkbox">
                                            <label data-role="label_' . $value->slug. '" class="bx-filter-param-label " for="' . $value->slug. '">
                                              <span class="bx-filter-input-checkbox">
                                                <input type="checkbox" 
                                                       class="filter-chbx" 
                                                       data-attribute_label=' . $tax->attribute_name . ' 
                                                       value="' . $value->slug. '"
                                                       name="' . $value->slug. '" 
                                                       id="' . $value->slug. '" >
                                                  <span class="bx-filter-param-text" title="' . $value->name . '"> '
                                            . $value->name
                                            . ' </span>
                                                </a>
                                              </span>
                                            </label>
                                          </div>';
                                    } elseif (in_array($value->name, $unique_categories_terms)) {
                                        echo '<div class="checkbox">
                                            <label data-role="label_' . $value->slug. '" class="bx-filter-param-label " for="' . $value->slug. '">
                                              <span class="bx-filter-input-checkbox">
                                                <input type="checkbox" 
                                                       class="filter-chbx" 
                                                       data-attribute_label=' . $tax->attribute_name . ' 
                                                       value="' . $value->slug. '"
                                                       name="' . $value->slug. '" 
                                                       id="' . $value->slug. '" >
                                                  <span class="bx-filter-param-text" title="' . $value->name . '"> '
                                                    . $value->name
                                                    . ' </span>
                                                </a>
                                              </span>
                                            </label>
                                          </div>';
                                    }
                                }
                                echo '</div>
                              </div>
                              <div style="clear: both"></div>
                            </div>
                            </div>';
                            }
            
                    endif;
                endforeach; // end foreach
            endif;
            
            echo '
            </div>
                <div class="row filter-bts countelementblock hidden fixed">
                <div class="col-xl-12 bx-filter-button-box">
                    <span class="bx-filter-text hidden-non"> 
                        Знайдено 
                        <span id="countelement" class="filter-text">0</span> 
                        товара 
                    </span>
                    <div class="bx-filter-block">
                    <div class="bx-filter-parameters-box-container">
                        <div class="filter-bts-row">
                        <div class="filter-cancel">
                            <input class="btn btn-link" type="button" id="del_filter" name="del_filter" value="Скинути">
                        </div>
                        </div>
                        <div class="bx-filter-popup-result left" id="modef" style="display: none"> Обрано: <span id="modef_num">0</span>
                        <span class="arrow"></span>
                        <br>
                        <a href="https://schoolbook.com.ua/catalog/knigi-dlya-doshkilnyat/" target="">Показати</a>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
                <div class="clb"></div>
            </form>
            </div>
            </div>
            </div>
            </div>';}else{
              echo '<div class="content-page filter-holder"><div class="sidebar">';
              
                      wp_nav_menu(
                          array(
                              'theme_location' => 'two_column_menu',
                              'menu_id'        => 'bx_hma_one_lvl',
                              'echo'           => true,
                              'container'      => 'nav',
                              'container_class' => 'nav-menu nav-menu__content-page'
  
                          )
                      );             
  
           echo '</div></div>';
            }
            $queried_objectq = get_queried_object();
           
                echo '
                
                <div class="catalog-holder">';	
                echo '<h1 class="h2-title" data-termslug="'.$queried_object->slug.'">';
                 echo $queried_object->name;
                echo '</h1>';

             if($queried_objectq->taxonomy != "pa_avtori"){    
                echo '
                <div class="filters-box">
                <div class="filters-box__holder">
                  <div class="filter-container">
                    <div class="filter-btn__holder">
                      <div class="filter-btn__box">
                        <span class="filter-btn">Фільтри</span>
                      </div>
                    </div>
                    <div class="sorting-holder">
                      <div class="sorting">
                        <span class="sorting-title">Сортувати за:</span>
                        <ul class="sorting-list">
                          <li class="label-container">
                            <label for="popularity">
                               <span>популярністю</span>
                              </a>
                            </label>
                          </li>
                          <li class="label-container">
                            <label for="price_down">
                               <span>ціною</span>
                                <i class="icon-price-down"></i>
                              </a>
                            </label>
                          </li>
                          <li class="label-container">
                            <label for="price_up">
                               <span>ціною</span>
                                <i class="icon-price-up"></i>
                              </a>
                            </label>
                          </li>
                          <li class="label-container active">
                            <label for="news">
                               <span>новинками</span>
                              </a>
                            </label>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>';
            }

                if($queried_objectq->taxonomy == "pa_avtori"){
               echo '
               <nav class="nav-menu sub-menu__about">
                <ul id="top_two_column_menu" class="menu">
                  <li id="menu-item-220" class="menu-item menu-item-type-post_type menu-item-object-page current-page-ancestor menu-item-220"><a href="/about/">Про нас</a></li>
                  <li id="menu-item-225" class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item page-item-223 current_page_item menu-item-225">
                    <a href="/about/magics/" aria-current="page">Чародії слова</a>
                  </li>
                  <li id="menu-item-226" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-226"><a href="/about/contacts/">Контакти</a></li>
                </ul> 
              </nav>';}

                
                
if($queried_objectq->taxonomy == "pa_avtori"):
   echo'<div class="container-fluid content-author">
     <div class="left-column">
        <div class="author-avatar">
          <div class="link">
            <a href="'.home_url().'/about/magics/">до всіх авторів</a>
          </div>
          <div class="img">';

          $author_img = carbon_get_term_meta( $queried_object->term_id, 'author-thumb' );
          if(!empty($author_img)):
           echo wp_get_attachment_image($author_img, 'post-thumbnail', 'true', array( ));
          endif;

          echo ' </div>
          <div class="autthor-info">
            <h1 class="author-name">' . $queried_object->name . '</h1>';
            echo carbon_get_term_meta( $queried_object->term_id, 'short-description' ) .
          '</div>
        </div>
      </div>
      <div class="right-column">
        <div class="author-about">'. wpautop($queried_objectq->description)  .'</div>
      </div>
    </div>';
endif;

if($queried_objectq->taxonomy == "pa_avtori")
    echo '<p class="h2-title" style="text-align:center">Книги автора</p>';
}

add_action( 'woocommerce_after_shop_loop', 'ss_woocommerce_wrapper_after' );

function ss_woocommerce_wrapper_after(){
            echo '
                </div>
            </div>

            <div class="desc-section">
                <div class="desc-text-holder">
                    <div class="desc-text">
                    </div>
                </div>
            </div>';					
                
}

add_action('wp_ajax_sjax', 'sjax' );
add_action('wp_ajax_nopriv_sjax', 'sjax' );

function sjax(){
    $nonce_back =  $_POST['nonce_code'];
    $nonce_front = wp_create_nonce('so_creator_nonce');
    check_ajax_referer( 'so_creator_nonce', 'nonce_code' );
    $filters = stripslashes($_POST['filters']);
    $data = json_decode($filters,true);

    $query_request = query_products_filters($data, $_POST['category']);

    $q = new WP_Query( $query_request );

    $request_products = "";

    $response = array();
    $response[0] = 0;
    $response[1] = "Нажаль за даними критеріями товарів не знайдено.";
    $allProducts = [];

    if( $q->have_posts() ) :

        $response[1] = "";
        while( $q->have_posts() ) : $q->the_post();
	       if ( $response[0] < 12) {
                $request_products .= so_render_catalog_product($q->post->ID) ;
            }
            
            array_push($allProducts, $q->post->ID);
            $response[0]++;
	
        endwhile;

        $response[1] = $request_products;
        $cookie_value = json_encode($allProducts);
        $response[2] = $cookie_value;

    endif;
    wp_reset_query();

    echo json_encode($response);

   wp_die();
}


add_action('wp_ajax_sjax', 'anyproductfilter' );
add_action('wp_ajax_nopriv_sjax', 'anyproductfilter' );

add_action('wp_ajax_more_products', 'more_products' );
add_action('wp_ajax_nopriv_more_products', 'more_products' );

function more_products() {
    $products = $_POST['products'];
    $request_products = '';

    foreach ($products as  $product) {
        $request_products .= so_render_catalog_product($product);
    }

    wp_reset_query();
    echo json_encode($request_products);
    wp_die();
}

/////////////// FILTERS  /////////////////////////////


// 1) Читаем фильтры: приоритет URL > cookie
function sb_get_current_filters(): array
{
    $filters = [];
    if (isset($_GET['currentFilters'])) {
        $data = json_decode(wp_unslash($_GET['currentFilters']), true);
        if (is_array($data)) $filters = $data;
    } elseif (isset($_COOKIE['products_filters'])) {
        $data = json_decode(wp_unslash($_COOKIE['products_filters']), true);
        if (is_array($data)) $filters = $data;
    }
    foreach ($filters as $k => $v) {
        $arr = is_array($v) ? $v : [$v];
        $arr = array_values(array_filter(array_map('sanitize_title', $arr)));
        if ($arr) $filters[$k] = $arr; else unset($filters[$k]);
    }
    return $filters;
}

/// ---------- Архивы магазина ----------
add_action('woocommerce_product_query', function(WP_Query $q) {

    $filters = sb_get_current_filters();
    if (!$filters) return;

    $sort = $filters['sort'];
    array_shift($filters);

    $tax_query = (array) $q->get('tax_query');
    if (!isset($tax_query['relation'])) $tax_query['relation'] = 'AND';

    foreach ($filters as $param => $values) {
        $values = array_values(array_filter((array)$values, 'strlen'));
        if (!$values) continue;

        $taxonomy = 'pa_' . $param;
        if (!taxonomy_exists($taxonomy)) continue;

        $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $values,
                'operator' => 'IN',
        ];
    }

    $q->set('tax_query', $tax_query);

    if (!empty($sort[0])) {
        switch (sanitize_text_field($sort[0])) {
            case 'news':       $q->set('orderby','date'); $q->set('order','DESC'); break;
            case 'price':      $q->set('meta_key','_price'); $q->set('orderby','meta_value_num'); $q->set('order','ASC');  break;
            case 'price-desc': $q->set('meta_key','_price'); $q->set('orderby','meta_value_num'); $q->set('order','DESC'); break;
        }
    }

}, 10);

// ---------- Шорткод [products] ----------
add_filter('woocommerce_shortcode_products_query', function(array $args, $atts) {

    $filters = sb_get_current_filters();
    if (!$filters) return $args;

    $sort = $filters['sort'];
    array_shift($filters);

    // базовый tax_query
    $tax_query = ['relation' => 'AND'];

    // если в $args уже был корректный tax_query — аккуратно переносим его к нам
    if (isset($args['tax_query']) && is_array($args['tax_query'])) {
        foreach ($args['tax_query'] as $clause) {
            if (is_array($clause) && isset($clause['taxonomy'])) {
                $tax_query[] = $clause;
            }
        }
    }

    foreach ($filters as $param => $values) {
        $values = array_values(array_filter((array)$values, 'strlen'));
        if (!$values) continue;

        $taxonomy = 'pa_' . $param;
        if (!taxonomy_exists($taxonomy)) continue;

        $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $values,
                'operator' => 'IN',
        ];
    }

    if (count($tax_query) > 1) { // есть хотя бы одна клауза
        $args['tax_query'] = $tax_query;
    }

    // сортировка — отдельно, НЕ в tax_query
    if (!empty($sort[0])) {
        switch (sanitize_text_field($sort[0])) {
            case 'news':
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
            case 'price':
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                break;
            case 'price-desc':
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
        }
    }

    // временно для отладки
//    if (defined('WP_DEBUG') && WP_DEBUG) {
//        error_log('tax_query=' . print_r($args['tax_query'], true));
//    }


    return $args;
}, 10, 2);




