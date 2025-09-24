<?php
/*
Template Name: registration
*/

get_header();

?>
<main id="primary" class="site-main">

<div class="container bx-content-seection">

<div class="row">

    <div class="col-lg-12" id="navigation">
        <div class="bx-breadcrumb" itemprop="http://schema.org/breadcrumb" itemscope="" itemtype="http://schema.org/BreadcrumbList">

            <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(''); ?>

    </div>
</div>                 
</div>


<div class="row">
    
        <div class="bx-content">

<div class="reg_autorize">
<?php

echo do_shortcode( '[woocommerce_my_account]');

?>
			</div>
	</div>
</div>
</div>
	</main><!-- #main -->
    ';






<?php

get_sidebar();
get_footer();
