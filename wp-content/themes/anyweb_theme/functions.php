<?php


if ( ! defined( '_S_VERSION' ) ) {
    define( '_S_VERSION', '1.0.0' );
}

add_filter( 'upload_mimes', 'svg_upload_allow' );

# Добавляет SVG в список разрешенных для загрузки файлов.
function svg_upload_allow( $mimes ) {
    $mimes['svg']  = 'image/svg+xml';

    return $mimes;
}


require get_template_directory() . '/inc/custom-header.php';

require get_template_directory() . '/inc/cart.php';

require get_template_directory() . '/inc/checkout.php';

require get_template_directory() . '/inc/thank-you-page.php';

require get_template_directory() . '/inc/template-tags.php';

require get_template_directory() . '/inc/template-functions.php';

require get_template_directory() . '/inc/customizer.php';

require get_template_directory() . '/inc/enqueue-scripts-style.php';

require get_template_directory() . '/inc/navigation-menu.php';

require get_template_directory() . '/inc/widget-areas.php';

require get_template_directory() . '/inc/theme-settings.php';

require get_template_directory() . '/inc/reg-autorize.php';

require get_template_directory() . '/inc/woocommerce.php';

require get_template_directory() . '/inc/product-page-filters.php';

require get_template_directory() . '/woocommerce/includes/wc-functions-remove.php';

require get_template_directory() . '/woocommerce/includes/wc-functions.php';

if ( defined( 'JETPACK__VERSION' ) ) {
    require get_template_directory() . '/inc/jetpack.php';
}
if ( class_exists( 'WooCommerce' ) ) {
    require get_template_directory() . '/inc/woocommerce.php';
}

add_action( 'carbon_fields_register_fields', 'so_register_custom_fields' );
function so_register_custom_fields() {
    require get_template_directory() . '/inc/metabox/metabox.php';
    require get_template_directory() . '/inc/metabox/catalog-mane-page.php';
}

add_action( 'after_setup_theme', 'crb_load' );
function crb_load() {
    require_once( 'inc/carbon-fields/vendor/autoload.php' );
    \Carbon_Fields\Carbon_Fields::boot();
}



function grncurrency_symbol( $currency_symbol, $currency ) {
    switch( $currency ) {
        case 'UAH': $currency_symbol = ' грн'; break;
    }
    return $currency_symbol;
}
add_filter('woocommerce_currency_symbol', 'grncurrency_symbol', 10, 2);


// coouner views post

// Подсчет количества посещений страниц
add_action( 'wp_head', 'kama_postviews' );

/**
 * @param array $args
 *
 * @return null
 */
function kama_postviews( $args = [] ){
    global $user_ID, $post, $wpdb;

    if( ! $post || ! is_singular() )
        return;

    $rg = (object) wp_parse_args( $args, [
        // Ключ мета поля поста, куда будет записываться количество просмотров.
        'meta_key' => 'views',
        // Чьи посещения считать? 0 - Всех. 1 - Только гостей. 2 - Только зарегистрированных пользователей.
        'who_count' => 1,
        // Исключить ботов, роботов? 0 - нет, пусть тоже считаются. 1 - да, исключить из подсчета.
        'exclude_bots' => true,
    ] );

    $do_count = false;
    switch( $rg->who_count ){

        case 0:
            $do_count = true;
            break;
        case 1:
            if( ! $user_ID )
                $do_count = true;
            break;
        case 2:
            if( $user_ID )
                $do_count = true;
            break;
    }

    if( $do_count && $rg->exclude_bots ){

        $notbot = 'Mozilla|Opera'; // Chrome|Safari|Firefox|Netscape - все равны Mozilla
        $bot = 'Bot/|robot|Slurp/|yahoo';
        if(
            ! preg_match( "/$notbot/i", $_SERVER['HTTP_USER_AGENT'] ) ||
            preg_match( "~$bot~i", $_SERVER['HTTP_USER_AGENT'] )
        ){
            $do_count = false;
        }

    }

    if( $do_count ){

        $up = $wpdb->query( $wpdb->prepare(
            "UPDATE $wpdb->postmeta SET meta_value = (meta_value+1) WHERE post_id = %d AND meta_key = %s",
            $post->ID, $rg->meta_key
        ) );

        if( ! $up ){
            add_post_meta( $post->ID, $rg->meta_key, 1, true );
        }

        wp_cache_delete( $post->ID, 'post_meta' );
    }

}

// breadcrumbs


/**
 * Хлебные крошки для WordPress (breadcrumbs)
 *
 * @param string $sep  Разделитель. По умолчанию ' » '.
 * @param array  $l10n Для локализации. См. переменную `$default_l10n`.
 * @param array  $args Опции. Смотрите переменную `$def_args`.
 *
 * @return void Выводит на экран HTML код
 *
 * version 3.3.3
 */
function kama_breadcrumbs( $sep = ' » ', $l10n = array(), $args = array() ){
    $kb = new Kama_Breadcrumbs;
    echo $kb->get_crumbs( $sep, $l10n, $args );
}

class Kama_Breadcrumbs {

    public $arg;

    // Локализация
    static $l10n = [
        'home'       => 'Головна',
        'paged'      => 'Сторінка %d',
        '_404'       => 'Помилка 404',
        'search'     => 'Результати пошуку по запросу - <b>%s</b>',
        'author'     => 'Архів автора: <b>%s</b>',
        'year'       => 'Архів за <b>%d</b> год',
        'month'      => 'Архів за: <b>%s</b>',
        'day'        => '',
        'attachment' => 'Медіа: %s',
        'tag'        => 'Записи по міткі: <b>%s</b>',
        'tax_tag'    => '%1$s з "%2$s" по тегу: <b>%3$s</b>',
        // tax_tag выведет: 'тип_записи из "название_таксы" по тегу: имя_термина'.
        // Если нужны отдельные холдеры, например только имя термина, пишем так: 'записи по тегу: %3$s'
    ];

    // Параметры по умолчанию
    static $args = [
        // выводить крошки на главной странице
        'on_front_page'   => true,
        // показывать ли название записи в конце (последний элемент). Для записей, страниц, вложений
        'show_post_title' => true,
        // показывать ли название элемента таксономии в конце (последний элемент). Для меток, рубрик и других такс
        'show_term_title' => true,
        // шаблон для последнего заголовка. Если включено: show_post_title или show_term_title
        'title_patt'      => '<span class="kb_title">%s</span>',
        // показывать последний разделитель, когда заголовок в конце не отображается
        'last_sep'        => true,
        // 'markup' - микроразметка. Может быть: 'rdf.data-vocabulary.org', 'schema.org', '' - без микроразметки
        // или можно указать свой массив разметки:
        // array( 'wrappatt'=>'<div class="kama_breadcrumbs">%s</div>', 'linkpatt'=>'<a href="%s">%s</a>', 'sep_after'=>'', )
        'markup'          => 'schema.org',
        // приоритетные таксономии, нужно когда запись в нескольких таксах
        'priority_tax'    => [ 'category' ],
        // 'priority_terms' - приоритетные элементы таксономий, когда запись находится в нескольких элементах одной таксы одновременно.
        // Например: array( 'category'=>array(45,'term_name'), 'tax_name'=>array(1,2,'name') )
        // 'category' - такса для которой указываются приор. элементы: 45 - ID термина и 'term_name' - ярлык.
        // порядок 45 и 'term_name' имеет значение: чем раньше тем важнее. Все указанные термины важнее неуказанных...
        'priority_terms'  => [],
        // добавлять rel=nofollow к ссылкам?
        'nofollow'        => false,

        // служебные
        'sep'             => '',
        'linkpatt'        => '',
        'pg_end'          => '',
    ];

