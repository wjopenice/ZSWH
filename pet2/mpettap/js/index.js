$.init();
$(document).on("click", ".icon-icon-", function() {
    $.openPanel("#panel-js-demo");
});
//回到顶部
$(function(){
    $("#goTop").click(function () {
        $('.content').scrollTop(0);
    });
});
