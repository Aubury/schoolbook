var btn = $('#bcktotop');


 $(window).scroll(function() {

  if ($(window).scrollTop() > 300) {

    btn.addClass('fixed');

  } else {

    btn.removeClass('fixed');

  }

});



btn.on('click', function(e) {

  e.preventDefault();

  $('html, body').animate({scrollTop:0}, '200');

});

///////////////////////// BASKET ///////////////////////////////////////////

 let bubble =  document.querySelector(".bubble"),
  basket_link_wrap = document.querySelector(".basket-link-wrap"),
  add_to_cart_ajx = document.querySelectorAll(".add_to_cart_ajx");
  add_to_cart_ajx.forEach(element => {
	element.addEventListener("click", function(e){
		e.preventDefault();
		jQuery(document).ready( function( jQuery ){
		  var jqXHR = {
				action:'backsjax',
				nonce_code: soJsLet.nonce,
				id: element.id
		  }
			jQuery.post( soJsLet.ajaxurl, jqXHR, function( response ){

		let backResponse = JSON.parse(response);
		let basketHTML = '';

		if (window.innerWidth > 541) {
			basketHTML =  '<span>У кошику<span>';
		} else {
			basketHTML =  '<span class="icon-block icon-red-backed"><span>';
		}

		$(element).hasClass('product-preorder') ? element.innerHTML = '<span>Перейти до кошика<span>' : element.innerHTML = basketHTML;

		// element.innerHTML = '<span>У кошику<span>'
		bubble.innerHTML = backResponse[0]
		basket_link_wrap.innerHTML = backResponse[1]
		element.href = "/cart"
			} );
		  } );
	}, { once: true })
  });

// *************************************************************






function getCookie(name) {
	var matches = document.cookie.match(new RegExp(
		"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
	));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}

function setCookie(name, value, options) {
	options = options || {};
	var expires = options.expires;
	if (typeof expires == "number" && expires) {
		var d = new Date();
		d.setTime(d.getTime() + expires * 1000);
		expires = options.expires = d;
	}
	if (expires && expires.toUTCString) {
		options.expires = expires.toUTCString();
	}
	value = encodeURIComponent(value);
	var updatedCookie = name + "=" + value;
	for (var propName in options) {
		updatedCookie += "; " + propName;
		var propValue = options[propName];
		if (propValue !== true) {
			updatedCookie += "=" + propValue;
		}
	}
	document.cookie = updatedCookie;
}


var timer;
var first_visit_time    = getCookie("first_visit_time");
var first_lid_grab_time      = getCookie("first_lid_grab_time");
var second_lid_grab_time      = getCookie("second_lid_grab_time");

var time_now = Math.round(new Date().getTime()/1000);

var date_cookie = new Date;
date_cookie.setFullYear(new Date().getFullYear() + 1);

if(typeof first_visit_time == 'undefined'){
	first_visit_time = time_now;
	setCookie('first_visit_time', first_visit_time, {expires: date_cookie, path: "/"});
}

if (typeof first_lid_grab_time == 'undefined') {

	var timer = 30 - Math.round(time_now - first_visit_time);
	console.info('Первый покажется через ' + timer +' екунды');
	setTimeout(function () {

		// show popup
		$('#callLidGrab').click();
		setCookie('first_lid_grab_time', time_now + timer, {expires: date_cookie, path: "/"});

	}, timer * 1000);

}else if (typeof second_lid_grab_time == 'undefined') {

	var timer = 45 - Math.round(time_now - first_lid_grab_time);
	console.info('Втоой покажется черз ' + timer +' секунды');
	setTimeout(function () {

		// show popup
		$('#callLidGrab').click();
		setCookie('second_lid_grab_time', timer, {expires: date_cookie, path: "/"});

	}, timer * 1000);

} else {

	// console.info('попап показывали уже дажды');

}

function position_pagination_text () {
    var pagination_text = $('.pagination-text');
    var slick_dots = $('.slick-dots');

    if (slick_dots) {
        var li_array = $('.slick-dots li');
        var position = li_array[0].offsetLeft;
        var next_pos = position - 200;

        pagination_text.css('left', next_pos + 'px');
    }
}

function videonews_border_none() {
	console.log('click');
	$('.videonews').css('border', 'none');
}