    function get_crumbs( $sep, $l10n, $args ){
        global $post, $wp_post_types;

        self::$args['sep'] = $sep;

        // Фильтрует дефолты и сливает
        $loc = (object) array_merge( apply_filters( 'kama_breadcrumbs_default_loc', self::$l10n ), $l10n );
        $arg = (object) array_merge( apply_filters( 'kama_breadcrumbs_default_args', self::$args ), $args );

        // $arg->sep = '<span class="kb_sep">' . $arg->sep . '</span>'; // дополним
        $arg->sep = ''; // дополним

        // упростим
        $sep = & $arg->sep;
        $this->arg = & $arg;

        // микроразметка ---
        if(1){
            $mark = & $arg->markup;

            // Разметка по умолчанию
            if( ! $mark ){
                $mark = [
                    'wrappatt'  => '<div class="bx-breadcrumb">%s</div>',
                    'linkpatt'  => '<a href="%s">%s</a>',
                    'sep_after' => '',
                ];
            }
            // rdf
            elseif( $mark === 'rdf.data-vocabulary.org' ){
                $mark = [
                    'wrappatt'  => '<div class="bx-breadcrumb" prefix="v: http://rdf.data-vocabulary.org/#">%s</div>',
                    'linkpatt'  => '<span typeof="v:Breadcrumb"><a href="%s" rel="v:url" property="v:title">%s</a>',
                    'sep_after' => '</span>', // закрываем span после разделителя!
                ];
            }
            // schema.org
            elseif( $mark === 'schema.org' ){
                $mark = [
                    'wrappatt'  => '<div class="bx-breadcrumb" itemscope itemptype="http://schema.org/BreadcrumbList">%s</div>',
                    'linkpatt'  => '<div class="bx-breadcrumb-item" itemprop="itemListElement" itemscope itemptype="http://schema.org/ListItem"><a href="%s" itemprop="item"><span itemprop="name">%s</span></a><i class="fa fa-angle-right"></i></div>',
                    'sep_after' => '',
                ];
            }

            elseif( ! is_array( $mark ) ){
                die( __CLASS__ . ': "markup" parameter must be array...' );
            }

            $wrappatt = $mark['wrappatt'];
            $arg->linkpatt = $arg->nofollow ? str_replace( '<a ', '<a rel="nofollow"', $mark['linkpatt'] ) : $mark['linkpatt'];
            $arg->sep .= $mark['sep_after'] . "\n";
        }

        $linkpatt = $arg->linkpatt; // упростим

        $q_obj = get_queried_object();

        // может это архив пустой таксы
        $ptype = null;
        if( ! $post ){
            if( isset( $q_obj->taxonomy ) ){
                $ptype = $wp_post_types[ get_taxonomy( $q_obj->taxonomy )->object_type[0] ];
            }
        }
        else{
            $ptype = $wp_post_types[ $post->post_type ];
        }

        // paged
        $arg->pg_end = '';
        $paged_num = get_query_var( 'paged' ) ?: get_query_var( 'page' );
        if( $paged_num ){
            $arg->pg_end = $sep . sprintf( $loc->paged, (int) $paged_num );
        }

        $pg_end = $arg->pg_end; // упростим

        $out = '';

        if( is_front_page() ){
            return $arg->on_front_page ? sprintf( $wrappatt, ( $paged_num ? sprintf( $linkpatt, get_home_url(), $loc->home ) . $pg_end : $loc->home ) ) : '';
        }
        // страница записей, когда для главной установлена отдельная страница.
        elseif( is_home() ){
            $out = $paged_num ? ( sprintf( $linkpatt, get_permalink( $q_obj ), esc_html( $q_obj->post_title ) ) . $pg_end ) : esc_html( $q_obj->post_title );
        }
        elseif( is_404() ){
            $out = $loc->_404;
        }
        elseif( is_search() ){
            $out = sprintf( $loc->search, esc_html( $GLOBALS['s'] ) );
        }
        elseif( is_author() ){
            $tit = sprintf( $loc->author, esc_html( $q_obj->display_name ) );
            $out = ( $paged_num ? sprintf( $linkpatt, get_author_posts_url( $q_obj->ID, $q_obj->user_nicename ) . $pg_end, $tit ) : $tit );
        }
        elseif( is_year() || is_month() || is_day() ){
            $y_url = get_year_link( $year = get_the_time( 'Y' ) );

            if( is_year() ){
                $tit = sprintf( $loc->year, $year );
                $out = ( $paged_num ? sprintf( $linkpatt, $y_url, $tit ) . $pg_end : $tit );
            }
            // month day
            else{
                $y_link = sprintf( $linkpatt, $y_url, $year );
                $m_url = get_month_link( $year, get_the_time( 'm' ) );

                if( is_month() ){
                    $tit = sprintf( $loc->month, get_the_time( 'F' ) );
                    $out = $y_link . $sep . ( $paged_num ? sprintf( $linkpatt, $m_url, $tit ) . $pg_end : $tit );
                }
                elseif( is_day() ){
                    $m_link = sprintf( $linkpatt, $m_url, get_the_time( 'F' ) );
                    $out = $y_link . $sep . $m_link . $sep . get_the_time( 'l' );
                }
            }
        }
        // Древовидные записи
        elseif( is_singular() && $ptype->hierarchical ){
            $out = $this->_add_title( $this->_page_crumbs( $post ), $post );
        }
        // Таксы, плоские записи и вложения
        else {
            $term = $q_obj; // таксономии

            // определяем термин для записей (включая вложения attachments)
            if( is_singular() ){
                // изменим $post, чтобы определить термин родителя вложения
                if( is_attachment() && $post->post_parent ){
                    $save_post = $post; // сохраним
                    $post = get_post( $post->post_parent );
                }

                // учитывает если вложения прикрепляются к таксам древовидным - все бывает :)
                $taxonomies = get_object_taxonomies( $post->post_type );
                // оставим только древовидные и публичные, мало ли...
                $taxonomies = array_intersect( $taxonomies, get_taxonomies( [
                    'hierarchical' => true,
                    'public'       => true,
                ] ) );

                if( $taxonomies ){
                    // сортируем по приоритету
                    if( ! empty( $arg->priority_tax ) ){

                        usort( $taxonomies, static function( $a, $b ) use ( $arg ) {
                            $a_index = array_search( $a, $arg->priority_tax );
                            if( $a_index === false ){
                                $a_index = 9999999;
                            }

                            $b_index = array_search( $b, $arg->priority_tax );
                            if( $b_index === false ){
                                $b_index = 9999999;
                            }

                            return ( $b_index === $a_index ) ? 0 : ( $b_index < $a_index ? 1 : -1 ); // меньше индекс - выше
                        } );
                    }

                    // пробуем получить термины, в порядке приоритета такс
                    foreach( $taxonomies as $taxname ){

                        if( $terms = get_the_terms( $post->ID, $taxname ) ){
                            // проверим приоритетные термины для таксы
                            $prior_terms = &$arg->priority_terms[ $taxname ];

                            if( $prior_terms && count( $terms ) > 2 ){

                                foreach( (array) $prior_terms as $term_id ){
                                    $filter_field = is_numeric( $term_id ) ? 'term_id' : 'slug';
                                    $_terms = wp_list_filter( $terms, [ $filter_field => $term_id ] );

                                    if( $_terms ){
                                        $term = array_shift( $_terms );
                                        break;
                                    }
                                }
                            }
                            else{
                                $term = array_shift( $terms );
                            }

                            break;
                        }
                    }
                }

                // вернем обратно (для вложений)
                if( isset( $save_post ) ){
                    $post = $save_post;
                }
            }

            // вывод

            // все виды записей с терминами или термины
            if( $term && isset( $term->term_id ) ){
                $term = apply_filters( 'kama_breadcrumbs_term', $term );

                // attachment
                if( is_attachment() ){
                    if( ! $post->post_parent ){
                        $out = sprintf( $loc->attachment, esc_html( $post->post_title ) );
                    }
                    else{
                        if( ! $out = apply_filters( 'attachment_tax_crumbs', '', $term, $this ) ){
                            $_crumbs = $this->_tax_crumbs( $term, 'self' );
                            $parent_tit = sprintf( $linkpatt, get_permalink( $post->post_parent ), get_the_title( $post->post_parent ) );
                            $_out = implode( $sep, [ $_crumbs, $parent_tit ] );
                            $out = $this->_add_title( $_out, $post );
                        }
                    }
                }
                // single
                elseif( is_single() ){
                    if( ! $out = apply_filters( 'post_tax_crumbs', '', $term, $this ) ){
                        $_crumbs = $this->_tax_crumbs( $term, 'self' );
                        $out = $this->_add_title( $_crumbs, $post );
                    }
                }
                // не древовидная такса (метки)
                elseif( ! is_taxonomy_hierarchical( $term->taxonomy ) ){
                    // метка
                    if( is_tag() ){
                        $out = $this->_add_title( '', $term, sprintf( $loc->tag, esc_html( $term->name ) ) );
                    }
                    // такса
                    elseif( is_tax() ){
                        $post_label = $ptype->labels->name;
                        $tax_label = $GLOBALS['wp_taxonomies'][ $term->taxonomy ]->labels->name;
                        $out = $this->_add_title( '', $term, sprintf( $loc->tax_tag, $post_label, $tax_label, esc_html( $term->name ) ) );
                    }
                }
                // древовидная такса (рибрики)
                elseif( ! $out = apply_filters( 'term_tax_crumbs', '', $term, $this ) ){
                    $_crumbs = $this->_tax_crumbs( $term, 'parent' );
                    $out = $this->_add_title( $_crumbs, $term, esc_html( $term->name ) );
                }
            }
            // влоежния от записи без терминов
            elseif( is_attachment() ){
                $parent = get_post( $post->post_parent );
                $parent_link = sprintf( $linkpatt, get_permalink( $parent ), esc_html( $parent->post_title ) );
                $_out = $parent_link;

                // вложение от записи древовидного типа записи
                if( is_post_type_hierarchical( $parent->post_type ) ){
                    $parent_crumbs = $this->_page_crumbs( $parent );
                    $_out = implode( $sep, [ $parent_crumbs, $parent_link ] );
                }

                $out = $this->_add_title( $_out, $post );
            }
            // записи без терминов
            elseif( is_singular() ){
                $out = $this->_add_title( '', $post );
            }
        }

        // замена ссылки на архивную страницу для типа записи
        $home_after = apply_filters( 'kama_breadcrumbs_home_after', '', $linkpatt, $sep, $ptype );

        if( '' === $home_after ){
            // Ссылка на архивную страницу типа записи для: отдельных страниц этого типа; архивов этого типа; таксономий связанных с этим типом.
            if( $ptype && $ptype->has_archive && ! in_array( $ptype->name, [ 'post', 'page', 'attachment' ] )
                && ( is_post_type_archive() || is_singular() || ( is_tax() && in_array( $term->taxonomy, $ptype->taxonomies ) ) )
            ){
                $pt_title = $ptype->labels->name;

                // первая страница архива типа записи
                if( is_post_type_archive() && ! $paged_num ){
                    $home_after = sprintf( $this->arg->title_patt, $pt_title );
                }
                // singular, paged post_type_archive, tax
                else{
                    $home_after = sprintf( $linkpatt, get_post_type_archive_link( $ptype->name ), $pt_title );

                    $home_after .= ( ( $paged_num && ! is_tax() ) ? $pg_end : $sep ); // пагинация
                }
            }
        }

        $before_out = sprintf( $linkpatt, home_url(), $loc->home ) . ( $home_after ? $sep . $home_after : ( $out ? $sep : '' ) );

        $out = apply_filters( 'kama_breadcrumbs_pre_out', $out, $sep, $loc, $arg );

        $out = sprintf( $wrappatt, $before_out . $out );

        return apply_filters( 'kama_breadcrumbs', $out, $sep, $loc, $arg );
    }

