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
      if (
              wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ),
                  'single-post-thumbnail' )
      ){
         
        $img_first =  wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );

      } else {
             $img_first[0]='/wp-content/uploads/woocommerce-placeholder.png';
      }

        $data = (object) [
            "permalink" => esc_url($product->get_permalink()),
            "image" => $product->get_image("woocommerce_single"),
            "second_image" => $product->get_gallery_image_ids(),
            "price" => $product->get_price_html(),
            "title" => $product->get_name(),
            "authors" => implode(', ', wc_get_product_terms($product_id, 'pa_avtori', ['fields' => 'names'])),
            "author" => implode(', ', wc_get_product_terms($product_id, 'pa_author-book', ['fields' => 'names'])),
            "age" => implode(', ', wc_get_product_terms($product_id, 'pa_vik', ['fields' => 'names'])),
            "is_new" => get_post_meta($product_id, '_is_new', true),
            "custom_preorder" => get_post_meta($product_id, '_custom_preorder', true)
        ];

        if( !empty($data->second_image[0]) ) {
            $second_image = wp_get_attachment_image($data->second_image[0], 'post-thumbnail', 'true');
        } else {
          $second_image = '';
        }

        $in_cart = WC()->cart &&
            WC()->cart->find_product_in_cart(
                WC()->cart->generate_cart_id($product_id)
            );

        if($data->is_new){
            $isnew = '<span class="new_book">new</span>';
        }

        $price = str_replace('> грн<', '>грн<', $data->price);
        $price = '<div class="price"><span class="num">' . $price . '</span></div>';

        $current_date = date('Y-m-d');

        $data->custom_preorder > $current_date
            ? $preorder = '<span class="preorder-book"></span>'
            : $preorder = '';

        $data->is_new
            ? $is_new = '<span class="new_book"></span>'
            : $is_new = '';

        $is_sale = '';
        if ($product->is_on_sale()) {
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
            $is_sale = '<span class="sale_book">-' . $discount_percentage . '<span class="percent">%</span></span>';
        }

        $_author = $data->authors ?? $data->author;

        $author_html = '<p class="info-author"> </p>';

        if (!empty($_author)) {
            $author_html = "<p class='info-author'>{$_author}</p>";
        }

        $age = '<p class="info-age">' . $data->age . '</p>' ?? '<p class="info-age"> </p>';



        $basketHTML= '<span class="text-block">До кошика</span>';

        if ($in_cart) {
            $basketHTML= '<span class="text-block">У кошику</span><span class="icon-block icon-red-backed"></span>';
        } else {
            $basketHTML= '<span class="text-block">До кошика</span><span class="icon-block"></span>';
        }

        $shortcode = do_shortcode( '[woosw id=' . $product_id . ']' );


        return 
		"<li class=\"product-item-list-col-1\">
            <div class=\"item\"> 
            
               <div class=\"product-card\">
        
                    <a href=\"{$data->permalink}\" class=\"so_product-link\">
                        {$preorder}
                        <div class='pr-info-new'>
                        {$is_new}{$is_sale}
                        </div>
                        <div class=\"img\">
                           {$data->image}
                        </div>
                    </a>
                    <div class='product-title-block'>
                        <div class=\"product-title\">
                               <h3>{$data->title}</h3>
                                <div class='info-block'>
                                 {$author_html} 
                                 {$age}
                                </div>
                         </div> 
                         {$price}
                     </div>
                     
                     <div class='favorite-basket-block'>
                        <div class=\"favorite-box\">
                               <span id=\"favorite_17177\" class=\"favorites-link bx-catalog-subscribe-button\">
                                  <i class=\"icon-favorite\">
                                      {$shortcode}                   
                                  </i>
                                  </span>
                               </div>
                       
                        <div class=\"button-to-buy\">
                             <a class=\"add_to_cart_ajx\" id=\"{$product_id}\">
                                {$basketHTML}
                             </a>
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
        //error_log('products_filters cookie is missing. $_COOKIE keys: ' . implode(', ', array_keys($_COOKIE)));
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

