$(document).ready(function () {
    $(function () {

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

        $('.recommendations .slider').not('.slick-initialized').slick({
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
                        arrows: false,
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        autoplay: true,
                        autoplaySpeed: 2000,
                    }
                }]
        });

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

        $('.new-books-slider .slider').not('.slick-initialized').slick({
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
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        arrows: false,
                        autoplay: true,
                        autoplaySpeed: 2000,
                    }
                }]
        });

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
});

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