    function _page_crumbs( $post ) {
        $parent = $post->post_parent;

        $crumbs = [];
        while( $parent ){
            $page = get_post( $parent );
            $crumbs[] = sprintf( $this->arg->linkpatt, get_permalink( $page ), esc_html( $page->post_title ) );
            $parent = $page->post_parent;
        }

        return implode( $this->arg->sep, array_reverse( $crumbs ) );
    }

    function _tax_crumbs( $term, $start_from = 'self' ) {
        $termlinks = [];
        $term_id = ( $start_from === 'parent' ) ? $term->parent : $term->term_id;
        while( $term_id ){
            $term = get_term( $term_id, $term->taxonomy );
            $termlinks[] = sprintf( $this->arg->linkpatt, get_term_link( $term ), esc_html( $term->name ) );
            $term_id = $term->parent;
        }

        if( $termlinks ){
            return implode( $this->arg->sep, array_reverse( $termlinks ) );
        }

        return '';
    }

    // добалвяет заголовок к переданному тексту, с учетом всех опций. Добавляет разделитель в начало, если надо.
    function _add_title( $add_to, $obj, $term_title = '' ) {

        // упростим...
        $arg = &$this->arg;
        // $term_title чиститься отдельно, теги моугт быть...
        $title = $term_title ?: esc_html( $obj->post_title );
        $show_title = $term_title ? $arg->show_term_title : $arg->show_post_title;

        // пагинация
        if( $arg->pg_end ){
            $link = $term_title ? get_term_link( $obj ) : get_permalink( $obj );
            $add_to .= ( $add_to ? $arg->sep : '' ) . sprintf( $arg->linkpatt, $link, $title ) . $arg->pg_end;
        }
        // дополняем - ставим sep
        elseif( $add_to ){
            if( $show_title ){
                $add_to .= $arg->sep . sprintf( $arg->title_patt, $title );
            }
            elseif( $arg->last_sep ){
                $add_to .= $arg->sep;
            }
        }
        // sep будет потом...
        elseif( $show_title ){
            $add_to = sprintf( $arg->title_patt, $title );
        }

        return $add_to;
    }

}



//  select template product page

// фильтр передает переменную $template - путь до файла шаблона.
// Изменяя этот путь мы изменяем файл шаблона.
add_filter( 'template_include', 'my_template' );
function my_template( $template ) {


    global $post;
    if(isset($post->post_type)){

        if( $post->post_type == 'product' ){
            return get_stylesheet_directory() . '/templates/product-template.php';
        }
    }

    return $template;

}


add_action( 'template_redirect', 'truemisha_recently_viewed_product_cookie', 20 );

function truemisha_recently_viewed_product_cookie() {
    if (! function_exists('is_product')) {
        return;
    }
    // если находимся не на странице товара, ничего не делаем
    if ( ! is_product() ) {
        return;
    }

    if ( empty( $_COOKIE[ 'woocommerce_recently_viewed_2' ] ) ) {
        $viewed_products = array();
    } else {
        $viewed_products = (array) explode( '|', $_COOKIE[ 'woocommerce_recently_viewed_2' ] );
    }

    // добавляем в массив текущий товар
    if ( ! in_array( get_the_ID(), $viewed_products ) ) {
        $viewed_products[] = get_the_ID();
    }

    // нет смысла хранить там бесконечное количество товаров
    if ( sizeof( $viewed_products ) > 15 ) {
        array_shift( $viewed_products ); // выкидываем первый элемент
    }

    // устанавливаем в куки
    wc_setcookie( 'woocommerce_recently_viewed_2', join( '|', $viewed_products ) );

}