function create_category_checkbox(
    $tax,
    $value,
    bool $is_extra = false,
    bool $is_checked = false
): string {
    $input_id = $tax->attribute_name . '-' . $value->slug;
    $classes  = 'checkbox filter-option';

    if ( $is_extra ) {
        $classes .= ' filter-option--extra';
    }

    return '<div class="' . esc_attr( $classes ) . '">
        <label
            data-role="label_' . esc_attr( $value->slug ) . '"
            class="filter-param-label"
            for="' . esc_attr( $input_id ) . '"
        >
            <span class="filter-input-checkbox">
                <input
                    type="checkbox"
                    class="filter-chbx"
                    data-attribute_label="' . esc_attr( $tax->attribute_name ) . '"
                    value="' . esc_attr( $value->slug ) . '"
                    name="' . esc_attr( $value->slug ) . '"
                    id="' . esc_attr( $input_id ) . '"
                    ' . checked( $is_checked, true, false ) . '
                >

                <span
                    class="filter-param-text"
                    title="' . esc_attr( $value->name ) . '"
                >
                    ' . esc_html( $value->name ) . '
                </span>
            </span>
        </label>
    </div>';
}

remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);

add_action(
    'woocommerce_before_shop_loop',
    'schoolbook_mobile_result_count',
    20
);

function schoolbook_mobile_result_count() {
    $total = (int) wc_get_loop_prop( 'total' );

    if ( $total <= 0 ) {
        return;
    }
    ?>

    <div
            class="mobile-result-count"
            role="status"
            aria-live="polite"
    >
        Знайдено
        <span class="mobile-result-count__number">
            <?php echo esc_html( $total ); ?>
        </span>
        товарів
    </div>

    <?php
}


add_action( 'woocommerce_before_shop_loop', 'ss_woocommerce_wrapper_before' );