$(document).ready(function () {
	let newDiv;

	///////////////////////////////////////////////
	$(document).mouseup(function (e) {
		var container = $('.login-block.opened, ' +
				'.callback.opened, ' +
				'.books-menu.opened, ' +
				'.bx-header.opened, ' +
				'.modal-menu.opened, ' +
				'.catalog-menu-section.opened'),
			button = $('.login-link, .callback-link, .books-button, .burger-menu, .catalog-menu-button');

		if (($(e.target).closest(button).length) || ($(e.target).closest(container).length)) {
			return;
		} else {
			container.removeClass('opened');
			button.removeClass('opened');
			e.stopPropagation();
		}

		if ($(e.target).closest('.sub-menu__about').length || $(e.target).closest('.lk-menu ').length) {
			return;
		} else {
			$('.sub-menu__about').removeClass('is-open');
			$('.lk-menu').removeClass('is-open');
		}
	});

	////////////////////////////////////////////////////////////////////////////

	$('.bx_hma_one_lvl.dropdown').on('mouseenter', function () {

		$('.bx_hma_one_lvl.dropdown')
			.not(this)
			.removeClass('opened');

		$(this).addClass('opened');

	});

	$('.catalog-menu_advanced').on('mouseleave', function () {
		$('.bx_hma_one_lvl.dropdown').removeClass('visual-hover');
	});

	////////////////////////////////////////////////////////////////////////////

	if ($(window).width() <= 991) {

		$(document).on('click', '#menu li.dropdown > a', function (e) {

			if (window.innerWidth > 991) return;

			let item = $(this).closest('li');
			let submenu = item.children('ul');

			if (!submenu.length) return;

			e.preventDefault();
			e.stopPropagation();

			// item.toggleClass('opened');
			// submenu.stop(true, true).slideToggle(200);

			if (item.hasClass('opened')) {
				item.removeClass('opened');
			} else {
				$('#menu li.opened')
					.removeClass('opened');

				item.addClass('opened');
			}

			return false;
		});
	}

	////////////////////////////////////////////////

	$(document).on('click', '.header-search-button', function(e) {
		e.preventDefault();

		if($(this).closest('.header-search').hasClass('opened')) {
			$(this).closest('.header-search').removeClass('opened');
		} else {
			$(this).parents('.header-search').addClass('opened');
		}
	});

	$(document).on('click', function(e){

		if (
			!$(e.target).closest('.header-search').length &&
			!$(e.target).closest('.aws-search-result').length
		) {
			$('.header-search').removeClass('opened');
		}

		if (
			!$(e.target).closest('.catalog-menu-button').length &&
			!$(e.target).closest('.mobile-catalog-menu-button').length
		) {
			$('body').css('position', 'relative');
			newDiv.remove();
		}

	});

	//////////////////////////////////////////////////

    setTimeout(position_pagination_text, 100);

    ///////////////////////////////////////////////////

	$('.catalog-menu-button, .mobile-catalog-menu-button').on('click', function() {
		// if ($(this).closest('.catalog-menu-section').hasClass('opened')) {
		// 	$(this).closest('.catalog-menu-section').removeClass('opened');
		// 	$('body').css('position', 'relative').remove(newDiv);
		// } else {
			$(this).closest('.catalog-menu-section').addClass('opened');
			newDiv = $('<div></div>');
			newDiv.css('background-color', 'rgba(0, 0, 0, 0.1)');
			newDiv.css('width', '100vw');
			newDiv.css('height', '100vh');
			newDiv.css('position', 'absolute');
			$('body').css('position', 'fixed').append(newDiv);
		// }
	});

	$('.books-button').on('click', function() {
		if($(this).parents('.books-menu').hasClass('opened')) {
			$(this).parents('.books-menu').removeClass('opened');
		} else {
			$(this).parents('.books-menu').addClass('opened');
		}
	});

	//////////////////////////////////////////////////////////
	
   $(function() {
        $('.show_sitemap').click(function(event) {
			if ( $(this).hasClass('fa-plus-circle') ) {
				$(this).addClass('fa-minus-circle');
				$(this).removeClass('fa-plus-circle');
			}else{
				$(this).addClass('fa-plus-circle');
				$(this).removeClass('fa-minus-circle');
			}
            $(this).next().toggle(300);
            return false;
        });
    });


	$(document).on('click', '.login-block .login-link',function(e) {
		e.preventDefault();

		if($(this).parents('.login-block').hasClass('opened')) {
			$(this).parents('.login-block').removeClass('opened');
		} else {
			$(this).parents('.login-block').addClass('opened');

			if($(this).parents('.bx-header').find(".books-menu").hasClass('opened')){
				$(this).parents('.bx-header').find(".books-menu").removeClass('opened')
			}
		}
	});

	$(document).on('click', '.callback .callback-link',function(e) {
		e.preventDefault();

		if($(this).parents('.callback').hasClass('opened')) {
			$(this).parents('.callback').removeClass('opened');
		} else {
			$(this).parents('.callback').addClass('opened');

			if($(this).parents('.menu-section').find(".login-block").hasClass('opened')){
				$(this).parents('.menu-section').find(".login-block").removeClass('opened')
			} else if($(this).parents('.bx-header').find(".books-menu").hasClass('opened')){
				$(this).parents('.bx-header').find(".books-menu").removeClass('opened')
			}
		}
	});

	/* click on body */

	// моб версия закрытие при кике на область вне окна login/callback
	$(document).mouseup(function (e){
	    var container = $(".login-block.opened");

	    if (!container.is(e.target) && container.has(e.target).length === 0) {
	      $(".login-block.opened").removeClass("opened");

	    } else {
	     $(".login-block").addClass("opened");
	    }
	});

	$(document).mouseup(function (e){
	    var container = $(".callback.opened");

	    if (!container.is(e.target) && container.has(e.target).length === 0) {
	      $(".callback.opened").removeClass("opened");

	    } else {
	     $(".lcallback").addClass("opened");
	    }
	});


    $('.form_validate').formValidation();
    //$('.validate_phone').mask('+380999999999');

/////////////////////////////////////////////////////////////////

    $(function() {
	$('.promo-slider').not('.slick-initialized').slick({
		infinite: true,
		dots: true,
		slidesToShow: 1,
		fade: true,
		slidesToScroll: 1,
  		autoplay: true,
  		autoplaySpeed: 5000,
		cssEase: 'linear',
		responsive: [
		{
			breakpoint: 500,
			settings: {
				arrows: false
			}
		}]
	});
})

	$(function() {
		$('.new-product .slider').not('.slick-initialized').slick({
			slidesToShow: 3,
			arrows: true,
			infinite: false,
			responsive: [
				{
					breakpoint: 1200,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 2
					}
				},
				{
					breakpoint: 992,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 2
					}
				},
				{
					breakpoint: 641,
					settings: {
						arrows: true,
						slidesToShow: 2,
						slidesToScroll: 1,
						autoplay: false,
						autoplaySpeed: 2000,
					}
				}]
		});

	})