function viewed_products() {

    if( empty( $_COOKIE[ 'woocommerce_recently_viewed_2' ] ) ) {
        $viewed_products = array();
    } else {
        $viewed_products = (array) explode( '|', $_COOKIE[ 'woocommerce_recently_viewed_2' ] );
    }

    if ( empty( $viewed_products ) ) {
        return;
    }

    // надо ведь сначала отображать последние просмотренные
    $viewed_products = array_reverse( array_map( 'absint', $viewed_products ) );

    return  $viewed_products;

}


function art_woo_custom_fields_save( $post_id ) {

    // Сохранение текстового поля.
    $woocommerce_text_field = $_POST['_page_count'];
    if ( ! empty( $woocommerce_text_field ) ) {
        update_post_meta( $post_id, '_page_count', esc_attr( $woocommerce_text_field ) );
    }
    // Сохранение текстового поля.
    $woocommerce_text_field = $_POST['_isbn'];
    if ( ! empty( $woocommerce_text_field ) ) {
        update_post_meta( $post_id, '_isbn', esc_attr( $woocommerce_text_field ) );
    }
    // Сохранение текстового поля.
    $woocommerce_text_field = $_POST['_mass'];
    if ( ! empty( $woocommerce_text_field ) ) {
        update_post_meta( $post_id, '_mass', esc_attr( $woocommerce_text_field ) );
    }
    // Сохранение текстового поля.
    $woocommerce_text_field = $_POST['_size'];
    if ( ! empty( $woocommerce_text_field ) ) {
        update_post_meta( $post_id, '_size', esc_attr( $woocommerce_text_field ) );
    }

}

add_action( 'woocommerce_process_product_meta', 'art_woo_custom_fields_save', 10 );



/*
Render product, main page
*/
function so_render_product_image($product_id)
{
    $product = wc_get_product($product_id);
    if ($product) {
        $data = (object) [
            "permalink" => esc_url($product->get_permalink()),
            "image" => $product->get_image("woocommerce_single"),
        ];

        return "
    <div class=\"item news_slide\"  style=\"width: 380px;\">
        <a href=\"{$data->permalink}\"> 
         {$data->image}
         </a>
    </div>";

    }

}


function so_render_product($product_id){

    $product = wc_get_product($product_id);

    if ($product) {
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

        $in_cart = WC()->cart &&
            WC()->cart->find_product_in_cart(
                WC()->cart->generate_cart_id($product_id)
         );

        if (!empty($data->second_image[0])) {
            $second_image = wp_get_attachment_image($data->second_image[0], 'woocommerce_single', 'true');
        } else {
            $second_image = $data->image;
        }

        $preorder = '';
        $isnew = '';
        $price = '<div class="price"><span class="num">' . $data->price . '</span></div>';

        $current_date = date('Y-m-d');

        if ( $data->custom_preorder > $current_date ) {
            $preorder = '<span class="preorder-book">Передзамовлення</span>';
        } else {

            if($data->is_new){
                $isnew = '<span class="new_book"></span>';
            }
        }

        $issale = '';
        if ($product->is_on_sale()) {
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
            $issale = '<span class="sale_book">-' . $discount_percentage . '<span class="percent">%</span></span>';
        }

        $_author = $data->authors ?? $data->author;

        $author_html = '';

        if (!empty($_author)) {
            $author_html = "<p>{$_author}</p>";
        }

        $basketHTML= '<span class="text-block">До кошика</span>';

        if ($in_cart) {
            $basketHTML= '<span class="text-block">У кошику</span><span class="icon-block icon-red-backed"></span>';
        } else {
            $basketHTML= '<span class="text-block">До кошика</span><span class="icon-block"></span>';
        }

        $shortcode = do_shortcode( '[woosw id=' . $product_id . ']' );
        return "
            <div class=\"item\"> 
            
               <div class=\"product-card\">
        
                    <a href=\"{$data->permalink}\" class=\"so_product-link\">
                        <span class=\"pr-info\">{$preorder}{$issale}</span>	
                        <span class='pr-info-new'>{$isnew}</span>	
                        <div class=\"img\">
                           {$data->image}
                        </div>
                    </a>
                    <div class=\"product-title\">
                       
                            <h3>{$data->title}</h3>
                              {$author_html} 
                            <p>{$data->age}</p>
                        
                     </div> 
                     
                    {$price}
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
            ";
    } else {
        return "<div class=\"so_product\">
        <h4>" . __("Error product ID: ", "SchoolBook") . $product_id ."</h4>
        </div>";
    }
}

function mytheme_comment( $comment, $args, $depth ) {

    if ( 'div' === $args['style'] ) {
        $tag       = 'div';
        $add_below = 'comment';
    }
    else {
        $tag       = 'li';
        $add_below = 'div-comment';
    }

    $classes = ' ' . comment_class( empty( $args['has_children'] ) ? '' : 'parent', null, null, false );
    ?>

    <<?= $tag . $classes; ?> id="comment-<?php comment_ID() ?>">
    <?php if ( 'div' != $args['style'] ) { ?>
        <div id="div-comment-<?php comment_ID() ?>" class="comment-body"><?php
    } ?>

    <div class="comment-author vcard">
        <?php
        if ( $args['avatar_size'] != 0 ) {
            echo get_avatar( $comment, $args['avatar_size'] );
        }
        printf(
            __( '<cite class="fn">%s</cite> <span class="says">says:</span>' ),
            get_comment_author_link()
        );
        ?>
    </div>

    <?php if ( $comment->comment_approved == '0' ) { ?>
        <em class="comment-awaiting-moderation">
            <?php _e( 'Your comment is awaiting moderation.' ); ?>
        </em><br/>
    <?php } ?>

    <div class="comment-meta commentmetadata">
        <a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>">
            <?php
            printf(
                __( '%1$s at %2$s' ),
                get_comment_date(),
                get_comment_time()
            ); ?>
        </a>

        <?php edit_comment_link( __( '(Edit)' ), '  ', '' ); ?>
    </div>

    <?php comment_text(); ?>

    <div class="reply">
        <?php
        comment_reply_link(
            array_merge(
                $args,
                array(
                    'add_below' => $add_below,
                    'depth'     => $depth,
                    'max_depth' => $args['max_depth']
                )
            )
        ); ?>
    </div>

    <?php if ( 'div' != $args['style'] ) { ?>
        </div>
    <?php }
}


add_filter('comment_form_default_fields', 'extend_comment_custom_default_fields');
function extend_comment_custom_default_fields($fields) {

    $commenter = wp_get_current_commenter();
    $req = get_option( 'require_name_email' );
    $aria_req = ( $req ? " aria-required='true'" : '' );

    $fields[ 'author' ] = '<div class="row">
	<div class="input-holder correct">
	<p class="comment-form-author">
	  <input id="author" name="author" type="text" class="input" value="'. esc_attr( $commenter['comment_author'] ) .
        '" size="30" tabindex="1"' . $aria_req . ' placeholder="Ваше iм’я*" /></p>
	  </div></div>';

    $fields[ 'email' ] = '<div class="row">
	<div class="input-holder correct">
	<p class="comment-form-email"><input id="email" name="email" type="text" class="input" value="'. esc_attr( $commenter['comment_author_email'] ) .
        '" size="30"  tabindex="2"' . $aria_req . 'placeholder="E-mail*" /></p>
	  </div></div>';

    $fields[ 'url' ] = '';



    return $fields;
}

