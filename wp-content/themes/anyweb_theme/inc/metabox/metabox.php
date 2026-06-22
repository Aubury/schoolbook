<?php
if (!defined("ABSPATH")) {
  exit;
}

use Carbon_Fields\Container;
use Carbon_Fields\Field;





Container::make( 'theme_options', __( 'SchoolBook settings' ) )
    ->add_fields( array(
        Field::make( 'text', 'video_link', __( ' Відео-новини' ) )->set_width(50 ),

        Field::make( 'text', 'price_link', __( ' Ссылка на прайс' ) )->set_width(50 ),
        Field::make( 'text', 'phone1', __( ' Телефон1' ) )->set_width( 50 ),
        Field::make( 'text', 'phone2', __( ' Телефон2' ) )->set_width( 50 ),

        Field::make( 'text', 'fb', __( ' facebook' ))->set_width( 50 ),
        Field::make( 'text', 'inst', __( ' instagramm' ))->set_width( 50 ),

        Field::make( 'rich_text', 'footer_contacts', __( ' Контакти Футер' ) )->set_width( 100 ),
    ) );


Container::make("post_meta", "Раздел Блог")
     
    ->show_on_template("templates/main-page.php")

    
    ->add_fields( array(
        
        Field::make( 'checkbox', 'visible_blog', 'Отображать блог?' ),
    ) );
Container::make("post_meta", "Слайдер")
     
    ->show_on_template("templates/main-page.php")

    
    ->add_fields( array(
        
        Field::make( 'complex', 'first_slider', 'максимум 5шт' )
          ->set_layout( 'tabbed-horizontal' )
          ->set_max( 10 )
          ->set_width( 80 )
        ->add_fields( array(
        Field::make( 'image', 'img', __(' изображение' ))->set_width( 10 ),
        Field::make( 'text', 'link', __( ' Ссылка блока' ))->set_width( 10 ),
      
        )),
    ) );


    Container::make("post_meta", "Наші рекомендації")
     
    ->show_on_template("templates/main-page.php")
    ->add_fields( array(
        Field::make( 'complex', 'rcmnd_slider', 'максимум 20шт' )->set_layout( 'tabbed-horizontal' )->set_max( 20 )->set_width( 80 )
        ->add_fields( array(
            Field::make( 'text', 'rcmnd_product_id', __( ' ID товара' )),
      
        )),
    ) );


    Container::make("post_meta", "Текст внизу страницы")
     
    ->show_on_template("templates/main-page.php")
    ->add_fields( array(
        Field::make( 'text', 'btm_title', __( ' Заголовок над текстом' )),
        Field::make( 'rich_text', 'btm_txt', __( ' Текст который видно всегда' )),
        Field::make( 'rich_text', 'btm_txt_hide', __( ' Текст спрятаный под словом "Детальніше"' )),

    ) );




    Container::make("post_meta", "Заголовок cторінки")
     
    ->show_on_template("templates/two-column.php")
    ->add_fields( array(
        Field::make( 'rich_text', 'h1_title', __( ' Заголовок cторінки' )),

    ) );




    Container::make("post_meta", "Заголовок cторінки")
        ->where( 'post_type', '=', 'page' )
        ->where( 'post_template', '=', 'templates/sales-agent.php' )
    ->add_fields( array(
        Field::make( 'checkbox', 'visible_map', 'Отображать карту?' )->set_option_value( 'yes' ),
        Field::make( 'complex', 'cities', 'Торгівельні представники' )
            ->set_layout( 'tabbed-horizontal' )
           ->set_max( 50 )
            ->set_width( 80 )
        ->add_fields( array(
            Field::make( 'text', 'city', __( ' Місто представника' )),
            Field::make( 'text', 'uf_gmaps_s', __( ' Південні координати:' )),
            Field::make( 'text', 'uf_gmaps_n', __( ' Північні координати:' )),
                Field::make( 'complex', 'parthners', 'Деталі представника'
                )->set_layout( 'tabbed-horizontal' )
                    ->set_max( 40 )
                    ->set_width( 80 )
                 ->add_fields( array(
                Field::make( 'text', 'latitude', __( ' Широта' ))->set_width( 20 ),
                Field::make( 'text', 'longitude', __( ' Довгота' ))->set_width( 20 ),
                Field::make( 'text', 'working_hours', __( ' Робочий час' ))->set_width( 20 ),
                Field::make( 'text', 'address', __( ' Адреса' ))->set_width( 20 ),
                Field::make( 'text', 'name', __( ' Назва' ))->set_width( 20 ),
                Field::make( 'textarea', 'phone', __( ' Телефон' ))->set_width( 100 ),
                Field::make( 'text', 'sate', __( ' Сайт' ))->set_width( 20 ),
                Field::make( 'text', 'sate_text', __( ' Текст сайту' ))->set_width( 20 ),
        ))
            
        ))
    ) );


