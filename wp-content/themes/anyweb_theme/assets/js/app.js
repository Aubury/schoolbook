const btn = $('#bcktotop');
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

////////////////////////////////////////////////////////////////////////

function initMobileDescription() {
	const description = document.querySelector(
		'.product-item-description'
	);

	if (!description) {
		return;
	}

	const limit = 180;

	// Сохраняем полный текст только один раз.
	if (!description.dataset.fullText) {
		description.dataset.fullText =
			description.textContent.trim();
	}

	const fullText = description.dataset.fullText;

	let button = document.querySelector(
		'.product-description-toggle'
	);

	if (!button) {
		button = document.createElement('button');
		button.type = 'button';
		button.className = 'product-description-toggle';

		description.insertAdjacentElement(
			'afterend',
			button
		);
	}

	// На десктопе выводим полный текст без кнопки.
	if (window.innerWidth > 576) {
		description.textContent = fullText;
		description.classList.remove('is-expanded');
		button.hidden = true;

		return;
	}

	// Если текст и так короткий.
	if (fullText.length <= limit) {
		description.textContent = fullText;
		button.hidden = true;

		return;
	}

	button.hidden = false;

	const shortText = fullText
		.slice(0, limit)
		.replace(/\s+\S*$/, '')
		.trim();

	const renderDescription = function (expanded) {
		description.classList.toggle(
			'is-expanded',
			expanded
		);

		description.textContent = expanded
			? fullText
			: shortText + '…';

		button.innerHTML = expanded
			? `
                <span>Згорнути</span>
                <span class="product-description-toggle__arrow">
					<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
					  <path d="M1 3L5.5 8L10 3" stroke="#525252" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</span>
              `
			: `
                <span>Читати більше</span>
                <span class="product-description-toggle__arrow">
					<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
					  <path d="M1 3L5.5 8L10 3" stroke="#525252" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</span>
              `;

		button.setAttribute(
			'aria-expanded',
			String(expanded)
		);
	};

	renderDescription(
		description.classList.contains('is-expanded')
	);

	if (!button.dataset.initialized) {
		button.dataset.initialized = 'true';

		button.addEventListener('click', function () {
			const expanded =
				button.getAttribute('aria-expanded') === 'true';

			renderDescription(!expanded);
		});
	}
}

document.addEventListener(
	'DOMContentLoaded',
	initMobileDescription
);

window.addEventListener(
	'resize',
	initMobileDescription
);

/////////////////////////////////////////////////////////////////////////

function initProductInfoTables() {
	document.querySelectorAll('.table-info').forEach(function (wrapper) {
		const table = wrapper.querySelector('table');
		const rows = wrapper.querySelectorAll('tbody tr');

		if (!table || rows.length <= 6) {
			return;
		}

		// Защита от повторного создания кнопки.
		if (wrapper.querySelector('.table-info-show-all')) {
			return;
		}

		wrapper.classList.add('table-info--collapsed');

		const button = document.createElement('button');

		button.type = 'button';
		button.className = 'table-info-show-all';
		button.setAttribute('aria-expanded', 'false');
		button.innerHTML = `
            <span class="table-info-show-all__text">
                Показати всі
            </span>
            <span class="table-info-show-all__arrow" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
				  <path d="M1 3L5.5 8L10 3" stroke="#525252" stroke-width="2" stroke-linecap="round"/>
				</svg>
            </span>
        `;

		button.addEventListener('click', function () {
			const isExpanded = wrapper.classList.toggle(
				'table-info--expanded'
			);

			wrapper.classList.toggle(
				'table-info--collapsed',
				!isExpanded
			);

			button.setAttribute(
				'aria-expanded',
				String(isExpanded)
			);

			button.querySelector(
				'.table-info-show-all__text'
			).textContent = isExpanded
				? 'Згорнути'
				: 'Показати всі';
		});

		table.insertAdjacentElement('afterend', button);
	});
}

document.addEventListener(
	'DOMContentLoaded',
	initProductInfoTables
);