$(function() {
	$('.viewed_products .slider').not('.slick-initialized').slick({
		slidesToShow: 4,
		arrows: true,
		infinite: false,
		responsive: [
		{
			breakpoint: 992,
			settings: {
				slidesToShow: 2,
    			slidesToScroll: 2
			}
		},
		{
			breakpoint: 641,
			settings: {
				slidesToShow: 1,
    			slidesToScroll: 1
			}
		}]
	});
})

$(function() {
	$('.series .slider').not('.slick-initialized').slick({
		slidesToShow: 4,
		arrows: true,
		infinite: false,
		responsive: [
		{
			breakpoint: 992,
			settings: {
				slidesToShow: 2,
    			slidesToScroll: 2
			}
		},
		{
			breakpoint: 641,
			settings: {
				slidesToShow: 1,
    			slidesToScroll: 1
			}
		}]
	});
})
$(function() {
	$('.recommendations .slider').not('.slick-initialized').slick({
		slidesToShow: 4,
		arrows: true,
		infinite: false,
		responsive: [
			{
				breakpoint: 1030,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
			},
		{
			breakpoint: 992,
			settings: {
				slidesToShow: 2,
    			slidesToScroll: 2
			}
		},
		{
			breakpoint: 641,
			settings: {
				arrows: true,
				slidesToShow: 2,
    			slidesToScroll: 1,
				autoplay: false,
				autoplaySpeed: 2000,
			}
		}]
	});
})

$(function() {
	$('.product-item-detail-slider-images-container').slick({
		rows: 0,
		infinite: true,
		dots: false,
		asNavFor: '.product-item-detail-slider-controls-block',
		focusOnSelect: true,
		slidesToShow: 1,
		fade: true,
		slidesToScroll: 1,
  		// autoplay: true,
  		autoplaySpeed: 5000,
		  prevArrow: '<span class="product-item-detail-slider-left" data-entity="slider-control-left" style=""></span>',
		  nextArrow: '<span class="product-item-detail-slider-right" data-entity="slider-control-right" style=""></span>',
		cssEase: 'linear',
		responsive: [
		{
			breakpoint: 500,
			settings: {
				arrows: false
			}
		}]
	});
})

$(function() {
$('.product-item-detail-slider-controls-block').not('.slick-initialized').slick({
	slidesToShow: 4,
	vertical: true,
	verticalSwiping: true,
	asNavFor: '.product-item-detail-slider-images-container',
	focusOnSelect: true,
	rows: 0,
	responsive: [
	{
		breakpoint: 768,
		settings: {
			vertical: false,
			verticalSwiping: false,
		}
	},
	{
		breakpoint: 400,
		settings: {
			slidesToShow: 3,
			vertical: false,
			verticalSwiping: false,
		}
	}]
});
})
// let popupContainer = document.querySelector('.product-item-detail-slider-container');
let	popupOpen = document.querySelector('.product-item-detail-slider-block');
// let	toogleCurcor = document.querySelector('.product-item-detail-slider-images-container');
let	popupClose = document.querySelector('.product-item-detail-slider-close');
	