Container::make("post_meta", "Заголовок cторінки")
    ->where( 'post_type', '=', 'page' )
    ->where( 'post_template', '=', 'templates/new-sales-agent.php' )
    ->add_fields( array(
        Field::make( 'checkbox', 'visible_map', 'Отображать карту?' )->set_option_value( 'yes' ),
        Field::make( 'complex', 'sales_agent_cities', 'Торгівельні представники' )
            ->set_layout( 'tabbed-horizontal' )
            ->set_max( 50 )
            ->set_width( 80 )
            ->add_fields( array(
                Field::make( 'text', 'sales_agent_city', __( ' Місто представника' )),
                Field::make( 'text', 'uf_gmaps_s', __( ' Південні координати:' )),
                Field::make( 'text', 'uf_gmaps_n', __( ' Північні координати:' )),
                Field::make( 'complex', 'sales_agent_partners', 'Деталі представника'
                )->set_layout( 'tabbed-horizontal' )
                    ->set_max( 40 )
                    ->set_width( 80 )
                    ->add_fields( array(
                        Field::make( 'text', 'latitude', __( ' Широта' ))->set_width( 20 ),
                        Field::make( 'text', 'longitude', __( ' Довгота' ))->set_width( 20 ),
                        Field::make( 'text', 'working_hours', __( ' Робочий час' ))->set_width( 20 ),
                        Field::make( 'text', 'address', __( ' Адреса' ))->set_width( 20 ),
                        Field::make( 'text', 'name', __( ' Назва' ))->set_width( 20 ),
                        Field::make( 'textarea', 'phone', __( ' Телефон' ))->set_width( 100 ),
                        Field::make( 'text', 'sate', __( ' Сайт' ))->set_width( 20 ),
                        Field::make( 'text', 'sate_text', __( ' Текст сайту' ))->set_width( 20 ),
                    ))

            ))
    ) );





    Container::make( 'term_meta', 'Настройки Term' )
    ->show_on_taxonomy( 'pa_avtori' ) // По умолчанию, можно не писать
    ->add_fields( array(
        Field::make( 'image', 'author-thumb', 'Фото автора' ),
        Field::make( 'textarea', 'short-description', 'Короткий опис' ),
        Field::make( 'text', 'leter', 'Літера для пошуку' ),
    ) );




















//     Container::make("post_meta", "1. Первый текстовый блок")     
//     ->show_on_template("templates/front-page.php") 
//     ->add_fields( array(
//         Field::make( 'rich_text', 'first_text', __( ' Первый текстовый блок' ))->set_width( 40 ),       
//         Field::make( 'rich_text', 'second_text', __( ' Заголовок галереи' ))->set_width( 40 ),        
//         Field::make( 'text', 'shortcode_gallery', __( ' шорткод галерея' ))->set_width( 20 ),        
//     ) );

//     Container::make("post_meta", "2. Наша деятельность в цифрах")     
//     ->show_on_template("templates/front-page.php") 
//     ->add_fields( array(
//         Field::make( 'textarea', 'statictic_title', __( ' Заголовок' ))->set_width( 20 ),
//         Field::make( 'complex', 'statistic_bloks', 'Блок "плитка"  максимум 20шт' )->set_layout( 'tabbed-horizontal' )->set_max( 20 )->set_width( 80 )
//         ->add_fields( array(
//             Field::make( 'image', 'icon', __( ' Иконка' ))->set_width( 20 ),
//             Field::make( 'text', 'alt', __( ' "Alt" иконки' ))->set_width( 20 ),
//             Field::make( 'rich_text', 'statistic_text', __( ' Текст' ))->set_width( 60 )
//         ))
//     ) );
//     Container::make("post_meta", "3. Как помочь?")     
//     ->show_on_template("templates/front-page.php") 
//     ->add_fields( array(
//         Field::make( 'textarea', 'how_to_help_title', __( ' Заголовок' ))->set_width( 20 ),
//         Field::make( 'complex', 'how_to_help_bloks', 'Блок "плитка"  максимум 20шт' )->set_layout( 'tabbed-horizontal' )->set_max( 20 )->set_width( 80 )
//         ->add_fields( array(
//             Field::make( 'rich_text', 'description', __( ' Текст' ))->set_width( 60 )
//         ))
//     ) );
//     Container::make("post_meta", "4. Видео галерея")     
//     ->show_on_template("templates/front-page.php") 
//     ->add_fields( array(
//         Field::make( 'textarea', 'video_title', __( ' Заголовок' ))->set_width( 20 ),
//         Field::make( 'complex', 'video_bloks', 'Блок "плитка"  максимум 20шт' )->set_layout( 'tabbed-horizontal' )->set_max( 20 )->set_width( 80 )
//         ->add_fields( array(
//             Field::make( 'textarea', 'description', __( ' Youtube embed code' ))->set_width( 60 )
//         ))
//     ) );

