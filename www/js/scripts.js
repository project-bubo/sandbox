var menuState = false;

$(document).ready(function(){

    $("#tree").treeview({
            collapsed: true,
            animated: "medium",
            control:"#sidetreecontrol",
            /*prerendered: true,*/
            persist: "cookie"
    });

    getWidth(true);
    $('.w_menu').live('click',function(){
        var width = getWidth(false)+'px';
        $('.w_menu').stop().animate({width:width,queue:false},"fast");
    });
    $('.w_menu').live('mouseover', function(){
        var width = getWidth(false)+'px';
        if(!menuState){
            $('.w_menu').stop().animate({width:width,queue:false},"fast");
            menuState = true;
        }
    });
    $('.w_menu').live('mouseleave', function(){
        $('.w_menu').stop().animate({width:'199px',queue:false},"fast",function(){});
        menuState = false;
    });

    /*ajax */
    $("a.ajax").live("click", function (event) {
        event.preventDefault();
        $.get(this.href);
    });

    /* AJAXové odeslání formulářů */
    $("form.ajax, form.mfu").live("submit", function () {
        $(this).ajaxSubmit();
        return false;
    });

    $("form.ajax :submit").live("click", function () {
        $(this).ajaxSubmit();
        return false;
    });
    $('a[rel=external]').live('click',function(ev){
        window.open(this.href);
        ev.preventDefault();
        return false;
    });


    $("div.smooth_toolbar > a, form.form-with-smooth-buttons input:submit").button();
    $("a.smooth_button").livequery(function() {
        $(this).button();
    })

});

function getWidth(reload){
    var $wmenu = $('.w_menu').width();
    $('.w_menu').css('width','auto');

    $('.sidebar').css('width','auto');
    $('.w_menu li').css('width','auto');
    var maxWidth = 0;
    for(var i=0;i < $('.w_menu li a:visible').length;i++){
        var elm = $($('.w_menu li a:visible')[i]);
        var tmpWidth = elm.width() +   parseInt(elm.parent().css('padding-left').substring(0, elm.parent().css('padding-left').length-2))*elm.attr('depth');
        if(tmpWidth > maxWidth) maxWidth = tmpWidth;
    }
    if(maxWidth < 199){
        maxWidth = 199;
    }else{
        maxWidth += 25;
    }
    if(reload) $wmenu = '199px';
    $('.w_menu').css('width',$wmenu);
    $('.sidebar').css('width','199px');
    $('.w_menu li').css('width',maxWidth+'px');
    return maxWidth;
}