// Добавляем поля для всех пользователей
add_action( 'comment_form_logged_in_after', 'extend_comment_custom_fields' );
add_action( 'comment_form_after_fields', 'extend_comment_custom_fields' );
function extend_comment_custom_fields() {



    echo '<p class="comment-form-rating">
			  <span class="commentratingbox">
			  <span class="rate-title">'. __('Оцiни книжечку') . '</span><span class="required">*</span>
			  <div class="rating">
			  <input id="item5" name="rating" type="radio" checked="" value="5" class="rating__check">
			  <label for="item5" class="rating__star rating__star_choose"></label>
			  <input id="item4" name="rating" type="radio" value="4" class="rating__check">
			  <label for="item4" class="rating__star rating__star_choose"></label>
			  <input id="item3" name="rating" type="radio" value="3" class="rating__check">
			  <label for="item3" class="rating__star rating__star_choose"></label>
			  <input id="item2" name="rating" type="radio" value="2" class="rating__check">
			  <label for="item2" class="rating__star rating__star_choose"></label>
			  <input id="item1" name="rating" type="radio" value="1" class="rating__check">
			  <label for="item1" class="rating__star rating__star_choose"></label>
		  </div>

	</span></p>';
}

add_action( 'comment_post', 'save_extend_comment_meta_data' );
function save_extend_comment_meta_data( $comment_id ){


    if( !empty( $_POST['rating'] ) ){
        $rating = intval($_POST['rating']);
        add_comment_meta( $comment_id, 'rating', $rating );
    }

}

// Проверяем, заполнено ли поле "Рейтинг"
add_filter( 'preprocess_comment', 'verify_extend_comment_meta_data' );
function verify_extend_comment_meta_data( $commentdata ) {

    // ничего не делаем если это ответ на комментарий
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'replyto-comment' ) {
        return $commentdata;
    }

    if ( empty( $_POST['rating'] ) || ! (int)$_POST['rating'] ) {
        wp_die( __( 'Error: You did not add a rating. Hit the Back button on your Web browser and resubmit your comment with a rating.' ) );
    }

    return $commentdata;
}

add_filter( 'comment_form_defaults', 'truemisha_comment_button_text', 25 );

function truemisha_comment_button_text( $args ) {

    $args[ 'label_submit' ] = 'Залишити відгук';
    return $args;

}

add_filter( 'comment_form_submit_button', 'truemisha_comment_button', 25, 2 );

function truemisha_comment_button( $submit_button, $args ) {

    return '<input type="submit" class="submit-btn btn" value="Залишити вiдгук">';

}

add_filter( 'comment_form_field_comment', 'truemisha_change_comment_label', 25 );

function truemisha_change_comment_label( $field ) {

    $field = str_replace( 'name="comment"', 'name="comment" placeholder="Відгук" class="input"', $field );
    return $field;

}

add_filter( 'comment_text', 'modify_extend_comment');
function modify_extend_comment( $text ){

    $plugin_url_path = WP_PLUGIN_URL;


    if( $commentrating = get_comment_meta( get_comment_ID(), 'rating', true ) ) {
        $stars = '';
        for ($i=0; $i < $commentrating; $i++) {
            $stars .= '<label for="item1" class="rating__star rating__star_choose"></label>';
        }
        $commentrating = '<div class="comment-rating rating">'.$stars.'</div>';
        $text = $text . $commentrating;
        return $text;
    } else {
        return $text;
    }
}

add_action( 'wp_enqueue_scripts', 'check_count_extend_comments' );
function check_count_extend_comments(){
    global $post;

    if( isset($post) && (int)$post->comment_count > 0 ){
        require_once ABSPATH .'wp-admin/includes/template.php';
        add_action('wp_enqueue_scripts', function(){
            wp_enqueue_style('dashicons');
        });

        $stars_css = '
		.star-rating .star-full:before { content: "\f155"; }
		.star-rating .star-empty:before { content: "\f154"; }
		.star-rating .star {
			color: #0074A2;
			display: inline-block;
			font-family: dashicons;
			font-size: 20px;
			font-style: normal;
			font-weight: 400;
			height: 20px;
			line-height: 1;
			text-align: center;
			text-decoration: inherit;
			vertical-align: top;
			width: 20px;
		}
		';

        wp_add_inline_style( 'dashicons', $stars_css );
    }

}
// Добавляем новый метабокс на страницу редактирования комментария
add_action( 'add_meta_boxes_comment', 'extend_comment_add_meta_box' );
function extend_comment_add_meta_box(){
    add_meta_box( 'title', __( 'Comment Metadata - Extend Comment' ), 'extend_comment_meta_box', 'comment', 'normal', 'high' );
}

// Отображаем наши поля
function extend_comment_meta_box( $comment ){
    $phone  = get_comment_meta( $comment->comment_ID, 'phone', true );
    $title  = get_comment_meta( $comment->comment_ID, 'title', true );
    $rating = get_comment_meta( $comment->comment_ID, 'rating', true );

    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
    ?>

    <p>
        <label for="rating"><?php _e( 'Rating: ' ); ?></label>
        <span class="commentratingbox">
		<?php
        for( $i=1; $i <= 5; $i++ ){
            echo '
		  <span class="commentrating">
			<input type="radio" name="rating" id="rating" value="'. $i .'" '. checked( $i, $rating, 0 ) .'/>
		  </span>';
        }
        ?>
		</span>
    </p>
    <?php
}

add_action( 'edit_comment', 'extend_comment_edit_meta_data' );
function extend_comment_edit_meta_data( $comment_id ) {
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) )
        return;

    if( !empty($_POST['phone']) ){
        $phone = sanitize_text_field($_POST['phone']);
        update_comment_meta( $comment_id, 'phone', $phone );
    }
    else
        delete_comment_meta( $comment_id, 'phone');

    if( !empty($_POST['title']) ){
        $title = sanitize_text_field($_POST['title']);
        update_comment_meta( $comment_id, 'title', $title );
    }
    else
        delete_comment_meta( $comment_id, 'title');

    if( !empty($_POST['rating']) ){
        $rating = intval($_POST['rating']);
        update_comment_meta( $comment_id, 'rating', $rating );
    }
    else
        delete_comment_meta( $comment_id, 'rating');

}

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// Регистрация контейнера и поля для таксономии product_cat
add_action('carbon_fields_register_fields', 'custom_register_fields');

function custom_register_fields() {
    Container::make('term_meta', __( ' Знижка на категорію'))
        ->where('term_taxonomy', '=', 'product_cat')
        ->add_fields(array(
            Field::make('select', 'category_discount', __( ' Знижка на категорію'))
                ->set_options(array(
                    'none' => 'Без знижки',
                    '1' => '1%',
                    '2' => '2%',
                    '3' => '3%',
                    '4' => '4%',
                    '5' => '5%',
                    '6' => '6%',
                    '7' => '7%',
                    '8' => '8%',
                    '9' => '9%',
                    '10' => '10%',
                    '11' => '11%',
                    '12' => '12%',
                    '13' => '13%',
                    '14' => '14%',
                    '15' => '15%',
                    '16' => '16%',
                    '17' => '17%',
                    '18' => '18%',
                    '19' => '19%',
                    '20' => '20%',
                    '21' => '21%',
                    '22' => '22%',
                    '23' => '23%',
                    '24' => '24%',
                    '25' => '25%',
                    '26' => '26%',
                    '27' => '27%',
                    '28' => '28%',
                    '29' => '29%',
                    '30' => '30%',
                    // Добавьте другие варианты скидок по желанию
                )),
        ));
}


// Используйте сохраненное значение в вашем коде
function custom_category_discount($price, $product) {
    $category = get_the_terms($product->get_id(), 'product_cat');

    if ($category) {
        // Получаем значение из метаполя
        $discount_option = carbon_get_term_meta($category[0]->term_id, 'category_discount');

        // Применяем скидку, если выбрана скидка
        if ($discount_option && $discount_option !== 'none') {
            $discount = floatval($discount_option);
            $price = floatval($price);
            $discount_amount = $price * ($discount / 100.0); // Преобразование в float
            $price -= $discount_amount;
        }
    }

    return $price;
}

add_filter('woocommerce_product_get_price', 'custom_category_discount', 10, 2);
// add_filter('woocommerce_product_get_regular_price', 'custom_category_discount', 10, 2);
add_filter('woocommerce_product_get_sale_price', 'custom_category_discount', 10, 2);



