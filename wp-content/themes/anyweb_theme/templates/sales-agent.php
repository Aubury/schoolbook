<?php
/*
Template Name: sales agent
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
echo '<div class="container-fluid content-about">';
                get_template_part( 'template-parts/content', 'page' );


	    	endwhile; 	
        ?>
        

<div class="content-page">

<div class="content">
    <h1 class="h2-title">Торг<span class="color-orange">о</span>вельні пр<span class="color-blue">е</span>дставники</h1>
    <div class="container-fluid">
        <div class="map-container">
            <?php

            $visible_map = carbon_get_post_meta( $post->ID, 'visible_map');
            $cities = carbon_get_post_meta( $post->ID, 'cities' );


// Украинский алфавит
$ukrainianAlphabetOrder = [
    'а', 'б', 'в', 'г', 'ґ', 'д', 'е', 'є', 'ж', 'з', 'и', 'і', 'ї', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ь', 'ю', 'я'
];

// Функция для сравнения двух элементов массива по полю "city"
function compareCities($a, $b) {
    $cityA = mb_strtolower($a['city'], 'UTF-8');
    $cityB = mb_strtolower($b['city'], 'UTF-8');

    global $ukrainianAlphabetOrder;

    $lenA = mb_strlen($cityA);
    $lenB = mb_strlen($cityB);

    for ($i = 0; $i < $lenA && $i < $lenB; ++$i) {
        $posA = array_search(mb_substr($cityA, $i, 1), $ukrainianAlphabetOrder);
        $posB = array_search(mb_substr($cityB, $i, 1), $ukrainianAlphabetOrder);

        if ($posA !== $posB) {
            return $posA - $posB;
        }
    }

    return $lenA - $lenB;
}

usort($cities, 'compareCities');




            if ( $visible_map ){
                echo '<div class="map multiple-map" id="map"></div>';
            }
            ?>
            <div class="sales-agent-container">
                <div class="sales-agent-header">
                    <span class="title">Торговельні представники </span>
                    <div class="input-holder">
                        <select id="city_select" class="pre_valid select-suggest" value="Київ" data-search-placeholder="Пошук...">
                    <?php 	


if ( ! empty( $cities ) ):
foreach ( $cities as $cnt => $item ): 
    if($item['parthners']){
if($item['city'] == 'Київ'):?>
    <option data-gmaps_s="<?php echo $item['uf_gmaps_n']?>" data-gmaps_n="<?php echo $item['uf_gmaps_s']?>" value="<?php echo $cnt ?>" selected><?php echo $item['city']?></option>	
<?php else:?>
    <option data-gmaps_s="<?php echo $item['uf_gmaps_n']?>" data-gmaps_n="<?php echo $item['uf_gmaps_s']?>" value="<?php echo $cnt ?>"><?php echo $item['city']?></option>	
<?php endif;

}

endforeach;
endif;

?>
                        </select>
                    </div>
                </div>
                <div class="sales-agent-content">

                    <?php 
if ( ! empty( $cities ) ):
                foreach ($cities as $section_id => $presenter):?>
                            
                            <ul id="<?php echo $section_id?>" <?php  
                            if($presenter['city'] != 'Київ'){
                                 echo 'style="display:none;"';
                                 } ?> >
                                
<?php
foreach ($presenter['parthners'] as $val) {

?>
                                <li 
                                    data-gmaps-s="<?php echo $val['latitude']?>"
                                    data-gmaps-n="<?php echo $val['longitude']?>"
                                    data-mobile="<?php echo $val['phone']?>"
                                    data-schedule="<?php echo $val['working_hours']?>"
                                    data-address="<?php echo $val['address']?>"
                                >
                                    <span class="phone"><?php echo $val['name']?></span>

                                        <address class="address"><?php echo $val['address']?></address>
                                    <?php 
                                    if(!empty($val['phone']))
                                       echo '<span class="phone">тел:'. wpautop( $val['phone']) . '</span>';
                                        ?>

                                    <?php if($val['sate']!='' && $val['sate_text']!=''){?>
                                        <span><?php echo $val['sate_text']?>:</span>
                                        <a class="address" style="color: #11aee8;" href="<?php echo $val['sate']?>"><?php echo $val['sate']?></a>
                                    <?php }?>
                                    <span class="work-time"><?php echo $val['working_hours']?></span>
                                    <?php
                            if(!empty($val['latitude']) || !empty( $val['longitude']))
                            if ( $visible_map ){
                                   echo  '<div class="get_directions">
                                        <a href="#map" onclick="goToGmaps(this);">Переглянути на мапі</a>
                                    </div>';}
                                    ?>
                                </li>

                                <?php  

                                $gm_Arr[] = array(
                                                    'SCHEDULE' => $val['working_hours'],
                                                    'ADDRESS' => $val['name'],
                                                    'GPS_N' => $val['latitude'],
                                                    'GPS_S' => $val['longitude'],
                                                    'UF_MOBILE' => $val['phone']
                                                );
 }                               
                                ?>

</ul>
                            <?php endforeach;
                            endif;
                            ?>
                        

                    

                </div>

            </div>
        </div>

        <?php

?>

        <script type="text/javascript">

            var markers = [];

            function initMap() {
                window.GoogleMap = new google.maps.Map(document.getElementById('map'), {
                    zoom: 10,
                    center: {lat: 50.4501, lng: 30.523400000000038},
                    scrollwheel: true,
                    styles: [{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]},{"featureType":"water","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#C6E2FF"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"color":"#C5E3BF"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"color":"#D1D1B8"}]}]
                });
                window.geocoder = new google.maps.Geocoder();
                geocoder.geocode({'address': 'kiev'}, function(results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        GoogleMap.setCenter(results[0].geometry.location);
                    }
                });
            }


            var gmArr = <?php echo json_encode($gm_Arr, JSON_UNESCAPED_UNICODE)?>;


            setTimeout(function(){
                
                window.SetMarker = function(s, n, adress, schedule, phone, mobile){
                    if(s == 0 || n == 0) return;
                    var marker = new google.maps.Marker({
                        map: GoogleMap,
                        position: {lat: +s, lng: +n},
                        icon: myScriptData.directory_uri + "/assets/image/marker_map.png"
                    }),
                        infowindow = new google.maps.InfoWindow(),
                        cont = "<div class='infoblock'>";
                    cont += "<div class='adress'>" + adress + "</div>";
                    if (phone || mobile) {
                        cont += "<div class='phone'>";
                        if (phone) {
                            cont += "<a href='tel:"+ phone +"' class='one_phone city_phone mobile'>"+ phone +"<a/>";
                        }
                        if (mobile) {
                            cont += "<a href='tel:"+ mobile +"' class='one_phone mobile_phone mobile'>"+ mobile +"<a/>";
                        }
                        cont += "</div>";
                    }
                    cont += "<div class='time'>"+ schedule + "</div>";
                    cont += "</div>";
                    google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){
                        return function() {
                            infowindow.setContent(content);
                            infowindow.open(GoogleMap,marker);
                        };
                    })(marker,cont,infowindow));

                    markers.push(marker);
                };
                for (var i = 0; i < gmArr.length; i++) {
                    SetMarker(gmArr[i]['GPS_N'],gmArr[i]['GPS_S'],gmArr[i]['ADDRESS'],gmArr[i]['SCHEDULE'],gmArr[i]['PHONE'],gmArr[i]['UF_MOBILE'])
                }

            },1000);


            function clearMarkers(){
                for (var i = 0; i < markers.length; i++) {
                    markers[i].setMap(null);
                }
            }

            function goToGmaps(e){
                var parent = $(e).parents('li');
                GoogleMap.setCenter({lat: +parent.data('gmaps-s'), lng: +parent.data('gmaps-n')});
                GoogleMap.setZoom(18);
                $( "body" ).scrollTop(320);
                return false;
            }


            $(document).change('.jq-selectbox__select-text', function() {					
                 var ul_id;  //= $('option:contains("'+ $('.jq-selectbox__select-text').html() +'")').val();
                 var gmaps_s;
                 var gmaps_n;

                 $.each($('option:contains("'+ $('.jq-selectbox__select-text').html() +'")'), function(index, elem){
                     if( elem['text'] == $('.jq-selectbox__select-text').html() ){
                         ul_id = elem['value'];
                         gmaps_s = elem.dataset.gmaps_s;
                         gmaps_n = elem.dataset.gmaps_n;
                     }
                 })
                 
                $('.sales-agent-content ul').each(function(){
                    ( $(this).attr('id') == ul_id ) ? $(this).css('display','block') : $(this).css('display','none');
                });
             
                  GoogleMap.setCenter({lat: +gmaps_s, lng: +gmaps_n});
                 GoogleMap.setZoom(10);

             });
         


        </script>
        <script src="https://maps.googleapis.com/maps/api/js?signed_in=true&callback=initMap" async defer></script>
                    
    </div>	
</div>
</div>


<!-- <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script> -->
<noscript>
<div style="display:inline;">
    <img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/924126576/?guid=ON&amp;script=0"/>
</div>
</noscript>
            </div>
         </div>
      </div>
   </div>
</div>





</div>
</div>
	</main><!-- #main -->
    <script async="" defer="" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB4NLjQDIbT5ecY8qH0ZZd69xeJUXXmPiE&amp;callback=initMap" type="text/javascript"></script>
<?php
get_sidebar();
get_footer();