$(document).ready(function () {
	slick_next = document.querySelector('.product-view .slick-next')
	

if(popupOpen){
	popupOpen.addEventListener("click", function(e){
	if(e.target.classList[1] != 'slick-arrow'){
		toogleCurcor.style.cursor = 'auto';
		if(slick_next){
		slick_next.click()}
		popupContainer.classList.toggle("popup");
}
});}
if(popupClose){

	popupClose.addEventListener("click", function(e){
		toogleCurcor.style.cursor = 'zoom-in';
		if(slick_next){
		slick_next.click()}
		popupContainer.classList.remove("popup");
	
	});
}
});
	function reinitAJAX(){
		if($(!'.slider-block .slider,.slick-initialized').length){
			$('.slider-block .slider').slick({
				slidesToShow: 4,
				arrows: true,
				infinite: false,
				responsive: [
				{
					breakpoint: 992,
					settings: {
						slidesToShow: 2,
		    			slidesToScroll: 2
					}
				},
				{
					breakpoint: 641,
					settings: {
						slidesToShow: 1,
		    			slidesToScroll: 1
					}
				}]
			});
		}
		if($(!'.recommendations .slider,.slick-initialized').length){
			$('.recommendations .slider').slick({
				slidesToShow: 4,
				arrows: true,
				infinite: false,
				responsive: [
				{
					breakpoint: 992,
					settings: {
						slidesToShow: 2,
		    			slidesToScroll: 2
					}
				},
				{
					breakpoint: 641,
					settings: {
						slidesToShow: 1,
		    			slidesToScroll: 1
					}
				}]
			});
		}
		if($(!'.new-books-slider .slider,.slick-initialized').length){
			$('.new-books-slider .slider').slick({
				slidesToShow: 3,
				arrows: true,
				infinite: false,
				responsive: [
				{
					breakpoint: 992,
					settings: {
						slidesToShow: 2,
		        		slidesToScroll: 2
					}
				},
				{
					breakpoint: 641,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1
					}
				}]
			});
		}
	};
	// BX.addCustomEvent('onAjaxSuccessFinish',reinitAJAX);

	var textMore = '';
	$('.desc-text-holder .more-text').on('click', function(e) {
		e.preventDefault();

		if ($(this).closest('.desc-text-holder').hasClass('active')) {
			$('.expanded', $(this).closest('.desc-text-holder')).slideUp(500, function() {
				$(this).closest('.desc-text-holder').removeClass('active');
			});
			$(this).find('span').text(textMore);
		} else {
			textMore = $(this).find('span').text();
			$('.expanded', $(this).closest('.desc-text-holder')).slideDown(500, function() {
				$(this).closest('.desc-text-holder').addClass('active');
			});
			$(this).find('span').text('Згорнути');
		}
	});

	$('.burger-menu').on('click', function() {
		if($(this).hasClass('opened')) {
			$(this).removeClass('opened');
			$(this).parents('.bx-header').removeClass('opened');

			$(this).closest('.header-menu-block').find('.modal-menu').removeClass('opened');

		} else {
			$(this).addClass('opened');
			$(this).parents('.bx-header').addClass('opened');

			$(this).closest('.header-menu-block').find('.modal-menu').addClass('opened');

		}
	});

	initModal();


	function eyeMove(){
		var el1 = $('#fox-eye-left'), eyeBall1 = el1.find('div');
		var el2 = $('#fox-eye-right'), eyeBall2 = el2.find('div');
		el1.show();
		el2.show();
		var x1 = el1.offset().left + 37, y1 = el1.offset().top + 25;
		var r = 3, x , y, x2, y2, isEyeProcessed = false;
		$('html').mousemove(function(e) {
		if (!isEyeProcessed){
		isEyeProcessed = true;
		var x2 = e.pageX, y2 = e.pageY;
		y = ((r * (y2 - y1)) / Math.sqrt((x2 - x1) * (x2 - x1) + (y2 - y1) * (y2 - y1))) + y1;
		x = (((y - y1) * (x2 - x1)) / (y2 - y1)) + x1;
		eyeBall1.css({
		marginTop: (y - y1 + 1) + 'px',
		marginLeft: (x - x1) + 'px'});
		eyeBall2.css({
		marginTop: (y - y1 + 1) + 'px',
		marginLeft: (x - x1) + 'px'});
		isEyeProcessed = false;}});
	};
	eyeMove();

	$(document).on('click', '.btn-up', function() {
		$('html, body').animate({scrollTop: 0}, 500);
	});
});

$(document).on('click', '.header-search-mobile button', function (e) {
	e.preventDefault();

	if($(this).closest('.header-search-mobile').hasClass('opened')) {
		$(this).closest('.header-search-mobile').removeClass('opened');
	} else {
		$(this).closest('.header-search-mobile').addClass('opened');
	}
})

$("#search #title-search-input").keyup(function(event){
	if(event.keyCode == 13){
		event.preventDefault();
		window.location.href='/catalog/?q='+$("#search #title-search-input").val();
	}
});
/* filter */
$(document).on('click', '.filter-btn', function() {
	if($(this).hasClass('active')) {
		$(this).removeClass('active');
		$(this).parents('.catalog-section-holder').find('.filter-holder').removeClass('opened-filter');
		$('body').removeClass('filter-is-open');
	} else {
		$(this).addClass('active');
		$(this).parents('.catalog-section-holder').find('.filter-holder').addClass('opened-filter');
		$('body').addClass('filter-is-open');
	}
});
$(document).on('click', '.close-filter', function() {
	if($(this).parents('.catalog-section-holder').find('.filter-btn').hasClass('active')) {
		$(this).parents('.catalog-section-holder').find('.filter-btn').removeClass('active');
		$(this).parents('.catalog-section-holder').find('.filter-holder').removeClass('opened-filter');
		$('body').removeClass('filter-is-open');
	} else {
		$(this).parents('.catalog-section-holder').find('.filter-btn').addClass('active');
		$(this).parents('.catalog-section-holder').find('.filter-holder').addClass('opened-filter');
		$('body').addClass('filter-is-open');
	}
});