//     Container::make("post_meta", "5.текст после Как помочь?")     
//     ->show_on_template("templates/front-page.php") 
//     ->add_fields( array(
//         Field::make( 'rich_text', 'after_how_to_help_description', __( ' Заголовок фото галереи' ))
//     ) );

//     Container::make("post_meta", "6. О фонде")     
//     ->show_on_template("templates/front-page.php") 
//     ->add_fields( array(
//         Field::make( 'textarea', 'found_info_title', __( ' Заголовок О фонде' ))->set_width( 20 ),
//         Field::make( 'rich_text', 'found_information', __( ' Текст О фонде' ))->set_width( 80 ),
//     ) );


// // -----------------------------------------------------------------
//     // help page
// // -----------------------------------------------------------------


//     Container::make("post_meta", "one_container", "1. Описание страницы")     
//     ->show_on_template("templates/help-template.php") 
//     ->add_fields( array(
//         Field::make( 'rich_text', 'help_page_information', __( ' Текст' ))->set_width( 100 ),
//     ) );

//     Container::make("post_meta", "two_container",  "2. Текст после  описания (голубой фон)")     
//     ->show_on_template("templates/help-template.php") 
//     ->add_fields( array(
//         Field::make( 'rich_text', 'after_help_page_information', __( ' Текст' ))
//     ) );

//     Container::make("post_meta", "three_container", "3. Наша деятельность в цифрах")     
//     ->show_on_template("templates/help-template.php") 
//     ->add_fields( array(
//         Field::make( 'textarea', 'htstatictic_title', __( ' Заголовок' ))->set_width( 20 ),
//         Field::make( 'complex', 'htstatistic_bloks', 'Блок "плитка"  максимум 20шт' )->set_layout( 'tabbed-horizontal' )->set_max( 20 )->set_width( 80 )
//         ->add_fields( array(
//             Field::make( 'image', 'icon', __( ' Иконка' ))->set_width( 20 ),
//             Field::make( 'text', 'alt', __( ' "Alt" иконки' ))->set_width( 20 ),
//             Field::make( 'rich_text', 'statistic_text', __( ' Текст' ))->set_width( 60 )
//         ))
//     ) );

//     Container::make("post_meta", "four_container", "4. Чем можно помочь приютам?")     
//     ->show_on_template("templates/help-template.php") 
//     ->add_fields( array(
//         Field::make( 'rich_text', 'how_information', __( ' Текст' ))->set_width( 100 ),
//         Field::make( 'complex', 'how__bloks', 'список максимум 20шт' )->set_layout( 'tabbed-vertical' )->set_max( 20 )
//         ->add_fields( array(
//             Field::make( 'image', 'icon', __( ' Иконка' ))->set_width( 20 ),
//             Field::make( 'text', 'alt', __( ' "Alt" иконки' ))->set_width( 20 ),
//             Field::make( 'rich_text', 'statistic_text', __( ' Текст' ))->set_width( 60 ),
       
//     )), 
//      Field::make( 'rich_text', 'after_list_information', __( 'Текст после списка' )),

//     ) );
//     Container::make("post_meta", "five_container",  "5. Текст (голубой фон)")     
//     ->show_on_template("templates/help-template.php") 
//     ->add_fields( array(
//         Field::make( 'rich_text', 'last_information', __( ' Текст' ))
//     ) );

// // ----------------------------------------
// // about page
// // ----------------------------------------

// Container::make("post_meta", "five_container",  "Текст ")     
// ->show_on_template("templates/about.php") 
// ->add_fields( array(
//     Field::make( 'rich_text', 'description_r', __( ' параграф с изображением справа' ))->set_width( 70 ),
//     Field::make( 'image', 'icon_r', __( ' Иконка' ))->set_width( 15 ),
//     Field::make( 'text', 'alt_r', __( ' "Alt" иконки' ))->set_width( 15 ),

// ) );


// Container::make("post_meta", "five_container",  "контактные данные ")     
// ->show_on_template("templates/contact.php") 
// ->add_fields( array(
//     Field::make( 'rich_text', 'contacts_list', __( ' параграф' ))->set_width(50 ),
//     Field::make( 'textarea', 'contacts_map', __( ' iframe google maps' ))->set_width(50 ),
//     Field::make( 'text', 'contact_form', __( ' contact form 7 shortcode' ))->set_width(100 ),

// ) );