// Добавление поля выбора даты "Дата закінчення передзамовлення (заявка в telegram)" на вкладку "Общие"
add_action( 'woocommerce_product_options_general_product_data', 'add_preorder_datepicker_to_general_tab' );
function add_preorder_datepicker_to_general_tab() {
    global $post;

    echo '<div class="options_group">';

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_preorder',
            'label'       => __( 'Дата закінчення передзамовлення (заявка в telegram)', 'woocommerce' ),
            'description' => __( 'Виберіть дату закінчення передзамовлення. Заявки приходять в telegram канал', 'woocommerce' ),
            'type'        => 'date',
        )
    );

    echo '</div>';
}

// Добавление поля выбора даты "Дата закінчення передзамовлення (заявка в telegram)" на вкладку "Общие"
add_action( 'woocommerce_product_options_general_product_data', 'add_preorder_datepicker_for_countdown' );
function add_preorder_datepicker_for_countdown() {
    global $post;

    echo '<div class="options_group">';

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_preorder_countdown',
            'label'       => __( 'Дата закінчення передзамовлення', 'woocommerce' ),
            'description' => __( 'Виберіть дату закінчення передзамовлення. Для зворотного відліку', 'woocommerce' ),
            'type'        => 'date',
        )
    );

    echo '</div>';
}

add_action( 'woocommerce_process_product_meta', 'save_preorder_date_general_tab' );
function save_preorder_date_general_tab( $post_id ) {

    $custom_preorder_date = isset( $_POST['_custom_preorder'] ) ? $_POST['_custom_preorder'] : '';
    update_post_meta( $post_id, '_custom_preorder', $custom_preorder_date );
}

add_action( 'woocommerce_process_product_meta', 'save_preorder_date_countdown' );
function save_preorder_date_countdown( $post_id ) {

    $custom_preorder_countdown = isset( $_POST['_custom_preorder_countdown'] ) ? $_POST['_custom_preorder_countdown'] : '';
    update_post_meta( $post_id, '_custom_preorder_countdown', $custom_preorder_countdown );
}


// Добавляем поле "Новинка" во вкладку "Основные" в товарах WooCommerce
function add_custom_meta_field() {

    $post_id = get_the_ID();
    $is_new = get_post_meta($post_id, '_is_new', true);

    echo '<div class="options_group">';
    echo '<p class="form-field custom-checkbox-field">';
    echo '<label for="is_new">' . __('Новинка', 'textdomain') . '</label>';
    echo '<span class="description">' . __('Поставте прапорець якщо товар являєтся новинкой.', 'textdomain') . '</span>';
    echo '<input type="checkbox" class="checkbox" name="is_new" id="is_new" value="1" ' . checked(1, $is_new, false) . ' />';
    echo '</p>';
    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'add_custom_meta_field');


function save_custom_meta_field($post_id) {
    // Проверяем, выполняется ли автосохранение или необходимые права доступа
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Проверяем, установлен ли чекбокс "Новинка"
    $is_new = isset($_POST['is_new']) ? '1' : '0';

    // Обновляем значение мета-поля "_is_new"
    update_post_meta($post_id, '_is_new', $is_new);

    // Если чекбокс был установлен, сохраняем текущую дату
    if ($is_new === '1') {
        update_post_meta($post_id, '_is_new_date', current_time('mysql'));
    } else {
        // Если чекбокс был снят, удаляем дату
        delete_post_meta($post_id, '_is_new_date');
    }
}