/* modals */
function initModal() {
	var main_modal = $('#modal-main');

	// при закрианні модалі
	main_modal.on('hidden.bs.modal', function () {
	});

	// при показуванні модалі
	main_modal.on('show.bs.modal', function () {
	    centerModal(main_modal);
	});

	// клк по підкладці модалі
	$(document).on('click', '.modal-backdrop', function () {

	});

	$(document).on('click', '[data-openmodal]', function() {

		var link = $(this).data('openmodal');
		console.log('вввв');
		main_modal.find('.modal-dialog').load(link, function() {
			main_modal.modal('show');
			$('.form_validate').formValidation();
			//$('.validate_phone').mask('+380999999999');

			/* one click thx */
			function one_click_form(form_serialized) {
				var main_modal = $('#modal-main');
			    $.ajax({
			        url: "/local/ajax/modal/one-click-buying.php",
			        type: "POST",
			        beforeSend: function () {
			        },
			        data: form_serialized,
			        success: function (data) {
                        if(site_dir == '/ru/'){
                            main_modal.find(".modal-dialog").load("/local/ajax" + site_dir +"modal/one-click-buying-thx.php", function() {
                                main_modal.modal('show');
                            });
                        }else{
                            main_modal.find(".modal-dialog").load("/local/ajax/modal/one-click-buying-thx.php", function() {
                                main_modal.modal('show');
                            });
                        }


			            setTimeout(function () {
			                main_modal.modal('hide');
			            }, 3000);
				    }
			    });
			}

			function lead_grabber_form(form_serialized) {
				var main_modal = $('#modal-main');
				$.ajax({
					url: "/local/ajax/modal/one-click-buying.php",
					type: "POST",
					beforeSend: function () {
					},
					data: form_serialized,
					success: function (data) {

						if(site_dir == '/ru/'){
							main_modal.find(".modal-dialog").load("/local/ajax" + site_dir +"modal/lead-grabber-thx.php", function() {
								main_modal.modal('show');
							});
						}else{
							main_modal.find(".modal-dialog").load("/local/ajax/modal/lead-grabber-thx.php", function() {
								main_modal.modal('show');
							});
						}



						var time_now = Math.round(new Date().getTime()/1000);

						var date_cookie = new Date;
						date_cookie.setFullYear(new Date().getFullYear() + 1);

						setCookie('second_lid_grab_time', time_now, {expires: date_cookie, path: "/"});


						setTimeout(function () {
							main_modal.modal('hide');
						}, 10000);
					}
				});
			}

			$('#one-click-buying-form').formValidation().on('submit', function (e) {
			    e.preventDefault();
			    if (!$(this).find('.input-holder').hasClass('error')) {
			        var form_serialized = $(this).serialize();
			        console.log('fffff');
					$('#one-click-buying-form').find(".submit-btn.btn").attr('disabled', 'disabled');
			        one_click_form(form_serialized);
			    }
			});

			$('#lead-grabber-form').formValidation().on('submit', function (e) {
				e.preventDefault();
				if (!$(this).find('.input-holder').hasClass('error')) {
					var form_serialized = $(this).serialize();
					lead_grabber_form(form_serialized);
				}
			});

			// review_click_form review page thx
			function review_click_form(form_serialized) {
				var main_modal = $('#modal-main');
			    $.ajax({
			        url: "/local/ajax/modal/review.php",
			        type: "POST",
			        beforeSend: function () {
			        },
			        data: form_serialized,
			        success: function (data) {
                        if(site_dir == '/ru/'){
                            main_modal.find(".modal-dialog").load("/local/ajax" + site_dir +"modal/review-thx.php", function() {
                                main_modal.modal('show');
                            });
                        }else{
                            main_modal.find(".modal-dialog").load("/local/ajax/modal/review-thx.php", function() {
                                main_modal.modal('show');
                            });
                        }


			            setTimeout(function () {
			                main_modal.modal('hide');
			            }, 3000);
				    }
			    });
			}

			$('#review-form').formValidation().on('submit', function (e) {
			    e.preventDefault();

			    if (!$(this).find('.input-holder').hasClass('error')) {
			        var form_serialized = $(this).serialize();
			        review_click_form(form_serialized);
			    }
			});
		})
	})
}

