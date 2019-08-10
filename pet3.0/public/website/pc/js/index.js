$(function () {
    $('.swiper-container').height($(window).height());
    new Swiper('.swiper-container', {
        direction: 'vertical',
        mousewheelControl : true,
        nextButton: '.next'
    });

});
