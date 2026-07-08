<?php
if (!defined("ABSPATH")) {
  exit;
}
//add nav menu
add_action( 'after_setup_theme', 'theme_register_nav_menu' );
function theme_register_nav_menu() {
	register_nav_menu( 'primary', 'головне меню' );
	register_nav_menu( 'primary-footer', 'головне меню футер' );
	register_nav_menu( 'mob-footer', 'мобильное меню футер' );
	register_nav_menu( 'category', 'Каталог товарів' );
	register_nav_menu( 'category-footer', 'Каталог товарів футер' );
	register_nav_menu( 'about-footer', 'Давайте знайомитись' );
	register_nav_menu( 'two_column_menu', 'two_column_menu' );
	register_nav_menu( 'top_two_column_menu', 'top two_column_menu' );
	register_nav_menu( 'wishlist', 'wishlist' );
    register_nav_menu( 'footer__copy_col-1', 'Footer site copy - 1' );
    register_nav_menu( 'footer__copy_col-2', 'Footer site copy - 2' );
    register_nav_menu( 'footer__copy_col-3', 'Footer site copy - 3' );
}



// Изменяет основные параметры меню
add_filter( 'wp_nav_menu_args', 'filter_wp_menu_args_primary' );

// Изменяем атрибут id у тега li
add_filter( 'nav_menu_item_id', 'filter_menu_item_css_id_primary', 10, 4 );

// Изменяем атрибут class у тега li
add_filter( 'nav_menu_css_class', 'filter_nav_menu_css_classes_primary', 10, 4 );

// Изменяет класс у вложенного ul
add_filter( 'nav_menu_submenu_css_class', 'filter_nav_menu_submenu_css_class_primary', 10, 3 );

// Добавляем классы ссылкам
add_filter( 'nav_menu_link_attributes', 'filter_nav_menu_link_attributes_primary', 10, 4 );

// Добавляем классы к ссылкам с определенным именем
add_filter( 'nav_menu_css_class', 'change_menu_item_css_classes_primary', 10, 4 );

function filter_wp_menu_args_primary( $args ) {
	if ( $args['theme_location'] === 'primary' ) {
		$args['container']  = false;
		$args['items_wrap'] = '<ul id="%2$s">%3$s</ul>';
//		$args['menu_class'] = 'horizontal-multilevel-menu';
	}
	return $args;
}

function filter_menu_item_css_id_primary( $menu_id, $item, $args, $depth ) {
	return $args->theme_location === 'primary' ? '' : $menu_id;
}

function filter_nav_menu_css_classes_primary( $classes, $item, $args, $depth ) {
	if ( $args->theme_location === 'primary' ) {
		$classes = [];
		if ( $item->current ) {
			$classes[] = 'menu-node--active';
		}
	}
	return $classes;
}

function filter_nav_menu_submenu_css_class_primary( $classes, $args, $depth ) {
	if ( $args->theme_location === 'primary' ) {
		$classes = [
			'menu',
			'menu--dropdown',
			'menu--vertical'
		];
	}
	return $classes;
}

function filter_nav_menu_link_attributes_primary( $atts, $item, $args, $depth ) {
	if ( $args->theme_location === 'primary' ) {
		$atts['class'] = 'root-item';
		if ( $item->current ) {
			$atts['class'] .= ' root-item-selected';
		}
	}
	return $atts;
}

function change_menu_item_css_classes_primary( $classes, $item, $args, $depth ) {
	if( 'Акції' === $item->title  && 'primary' === $args->theme_location ){
		$classes[] = 'with-icon';
	}
	if( 'Відео- новини' === $item->title  && 'primary' === $args->theme_location ){
		$classes[] = 'with-icon videonews';
	}

	return $classes;
}

// -----------------------------


// Изменяет основные параметры меню
add_filter( 'wp_nav_menu_args', 'filter_wp_menu_args_category' );

// Изменяем атрибут id у тега li
add_filter( 'nav_menu_item_id', 'filter_menu_item_css_id_category', 10, 4 );

// Изменяем атрибут class у тега li
add_filter( 'nav_menu_css_class', 'filter_nav_menu_css_classes_category', 10, 4 );

// Изменяет класс у вложенного ul
add_filter( 'nav_menu_submenu_css_class', 'filter_nav_menu_submenu_css_class_category', 10, 3 );

// Добавляем классы ссылкам
add_filter( 'nav_menu_link_attributes', 'filter_nav_menu_link_attributes_category', 10, 4 );

// Добавляем классы к ссылкам с определенным именем
add_filter( 'nav_menu_css_class', 'change_menu_item_css_classes_category', 10, 4 );

function filter_wp_menu_args_category( $args ) {
	if ( $args['theme_location'] === 'category' ) {
		$args['container']  = false;
		$args['items_wrap'] = '<ul id="%2$s">%3$s</ul>';
		// $args['menu_class'] = 'horizontal-multilevel-menu';
	}
	return $args;
}

function filter_menu_item_css_id_category( $menu_id, $item, $args, $depth ) {
	return $args->theme_location === 'category' ? '' : $menu_id;
}

function filter_nav_menu_css_classes_category( $classes, $item, $args, $depth ) {
	if ( $args->theme_location === 'category' && $depth == 0) {
		$classes = ['bx_hma_one_lvl dropdown visual_hover'];
		// if ( $item->current ) {
		// 	$classes[] = 'menu-node--active';
		// }
	}
	if ( $args->theme_location === 'category' && $depth == 1) {
		$classes = ['parent'];
		// if ( $item->current ) {
		// 	$classes[] = 'menu-node--active';
		// }
	}
	return $classes;
}

function filter_nav_menu_submenu_css_class_category( $classes, $args, $depth ) {
	if ( $args->theme_location === 'category' ) {
		$classes = [
			'bx_hma_one_lvl-ul'
		];
	}
	return $classes;
}

function filter_nav_menu_link_attributes_category( $atts, $item, $args, $depth ) {
	if ( $args->theme_location === 'category' ) {
		$atts['class'] = 'root-item';
		if ( $item->current ) {
			$atts['class'] .= ' root-item-selected';
		}
	}
	return $atts;
}

function change_menu_item_css_classes_category( $classes, $item, $args, $depth ) {
	if( 'Акції' === $item->title  && 'category' === $args->theme_location ){
		$classes[] = 'with-icon';
	}

	return $classes;
}

// Добавление пункта "Избранное" в меню
function add_favorites_icon_to_menu( $items, $args ) {
    if ( $args->theme_location == 'wishlist' ) { // Убедитесь, что используете правильное местоположение меню
        $favorites_url = home_url( '/favorites' ); // URL страницы "Избранное"
        $favorites_count = count_user_favorites(); // Функция для подсчета избранных товаров пользователя

        $items .= '<li class="menu-item woosw-menu-item menu-item-type-woosw">
            <a href="' . esc_url( $favorites_url ) . '">
                <span class="woosw-menu-item-inner" data-count="' . esc_html( $favorites_count ) . '"></span>
            </a>
        </li>';
    }
    return $items;
}
add_filter( 'wp_nav_menu_items', 'add_favorites_icon_to_menu', 10, 2 );

// Функция для подсчета избранных товаров пользователя
function count_user_favorites() {
    $user_id = get_current_user_id();
    $favorites = get_user_meta( $user_id, 'user_favorites', true );
    if ( !is_array( $favorites ) ) {
        $favorites = array();
    }
    return count( $favorites );
}