function centerModal(modalBox) {
	if (modalBox === undefined) {
        modalBox = $('#modal-main');
    }

    var wrapper = $('body'),
    	modalDialog = modalBox.find('.modal-dialog'),
    	widthMain = wrapper.outerWidth(),
    	widthModal = modalDialog.find('.modal-body').outerWidth(),
    	centerDistance = (widthMain - widthModal) / 2,
    	centerVertical = ($(window).outerHeight() - modalDialog.outerHeight())/2;

	modalDialog.css('margin-left', centerDistance + 'px');

	if (centerVertical>0) {
		modalDialog.css('margin-top', centerVertical + 'px');
	} else {
		modalDialog.css('margin-top', '0');
	}

	$(window).resize(function() {
		var modalDialog = modalBox.find('.modal-dialog'),
			widthMain = wrapper.outerWidth(),
			widthModal = modalDialog.find('.modal-body').outerWidth(),
			centerDistance = (widthMain - widthModal) / 2;

		modalDialog.css('margin-left', centerDistance + 'px');

	});
};





$(window).scroll(function() {
	if ($(window).width() > 991){
		if($('footer:not(.visible)').length)
			if (  ( $(window).scrollTop() + $(window).height() ) >=  $('footer:not(.visible)').offset().top)   {
				  $('footer:not(.visible)').addClass('visible');

				    var x,
				    	walk = myScriptData.directory_uri + "/assets/image/walk/";

					for (var i = 1; i <= 12; i++) {
					    setTimeout(function(x) {
					     return function() {
					      $(".footer-walk img").attr("src" , walk + x + ".png");
					     };
					    }(i), 1000*i);
					}
			}
		}
	if (bugMove()) {
		$(".new-books-slider").addClass("anim-bug");
	}
	if (girlMove()) {
		$(".blog-items").addClass("anim-img");
	}
});

// popup add to favorites
jQuery(function($){
	$(document).mouseup(function (e){ // click on document
		var div = $(".popup-window-with-titlebar"); // element
		if (!div.is(e.target) // if click not element
		    && div.has(e.target).length === 0) { // if click not element child
			div.hide();
			$(".popup-window-overlay").hide();
			$('body').css('overflow' , "");
		}
	});
});

// animation after scroll main page
function bugMove() {
	if($('*').is(".new-books-slider")){
	var $block2 = $('.new-books-slider');
	var windowBottom = $(window).scrollTop() + $(window).height();
	var block2Bottom = $block2.offset().top + $('.slick-slider').height();
	return windowBottom >= block2Bottom;
	}
}
function girlMove() {
	if($('*').is(".blog-section")){
	var $blockNew = $('.blog-section');
	var windowBottom = $(window).scrollTop() + $(window).height();
	var blockNewBottom = $blockNew.offset().top + $('.blog-section .blog-items').height();
	return windowBottom >= blockNewBottom;
	}
}

function addFavorite(id)
{
	var paramID = id;
	$.ajax({
		url:     '/local/ajax/modal/favorit.php', // URL отправки запроса
		type:     "POST",
		dataType: "json",
		data: {id:paramID, type:'favorit'},
		success: function(response) { // Если Данные отправлены успешно
			var result =response;
			console.log(result);
			if(result == 1){ // Ели всё чётко, то выполняем действи, которые показывают, что данные отправлены :)
				$('#favorite_'+paramID).addClass('disabled');
				console.log($('#favorite_'+paramID));
				console.log(paramID);
				var wishCount = parseInt($('.bx-basket-block .favorites-holder .bubble').text()) + 1;
				$('.bx-basket-block .favorites-holder .bubble').text(wishCount); // Визуальо меняем количесво у иконки
			}
			if(result == 2){
				$('#favorite_'+paramID).removeClass('disabled');
				var wishCount = parseInt($('.bx-basket-block .favorites-holder .bubble').text()) + 1;
				$('.bx-basket-block .favorites-holder .bubble').text(wishCount); // Визуально меняем количество у иконки
			}
		},
		error: function(jqXHR, textStatus, errorThrown){ // Если ошибка, то выкладывем печаль в консоль
			console.log('Error: '+ errorThrown);
		}
	});
}




	let inlaked = document.querySelector('.inlaked')

	if(inlaked) {
		inlaked.addEventListener("click", function(e) {
			e.preventDefault()
		});
	}

	// filters///////////////////////////////////////

	let del_filter= document.querySelector("#del_filter");
	let filter_chbx= document.querySelectorAll(".filter-chbx");
	let countelementblock = document.querySelector('.countelementblock');
	let h2_title = document.querySelector('.h2-title');
	let product_item_container = document.querySelector('.products');
	let countelement = document.querySelector('#countelement');
	let bx_filter_text = document.querySelector('.bx-filter-text');
	let step = 12;

	if(del_filter){
		del_filter.addEventListener('click', function(){
			filter_chbx.forEach(element => {
				if (element.checked) {
					element.checked = false;
                }
            });

            setCookie('products_filters', null);

            arr = {};
            arr = {
                sort: ['news']
            };

            jQuery(document).ready( function( jQuery ){
					var jqXHR = {
						action:'sjax',
						nonce_code: soJsLet.nonce,
						category: h2_title.dataset.termslug,
						filters: JSON.stringify(arr),
                        flag: 'delete_filter',
					}

				  jQuery.post( soJsLet.ajaxurl, jqXHR, function( response){
					// bx_filter_text.classList.remove("hidden-non");
					let backResponse = JSON.parse(response);
                    countelementblock.classList.add("hidden");
					product_item_container.innerHTML = '';
					product_item_container.innerHTML = backResponse[1];
					setCookie('products', backResponse[2]);
					  let arr = JSON.parse(backResponse[2]);
					  let list = arr.length;

					  if (list < step) {
						  $('.woocommerce-pagination').css('display', 'none');
					  } else {
						  $('.woocommerce-pagination').css('display', 'block');
					  }

                      location.href = location.pathname;

                  });
			});

		 })
	}

