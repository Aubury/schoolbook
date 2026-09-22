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
            },
            ]
    });
})

function setSlickArrowsState($slider, slick) {
    if (!$slider.length || !slick) {
        return;
    }

    requestAnimationFrame(function () {
        const hasArrows =
            slick.options.arrows === true &&
            slick.slideCount > slick.options.slidesToShow &&
            $slider.find('.slick-prev, .slick-next').length > 0;

        // Класс непосредственно на слайдере
        $slider.toggleClass('has-slider-arrows', hasArrows);
        $slider.toggleClass('has-no-slider-arrows', !hasArrows);

        // При наличии специального родительского контейнера
        const $container = $slider.closest('[data-slick-container]');

        if ($container.length) {
            $container.toggleClass('has-slider-arrows', hasArrows);
            $container.toggleClass('has-no-slider-arrows', !hasArrows);
        }

        console.log({
            slider: $slider[0],
            slides: slick.slideCount,
            slidesToShow: slick.options.slidesToShow,
            arrowsAdded: hasArrows
        });
    });
}

function updateSlickArrowsState(event, slick) {
    setSlickArrowsState($(this), slick);
}

$(document).on(
    'init reInit breakpoint setPosition destroy',
    '.js-slick-slider',
    function (event, slick) {
        if (event.type === 'destroy') {
            const $slider = $(this);
            const $container = $slider.closest('[data-slick-container]');

            $slider
                .removeClass('has-slider-arrows')
                .addClass('has-no-slider-arrows');

            $container
                .removeClass('has-slider-arrows')
                .addClass('has-no-slider-arrows');

            return;
        }

        setSlickArrowsState($(this), slick);
    }
);

$(function() {

    $('.viewed-catalog-section .slider').not('.slick-initialized').slick({
        slidesToShow: 4,
        arrows: true,
        infinite: false,
        rows: 0,
        responsive: [
            {
                breakpoint: 1400,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                }
            },
            {
                breakpoint: 1030,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2,
                }
            },
            {
                breakpoint: 641,
                settings: {
                    arrows: true,
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    autoplay: false,
                }
            }]
    });
})

$(function() {

    $('.recommendations-product-page .slider').not('.slick-initialized').slick({
        slidesToShow: 4,
        arrows: true,
        infinite: false,
        rows: 0,
        responsive: [
            {
                breakpoint: 1400,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                }
            },
            {
                breakpoint: 1030,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2,
                }
            },
            {
                breakpoint: 641,
                settings: {
                    arrows: true,
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    autoplay: false,
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
                breakpoint: 1400,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    gap: 32
                }
            },
            {
                breakpoint: 1030,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    gap: 24
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
    $('.preparing').not('.slick-initialized').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        infinite: false,
        variableWidth: false,
        arrows: true,
        responsive: [
            {
                breakpoint: 1300,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    infinite: false,
                    arrows: false,
                    gap: 50
                }
            },
            {
                breakpoint: 1030,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    gap: 50
                }
            },

            {
                breakpoint: 992,
                settings: {
                    arrows: true,
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    autoplay: false,
                    autoplaySpeed: 2000,
                }
            },

            ]
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
        slidesToShow: 3,
        vertical: true,
        verticalSwiping: true,
        asNavFor: '.product-item-detail-slider-images-container',
        focusOnSelect: true,
        rows: 0,
        responsive: [
            {
                breakpoint: 770,
                settings: {
                    vertical: false,
                    verticalSwiping: false,
                }
            }]
    });
})

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