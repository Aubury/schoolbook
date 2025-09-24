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

?>


<footer class="bx-footer">
	<div class="desktop-show">
		<div class="bx-footer-section container bx-center-section">
			<div class="icon-fox">
				<div id="fox-eye-left" class="fox-eye">
					<div class="fox-eyeball" style="margin-top: -1.99111px; margin-left: -0.230774px;"></div>
				</div>
				<div id="fox-eye-right" class="fox-eye">
					<div class="fox-eyeball" style="margin-top: -1.99111px; margin-left: -0.230774px;"></div>
				</div>
			</div>
			<div class="footer-walk" style="margin-top: 10px;">
				<div class="paw"></div>
				<div class="hidden-img">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/12.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/11.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/10.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/9.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/8.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/7.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/6.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/5.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/4.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/3.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/2.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/1.png" alt="walk">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/image/walk/0.png" alt="walk">
				</div>
			</div>
			<i class="icon-rabbit"></i>
			<div class="col-sub-menu">


				<p class="bx-block-title">Каталог товарів</p>
				<?php
				$bx_inclinksfooter_container = wp_nav_menu(
					array(
						'theme_location' => 'category-footer',
						'menu_id'        => 'category-footer',
						'menu_class'      => 'bx-inclinksfooter-list',
						'container'       => 'nav',
						'container_class' => 'bx-inclinksfooter-container',
						'echo'            => false,

					)
				);
				echo $bx_inclinksfooter_container;
				?>

			</div>
			<div class="col-sub-menu">

				<p class="bx-block-title">Давайте знайомитись</p>
				<?php
				$bx_inclinksfooter_container = wp_nav_menu(
					array(
						'theme_location' => 'about-footer',
						'menu_id'        => 'about-footer',
						'menu_class'      => 'bx-inclinksfooter-list',
						'container'       => 'nav',
						'container_class' => 'bx-inclinksfooter-container',
						'echo'            => false,

					)
				);
				echo $bx_inclinksfooter_container;
				?>

			</div>
			<div class="col-menu" style="position: relative;">



				<?php
				$bx_inclinksfooter_container = wp_nav_menu(
					array(
						'theme_location' => 'primary-footer',
						'menu_id'        => 'primary-footer',
						'menu_class'      => 'footer-menu',
						'container'       => false,
						'echo'            => false,

					)
				);
				echo $bx_inclinksfooter_container;
				?>

			</div>
			<div class="contacts-col">
				<p class="bx-block-title">Зв’язатися з нами</p>
				<div class="phone-box">
					<?php
					$footer_contacts = carbon_get_theme_option( 'footer_contacts' );
					if ( ! empty( $footer_contacts ) ):
						echo  wpautop( $footer_contacts);
					endif;
					?>
				</div>


				<div class="socials">
					<div class="bx-socialsidebar">
						<div class="bx-socialsidebar-group">
							<ul>
								<li><a class="fb bx-socialsidebar-icon" target="_blank" href="

										<?php
									$fb = carbon_get_theme_option( 'fb' );
									if ( ! empty( $fb ) ):
										echo  $fb;
									endif;
									?>

										" rel="nofollow"></a></li>
								<li><a class="in bx-socialsidebar-icon" target="_blank" href="
										<?php
									$inst = carbon_get_theme_option( 'inst' );
									if ( ! empty( $inst ) ):
										echo  $inst;
									endif;
									?>"
									   rel="nofollow"></a>
								</li>
							</ul>
						</div>
					</div>
				</div>


			</div>

		</div>
		<div class="bx-footer-bottomline">
			<div class="bx-footer-section container">
				<div class="col-sm-6">
					<span class="copy">© 2002-2023 schoolbook.com.ua Усі права захищені</span>
				</div>
				<div class="col-sm-6 bx-up">
					<span class="developing"></span>
				</div>
			</div>
		</div>
	</div>
	
	<span id="bcktotop" class="btn-up"></span>

	<div class="mob-show">
		<div class="bx-footer-section container ">
			<div class="pt-3 pb-3 w-100 bx-footer-logo">
				<?php
				echo get_custom_logo();
				?>
			</div>

			<div class="socials">
				<div class="bx-socialsidebar">
					<div class="bx-socialsidebar-group">
						<ul>
							<li><a class="fb bx-socialsidebar-icon" target="_blank" href="

										<?php
								$fb = carbon_get_theme_option( 'fb' );
								if ( ! empty( $fb ) ):
									echo  $fb;
								endif;
								?>

										" rel="nofollow"></a></li>
							<li><a class="in bx-socialsidebar-icon" target="_blank" href="
										<?php
								$inst = carbon_get_theme_option( 'inst' );
								if ( ! empty( $inst ) ):
									echo  $inst;
								endif;
								?>"
								   rel="nofollow"></a>
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="contacts-col">
				<p class="bx-block-title">Зв’язатися з нами</p>
				<div class="phone-box  d-flex flex-sm-row flex-column justify-content-evenly">
					<?php
					$footer_contacts = carbon_get_theme_option( 'footer_contacts' );
					if ( ! empty( $footer_contacts ) ):
						echo  wpautop( $footer_contacts);
					endif;
					?>
				</div>
			</div>

			<div class="w-100">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'mob-footer',
						'menu_id'        => 'mob-footer',
						'menu_class'      => 'footer-menu',
						'container'       => false,
					)
				);
				?>
			</div>


		</div>
		<div class="bx-footer-bottomline">
			<div class="bx-footer-section container">
				<div class="col-sm-6">
					<span class="copy">© 2002-2023 schoolbook.com.ua Усі права захищені</span>
				</div>
				<div class="col-sm-6 bx-up">
					<span class="developing"></span>
				</div>
			</div>
		</div>
	</div>
</footer>
</div>
<div class="modal fade" id="modal-main" role="dialog" >
<div class="modal-dialog"></div></div>



</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
