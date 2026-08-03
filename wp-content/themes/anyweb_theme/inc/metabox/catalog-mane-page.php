<?php
if (!defined("ABSPATH")) {
    exit;
}

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make("post_meta", "Ваші улюблені категорії")
    ->where( 'post_type', '=', 'page' )
    ->where( 'post_template', '=', 'templates/main-page.php' )
    ->add_fields( array(
        Field::make( 'rich_text', 'crb_your_favorite_categories_title', __( ' Заголовок' ) )
            ->set_width(100 )
            ->set_default_value('Ваші улюблені категорії'),

        Field::make( 'complex', 'crb_your_favorite_categories', 'Улюблені категорії' )->set_width(100)
            ->add_fields( array(
                Field::make( 'image', 'crb_image_first', __( 'Зображення' ))->set_width(10),
                Field::make( 'text', 'crb_title', __( 'Назва категорії' ))->set_width(100),
                Field::make( 'text', 'crb_age', __( 'Вік' ))->set_width(100),
                Field::make( 'text', 'crb_link', __( 'Посилання на сторінку' ))->set_width(100),
            )),
        )
    );
