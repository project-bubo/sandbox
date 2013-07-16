var $select = '#frmpageForm-layout_id';

jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", (($(window).height() - this.outerHeight()) / 2) + $(window).scrollTop() + "px");
    this.css("left", (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft() + "px");
    return this;
}

$(document).ready(function(){
    $($select).parent().append("<img src='"+path+"/images/layout.png' id='layoutBtn' alt='Změnit rozložení' title='Změnit rozložení' />");
    $('#layoutBtn').click(function(){
        $('.layout_overlay').remove();
        $('body').append("<div class='layout_overlay'> </div>");
        $('.layout_overlay').fadeIn();
        //$('body').append("<div id='layout_workspace'> </div>");
        var $width = $('#layout_workspace > div').css('width');
        var $height = $('#layout_workspace > div').css('height');
        $width = '800px';
        $height = '200px';
        $('#layout_workspace').css({width:$width,height:$height,display:'none'});
        $('#layout_workspace').center();
        $('#layout_workspace').css({width:'0px',height:'5px',display:'block'});
        $('#layout_workspace').animate({width:$width,queue:false},'slow');
        $('#layout_workspace').animate({height:$height,queue:false},'slow',function() {$('#layout_workspace').center();});
        $(window).resize(function(){
            $('#layout_workspace').center();
        });
        //$('#layout_workspace').animate(function(){$(this).center()});
        $('#layout_workspace > div').fadeIn('slow');
        $('.layout_overlay').click(function(){
            lClose();
        });
    });
});

function lClose(){
    $('#layout_workspace > div').hide();
    $('#layout_workspace').stop().css({height:'5px',width:'0px'});
    $('.layout_overlay').stop().fadeOut();
}

function setPageSelectValue(val){
    $($select).val(val);
    lClose();
    return false;
}