function ss_woocommerce_wrapper_before(){
   $queried_object = get_queried_object(); // Получаем данные категории

   $slug = $queried_object->slug;

    $products_filters = get_cookie_products_filters();
    if ( ! is_array( $products_filters ) ) {
        $products_filters = array();
    }

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

    $catalog_product_ids = array_values(
        array_unique(
            array_map(
                'absint',
                wp_list_pluck( $products->posts, 'ID' )
            )
        )
    );

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

    if ( $queried_object->taxonomy != "pa_avtori" ) :
        $current_filters = get_cookie_products_filters();

        $current_sort = is_array( $current_filters )
            ? ( $current_filters['sort'][0] ?? 'news' )
            : 'news';
    ?>
        <div class="sidebar">
            <div class="mob-filters-container">
                <div class="filters-modal-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                      <path d="M5.34979 6.99839C5.11617 6.76493 4.99957 6.49021 5 6.17423C5.00043 5.85826 5.11682 5.58345 5.34916 5.34981C5.5815 5.11618 5.8562 4.99957 6.17326 5C6.49032 5.00043 6.7651 5.11682 6.99763 5.34918L18.6451 16.9979C18.8787 17.2314 18.9956 17.5064 18.9957 17.8229C18.9958 18.1394 18.8791 18.414 18.6457 18.6465C18.4122 18.879 18.1373 18.9959 17.8208 18.9971C17.5043 18.9983 17.2297 18.8817 16.9972 18.6471L5.34979 6.99839ZM7.00314 18.6528C6.76895 18.8857 6.49388 19.0014 6.17793 19C5.86198 18.9986 5.58756 18.8813 5.35468 18.6482C5.1218 18.4151 5.00607 18.14 5.00751 17.8229C5.00895 17.5059 5.12621 17.2314 5.35929 16.9996L17.0036 5.34803C17.2377 5.11514 17.5131 4.99914 17.8296 5.00002C18.1461 5.00091 18.4202 5.11845 18.652 5.35265C18.8838 5.58684 18.9998 5.8622 19 6.17873C19.0002 6.49526 18.8827 6.76942 18.6474 7.00121L7.00314 18.6528Z" fill="white"/>
                      <path d="M6.17793 19C6.49388 19.0014 6.76895 18.8857 7.00314 18.6528L18.6474 7.00121C18.8827 6.76942 19.0002 6.49526 19 6.17873C18.9998 5.8622 18.8838 5.58684 18.652 5.35265C18.4202 5.11845 18.1461 5.00091 17.8296 5.00002C17.5131 4.99914 17.2377 5.11514 17.0036 5.34803L5.35929 16.9996C5.12621 17.2314 5.00895 17.5059 5.00751 17.8229C5.00607 18.14 5.1218 18.4151 5.35468 18.6482C5.58756 18.8813 5.86198 18.9986 6.17793 19Z" fill="white"/>
                    </svg>
                </div>
               <div class="filter-holder">
                 <div class="filter-sidebar-wrap">
                     <div class="filter-box">
                         <div class="container-fluid-filter">
                            <form name="_form" method="get" >

                             <input
                                class="filter-chbx"
                                type="radio"
                                data-attribute_label="sort"
                                id="news"
                                name="sorting"
                                value="news"
                                <?php checked( $current_sort, 'news' ); ?>
                            >

                            <input
                                class="filter-chbx"
                                type="radio"
                                data-attribute_label="sort"
                                id="price_down"
                                name="sorting"
                                value="price_down"
                                <?php checked( $current_sort, 'price_down' ); ?>
                            >

                            <input
                                class="filter-chbx"
                                type="radio"
                                data-attribute_label="sort"
                                id="price_up"
                                name="sorting"
                                value="price_up"
                                <?php checked( $current_sort, 'price_up' ); ?>
                            >

                              <div class="container-filters">

                <?php
                $saved_attributes = get_option('selected_attributes');
                $array_saved_attributes = explode(',', $saved_attributes);
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                $taxonomy_terms = array();
                $flag;
                if ($attribute_taxonomies) :
                    foreach ($attribute_taxonomies as $tax) :
                      $flag = false;
                      $taxonomy = wc_attribute_taxonomy_name( $tax->attribute_name );

                      foreach ( $array_saved_attributes as $attr_name ) :
                            if( $attr_name == $tax->attribute_name ) :
                             $flag = true;
                            endif;
                      endforeach;

                        if (taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name)) && $flag) :

                            $visible_terms = get_terms(
                                array(
                                    'taxonomy'   => $taxonomy,
                                    'orderby'    => 'name',
                                    'hide_empty' => true,
                                    'object_ids' => $catalog_product_ids,
                                )
                            );


                            $taxonomy_terms = get_terms(wc_attribute_taxonomy_name($tax->attribute_name), 'orderby=name&hide_empty=0');

                            if ( ! is_wp_error( $visible_terms ) && ! empty( $visible_terms ) )  : ?>
                                <div class="filter-parameters-box bx-active">

                                  <div class="filter-parameters-box-title opened">
                                    <?php echo esc_html( $tax->attribute_label ) ?>

                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                          <path d="M1.47206 11.3495C1.44206 10.4395 1.91206 9.86952 2.69206 9.47952C5.00206 8.31952 7.11206 6.85952 9.09206 5.21952C10.0921 4.38952 11.0621 4.28952 12.1321 5.02952C14.6121 6.75952 16.3121 9.23952 18.1921 11.5395C18.9321 12.4495 18.6321 13.7395 17.8221 14.2995C17.0021 14.8595 15.9621 14.6695 15.1721 13.7695C13.9521 12.3595 12.7421 10.9395 11.5421 9.50952C11.1121 8.99952 10.7921 8.89952 10.1821 9.33952C8.52206 10.4995 6.80206 11.5495 5.09206 12.6295C4.58206 12.9495 4.03206 13.1795 3.41206 13.1695C2.31206 13.1695 1.49206 12.3995 1.47206 11.3495Z" fill="#FF5A2D"/>
                                        </svg>
                                    </span>
                                </div>

                                <div class="filter-block" data-role="bx_filter_block">
                                  <div class="filter-parameters-box-container">
                                    <?php
                                    $selected_terms = $products_filters[ $tax->attribute_name ] ?? array();

                                    $selected_terms = array_map(
                                        'sanitize_title',
                                        (array) $selected_terms
                                    );

                                    foreach ( $visible_terms as $index => $value ) :
                                        $is_checked = in_array(
                                            $value->slug,
                                            $selected_terms,
                                            true
                                        );

                                        if ( $index >= 3 && $is_checked ) {
                                            $has_hidden_checked = true;
                                        }

                                        echo create_category_checkbox( $tax, $value, $index >= 3, $is_checked );
                                    endforeach;

                                    if ( count( $visible_terms ) > 3 ) : ?>
                                        <button type="button" class="filter-show-all" aria-expanded="false">
                                            <span class="filter-show-all__text">Дивитися всі</span>
                                            <span class="filter-show-all__arrow" aria-hidden="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
                                                  <path d="M1 3L5.5 8L10 3" stroke="#333333" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                            </span>
                                        </button>
                                    <?php endif; ?>

                                  </div>
                                 </div>
                                </div>
                            <?php endif;
                        endif;
                    endforeach; // end foreach
                endif; ?>
                </div> <!-- end .container-filters -->
                         </div> <!-- end .container-fluid-filter -->
                     </div> <!-- end .filter-box -->
                 </div> <!-- end .filter-sidebar-wrap -->
                <div class="delete-filter-bts filter-bts countElementBlock hidden">
                    <div class="delete-filter-button-box">
                        <span class="delete-filter-text hidden-non">
                            Знайдено <span id="countElement" class="filter-text">0</span> товарів
                        </span>
                        <div class="delete-filter-cancel">

                                <div class="filter-bts-row">
                                    <div class="filter-cancel">
                                        <input type="button" id="del_filter" name="del_filter" value="Скинути фільтри">
                                    </div>
                                </div>

                        </div>
                    </div>
                    </div>

                </form>
            </div> <!-- end .filter-holder -->
            </div>
    </div> <!-- end .sidebar -->

    <?php else : ?>
       <div class="content-page filter-holder"><div class="sidebar">
           <?php
                      wp_nav_menu(
                          array(
                              'theme_location' => 'two_column_menu',
                              'menu_id'        => 'bx_hma_one_lvl',
                              'echo'           => true,
                              'container'      => 'nav',
                              'container_class' => 'nav-menu nav-menu__content-page'
  
                          )
                      );
           ?>
  
     </div></div>
   <?php endif;
       $queried_objectq = get_queried_object(); ?>

       <div class="catalog-holder">
          <h1
                class="catalog-title"
                data-termslug="<?php echo esc_attr( $queried_object->slug ); ?>"
            >
                <?php
                $letters = mb_str_split( $queried_object->name );

                foreach ( $letters as $letter ) {
                    echo '<span class="catalog-title__letter">'
                        . esc_html( $letter )
                        . '</span>';
                }
                ?>
            </h1>

          <?php
             if( $queried_objectq->taxonomy != "pa_avtori") : ?>

                 <div class="sorting-holder">
                    <div class="filter-sorting-holder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                          <path d="M8 5.00245C7.73478 5.00245 7.48043 5.10781 7.29289 5.29534C7.10536 5.48288 7 5.73723 7 6.00245C7 6.26767 7.10536 6.52202 7.29289 6.70956C7.48043 6.89709 7.73478 7.00245 8 7.00245C8.26522 7.00245 8.51957 6.89709 8.70711 6.70956C8.89464 6.52202 9 6.26767 9 6.00245C9 5.73723 8.89464 5.48288 8.70711 5.29534C8.51957 5.10781 8.26522 5.00245 8 5.00245ZM5.17 5.00245C5.3766 4.41692 5.75974 3.90988 6.2666 3.55124C6.77346 3.1926 7.37909 3 8 3C8.62091 3 9.22654 3.1926 9.7334 3.55124C10.2403 3.90988 10.6234 4.41692 10.83 5.00245H20C20.2652 5.00245 20.5196 5.10781 20.7071 5.29534C20.8946 5.48288 21 5.73723 21 6.00245C21 6.26767 20.8946 6.52202 20.7071 6.70956C20.5196 6.89709 20.2652 7.00245 20 7.00245H10.83C10.6234 7.58798 10.2403 8.09502 9.7334 8.45366C9.22654 8.81231 8.62091 9.0049 8 9.0049C7.37909 9.0049 6.77346 8.81231 6.2666 8.45366C5.75974 8.09502 5.3766 7.58798 5.17 7.00245H4C3.73478 7.00245 3.48043 6.89709 3.29289 6.70956C3.10536 6.52202 3 6.26767 3 6.00245C3 5.73723 3.10536 5.48288 3.29289 5.29534C3.48043 5.10781 3.73478 5.00245 4 5.00245H5.17ZM14 11.0025C13.7348 11.0025 13.4804 11.1078 13.2929 11.2953C13.1054 11.4829 13 11.7372 13 12.0025C13 12.2677 13.1054 12.522 13.2929 12.7096C13.4804 12.8971 13.7348 13.0025 14 13.0025C14.2652 13.0025 14.5196 12.8971 14.7071 12.7096C14.8946 12.522 15 12.2677 15 12.0025C15 11.7372 14.8946 11.4829 14.7071 11.2953C14.5196 11.1078 14.2652 11.0025 14 11.0025ZM11.17 11.0025C11.3766 10.4169 11.7597 9.90988 12.2666 9.55124C12.7735 9.1926 13.3791 9 14 9C14.6209 9 15.2265 9.1926 15.7334 9.55124C16.2403 9.90988 16.6234 10.4169 16.83 11.0025H20C20.2652 11.0025 20.5196 11.1078 20.7071 11.2953C20.8946 11.4829 21 11.7372 21 12.0025C21 12.2677 20.8946 12.522 20.7071 12.7096C20.5196 12.8971 20.2652 13.0025 20 13.0025H16.83C16.6234 13.588 16.2403 14.095 15.7334 14.4537C15.2265 14.8123 14.6209 15.0049 14 15.0049C13.3791 15.0049 12.7735 14.8123 12.2666 14.4537C11.7597 14.095 11.3766 13.588 11.17 13.0025H4C3.73478 13.0025 3.48043 12.8971 3.29289 12.7096C3.10536 12.522 3 12.2677 3 12.0025C3 11.7372 3.10536 11.4829 3.29289 11.2953C3.48043 11.1078 3.73478 11.0025 4 11.0025H11.17ZM8 17.0025C7.73478 17.0025 7.48043 17.1078 7.29289 17.2953C7.10536 17.4829 7 17.7372 7 18.0025C7 18.2677 7.10536 18.522 7.29289 18.7096C7.48043 18.8971 7.73478 19.0025 8 19.0025C8.26522 19.0025 8.51957 18.8971 8.70711 18.7096C8.89464 18.522 9 18.2677 9 18.0025C9 17.7372 8.89464 17.4829 8.70711 17.2953C8.51957 17.1078 8.26522 17.0025 8 17.0025ZM5.17 17.0025C5.3766 16.4169 5.75974 15.9099 6.2666 15.5512C6.77346 15.1926 7.37909 15 8 15C8.62091 15 9.22654 15.1926 9.7334 15.5512C10.2403 15.9099 10.6234 16.4169 10.83 17.0025H20C20.2652 17.0025 20.5196 17.1078 20.7071 17.2953C20.8946 17.4829 21 17.7372 21 18.0025C21 18.2677 20.8946 18.522 20.7071 18.7096C20.5196 18.8971 20.2652 19.0025 20 19.0025H10.83C10.6234 19.588 10.2403 20.095 9.7334 20.4537C9.22654 20.8123 8.62091 21.0049 8 21.0049C7.37909 21.0049 6.77346 20.8123 6.2666 20.4537C5.75974 20.095 5.3766 19.588 5.17 19.0025H4C3.73478 19.0025 3.48043 18.8971 3.29289 18.7096C3.10536 18.522 3 18.2677 3 18.0025C3 17.7372 3.10536 17.4829 3.29289 17.2953C3.48043 17.1078 3.73478 17.0025 4 17.0025H5.17Z" fill="#333333"/>
                        </svg>
                        <span class="bubble">0</span>
                    </div>
                    <div class="sorting">
                        <div class="sorting-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                              <path d="M11.3472 15.4337C11.4444 15.5306 11.5216 15.6458 11.5742 15.7725C11.6269 15.8993 11.654 16.0353 11.654 16.1726C11.654 16.3099 11.6269 16.4458 11.5742 16.5726C11.5216 16.6994 11.4444 16.8146 11.3472 16.9115L8.5655 19.6932C8.46859 19.7904 8.35343 19.8676 8.22664 19.9202C8.09984 19.9729 7.96391 20 7.82661 20C7.68932 20 7.55338 19.9729 7.42659 19.9202C7.2998 19.8676 7.18464 19.7904 7.08773 19.6932L4.30606 16.9115C4.20902 16.8144 4.13205 16.6992 4.07954 16.5725C4.02703 16.4457 4 16.3098 4 16.1726C4 16.0353 4.02703 15.8995 4.07954 15.7727C4.13205 15.6459 4.20902 15.5307 4.30606 15.4337C4.40309 15.3366 4.51828 15.2597 4.64506 15.2072C4.77184 15.1547 4.90772 15.1276 5.04494 15.1276C5.18216 15.1276 5.31804 15.1547 5.44482 15.2072C5.5716 15.2597 5.68679 15.3366 5.78382 15.4337L6.78436 16.4325V5.04484C6.78436 4.76818 6.89426 4.50285 7.08988 4.30722C7.28551 4.11159 7.55083 4.00169 7.82748 4.00169C8.10414 4.00169 8.36946 4.11159 8.56509 4.30722C8.76071 4.50285 8.87061 4.76818 8.87061 5.04484V16.4325L9.87115 15.4311C9.9682 15.3343 10.0834 15.2575 10.2101 15.2052C10.3368 15.153 10.4726 15.1262 10.6097 15.1264C10.7468 15.1267 10.8825 15.1539 11.009 15.2067C11.1356 15.2594 11.2505 15.3365 11.3472 15.4337ZM19.6922 7.08853L16.9105 4.30681C16.8136 4.20956 16.6985 4.1324 16.5717 4.07975C16.4449 4.0271 16.3089 4 16.1716 4C16.0344 4 15.8984 4.0271 15.7716 4.07975C15.6448 4.1324 15.5297 4.20956 15.4328 4.30681L12.6511 7.08853C12.4551 7.2845 12.345 7.55028 12.345 7.82742C12.345 8.10456 12.4551 8.37035 12.6511 8.56632C12.847 8.76228 13.1128 8.87238 13.39 8.87238C13.6671 8.87238 13.9329 8.76228 14.1289 8.56632L15.1294 7.56577V18.9534C15.1294 19.2301 15.2393 19.4954 15.4349 19.691C15.6305 19.8867 15.8959 19.9966 16.1725 19.9966C16.4492 19.9966 16.7145 19.8867 16.9101 19.691C17.1057 19.4954 17.2156 19.2301 17.2156 18.9534V7.56577L18.2162 8.56719C18.4121 8.76315 18.6779 8.87325 18.9551 8.87325C19.2322 8.87325 19.498 8.76315 19.6939 8.56719C19.8899 8.37122 20 8.10543 20 7.82829C20 7.55115 19.8899 7.28537 19.6939 7.0894L19.6922 7.08853Z" fill="#333333"/>
                            </svg>
                            <span>Сортувати за:</span>
                        </div>

                        <div class="sorting-select">
                            <button
                                type="button"
                                class="sorting-select__button"
                                aria-expanded="false"
                            >
                                <span class="sorting-select__value">Новинками</span>

                                <span class="sorting-select__arrow">
                                    <svg width="11" height="8" viewBox="0 0 11 8" fill="none">
                                        <path
                                            d="M1 1L5.5 6L10 1"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </span>
                            </button>

                            <ul class="sorting-list">
                                <li class="label-container active">
                                    <label for="news" data-sorting-text="Новинками">
                                        Новинками
                                    </label>
                                </li>

                                <li class="label-container">
                                    <label
                                        for="price_up"
                                        data-sorting-text="За зростанням ціни"
                                    >
                                        За зростанням ціни
                                    </label>
                                </li>

                                <li class="label-container">
                                    <label
                                        for="price_down"
                                        data-sorting-text="За спаданням ціни"
                                    >
                                        За спаданням ціни
                                    </label>
                                </li>
                            </ul>
                        </div>

                      </div>
                 </div>

         <?php endif;

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
            case 'price_up':
                $q->set( 'meta_key', '_price' );
                $q->set(
                    'orderby',
                    array(
                        'meta_value_num' => 'ASC',
                        'ID'             => 'ASC',
                    )
                );
                break;

            case 'price_down':
                $q->set( 'meta_key', '_price' );
                $q->set(
                    'orderby',
                    array(
                        'meta_value_num' => 'DESC',
                        'ID'             => 'DESC',
                    )
                );
                break;

            case 'news':
            default:
                $q->set(
                    'orderby',
                    array(
                        'date' => 'DESC',
                        'ID'   => 'DESC',
                    )
                );
                break;
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
            case 'price_up':
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                break;
            case 'price_down':
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