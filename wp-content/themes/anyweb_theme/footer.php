<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package anyweb
 */

$video_link = carbon_get_theme_option( 'video_link' );
$fb_link = carbon_get_theme_option( 'fb' );
$inst_link = carbon_get_theme_option( 'inst' );
?>


<footer class="footer">
    <div class="footer-before"></div>
    <div class="icon-fox"></div>
    <div class="icon-rabbit"></div>

    <div class="container">

        <div class="footer-content-wrap">

            <div class="footer-logo-block">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/image/LOGO_SCHOOL_footer.svg" alt="logo">
                </a>

                <div class="footer-social-block">
                    <?php

                    if ( ! empty( $fb_link ) ) :
                        echo '<a class="fb social_sidebar-icon" 
                                    target="_blank" 
                                    href="' . $fb_link . '" 
                                    rel="nofollow">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                          <path d="M13 13.5H15.5L16.5 9.5H13V7.5C13 6.47 13 5.5 15 5.5H16.5V2.14C16.174 2.097 14.943 2 13.643 2C10.928 2 9 3.657 9 6.7V9.5H6V13.5H9V22H13V13.5Z" fill="#ffffff"/>
                                        </svg>
                                    </span>
                                 </a>';
                    endif;
                    ?>

                    <?php

                    if ( ! empty( $inst_link ) ) :
                        echo '<a class="inst social_sidebar-icon"
                                 target="_blank"
                                 href="' . $inst_link . '"
                                 rel="nofollow">
                                 <span>
                                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                          <path d="M13.0276 2C14.1526 2.003 14.7236 2.009 15.2166 2.023L15.4106 2.03C15.6346 2.038 15.8556 2.048 16.1226 2.06C17.1866 2.11 17.9126 2.278 18.5496 2.525C19.2096 2.779 19.7656 3.123 20.3216 3.678C20.8303 4.17773 21.2238 4.78247 21.4746 5.45C21.7216 6.087 21.8896 6.813 21.9396 7.878C21.9516 8.144 21.9616 8.365 21.9696 8.59L21.9756 8.784C21.9906 9.276 21.9966 9.847 21.9986 10.972L21.9996 11.718V13.028C22.002 13.7574 21.9944 14.4868 21.9766 15.216L21.9706 15.41C21.9626 15.635 21.9526 15.856 21.9406 16.122C21.8906 17.187 21.7206 17.912 21.4746 18.55C21.2238 19.2175 20.8303 19.8223 20.3216 20.322C19.8219 20.8307 19.2171 21.2242 18.5496 21.475C17.9126 21.722 17.1866 21.89 16.1226 21.94L15.4106 21.97L15.2166 21.976C14.7236 21.99 14.1526 21.997 13.0276 21.999L12.2816 22H10.9726C10.2429 22.0026 9.51312 21.9949 8.78359 21.977L8.58959 21.971C8.3522 21.962 8.11487 21.9517 7.87759 21.94C6.81359 21.89 6.08759 21.722 5.44959 21.475C4.78242 21.2241 4.17804 20.8306 3.67859 20.322C3.16954 19.8224 2.7757 19.2176 2.52459 18.55C2.27759 17.913 2.10959 17.187 2.05959 16.122L2.02959 15.41L2.02459 15.216C2.00616 14.4868 1.99782 13.7574 1.99959 13.028V10.972C1.99682 10.2426 2.00416 9.5132 2.02159 8.784L2.02859 8.59C2.03659 8.365 2.04659 8.144 2.05859 7.878C2.10859 6.813 2.27659 6.088 2.52359 5.45C2.77529 4.7822 3.16982 4.17744 3.67959 3.678C4.17875 3.16955 4.78278 2.77607 5.44959 2.525C6.08759 2.278 6.81259 2.11 7.87759 2.06C8.14359 2.048 8.36559 2.038 8.58959 2.03L8.78359 2.024C9.51278 2.00623 10.2422 1.99857 10.9716 2.001L13.0276 2ZM11.9996 7C10.6735 7 9.40174 7.52678 8.46406 8.46447C7.52638 9.40215 6.99959 10.6739 6.99959 12C6.99959 13.3261 7.52638 14.5979 8.46406 15.5355C9.40174 16.4732 10.6735 17 11.9996 17C13.3257 17 14.5974 16.4732 15.5351 15.5355C16.4728 14.5979 16.9996 13.3261 16.9996 12C16.9996 10.6739 16.4728 9.40215 15.5351 8.46447C14.5974 7.52678 13.3257 7 11.9996 7ZM11.9996 9C12.3936 8.99993 12.7837 9.07747 13.1477 9.22817C13.5117 9.37887 13.8424 9.5998 14.1211 9.87833C14.3997 10.1569 14.6207 10.4875 14.7715 10.8515C14.9224 11.2154 15 11.6055 15.0001 11.9995C15.0002 12.3935 14.9226 12.7836 14.7719 13.1476C14.6212 13.5116 14.4003 13.8423 14.1218 14.121C13.8432 14.3996 13.5126 14.6206 13.1486 14.7714C12.7847 14.9223 12.3946 14.9999 12.0006 15C11.2049 15 10.4419 14.6839 9.87927 14.1213C9.31666 13.5587 9.00059 12.7956 9.00059 12C9.00059 11.2044 9.31666 10.4413 9.87927 9.87868C10.4419 9.31607 11.2049 9 12.0006 9M17.2506 5.5C16.9191 5.5 16.6011 5.6317 16.3667 5.86612C16.1323 6.10054 16.0006 6.41848 16.0006 6.75C16.0006 7.08152 16.1323 7.39946 16.3667 7.63388C16.6011 7.8683 16.9191 8 17.2506 8C17.5821 8 17.9001 7.8683 18.1345 7.63388C18.3689 7.39946 18.5006 7.08152 18.5006 6.75C18.5006 6.41848 18.3689 6.10054 18.1345 5.86612C17.9001 5.6317 17.5821 5.5 17.2506 5.5Z" fill="#ffffff"/>
                                     </svg>
                                 </span>
                              </a>';
                    endif;
                    ?>

                    <?php

                    if ( ! empty( $video_link ) ):
                        echo '<a class="ytb social_sidebar-icon"
                                 target="_blank"
                                 href="'. $video_link . '"
                                 rel="nofollow">   
                                <span>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 4C12.855 4 13.732 4.022 14.582 4.058L15.586 4.106L16.547 4.163L17.447 4.224L18.269 4.288C19.1612 4.35589 20.0008 4.73643 20.64 5.3626C21.2791 5.98877 21.6768 6.8204 21.763 7.711L21.803 8.136L21.878 9.046C21.948 9.989 22 11.017 22 12C22 12.983 21.948 14.011 21.878 14.954L21.803 15.864L21.763 16.289C21.6768 17.1798 21.2789 18.0115 20.6396 18.6377C20.0002 19.2639 19.1604 19.6443 18.268 19.712L17.448 19.775L16.548 19.837L15.586 19.894L14.582 19.942C13.7218 19.9793 12.861 19.9986 12 20C11.139 19.9986 10.2782 19.9793 9.418 19.942L8.414 19.894L7.453 19.837L6.553 19.775L5.731 19.712C4.83881 19.6441 3.9992 19.2636 3.36004 18.6374C2.72089 18.0112 2.32319 17.1796 2.237 16.289L2.197 15.864L2.122 14.954C2.04583 13.9711 2.00514 12.9858 2 12C2 11.017 2.052 9.989 2.122 9.046L2.197 8.136L2.237 7.711C2.32316 6.82055 2.72071 5.98905 3.35966 5.36291C3.99861 4.73676 4.83799 4.35612 5.73 4.288L6.551 4.224L7.451 4.163L8.413 4.106L9.417 4.058C10.2775 4.02073 11.1387 4.00139 12 4ZM10 9.575V14.425C10 14.887 10.5 15.175 10.9 14.945L15.1 12.52C15.1914 12.4674 15.2673 12.3916 15.3201 12.3003C15.3729 12.209 15.4007 12.1055 15.4007 12C15.4007 11.8945 15.3729 11.791 15.3201 11.6997C15.2673 11.6084 15.1914 11.5326 15.1 11.48L10.9 9.056C10.8088 9.00332 10.7053 8.9756 10.5999 8.97562C10.4945 8.97563 10.3911 9.00339 10.2998 9.0561C10.2086 9.1088 10.1329 9.1846 10.0802 9.27587C10.0276 9.36713 9.99993 9.47065 10 9.576V9.575Z" fill="white"/>
                                    </svg>
                                </span>
                           </a>';
                    endif;
                    ?>
                </div> <!-- end .footer-social-block -->

            </div> <!-- end .footer-logo-block -->

            <div class="footer-menu-block">

                <div class="footer-column">
                    <h3>Каталог товарів  <span></span></h3>
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'category-footer',
                            'menu_id'        => 'category-footer',
                            'menu_class'      => 'footer-list-menu',
                            'container'       => 'nav',
                            'container_class' => 'footer-menu-container',
                            'echo'            => true,

                        )
                    );
                    ?>
                </div> <!-- end .footer-column-->

                <div class="footer-column">
                    <h3>Давайте знайомитись <span></span></h3>

                    <?php
                    wp_nav_menu(
                            array(
                                'theme_location' => 'about-footer',
                                'menu_id'        => 'about-footer',
                                'menu_class'      => 'footer-list-menu',
                                'container'       => 'nav',
                                'container_class' => 'footer-menu-container',
                                'echo'            => true,

                        )
                    );

                    ?>
                </div> <!-- end .footer-column-->

                <div class="footer-column">

                    <h3>Зв’язатися з нами <span></span></h3>

                    <div class="sub-column">
                        <?php
                        $footer_contacts = carbon_get_theme_option( 'footer_contacts' );
                        if ( ! empty( $footer_contacts ) ):
                            echo  wpautop( $footer_contacts);
                        endif;
                        ?>
                    </div> <!-- end .sub-column-->

                </div> <!-- end .footer-column-->

            </div> <!-- end .footer-menu-block-->

        </div> <!-- end .footer-content-wrap-->

        <div class="footer-content-wrap copyright">
            <div class="footer-info-block">
                <div class="site-footer__copy">
                    &copy; <?php bloginfo('name'); ?>,  <?php echo esc_html(date('Y')); ?>
                    <span class="opacity-8">Дизайн Наталя Переходенко</span>
                </div> <!-- end .site-footer__copy-->
            </div> <!-- end .footer-info-block-->

            <div class="footer-column-wrap">
                <div class="footer-column">
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'footer__copy_col-1',
                            'menu_class'      => 'footer-list-menu',
                            'container'       => 'nav',
                            'container_class' => 'footer-menu-container',
                            'echo'            => true,

                        )
                    );

                    ?>
                </div> <!-- end .footer-column-->

                <div class="footer-column">
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'footer__copy_col-2',
                            'menu_class'      => 'footer-list-menu',
                            'container'       => 'nav',
                            'container_class' => 'footer-menu-container',
                            'echo'            => true,

                        )
                    );

                    ?>
                </div> <!-- end .footer-column-->

                <div class="footer-column">
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'footer__copy_col-3',
                            'menu_class'      => 'footer-list-menu',
                            'container'       => 'nav',
                            'container_class' => 'footer-menu-container',
                            'echo'            => true,

                        )
                    );
                    ?>
                </div> <!-- end .footer-column-->

            </div> <!-- end .footer-column-wrap-->
        </div> <!-- end .footer-content-wrap copyright-->
        <div class="footer-walk" style="margin-top: 15px;">
            <div class="paw"></div>
            <div class="hidden-img">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/12.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/11.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/10.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/9.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/8.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/7.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/6.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/5.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/4.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/3.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/2.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/1.png' ); ?>" alt="walk">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/image/walk/0.png' ); ?>" alt="walk">
            </div> <!-- end .hidden-img -->
        </div> <!-- end .footer-walk -->
    </div> <!-- end .container -->



	<span id="bcktotop" class="btn-up"></span>
</footer>

<div class="modal fade" id="modal-main" role="dialog">
    <div class="modal-dialog"></div>
</div> <!-- end .modal -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