//////////////////////////////////////////////////////////////////////////

jQuery(function ($) {
	$('.aws-search-form').each(function () {
		const $form = $(this);
		const $field = $form.find('.aws-search-field');
		const $wrapper = $form.find('.aws-wrapper');

		if (!$field.length || !$wrapper.length) {
			return;
		}

		// Не добавляем повторно.
		if ($wrapper.find('.custom-aws-clear').length) {
			return;
		}

		const $clearButton = $('<button>', {
			type: 'button',
			class: 'custom-aws-clear',
			'aria-label': 'Очистити пошук',
			html: '&times;'
		});

		$wrapper.append($clearButton);

		function toggleClearButton() {
			$clearButton.toggleClass(
				'is-visible',
				$field.val().trim().length > 0
			);
		}

		$field.on('input change', toggleClearButton);

		$clearButton.on('pointerdown click', function (event) {
			event.preventDefault();
			event.stopPropagation();
			event.stopImmediatePropagation();

			// Очищаем строку поиска.
			$field.val('');

			// Сбрасываем скрытые поля только этой формы.
			$form.find('.awsFilterHidden').val('');
			$form.find('input[name="product_type"]').val('');
			$form.find('input[name="type_aws"]').val('');
			$form.find('input[name="aws_id"]').val('');

			// Убираем состояние открытого поиска.
			$form.removeClass('aws-focus');

			$('.aws-search-result').hide();

			$('.body-mask').removeClass('opened');

			$clearButton.removeClass('is-visible');

			// Уведомляем плагин об изменении поля.
			$field.trigger('input').trigger('change').trigger('keyup');

			// Фокус можно убрать, чтобы поиск снова не открылся.
			$field.trigger('blur');

			console.log('Поиск полностью очищен');
		});

		toggleClearButton();
	});
});

function changePlaceholder() {
	const input = document.querySelector('.aws-search-field');

	if (!input) return;

	input.placeholder = window.innerWidth <= 1200
		? 'ПOШУК'
		: 'ПOШУК за назвою книги, автором або категорією';
}

changePlaceholder();

new MutationObserver(changePlaceholder).observe(document.body, {
	childList: true,
	subtree: true
});

window.addEventListener('resize', changePlaceholder);

///////////////////////// BASKET ///////////////////////////////////////////


jQuery(function ($) {
	let noticeTimer;

	function hideCartNotice() {
		clearTimeout(noticeTimer);
		$('#cart-added-notice').prop('hidden', true);
	}

	function showCartNotice(html) {
		clearTimeout(noticeTimer);

		$('#cart-added-notice')
			.html(html)
			.prop('hidden', false);

		// noticeTimer = setTimeout(hideCartNotice, 5000);
	}

	$(document).on('click.cartAdd', '.add_to_cart_ajx', function (e) {
		const $button = $(this);

		// После добавления ссылка ведёт в корзину.
		if ($button.hasClass('is-in-cart')) {
			return;
		}

		e.preventDefault();

		if ($button.hasClass('is-loading')) {
			return;
		}

		$button.addClass('is-loading');

		$.ajax({
			url: soJsLet.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'backsjax',
				nonce_code: soJsLet.nonce,
				id: this.id
			}
		})
			.done(function (response) {
				if (response.error) {
					alert(response.message);
					return;
				}

				// Обновляем все экземпляры корзины в шапке.
				$('.header-basket-block .bubble').text(response[0]);
				$('.basket-link-wrap').html(response[1]);
				$('.header-cart__content').html(response[2]);

				let buttonHTML;

				if ($button.hasClass('product-preorder')) {
					buttonHTML = '<span>У кошику</span>';
				} else if (window.innerWidth > 541) {
					buttonHTML = '<span class="text-block">У кошику</span>';
				} else {
					buttonHTML =
						'<span class="icon-block icon-red-backed"></span>';
				}

				const cartUrl = $('.header-basket-block')
					.first()
					.attr('href');

				$button
					.addClass('is-in-cart')
					.html(buttonHTML)
					.attr('href', cartUrl || '/cart/');

				showCartNotice(response[3]);
			})
			.fail(function (xhr) {
				console.error(
					'Ошибка добавления товара:',
					xhr.status,
					xhr.responseText
				);
			})
			.always(function () {
				$button.removeClass('is-loading');
			});
	});

	$(document).on(
		'click.cartNotice',
		'.cart-added-notice__close',
		hideCartNotice
	);
});

