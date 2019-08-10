$(function(){

    //点击每一个小商品图片
    $(document).on("click",'.sub_little_box',function(){
        $(this).addClass('active').siblings().removeClass('active');
        var imgsrc = $(this).attr("src");
        $(".goods_big img").attr("src",imgsrc)
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