

$(document).ready(function(){

    $(".owl-carousel").owlCarousel({
      items: 3,
      margin: 10,
      autoplayTimeout: 2000,
      dots: false,
      mouseDrag: true,
      touchDrag: true,
      loop: true,
      nav: false,
      lazyLoad: true,
      autoplay: true,
      autoplayHoverPause: true,
      responsiveClass:true,
      responsive:{
          0:{
              items:1,
              nav:true
          },
          768:{
              items:2,
              nav:false
          },
          1024:{
              items:3,
              nav:true,
              loop:false
          }
      }
    });
    });