jQuery(function ($) {
	const $notice = $('#cart-added-notice');

	if (!$notice.length) return;

	// Запоминаем исходное место в шапке.
	const $originalPlace = $('<span hidden></span>');
	$notice.before($originalPlace);

	const mobileMedia = window.matchMedia('(max-width: 992px)');

	function moveCartAddedNotice() {
		// Укажи здесь обёртку своих хлебных крошек.
		const $breadcrumbs = $('#navigation').first();

		if (mobileMedia.matches && $breadcrumbs.length) {
			let $noticeSlot = $('#cart-added-notice-slot');

			if (!$noticeSlot.length) {
				$noticeSlot = $('<div id="cart-added-notice-slot"></div>');
				$noticeSlot.insertAfter($breadcrumbs);
			}

			$notice.appendTo($noticeSlot);
		} else {
			$notice.insertAfter($originalPlace);
		}
	}

	mobileMedia.addEventListener('change', moveCartAddedNotice);
	moveCartAddedNotice();
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


$(document).ready(function () {
	// let newDiv;

	///////////////////////////////////////////////
	$(document).mouseup(function (e) {

		const container = $(
			'.login-block.opened, ' +
			'.callback.opened, ' +
			'.books-menu.opened, ' +
			'.header.opened, ' +
			'.modal-menu.opened, ' +
			'.catalog-menu-section.opened'
		);

		const button = $(
			'.login-link, ' +
			'.callback-link, ' +
			'.books-button, ' +
			'.burger-menu, ' +
			'.catalog-menu-button, ' +
			'.mobile-catalog-menu-button'
		);

		if (
			$(e.target).closest(button).length ||
			$(e.target).closest(container).length
		) {
			return;
		}

		container.removeClass('opened');
		button.removeClass('opened');
		$('.body-mask').removeClass('opened');

		// Закрываем все дочерние пункты каталога
		$('.bx_hma_one_lvl.dropdown').removeClass('opened');
		$('.bx_hma_one_lvl-ul')
			.removeClass('opened');


		if ($(e.target).closest('.sub-menu__about').length || $(e.target).closest('.lk-menu ').length) {
			return;
		} else {
			$('.sub-menu__about').removeClass('is-open');
			$('.lk-menu').removeClass('is-open');
		}
	});

	////////////////////////////////////////////////////////////////////////////
	if ($(window).width() > 991) {
		$('.bx_hma_one_lvl.dropdown').on('mouseenter', function () {
			$('.bx_hma_one_lvl.dropdown, .bx_hma_one_lvl-ul').removeClass('opened');

			$(this)
				.addClass('opened')
				.children('.bx_hma_one_lvl-ul')
				.addClass('opened');
		});
	}

	////////////////////////////////////////////////////////////////////////////

	if ($(window).width() <= 991) {

		$(document).on('click', '#menu li.dropdown > a', function (e) {

			if (window.innerWidth > 991) return;

			let item = $(this).closest('li');
			let submenu = item.children('ul');
			let dropDown = item.find('.bx_hma_one_lvl-ul');

			if (!submenu.length) return;

			e.preventDefault();
			e.stopPropagation();

			// item.toggleClass('opened');
			// submenu.stop(true, true).slideToggle(200);

			if (item.hasClass('opened')) {
				item.removeClass('opened');
				dropDown.removeClass('visible');
				dropDown.addClass('visible-none');
			} else {
				$('#menu li.opened').removeClass('opened');

				item.addClass('opened');
				dropDown.removeClass('visible-none');
				dropDown.addClass('visible');
			}

			return false;
		});
	}

	////////////////////////////////////////////////

	const form = document.querySelector('.aws-search-form');

	$(document).on('click', '.header-search-button', function(e) {
		e.preventDefault();

		if($(this).closest('.header-search').hasClass('opened')) {
			$(this).closest('.header-search').removeClass('opened');

			if ($(window).width() <= 541) {
				$('.catalog-menu-button').css('visibility', 'visible');
			}
		} else {
			$(this).parents('.header-search').addClass('opened');

			if ($(window).width() <= 541) {
                $('.catalog-menu-button').css('visibility', 'hidden');
			}
		}
	});

	const aws_form = document.querySelector('.aws-container form');

	if (aws_form) {
		const updateMask = () => {
			$('.body-mask').toggleClass(
				'opened',
				form.classList.contains('aws-form-active')
			);
		};

		const observer = new MutationObserver(updateMask);

		observer.observe(form, {
			attributes: true,
			attributeFilter: ['class']
		});

		updateMask();
	}

	$(document).on('click', function(e){
		const $target = $(e.target);

		if (
			!$(e.target).closest('.header-search').length &&
			!$(e.target).closest('.aws-search-result').length
		) {
			$('.header-search').removeClass('opened');
			$('.catalog-menu-button').css('visibility', 'visible');
		}

		if (
			!$(e.target).closest('.catalog-menu-button').length &&
			!$(e.target).closest('.mobile-catalog-menu-button').length &&
			!$(e.target).closest('.filter-sorting-holder').length
		) {
			$('body').css('position', 'relative');
		}

	});


    ///////////////////////////////////////////////////

	$('.catalog-menu-button, .mobile-catalog-menu-button').on('click', function() {
		if ($(this).closest('.catalog-menu-section').hasClass('opened')) {
			$(this).closest('.catalog-menu-section').removeClass('opened');

			if (
				$(this).closest('.header-main-menu-section').length &&
				!$(this).closest('.mobile-catalog-menu').length
			) {
				$('.body-mask').removeClass('opened');
				$('body').css('position', 'relative');
			}

		} else {
			$(this).closest('.catalog-menu-section').addClass('opened');

			if (
				$(this).closest('.header-main-menu-section').length &&
				!$(this).closest('.mobile-catalog-menu').length
			) {
				$('.body-mask').addClass('opened');
				$('body').css('position', 'fixed');
			}
		}
	});

	$('.books-button').on('click', function() {
		if($(this).parents('.books-menu').hasClass('opened')) {
			$(this).parents('.books-menu').removeClass('opened');
		} else {
			$(this).parents('.books-menu').addClass('opened');
		}
	});

	$('.footer-column').on('click', function() {
		if($(this).hasClass('opened')) {
			$(this).removeClass('opened');
		} else {
			$(this).addClass('opened');
		}
	});

	$('.filter-sorting-holder').on('click', function() {
		let body = $('body');
		if($(this).hasClass('opened')) {
			$('.filters-modal').removeClass('opened');
			body.css('position', 'relative');
		} else {
			$('.filters-modal').addClass('opened');
			body.css('position', 'fixed');
		}
	});

	$('.filters-modal-close').on('click', function() {
		if($(this).parents('.filters-modal').hasClass('opened')) {
			$(this).parents('.filters-modal').removeClass('opened');
			$('body').css('position', 'relative');
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

/////////////////////////////////////////////////////////////////

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


	// function eyeMove(){
	// 	var el1 = $('#fox-eye-left'), eyeBall1 = el1.find('div');
	// 	var el2 = $('#fox-eye-right'), eyeBall2 = el2.find('div');
	// 	el1.show();
	// 	el2.show();
	// 	var x1 = el1.offset().left + 37, y1 = el1.offset().top + 25;
	// 	var r = 3, x , y, x2, y2, isEyeProcessed = false;
	// 	$('html').mousemove(function(e) {
	// 	if (!isEyeProcessed){
	// 	isEyeProcessed = true;
	// 	var x2 = e.pageX, y2 = e.pageY;
	// 	y = ((r * (y2 - y1)) / Math.sqrt((x2 - x1) * (x2 - x1) + (y2 - y1) * (y2 - y1))) + y1;
	// 	x = (((y - y1) * (x2 - x1)) / (y2 - y1)) + x1;
	// 	eyeBall1.css({
	// 	marginTop: (y - y1 + 1) + 'px',
	// 	marginLeft: (x - x1) + 'px'});
	// 	eyeBall2.css({
	// 	marginTop: (y - y1 + 1) + 'px',
	// 	marginLeft: (x - x1) + 'px'});
	// 	isEyeProcessed = false;}});
	// };
	// eyeMove();

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
				    },
					error: function (xhr) {
						$('#one-click-buying-form').trigger('oneClickRequestFailed');

						console.error('Ошибка отправки:', xhr.status, xhr.responseText);
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

			// $('#one-click-buying-form').formValidation().on('submit', function (e) {
			//     e.preventDefault();
			//     if (!$(this).find('.input-holder').hasClass('error')) {
			//         var form_serialized = $(this).serialize();
			//         console.log('fffff');
			// 		$('#one-click-buying-form').find(".submit-btn").attr('disabled', 'disabled');
			//         one_click_form(form_serialized);
			//     }
			// });

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
}

////////////////////// Анимация Footer //////////////////////////////

$(window).scroll(function() {
	if ($(window).width() > 991){
		if($('footer:not(.visible)').length)
			if (  ( $(window).scrollTop() + $(window).height() ) >=  $('footer:not(.visible)').offset().top)   {
				  $('footer:not(.visible)').addClass('visible');

				    // var x,
				    // 	walk = myScriptData.directory_uri + "/assets/image/walk/";
					//
					// for (var i = 1; i <= 12; i++) {
					//     setTimeout(function(x) {
					//      return function() {
					//       $(".footer-walk img").attr("src" , walk + x + ".png");
					//      };
					//     }(i), 1000*i);
					// }
			}
		}
	if (bugMove()) {
		$(".new-books-slider").addClass("anim-bug");
	}
	if (girlMove()) {
		$(".blog-items").addClass("anim-img");
	}
});

///////////////////////////////////////////////////////////////

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


	// filters\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

	$(document).on('click', '.filter-alphabet li', function (e) {
		e.preventDefault();
	
		var item = $(this).data('l'),
			str;
	
		$(this).addClass("active").siblings().removeClass("active");
	
	
		$('.filter-authors li').each(function () {
			str = $(this).data('m');
	
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



//////////////////////////////////////////////////////
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
//////////////////// CARD PAGE /////////////////////////////////
function initFreeShippingProgress($) {
	let request = null;
	let timer = null;

	function updateFreeShippingProgress() {
		clearTimeout(timer);

		timer = setTimeout(function () {
			const $block = $('#cart-free-shipping-message');
			const url = $block.attr('data-url');

			if (!$block.length || !url) {
				return;
			}

			if (request) {
				request.abort();
			}

			request = $.ajax({
				url: url,
				type: 'POST',
				dataType: 'json'
			})
				.done(function (response) {
					if (response.success && response.data) {
						$('#cart-free-shipping-message')
							.html(response.data.html);
					}
				})
				.fail(function (xhr, status) {
					if (status !== 'abort') {
						console.error(
							'Ошибка обновления бесплатной доставки:',
							xhr.status,
							xhr.responseText
						);
					}
				});
		}, 100);
	}

	$(document.body)
		.off('.freeShippingProgress')
		.on(
			[
				'updated_wc_div.freeShippingProgress',
				'updated_cart_totals.freeShippingProgress',
				'removed_from_cart.freeShippingProgress',
				'applied_coupon.freeShippingProgress',
				'removed_coupon.freeShippingProgress'
			].join(' '),
			updateFreeShippingProgress
		);

	updateFreeShippingProgress();
}

jQuery(function ($) {
	initFreeShippingProgress($);
});

function initCartQuantity($) {
	let timer;

	$(document.body).on(
		'click.cartQuantity',
		'.woocommerce-cart-form .cart-qty-minus, .woocommerce-cart-form .cart-qty-plus',
		function () {
			const $input = $(this).closest('.quantity').find('input.qty');

			if (!$input.length || $input.prop('disabled') || $input.prop('readonly')) {
				return;
			}

			const input = $input[0];
			const previous = input.value;

			if ($(this).hasClass('cart-qty-plus')) {
				input.stepUp();
			} else {
				input.stepDown();
			}

			if (input.value !== previous) {
				$input.trigger('change');
			}
		}
	);

	$(document.body).on(
		'change.cartQuantity',
		'.woocommerce-cart-form input.qty',
		function () {
			clearTimeout(timer);

			timer = setTimeout(function () {
				$('.woocommerce-cart-form [name="update_cart"]')
					.prop('disabled', false)
					.trigger('click');
			}, 400);
		}
	);
}

jQuery(function ($) {
	initCartQuantity($);
});

jQuery(function ($) {
	$(document.body).on(
		'focus',
		'.cart-promo-form__input:not([readonly])',
		function () {
			$(this).attr('placeholder', 'Введіть промо-код');
		}
	);

	$(document.body).on(
		'blur',
		'.cart-promo-form__input:not([readonly])',
		function () {
			if (!$(this).val().trim()) {
				$(this).attr('placeholder', 'Маєте промо-код?');
			}
		}
	);
});

jQuery(function ($) {
	const media = window.matchMedia('(max-width: 1200px)');

	function moveCartRecommendations() {
		const $layout = $('.anyweb-cart-layout').first();
		const $main = $layout.children('.anyweb-cart-main');
		const $slider = $layout.find('.anyweb-cart-recommendations').first();

		if (!$slider.length) {
			return;
		}

		const $destination = media.matches ? $layout : $main;

		if ($slider.parent()[0] !== $destination[0]) {
			$slider.appendTo($destination);

			// Пересчитываем ширину после перемещения.
			$slider.find('.slick-initialized').each(function () {
				$(this).slick('setPosition');
			});
		}
	}

	media.addEventListener('change', moveCartRecommendations);

	$(document.body).on('updated_wc_div', moveCartRecommendations);

	moveCartRecommendations();
});

jQuery(function ($) {
	// console.log('Проверка рекомендаций подключена');

	function updateCartRecommendations() {
		const $state = $('.js-cart-recommendations-state').first();
		const $slider = $('.anyweb-cart-recommendations');

		if (!$state.length) {
			// console.warn('Не найден .js-cart-recommendations-state');
			return;
		}

		if (!$slider.length) {
			// console.warn('Не найден .anyweb-cart-recommendations');
			return;
		}

		const total = Number($state.attr('data-total'));

		if (!Number.isFinite(total)) {
			// console.warn('Некорректная сумма:', $state.attr('data-total'));
			return;
		}

		const shouldHide = total > 1000;

		$slider.toggleClass('is-hidden-by-total', shouldHide);

		// console.log('Сумма:', total, 'Слайдер скрыт:', shouldHide);

		if (!shouldHide && typeof $.fn.slick === 'function') {
			$slider.find('.slick-initialized').slick('setPosition');
		}
	}

	$(document.body)
		.off('.cartRecommendationsVisibility')
		.on(
			'updated_wc_div.cartRecommendationsVisibility ' +
			'updated_cart_totals.cartRecommendationsVisibility',
			updateCartRecommendations
		);

	updateCartRecommendations();
});

jQuery(function ($) {
	const formSelector = '#one-click-buying-form';

	function validatePhone(form, showError = false) {
		const $form = $(form);

		// Находим видимое поле телефона.
		const $phone = $form.find(
			'input.phone-field__input, input[type="tel"], input[name="phone"]:not([type="hidden"])'
		).first();

		if (!$phone.length) {
			console.warn('Поле телефона не найдено');
			return false;
		}

		const $holder = $phone.closest('.input-holder');
		let $error = $holder.find('.phone-error');

		// Создаём сообщение, если его ещё нет.
		if (!$error.length) {
			$error = $('<span>', {
				class: 'phone-error',
				text: ' в невірному форматі',
				hidden: true
			});

			$holder.find('label').first().append($error);
		}

		let digits = String($phone.val() || '').replace(/\D/g, '');

		// Поддерживаем ввод полного номера и номера без префикса.
		if (digits.length === 12 && digits.startsWith('380')) {
			digits = digits.slice(3);
		} else if (digits.length === 10 && digits.startsWith('0')) {
			digits = digits.slice(1);
		}

		const valid = /^[0-9]{9}$/.test(digits);
		const show = showError && !valid;

		$holder.toggleClass('has-phone-error', show);
		$error.prop('hidden', !show);
		$phone.attr('aria-invalid', String(show));

		$form.find('.phone-field__value')
			.val(valid ? '+380' + digits : '');

		const hasErrors =
			(showError && !valid) ||
			$form.find('.input-holder.error').length > 0;

		$form.find('.submit-btn, button[type="submit"], input[type="submit"]')
			.prop(
				'disabled',
				hasErrors || Boolean($form.data('sending'))
			);

		return valid;
	}

	function prepareForm() {
		$(formSelector).each(function () {
			// Ошибки показываем своими стилями вместо браузерной подсказки.
			this.noValidate = true;
			validatePhone(this, false);
		});
	}

	// Работает и для формы, загруженной в модальное окно через AJAX.
	$(document).on(
		'input.oneClickPhone change.oneClickPhone focusout.oneClickPhone',
		formSelector + ' input:not([type="hidden"]):not([type="submit"])',
		function () {
			const form = this.form;

			// Даём плагину завершить проверку имени.
			setTimeout(function () {
				validatePhone(form, true);
			}, 0);
		}
	);

	$(document).on('shown.bs.modal.oneClickPhone', prepareForm);

	// Сохраняем проверку остальных полей твоим плагином.
	$(formSelector).formValidation();

	$(document).on('submit.oneClickPhone', formSelector, function (e) {
		e.preventDefault();

		const $form = $(this);

		if ($form.data('sending')) {
			return;
		}

		if (!validatePhone(this, true)) {
			return;
		}

		if ($form.find('.input-holder.error').length) {
			return;
		}

		const fields = $form.serializeArray();
		const phone = $form.find('.phone-field__value').val();

		$form.data('sending', true);
		$form.find('.submit-btn').prop('disabled', true);

		$.ajax({
			url: soJsLet.ajaxurl,
			type: 'POST',
			data: {
				action: 'one_click_buying',
				nonce_code: soJsLet.nonce,
				filds: fields,

				// Для серверной проверки через $_POST['phone'].
				phone: phone
			}
		})
			.done(function (response) {
				let html;

				try {
					html = typeof response === 'string'
						? JSON.parse(response)
						: response;
				} catch (error) {
					console.error('Некорректный ответ сервера:', response);
					return;
				}

				// Твой текущий PHP возвращает HTML как JSON-строку.
				if (typeof html !== 'string') {
					console.error('Неожиданный ответ сервера:', html);
					return;
				}

				const $body = $form.closest('.modal-body');

				$body
					.html(html)
					.removeClass('modal-body--one-click')
					.addClass('modal-body--one-click-thx');

				setTimeout(function() {
					window.location.replace("/");
				}, 5000);
			})
			.fail(function (xhr) {
				if (xhr.status === 422) {
					const $holder = $form.find('.phone-holder');

					$holder.addClass('has-phone-error');
					$holder.find('.phone-error').prop('hidden', false);
				}

				console.error(
					'Ошибка отправки заказа:',
					xhr.status,
					xhr.responseJSON || xhr.responseText
				);
			})
			.always(function () {
				$form.data('sending', false);
				validatePhone($form[0], false);
			});
	});

	prepareForm();
});

