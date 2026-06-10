<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package anyweb
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="designer" content="Designed with Soul and Care by Any-web Team | https://any-web.net/">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'anyweb' ); ?></a>

    <header class="bx-header">
		<div class="bx-header-section">
			<div class="header-top">
				<div class="container">
					<div class="row">
						<div class="burger-menu">
							<span></span>
							<span></span>
							<span></span>
						</div>
						<div class="header-top-l">
							<div class="socials">
								<div class="price_list">
									<?php
										$price_link = carbon_get_theme_option( 'price_link' );
										if ( ! empty( $price_link ) ):
										echo '<a href="'. $price_link.'" class="price_list_link">Прайс-лист</a>';
										endif;
									?>
								</div>
							</div>
							<div class="phones-block">
								<div class="bx-inc-orginfo-phone">
									<div class="phones">
	<div class="phone">
	<?php
		 $phone1 = carbon_get_theme_option( 'phone1' );
		if ( ! empty( $phone1 ) ):
			preg_match_all('/\d/', $phone1, $arr);
		echo  '<a href="tel:'. implode("",$arr[0]).'">'.$phone1.'</a>';
		endif;
		?>
	</div>
	<div class="phone">
	<?php
		 $phone2 = carbon_get_theme_option( 'phone2' );
		if ( ! empty( $phone2 ) ):
			preg_match_all('/\d/', $phone2, $arr);
		echo  '<a href="tel:'. implode("",$arr[0]).'">'.$phone2.'</a>';
		endif;
		?>
	</div>
</div>
<!-- <br> -->
								</div>
							</div>
						</div>
						<div class="bx-logo" itemscope="" itemtype="https://schema.org/Organization">
							<meta itemprop="name" content="Видавничий дім Школа">
							<div itemprop="address" itemscope="" itemtype="https://schema.org/PostalAddress" style="display:none;">
								<meta itemprop="addressLocality" content="Україна, Харків">
								<meta itemprop="postalCode" content="61103">
								<meta itemprop="streetAddress" content="А/С №535">
							</div>
							<meta itemprop="telephone" content="+380 (67)-766-00-77">
							<meta itemprop="email" content="sales@schoolbook.com.ua">
							<link itemprop="sameAs" href="https://www.facebook.com/vidavnichiidimshkola/">
							<link itemprop="sameAs" href="https://www.instagram.com/vd_shkola/">
							<span itemprop="url" class="bx-logo-block hidden-xs" content="https://schoolbook.com.ua/"><?php echo get_custom_logo(); ?></span>
							<!-- <span class="bx-logo-block hidden-lg hidden-md d-sm-none text-center" content="https://schoolbook.com.ua/">
							<?php 
							// echo get_custom_logo();
							 ?>
								</span> -->
						</div>
						<div class="header-top-r">

							<div class="bx-basket-block search-mobile">
								<div id="search-mob" class="bx-searchtitle">
										<div class="bx-input-group">
											<span class="clear-search"></span>
											<?php aws_get_search_form( true, array( 'id' => 1 ) ); ?>
											<span class="bx-input-group-btn">
												<button class="btn btn-default" type="submit" name="s"><i class="fa fa-search"></i></button>
											</span>
										</div>
								</div>
							</div>

							<div id="video" class="bx-video mobile">
								<div class="bx-input-group">            
									<span class="bx-input-group-btn"><a href="http://youtube.com"><i class="fa-solid fa-camera-movie"></i></a></span>
								</div>
							</div>

							<div>
								<div id="bx_basketFKauiI" class="bx-basket bx-opener"><!--'start_frame_cache_bx_basketFKauiI'--><div class="bx-hdr-profile">

							<?php if(!is_user_logged_in()):
								 