add_action('woocommerce_process_product_meta', 'save_custom_meta_field');
function get_products_with_preorder_date() {
    global $wpdb;

    $current_date = date('Y-m-d H:i:s'); // Текущая дата и время

    $product_ids = $wpdb->get_col( $wpdb->prepare( "
        SELECT post_id
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_custom_preorder' 
        AND meta_value <> '' 
        AND meta_value > %s
    ", $current_date ) );

    return $product_ids;
}



function set_new_flag_after_60_days() {
    // Получаем массив всех товаров с установленной датой "Дата закінчення передзамовлення"
    global $wpdb;

    $current_date = date('Y-m-d H:i:s'); // Текущая дата и время
    $previous_date = date('Y-m-d H:i:s', strtotime('-60 day', strtotime($current_date)));

    $product_ids = $wpdb->get_col( $wpdb->prepare( "
        SELECT post_id
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_custom_preorder' 
        AND meta_value <> '' 
        AND meta_value >= %s
    ", $previous_date) );

    foreach ($product_ids as $product_id) {
        $preorder_end_date = get_post_meta($product_id, '_custom_preorder', true);

        // Проверяем, наступила ли уже указанная дата "Дата закінчення передзамовлення"
        $preorder_end_date_timestamp = strtotime($preorder_end_date);
        $current_timestamp = current_time('timestamp');

        // Если текущая дата больше или равна дате "Дата закінчення передзамовлення", устанавливаем чекбокс "Новинка"
        if ($current_timestamp >= $preorder_end_date_timestamp) {
            // Если прошло 60 дней с даты "Дата закінчення передзамовлення", отключаем чекбокс "Новинка"
            $sixty_days_later_timestamp = strtotime('+60 days', $preorder_end_date_timestamp);
            if ($current_timestamp > $sixty_days_later_timestamp) {
                update_post_meta($product_id, '_is_new', '0');
            }else{
                update_post_meta($product_id, '_is_new', '1');
                update_post_meta($product_id, '_is_new_date', current_time('mysql'));
            }}
    }
    // Получаем все посты, у которых установлен мета-ключ "_is_new_date"
    $args = array(
        'post_type' => 'product',
        'meta_query' => array(
            array(
                'key' => '_is_new_date',
                'compare' => 'EXISTS',
            ),
        ),
        'posts_per_page' => -1,
    );

    $products_query = new WP_Query($args);

    // Проверяем, если есть посты
    if ($products_query->have_posts()) {
        while ($products_query->have_posts()) {
            $products_query->the_post();

            $post_id = get_the_ID();
            $is_new_date = get_post_meta($post_id, '_is_new_date', true);

            // Получаем текущую дату и время
            $current_timestamp = current_time('timestamp');

            // Проверяем, если прошло более 60 дней с даты "_is_new_date"
            $is_new_date_plus_60_days = strtotime('+60 days', strtotime($is_new_date));
            if ($current_timestamp > $is_new_date_plus_60_days) {
                // Очищаем чекбокс "новинка"
                update_post_meta($post_id, '_is_new', '0');
            }
        }

        // Сбрасываем состояние пост-запроса
        wp_reset_postdata();
    }
}

// Регистрируем событие в планировщике событий WordPress при активации темы или плагина
if( isset($_GET['activated']) && $_GET['activated'] === 'true' ) {
    // Планируем выполнение функции один раз в день в 00:00
    if (!wp_next_scheduled('daily_event_hook')) {
        wp_schedule_event(strtotime('00:00:00'), 'daily', 'book_60_day');
    }
}
// Добавляем функцию, которая будет выполняться по расписанию
add_action('book_60_day', 'set_new_flag_after_60_days');

// Отменяем событие в планировщике событий WordPress при деактивации темы или плагина
register_deactivation_hook(__FILE__, 'unschedule_daily_event');

function unschedule_daily_event() {
    wp_clear_scheduled_hook('daily_event_hook');
}



// добавляем поле Видео
function add_custom_video_field() {
    $post_id = get_the_ID();

    $video_link = get_post_meta($post_id, '_video_link', true);

    echo '<div class="options_group">';
    echo '<p class="form-field custom-video-field">';
    echo '<label for="video_link">' . __('Посилння на відео', 'textdomain') . '</label>';
    echo '<input type="text" class="input-text" name="video_link" id="video_link" value="' . esc_attr($video_link) . '" />';
    echo '</p>';
    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'add_custom_video_field');

function save_custom_video_field($post_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $video_link = sanitize_text_field($_POST['video_link']);
    update_post_meta($post_id, '_video_link', $video_link);
}
add_action('woocommerce_process_product_meta', 'save_custom_video_field');


//создание категорий основываясь на атрибуте с началом назваия КТ
function add_1c_category_menu() {
    add_submenu_page(
        'woocommerce',
        '1С категорії',
        '1С категорії',
        'manage_options',
        '1c_category_settings',
        'display_1c_category_settings'
    );
}
add_action('admin_menu', 'add_1c_category_menu');


function display_1c_category_settings() {
    ?>
    <div class="wrap">
        <h1>Наслаштування 1С категорій</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('1c_category_settings');
            do_settings_sections('1c_category_settings');
            submit_button();
            ?>
        </form>
        <p>Увімкнувши синхронізацію, ви активуєте скрипт, який шукає товари, що знаходяться в категорії <b>"склад"</b> із слагом <b>"sklad"</b>. <br>
            Якщо у цього товару є атрибути з назвою, що починається з <b>"КТ"</b>, то скрипт шукає категорію з такою самою назвою, <br>
            за винятком перших двох символів. Якщо така категорія існує, товар призначається їй, а також категорії, ім'я  <br>
            якої відповідає значенню атрибута. <br>
            Якщо такої категорії не існує, скрипт створить таку категорію і призначить товар до цієї категорії і підкатегорії, <br>
            відповідно до назви та значення атрибута. <br>Після призначення категорій товар автоматично видаляється з категорії <b>"склад"</b>.
        </p>

    </div>
    <?php
}

function setup_1c_category_settings() {
    register_setting('1c_category_settings', 'enable_1c_category_sync');

    add_settings_section(
        '1c_category_section',
        'Налаштування синхронізації з категоріями 1С',
        '',
        '1c_category_settings'
    );

    add_settings_field(
        'enable_1c_category_sync',
        'Увімкнути синхронізацію',
        'enable_1c_category_sync_callback',
        '1c_category_settings',
        '1c_category_section'
    );
}
add_action('admin_init', 'setup_1c_category_settings');


function enable_1c_category_sync_callback() {
    $checked = get_option('enable_1c_category_sync') ? 'checked' : '';
    echo '<input type="checkbox" name="enable_1c_category_sync" ' . $checked . '>';
}

add_action('wp_loaded', 'so_add_cat_product');
function so_add_cat_product() {
    $enable_sync = get_option('enable_1c_category_sync');
    if (!$enable_sync) {
        return;
    }
    $query_request =  array(
        'product_cat' => 'sklad',
        'post_type' => array('product'),
    );
    $products_query = new WP_Query($query_request);

    if ($products_query->have_posts()) {
        while ($products_query->have_posts()) {
            $products_query->the_post();
            $product_id = get_the_ID();
            update_or_create_product($product_id);
        }
        wp_reset_postdata();
    }
}

function update_or_create_product($post_id) {
    if (get_post_type($post_id) !== 'product') {
        return;
    }

    $product = wc_get_product($post_id);
    $attributes = $product->get_attributes();

    foreach ($attributes as $attribute) {
        $attribute_slug = strtolower($attribute->get_name());

        if (strpos($attribute_slug, 'pa_kt-') === 0) {
            $attribute_name = preg_replace('/^КТ\s*/u', '', wc_attribute_label($attribute_slug));
            $attribute_value = $product->get_attribute($attribute_slug);

            $option = get_term_by('name', $attribute_value, $attribute_slug);

            $category_slug = strtolower($attribute_slug);
            $subcategory_slug = $option ? strtolower($option->slug) : '';

            $category_id = get_term_by('slug', $category_slug, 'product_cat');
            $subcategory_id = $subcategory_slug ? get_term_by('slug', $subcategory_slug, 'product_cat') : '';

            if (!$category_id) {
                $new_category = wp_insert_term($attribute_name, 'product_cat', array('slug' => $category_slug, 'parent' => 0));
                if (!is_wp_error($new_category)) {
                    $category_id = $new_category['term_id'];
                }
            }

            $subcategory_id = $subcategory_slug ? get_term_by('slug', $subcategory_slug, 'product_cat') : '';

            if ($category_id && !$subcategory_id) {
                $new_subcategory = wp_insert_term($attribute_value, 'product_cat', array('slug' => $subcategory_slug, 'parent' => $category_id));
                if (!is_wp_error($new_subcategory)) {
                    $subcategory_id = $new_subcategory['term_id'];
                }
            }

            if ($subcategory_id && $category_id) {
                wp_update_term($subcategory_id->term_id, 'product_cat', array('parent' => $category_id->term_id));
            }

            $current_terms = wp_get_object_terms($post_id, 'product_cat', array('fields' => 'ids'));

            if ($category_id && !in_array($category_id->term_id, $current_terms)) {
                wp_set_object_terms($post_id, array(), 'product_cat');
                wp_set_object_terms($post_id, array($category_id->slug), 'product_cat', true);
            }

            if ($subcategory_id && !in_array($subcategory_id->term_id, $current_terms)) {
                wp_set_object_terms($post_id, array($subcategory_id->slug), 'product_cat', true);
            }
        }
    }

}

// Добавляем вкладку "Удалить товары" в меню Товары
function add_custom_menu_page() {
    add_submenu_page(
        'edit.php?post_type=product',
        'Удалить товары',
        'Удалить товары',
        'manage_options',
        'delete_products_page',
        'delete_products_page_callback'
    );
}
add_action('admin_menu', 'add_custom_menu_page');

function delete_products_page_callback() {
    if (isset($_POST['delete_items'])) {
        $delete_products = isset($_POST['delete_products']);
        $delete_categories = isset($_POST['delete_categories']);
        $delete_attributes = isset($_POST['delete_attributes']);

        delete_selected_items($delete_products, $delete_categories, $delete_attributes);

        echo '<div class="updated"><p>Выбранные элементы успешно удалены!</p></div>';
    }

    echo '<div class="wrap">';
    echo '<h2>Удаление товаров, категорий и атрибутов</h2>';
    echo '<form method="post">';
    echo '<p>Выберите элементы для удаления:</p>';
    echo '<label><input type="checkbox" name="delete_products"> Товары</label><br>';
    echo '<label><input type="checkbox" name="delete_categories"> Категории</label><br>';
    echo '<label><input type="checkbox" name="delete_attributes"> Атрибуты</label><br>';
    echo '<input type="submit" name="delete_items" class="button button-primary" value="Удалить выбранные элементы">';
    echo '</form>';
    echo '</div>';
}

function delete_selected_items($delete_products, $delete_categories, $delete_attributes) {
    if ($delete_products) {
        delete_all_products();
    }

    if ($delete_categories) {
        delete_all_categories();
    }

    if ($delete_attributes) {
        delete_all_attributes();
    }
}

function delete_all_products() {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
    );

    $products = get_posts($args);

    foreach ($products as $product) {
        wp_delete_post($product->ID, true);
    }
}


function delete_all_categories() {
    $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));

    foreach ($categories as $category) {
        wp_delete_term($category->term_id, 'product_cat');
    }
}

function delete_all_attributes() {
    $attributes = wc_get_attribute_taxonomies();

    foreach ($attributes as $attribute) {
        wc_delete_attribute($attribute->attribute_id);
    }
}


// Добавление текстового поля и чекбокса во вкладку "общие" Віртуальна позиція (задля роялті)

add_action( 'woocommerce_product_options_general_product_data', 'add_custom_fields_to_general_tab' );
function add_custom_fields_to_general_tab() {
    global $post;

    echo '<div class="options_group">';
    echo '<h3 class="adminka-field">' . __( 'Віртуальна позиція (задля роялті)', 'woocommerce' ) . '</h3>';

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_status',
            'label'       => __( 'Статус', 'woocommerce' ),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => __( 'Введіть статус.', 'woocommerce' ),
        )
    );

    woocommerce_wp_checkbox(
        array(
            'id'          => '_custom_royalty',
            'label'       => __( 'Роялті', 'woocommerce' ),
            'description' => __( 'Поставте прапорець, якщо товар належить до роялті', 'woocommerce' ),
        )
    );

    echo '
	<p>Якщо встановлено прапорець "роялті", то кнопка "купити" буде схована в картці товару, а замість неї відобразиться текст з поля "статус"</p>
	</div>';
}