// sort
	var radioButtons = document.querySelectorAll('input[type="radio"]');

	radioButtons.forEach(function(radioButton) {
		radioButton.addEventListener('change', function() {
			// Удаляем класс checked у всех label-container
			document.querySelectorAll('.label-container ').forEach(function(labelContainer) {
				labelContainer.classList.remove('active');
			});

			// Проверяем, соотетствует ли label рдиокнопке, которя была выбрана
			var labelForRadio = document.querySelector('label[for="' + this.id + '"]');
			if (labelForRadio) {
				labelForRadio.parentNode.classList.add('active');
				selectedValue = this.value;
				// console.log("Выбрано значение: " + selectedValue)
			}
		});
	});

// \sort

	var arr = {};

	if(filter_chbx){
		filter_chbx.forEach(element => {
			
			arr[element.dataset.attribute_label] = [];

            element.addEventListener('change', function (evt) {
				var el = evt.target;
				filter_chbx.forEach(element => {
					if (element.checked) {
						countelementblock.classList.remove("hidden");
						var mayak = true

					if (arr[element.dataset.attribute_label]!='') {
						arr[element.dataset.attribute_label].forEach(ele => {
							if(ele == element.value){
								mayak = false
							}
						});

						if(mayak) {
							if(element.name == 'sorting'){
								arr[element.dataset.attribute_label][0] = element.value;
							}else{
							arr[element.dataset.attribute_label].push( element.value)
							}
							mayak = true
						}

					} else {
						if(element.name == 'sorting'){
							arr[element.dataset.attribute_label][0] = element.value;
						} else {
						    arr[element.dataset.attribute_label].push( element.value);
						}
					}
				}
				});							
				
				if (el.checked) {

				} else {
					for (let index = 0 ; index < arr[element.dataset.attribute_label].length; index++) {
						if(arr[element.dataset.attribute_label][index] == el.value){
							arr[element.dataset.attribute_label].splice(index, 1)
						}
					}
				}

				console.log(arr);

                setCookie('products_filters', JSON.stringify(arr));
				
				jQuery(document).ready( function( jQuery ){
					var jqXHR = {
						  action:'sjax',
						  nonce_code: soJsLet.nonce,
						  filters: JSON.stringify(arr),
						  category: h2_title.dataset.termslug,
					}
					  jQuery.post( soJsLet.ajaxurl, jqXHR, function( response ){
						bx_filter_text.classList.remove("hidden-non");
						let backResponse = JSON.parse(response);
						countelement.innerHTML = backResponse[0];
						product_item_container.innerHTML = '';
						product_item_container.innerHTML = backResponse[1];
						setCookie('products', backResponse[2]);
						let arr = JSON.parse(backResponse[2]);
						let list = arr.length;

						  if (list < step) {
							  $('.woocommerce-pagination').css('display', 'none');
						  } else {
							  $('.woocommerce-pagination').css('display', 'block');
						  }

                      } );
					} );   

			}, false);
          });
	}

		  

		  


	// filters\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

	$(document).on('click', '.filter-alphabet li', function (e) {
		e.preventDefault();
	
		var item = $(this).data('l'),
			str;
	
		$(this).addClass("active").siblings().removeClass("active");
	
	
		$('.filter-authors li').each(function () {
			str = $(this).data('m');
			//console.log(str);
	
			if(str.indexOf(item) + 1 == 0) {
				if (item == 'all') {
					$(this).addClass("active");
				} else {
					$(this).removeClass("active");
				}
			} else {
			   $(this).addClass("active");
			}
		 });
	});


	$( "#one-click-buying-form" ).on( "submit", function( event ) {
		event.preventDefault();

		let data_form =  $( this ).serializeArray();
		jQuery(document).ready( function( jQuery ){
			var jqXHR = {
				  action:'one_click_buying',
				  nonce_code: soJsLet.nonce,
				  filds: data_form
			}
			  jQuery.post( soJsLet.ajaxurl, jqXHR, function( response ){
				let backResponse = JSON.parse(response)
				document.querySelector('.modal-body--one-click').innerHTML = backResponse 

			  } );
			} );   
			setTimeout(function() {
				window.location.replace("/");
			}, 5000);

	  } );

