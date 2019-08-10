$(function(){
    $(".sub_detail_tab").click(function(){
        var index = $(this).index();
        $(this).addClass("active").siblings().removeClass("active");
        $(".detail_li").eq(index).addClass('active').siblings().removeClass('active');
    });

    //收藏
    $(document).on("click",'.btn_fav',function(){
        var isActive = $(this).hasClass('active');
        if(!isActive){
            $(this).addClass('active');
        }else{
            $(this).removeClass('active');
        }
    });

});