add_action( 'woocommerce_process_product_meta', 'save_custom_fields_general_tab' );
function save_custom_fields_general_tab( $post_id ) {
    $custom_status = isset( $_POST['_custom_status'] ) ? sanitize_text_field( $_POST['_custom_status'] ) : '';
    update_post_meta( $post_id, '_custom_status', $custom_status );

    $custom_royalty = isset( $_POST['_custom_royalty'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_custom_royalty', $custom_royalty );
}



// function so_preorder(){
//   $nonce_back =  $_POST['nonce_code'];
//   $filds =  $_POST['filds'];
//   $nonce_front = wp_create_nonce('so_creator_nonce');
//   $product = wc_get_product($filds['id']);
//   check_ajax_referer( 'so_creator_nonce', 'nonce_code' );

//   $response = '
//   <div class="modal-body modal-body--one-click-thx">
// 	  <div class="popup-form-wrap">
// 		  <h2 class="h2-title">Дя<span class="color-orange">ку</span>є<span class="color-blue">мо</span>!</h2>
// 		  <div class="text-center">
// 			  <h3>Ми зателефонуємо вам найближчим часом</h3>
// 		  </div>
// 	  </div>
//   </div>
// ';
// $data = json_encode($response,true);

// $msg='';

// $msg .= 'Передзамовлення на книгу: ' ."\r\n\r\n". $product->get_name() ."\r\n\r\n". 'артикул: '. $product->get_sku() . "\r\n\r\n";
// $msg .= $filds['name'] ."\r\n\r\n". ' tel: '. $filds['phone'] . "\r\n\r\n";

// $token = '6343897793:AAE_Jbf8b0Mi0sfahr-8hZBSMspoVMz8z_A';
// file_get_contents('https://api.telegram.org/bot'. $token .'/sendMessage?chat_id=-1002006914562&text=' . urlencode($msg));

// echo $data;
//   wp_die();
// }



// add_action('wp_ajax_so_preorder', 'so_preorder' );
// add_action('wp_ajax_nopriv_so_preorder', 'so_preorder' );


//отключить проверку обновлений плагинов и движка
add_filter( 'auto_update_plugin', '__return_false' );
add_filter( 'pre_site_transient_update_core', '__return_null' );
function disable_plugin_updates($value) {
    if(isset($value) && is_object($value)) {
        unset($value->response);
    }
    return $value;
}
add_filter('site_transient_update_plugins', 'disable_plugin_updates');

add_filter( 'woocommerce_rest_check_permissions', 'my_woocommerce_rest_check_permissions', 90, 4 );

function my_woocommerce_rest_check_permissions( $permission, $context, $object_id, $post_type  ){
    return true;
}

add_filter( 'aws_image_size', 'my_aws_image_size' );
function my_aws_image_size( $image_size ) {
    return 'full';
}

function set_global_posts_per_page( $query ) {
    // Проверяем, что это основной запрос, не админка и не RSS/feed
    if ( $query->is_main_query() && ! is_admin() && ! is_feed() ) {
        // Устанавливаем количество постов для главной страницы или страницы блога
        if ( is_home() || is_front_page() ) { // Или только is_home() для страницы блога
            $query->set( 'posts_per_page', 48 );
        }
    }
}
add_action( 'pre_get_posts', 'set_global_posts_per_page' );


add_filter('template_include', function ($template) {

    // Это AWS-поиск товаров
    if (
        is_search()
        && isset($_GET['type_aws']) && $_GET['type_aws'] === 'true'
        && isset($_GET['post_type']) && $_GET['post_type'] === 'product'
    ) {
        // Всегда используем архив товаров
        $archive = locate_template('search.php');
        if ($archive) {
            return $archive;
        }
    }

    return $template;
}, 50);


add_filter( 'woocommerce_shipping_chosen_method', '__return_false', 999 );

add_action( 'woocommerce_before_cart', 'default_shipping_country_force' );
add_action( 'woocommerce_before_checkout_form', 'default_shipping_country_force' );

function default_shipping_country_force() {
    // Проверяем наличие WooCommerce и объекта клиента
    if ( is_admin() || ! function_exists( 'WC' ) || ! WC()->customer ) {
        return;
    }

    // Если страна еще не определена, ставим UA
    if ( empty( WC()->customer->get_shipping_country() ) ) {
        WC()->customer->set_billing_country( 'UA' );
        WC()->customer->set_shipping_country( 'UA' );

        // Пересчитываем корзину
        WC()->cart->calculate_totals();
    }
}

add_action( 'wp_footer', 'force_shipping_selection_script' );
function force_shipping_selection_script() {
    // Работает только на страницах корзины и оформления
    if ( is_cart() || is_checkout() ) {
        ?>
        <script type="text/javascript">
            jQuery(function($){
                // Функция проверки выбора
                function checkShippingSelection() {
                    var isSelected = $('input[name^="shipping_method"]:checked').length > 0;
                    var checkoutBtn = $('.checkout-button, #place_order');

                    if ( !isSelected ) {
                        checkoutBtn.css({
                            'pointer-events': 'none',
                            'opacity': '0.5',
                            'filter': 'grayscale(1)'
                        });
                    } else {
                        checkoutBtn.css({
                            'pointer-events': 'auto',
                            'opacity': '1',
                            'filter': 'none'
                        });
                    }
                }

                // Проверяем при загрузке и при каждом обновлении корзины (AJAX)
                $(document.body).on('updated_cart_totals updated_checkout', function(){
                    checkShippingSelection();
                });

                // Проверяем при клике на метод доставки
                $(document).on('change', 'input[name^="shipping_method"]', function(){
                    checkShippingSelection();
                });

                checkShippingSelection(); // Инициализация при первой загрузке
            });
        </script>
        <?php
    }
}

add_action( 'woocommerce_check_cart_items', 'prevent_checkout_without_shipping' );
add_action( 'woocommerce_checkout_process', 'prevent_checkout_without_shipping' );

function prevent_checkout_without_shipping() {
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

    if ( WC()->cart->get_cart_contents_count() > 0 ) {
        // Если метод не выбран или массив пуст
        if (empty($chosen_methods) || !isset($chosen_methods[0])) {
            wc_add_notice('Будь ласка, оберіть метод доставки, щоб продовжити оформлення замовлення.', 'error');
        }
    }
}

function any_web_theme_has_woocommerce(): bool {
    return class_exists('WooCommerce');
}

function any_web_theme_account_url(): string {
    if ( any_web_theme_has_woocommerce() && function_exists('wc_get_page_permalink') ) {
        $url = wc_get_page_permalink('myaccount');
        if ( $url ) return $url;
    }
    return home_url('/'); // fallback (или wp_login_url())
}

function any_web_theme_cart_url(): string {
    return ( any_web_theme_has_woocommerce() && function_exists('wc_get_cart_url') )
        ? wc_get_cart_url()
        : home_url('/');
}



// add_action( 'woocommerce_before_cart', function () {

//     if ( WC()->cart->get_cart_contents_count() > 0 ) {
//         wc_print_notice(
//             '<h2 class="bg-error text-center">⚠ Увага! Ваше замовлення буде опрацьовано з 05 січня 2026 року.</h2>',
//             'notice'
//         );
//     }

// });