// 	$( "#preorder" ).on( "submit", function( event ) {
// 		event.preventDefault();
// 		var formData = {}
// 		formData['id'] = document.getElementById('prodid').value;
// 		formData['name'] = document.getElementById('name').value;
// 		formData['phone'] = document.getElementById('phone').value;
// 		// Выводим даные в консоль (дл демонстрации)
// 		console.log(formData);
// 		jQuery(document).ready( function( jQuery ){
// 			var jqXHR = {
// 				  action:'so_preorder',
// 				  nonce_code: soJsLet.nonce,
// 				  filds: formData
// 			}
// 			  jQuery.post( soJsLet.ajaxurl, jqXHR, function( response ){
// 				let backResponse = JSON.parse(response)
// 				document.querySelector('.modal-body').innerHTML = backResponse 

// 			  } );
// 			} );   
// 			setTimeout(function() {
// 				window.location.replace("/");
// 			}, 5000);

// 	  } );


	document.addEventListener('DOMContentLoaded', function () {
    var sortingElement = document.querySelector('.sorting');

	if(sortingElement){
        sortingElement.addEventListener('click', function () {
            // Переключаем клас при каждом клике
            sortingElement.classList.toggle('opened');
        });
	}
});

///////////////// PAGINATION //////////////////////////////

if (getCookie('products') !== undefined) {
	var cookie_products = JSON.parse(getCookie('products'));

	var ul_products = $('.products');
	var list_products = ul_products.find('.product-item-list-col-1');
	var show_now = 12;
	var nav = document.createElement('nav');
	var button_more = document.createElement('button');

	$(nav).addClass('woocommerce-pagination');
	$(button_more).addClass('btn btn-primary btn-lg');
	$(button_more).attr('id', 'load-more');
	$(button_more).text('Завантажити ще');

	var button = $( '#load-more' ),
		products_on_page = $('.product-item-list-col-1').length,
		maxPages = cookie_products.length;

	if (products_on_page < maxPages)  {
		button.click( function( event ) {
			event.preventDefault();
			cookie_products = JSON.parse(getCookie('products'));
			products_on_page = $('.product-item-list-col-1').length,
				maxPages = cookie_products.length;
			var new_products = [];

			for (var i = products_on_page; i < (products_on_page + step); i++) {
				new_products.push(cookie_products[i]);
			}

			var data = {
				action:'more_products',
				nonce_code: add_more_object.nonce,
				products: new_products,
			}

			$.ajax({
				url : add_more_object.url, // обработчик
				data: data,
				type : 'POST', // тип запроса
				success : function( request, xhr, status, error, data ){
					if (xhr === "success") {
						let backResponse = JSON.parse(request);
						ul_products.append(backResponse);
						if ($('.product-item-list-col-1').length === maxPages) {
							$('.woocommerce-pagination').css('display', 'none');
						}

					}
				},
				error : function (error) {
					console.log(error);
				}
			});

		} );
	}
}

const lightbox = new PhotoSwipeLightbox({
	gallery: '#products-gallery', // одна или несколько картинок
	children: 'img',
	showHideAnimationType: 'zoom',
	doubleTapAction: 'zoom',
	wheelToZoom: true,
	pswpModule: PhotoSwipe,

});


lightbox.addFilter('itemData', (itemData, index) => {
	const img = itemData.element;
	itemData.src = img.dataset.pswpSrc;
	itemData.width = parseInt(img.dataset.pswpWidth, 10);
	itemData.height = parseInt(img.dataset.pswpHeight, 10);
	itemData.webpSrc = img.dataset.pswpWebpSrc;
	return itemData;
});


lightbox.init();


/////////////////////////////////
function checkDelivery() {
    const deliveryMethods = document.getElementsByName('delivery');
    const checkoutBtn = document.getElementById('checkout-btn');
    const warningMsg = document.getElementById('warning-msg');
    const addressInput = document.getElementById('user-address');
    const addressDetails = document.getElementById('address-details');

    let isMethodSelected = false;
    let selectedValue = "";

    // 1. Проверяем, выбран ли хоть один Radio Button
    for (const method of deliveryMethods) {
        if (method.checked) {
            isMethodSelected = true;
            selectedValue = method.value;
            break;
        }
    }

    // Показываем поле ввода адреса, если метод выбран
    if (isMethodSelected) {
        addressDetails.style.display = "block";
    }

    // 2. Условие разблокировки: выбран метод И введен адрес (текст)
    if (isMethodSelected && addressInput.value.length > 3) {
        checkoutBtn.disabled = false;
        checkoutBtn.style.backgroundColor = "#ff5722"; // Оранжевый как на скрине
        checkoutBtn.style.cursor = "pointer";
        warningMsg.style.display = "none";
    } else {
        // Если что-то не заполнено — блокируем обратно
        checkoutBtn.disabled = true;
        checkoutBtn.style.backgroundColor = "grey";
        checkoutBtn.style.cursor = "not-allowed";
        warningMsg.style.display = "block";
    }
}

