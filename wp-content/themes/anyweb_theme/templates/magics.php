<?php
/*
Template Name: magics
*/

get_header();
?>

<main id="primary" class="site-main magic">

<div class="container bx-content-seection">

<div class="row">

    <div class="col-lg-12" id="navigation">
        <div class="bx-breadcrumb" itemprop="http://schema.org/breadcrumb" itemscope="" itemtype="http://schema.org/BreadcrumbList">

            <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(''); ?>

    </div>
</div>                 
</div>


<div class="row">
   <div class="bx-content ">
      <div class="content-page">
         <div class="sidebar">
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

         </div>
         <div class="content content-aboutus">
         <h1 class="h2-title">
             <?php $h1_title = carbon_get_post_meta( $post->ID, 'h1_title' );
                if ( ! empty( $h1_title ) ):
                echo  wpautop($h1_title) ;
                endif;
            ?>
         </h1>

                <?php
            while ( have_posts() ) :
                the_post();

if($post->post_name == 'about' || $post->post_name == 'contacts' || $post->post_name == 'magics'){
    wp_nav_menu(
        array(
            'theme_location' => 'top_two_column_menu',
            'menu_id'        => 'top_two_column_menu',
            'echo'           => true,
            'container'      => 'nav',
            'container_class' => 'nav-menu sub-menu__about'

        )
    );
}
if($post->post_content != ""){
echo '<div class="container-fluid content-about">';
                get_template_part( 'template-parts/content', 'page' );


                echo '</div>';
 }               
	    	endwhile; 	



$tax = 'pa_avtori';
$pa_args = get_terms( $tax, array(
      'hide_empty' => false,
    )
 );

 

$authors = array();


$letter='';

foreach ($pa_args as $key => $author) {
    
 if (empty($author->term_id)){
    return;
 }
    $letter = carbon_get_term_meta( $author->term_id, 'leter' );

    $delimiters = ['.', '!', '?', '/', '-', '+', '&', '\\', ',', ' '];

    $newStr = str_replace($delimiters, '', $letter); // 'foo. bar. baz.'

    $upperCaseStr = mb_strtoupper($newStr, 'UTF-8');

    $arr = mb_str_split($upperCaseStr);

    foreach ($arr as $key => $letter_symbol) {

        if (!array_key_exists($letter_symbol, $authors)) {
            $authors[$letter_symbol] = array();
            array_push($authors[$letter_symbol], $author);
        } else {
            array_push($authors[$letter_symbol], $author);
        }

    }

}

$order = "АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЬЮЯЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ";

uksort($authors, function($a, $b) use ($order) {
    $posA = mb_strpos($order, $a);
    $posB = mb_strpos($order, $b);

    return $posA - $posB;
});

?>


<div class="container-fluid authors-list">
			<div class="filter-alphabet">
				<ul>
                  <?php 
                    $first_leter = true;
					foreach( $authors as $leter => $author ): 
							 if($first_leter ){ 
								 echo '<li data-l="' . $leter . '" class="active">' . $leter . '</li>';
                                $first_leter = false;
                             }else{                                     
								echo '<li data-l="' . $leter . '">' . $leter . '</li>';
                            }
						
					 endforeach; ?>				
				</ul>				
			</div>







			<div class="filter-authors">
				<ul>
					<?php 
                    $first_leter = true;
                    foreach($authors as $leter => $authors):
						foreach ($authors as $author):
							 if( $first_leter ): ?>
								<li data-m="<?php echo $leter?>" class="active">
							<?php else: ?>
								<li data-m="<?php echo $leter?>">
							<?php endif; ?>
								<div class="item-author">
									<a href="<?php echo home_url() ?>/avtori/<?php echo $author->slug?>/">
									<div class="img">
									<?php	
                                            $author_img = carbon_get_term_meta( $author->term_id, 'author-thumb' );
                                            if(!empty($author_img)):
                                            echo wp_get_attachment_image($author_img, 'post-thumbnail', 'true', array( ));
                                            endif;
                                    ?>
									</div>
									</a>
									<div class="author-name"><?php echo $author->name ?></div>
									<div class="link-more_detail"><a href="<?php echo home_url()?>/avtori/<?php echo $author->slug ?>/"><?php echo __('Детальніше');?></a></div>
								</div>					
							</li>
						<?php endforeach; ?>
					<?php endforeach; ?>				
				</ul>					
			</div>










</div>
</div>
            
            </div>
         </div>
      </div>
   </div>
</div>
	</main><!-- #main -->
<?php
get_sidebar();
get_footer();