echo 
	'<div class="bx-basket-block login-box">
		<div class="login-block">
			<i class="fa fa-user"></i>
			
				<a class="login-link" href="javascript:void(0)">Вхід та реєстація</a>
				<div class="login-dropdown">' .					

  				do_shortcode(  '[misha_custom_login]' ) .

					'<div class="registration-link">
						<span>аб</span>
						<a href="/registration">Зареєструйтеь</a>
					</div>				
				</div>
		</div>
	</div>';
	 endif; ?>

	<!-- <div class="bx-basket-block search-mobile">
			</div> -->
	<div class="bx-basket-block d-flex align-items-center justify-content-center relative">
		<div class="favorites-holder d-flex align-items-center justify-content-center relative">
			<i class="icon-favorites d-flex align-items-center justify-content-center relative">
			<?php
				$bx_hma_one_lvl = wp_nav_menu(
					array(
						'theme_location' => 'wishlist',
						'menu_id'        => 'wishlist',
						'echo'            => true,
						'container'      => false, // Отключаем обертку
        				'items_wrap'     => '<div class="d-flex align-items-center justify-content-center relative">%3$s</div>'

					)
				);
			?>
			</i>
			<!-- <span class="bubble">0</span> -->
		</div>
	</div>
	<?php
	if (! function_exists('WC')) {
		return;
	}
	?>
    	<div class="bx-basket-block">
		<div class="basket-holder">
		
							<a href="/cart" class="fa fa-shopping-cart">
					<span class="bubble"><?php echo WC()->cart->cart_contents_count?></span>
				</a>
						
			<div class="basket-link-wrap">
				<span class="cart-title">У кошику:</span>
					<?php echo sprintf(_n('%d товар', '%d товарів', WC()->cart->cart_contents_count, 'store'), WC()->cart->cart_contents_count); ?>
					<br>
				<span class="basket-sum">на cуму <strong><?php echo WC()->cart->get_cart_total(); ?></strong></span>
			</div>
		</div>
	</div>




								</div><!--'end_frame_cache_bx_basketFKauiI'--></div>
							</div>
						</div>
					</div>
				</div>
			</div>

		<div class="header-center">
			<div class="container">
				<div class="row menu-row">
					<div class="menu-section">
						<div class="scroll_menu">



										<div class="books-menu">
											<div class="books-button sites-s1">Каталог товарів <i class="icon-arrow"></i></div>
											<div class="bx_vertical_menu_advanced bx_blue" id="catalog_menu_LkGdQn">
											<?php
																	$bx_hma_one_lvl=wp_nav_menu(
																			array(
																				'theme_location' => 'category',
																				'menu_id'        => 'bx_hma_one_lvl',
																				'echo'            => false,
																				'link_before'     => '<span>',
															                    'link_after'      => '</span>',

																			)
																		);
																	// echo $bx_hma_one_lvl;

					$bbx_hma_one_lvl = preg_replace('/<\/li><\/ul>/', '<\/li></div></div><\/ul>', $bx_hma_one_lvl);
					// $bbbx_hma_one_lvl = preg_replace('/class=\"bx_hma_one_lvl dropdown/', 'onmouseover="BX.CatalogVertMenu.itemOver(this);" onmouseout="BX.CatalogVertMenu.itemOut(this)" class="bx_hma_one_lvl dropdown', $bbx_hma_one_lvl);

echo preg_replace('/<ul class="bx_hma_one_lvl-ul">/', '<div class="bx_children_container b2"><div class="bx_children_block"><ul>', $bbx_hma_one_lvl);



															?>

											</div>
										</div>
										<?php
																	$mobile = wp_nav_menu(
																			array(
																				'theme_location' => 'primary',
																				'menu_id'        => 'horizontal-multilevel-menu',
																				'echo'            => false,

																			)
																		);

																		 echo str_replace('Відео- ', 'Відео-', $mobile);

															?>


										<div class="menu-clear-left"></div>

										<div class="phones-block phones-block-mobile">
											<div class="bx-inc-orginfo-phone">
												<div class="phones">
														<div class="phone">
															<?php
															$phone1 = carbon_get_theme_option( 'phone1' );
															if ( ! empty( $phone1 ) ):
															echo  '<a href="tel:'.$phone1.'">'.$phone1.'</a>';
															endif;
															?>
														</div>
														<div class="phone">
														<?php
															$phone2 = carbon_get_theme_option( 'phone2' );
															if ( ! empty( $phone2 ) ):
															echo  '<a href="tel:'.$phone2.'">'.$phone2.'</a>';
															endif;
															?>
														</div>
												</div>
											</div>
										</div>

										<div class="price_list-mobile">
											<a target="_blank" href="https://mega.nz/#F!U4BHkS6L!-qzYndg9SbUxeDzz_i1XBQ" class="price_list_link">Прайс-лист</a>
										</div>

										<div class="socials-row">
											<div class="socials">
												<div class="bx-socialsidebar">
													<div class="bx-socialsidebar-group">
														<ul>
															<li><a class="fb bx-socialsidebar-icon" target="_blank" href="https://www.facebook.com/vidavnichiidimshkola/" rel="nofollow"></a></li>
															<li><a class="in bx-socialsidebar-icon" target="_blank" href="https://www.instagram.com/vd_shkola/" rel="nofollow"></a></li>
														</ul>
													</div>
												</div>
											</div>
										</div>
						</div>

					<div id="search" class="bx-searchtitle">
							<div class="bx-input-group">            
								<?php aws_get_search_form( true, array( 'id' => 1 ) ); ?>
								<span class="bx-input-group-btn"><button class="btn btn-default"  name="s"><i class="fa fa-search"></i></button></span>
							</div>
					</div>
					<!-- <div id="video" class="bx-video">
							<div class="bx-input-group">            
								<span class="bx-input-group-btn"><a href="http://youtube.com"><i class="fa-solid fa-camera-movie"></i></a></span>
							</div>
					</div> -->

					</div>



					<i class="ladybug"></i>
				</div>
			</div>
		</div>

		
	
				</div>
	</header>