//dom ready
$(function() {

    _js_init();

    $('a.remove-media-item').live('click', function(e) {
        e.preventDefault();
        $('div.media-container-content', $(this).parent()).hide();
        $(this).hide();
        $cid = $(this).attr('cid');
        $('#'+$cid).val('');
    });

    $("a.colorbox").live('click', function() {

        $.colorbox({
            href:$(this).attr('href')
        });
        return false;
    });

    $(document).bind('cbox_open', function() {
        $('body').css({ overflow: 'hidden' });
    }).bind('cbox_closed', function() {
        $('body').css({ overflow: '' });
    });

    //progress bar
    $('.vd-progressbar').progressbar({ value: 25 });

	//buttons on item hover
	$('.gridLayout .vd-item').live('mouseenter mouseleave',function(e){
		if ( e.type == 'mouseenter') {
			$(this).find('.itemContextMenuButton').stop(true,true).fadeIn(100);
		} else {
			$(this).find('.itemContextMenuButton').stop(true,true).hide();;
		}
	});

    //context menu
    $('.itemContextMenuButton').live('click',function(e){
        e.preventDefault();
        e.stopPropagation();

        if ( $(this).hasClass('opened') ) {
            $('.itemContextMenuButton').removeClass('opened').next('.vd-contextMenu').hide();
        } else {        
            $('.itemContextMenuButton').removeClass('opened').next('.vd-contextMenu').hide();
            $(this).addClass('opened').next('.vd-contextMenu').show();
        };
    });

    $('html, body').live('click',function() {
        $('.vd-contextMenu').hide();
        $('.itemContextMenuButton').removeClass("opened");
    });

    //layout switcher
    if ($('.vd-content').hasClass('gridLayout') == true) {
        $('.vd-drawStyleSwitch').find('.icon-th').addClass('current')
    } else if ($('.vd-content').hasClass('tableLayout') == true) {
        $('.vd-drawStyleSwitch').find('.icon-th-list').addClass('current')
    };
    $('.vd-drawStyleSwitch i').live('click', function() {
        if ( $(this).hasClass('icon-th') == true ) {
            $('i.icon-th-list').removeClass('current');
            $(this).addClass('current');
            $('.vd-content').removeClass('tableLayout').addClass('gridLayout');
        } else {
            $('i.icon-th').removeClass('current');
            $(this).addClass('current');
            $('.vd-content').removeClass('gridLayout').addClass('tableLayout');
        };
    });

	//select item
	$('.vd-item').live('click', function() {
		$('.vd-item').removeClass('selected');
		$(this).addClass('selected');
	});

    //Item proportions
    $('.vd-itemProportions input').live('keypress',function(e) {
        if (e.which == 13) {
            $(this).blur();
        } 
    });

    $('.vd-itemProportions').find('input').live('click',function(){
        $(this).select();
    });

	//modal dialog
	$('.vd-popupContainer').hide();

	$('.vd-popupWrapper').css({top: '-50%'});

	$('.openDialog').live('click',function() {
		openDialog();
	});
	$('.closeDialog, .vd-popupOverlay').live('click',function() {
		closeDialog();
	});
});
//dom ready end

//functions
function openDialog() {
	$('.vd-popupContainer').fadeIn(300).find('.vd-popupWrapper').delay(300).css({top: '50%'},150).find('input[type="text"]').focus();
    var popupHeight = $('.vd-popupWrapper').outerHeight();
    $('.vd-popupWrapper').css({'margin-top': '-'+popupHeight / 2+'px'});
}

function closeDialog() {
	$('.vd-popupContainer').fadeOut(300).find('.vd-popupWrapper').css({top: '-50%'},100);
}


function _ajaxUpdateSnippet(snippetName, state, mediaData) {
    //console.log(snippetName);
    
    switch(snippetName) {
        case 'snippet-media-contentPane':
            mediaJsConfig.offset = mediaJsConfig.initialOffset;
            mediaJsConfig.section = state['media-section'];
            mediaJsConfig.folderId = state['media-folderId'];
            
            //_ajaxUpateScrollBar();
            _ajaxUpateSortable();
            
            // contentType: null | image-detail | file-detail
            if (mediaData && mediaData.contentType) {
                switch (mediaData.contentType) {
                    case 'image-detail':
                        _ajaxImageDetail();
                        _ajaxLabelTabs();
                        break;
                    case 'file-detail':
                        _ajaxLabelTabs();
                        break;
                }
            }

            break;

        case 'snippet-media-content-popUp':
            _ajaxLabelTabs();
            break;
        
    }
    
}

//init tooltips
function _ajaxUpateTooltip() {
    $('.tp').tooltip({
            position:'top',
            opacity:1,
            borderRadius: 3,
            transition: 'fade',
            speed: 300
    });
}

//init scrollbar
function _ajaxUpateScrollBar() {
    $(".vd-content").mCustomScrollbar("update");
//    $('.vd-content').mCustomScrollbar({
//        set_width: 750,
//        set_height:470,
//        mouseWheelPixels: 154,
//        scrollInertia: 170,
//        advanced: {
//            updateOnContentResize: true
//        },
//        callbacks: {
//            onTotalScroll: function() {
//                var $data = {
//                          'media-content-offset' :  mediaJsConfig.offset,
//                          'media-content-folderId': mediaJsConfig.folderId,
//                          'media-content-section': mediaJsConfig.section
//                };
//                
//                if (mediaJsConfig.state) {
//                    $data = mediaJsConfig.state
//                }
//                
//                $.get(mediaJsConfig.link, $data, function(payload) {
//                    $.nette.success(payload);
//                })
//            }
//        }
//    });
}


//init sortable
function _ajaxUpateSortable() {
    $('.vd-item').disableSelection();

    $('.vd-content').sortable({
        items: '.vd-sortMe',
        cancel: '.deleteButton, .vd-contextMenu, .itemContextMenuButton',
        revert: 200,
        tolerance: 'pointer',
        forcePlaceholderSize: true,
        change:function(e,ui){
            var p=ui.position.top,
                h=ui.helper.outerHeight(true),
                s=ui.placeholder.position().top,
                elem=$(".vd-content .mCustomScrollBox")[0],
                elemHeight=$(".vd-content .mCustomScrollBox").height();
                pos=findPos(elem),
                mouseCoordsY=+e.originalEvent.pageY-pos[0];
            if(mouseCoordsY<h || mouseCoordsY>elemHeight-h){
                $(".vd-content").mCustomScrollbar("scrollTo",p-(elemHeight/2));
            }
        }
    });
    function findPos(obj){
        var curleft=curtop=0;
        if (obj.offsetParent){
            curleft=obj.offsetLeft
            curtop=obj.offsetTop
            while(obj=obj.offsetParent){
                curleft+=obj.offsetLeft
                curtop+=obj.offsetTop
            }
        }
        return [curtop,curleft];
    }
}


function findPos(obj){
    var curleft=curtop=0;
    if (obj.offsetParent){
        curleft=obj.offsetLeft
        curtop=obj.offsetTop
        while(obj=obj.offsetParent){
            curleft+=obj.offsetLeft
            curtop+=obj.offsetTop
        }
    }
    return [curtop,curleft];
}

//item detail
function _ajaxImageDetail() {
    $('.vd-itemPreview.jCrop img').Jcrop();
    //image proportions
    if ($('.vd-itemDetail').length > 0) {

        var imageRel = $('.vd-itemPreview').find('img').attr('rel');
        
        if (imageRel != undefined) {
        
            var imageSize = imageRel.split('x');

            var maxWidth = imageSize[0];
            var maxHeight = imageSize[1];

    //        $('.vd-itemProportions').find('.itemWidth').val(imageSize[0]);
    //        $('.vd-itemProportions').find('.itemHeight').val(imageSize[1]);


            $('.vd-bigPreview').append('<span class="valIndicator"></span>');
            $('.valIndicator').hide();
            $('.vd-rangeSlider').noUiSlider({
                handles: 1,
                range: [5, 100],
                start: 100,
                step: 5,
                slide: function() {
                    var sliderValue = $('.vd-rangeSlider').val();
                    $('.vd-bigPreview').find('.valIndicator').html(sliderValue+'%');

                    _imageDetailPreviewUpdate('rangeSlider');
                    $('.itemWidth').val(Math.round(maxWidth * (sliderValue / 100)));
                    $('.itemWidth').blur();
                }
            });

            currentWidth = $('.vd-itemProportions input.itemWidth').val();
            $('.vd-rangeSlider').val(Math.round((currentWidth/maxWidth * 100)));

            function resetImage() {
                $('.itemHeight').val(imageSize[1]);
                $('.itemWidth').val(imageSize[0]);
                $('.vd-rangeSlider').val(100);
                $('.vd-bigPreview').show().delay(3500).fadeOut(100);
                $('.vd-bigPreview img').css({
                    width:imageSize[0],
                    height:imageSize[1]
                });
            }
            $('.resetImage').live('click',function(){
            $('.itemWidth').val(imageSize[0]);
                resetImage();
            });

            $('.vd-itemProportions input').blur(function(){

                if (isNaN(this.value)) {
                    alert('Musíte zadat číslo!');
                };

                var currentWidth = parseInt($('.itemWidth').val(), 10);
                var currentHeight = parseInt($('.itemHeight').val(), 10);

                //console.log('CURR: '+currentWidth+' MAX: '+maxWidth);
                //console.log(currentWidth <= maxWidth);
                //var sliderValue = $('.vd-rangeSlider').val();
                //var sliderLeft = $('.vd-rangeSlider a').css('left');
                //    sliderLeft = parseFloat(sliderLeft);
                //    $('.vd-rangeSlider').find('.valIndicator').html(sliderValue+'%').css({
                //        left: sliderLeft + 35
                //    });

                if ( $(this).hasClass('itemWidth') ) {
                    if (currentWidth <= maxWidth) {

                        var newHeight = Math.round(maxHeight / maxWidth * currentWidth);
                        $('.itemHeight').val(newHeight);
                        var newWidth = $('.itemWidth').val();
                        _imageDetailPreviewUpdate('input',currentWidth,maxWidth);
                        $('.vd-bigPreview img').css({
                            width:newWidth,
                            height:newHeight
                        });

                    } else {
                        resetImage();
                        alert('Nově nastavená šířka je větší než původní!');
                    };
                };

                if ( $(this).hasClass('itemHeight') ) {
                    if (currentHeight <= maxHeight) {
                        var newWidth = Math.round(maxWidth / maxHeight * currentHeight);
                        $('.itemWidth').val(newWidth);
                        var newHeight = $('.itemHeight').val();


                        _imageDetailPreviewUpdate('input',currentHeight,maxHeight);
                        $('.vd-bigPreview img').css({
                            width:newWidth,
                            height:newHeight
                        });


                    } else {
                        resetImage();
                        alert('Nově nastavená výška je větší než původní!');
                    };
                }; 
                if ( currentWidth <= 10 || currentHeight <= 10 || !this.value  ) {
                    resetImage()
                    alert('Obrázek je příliš malý!');
                }

            });
        };
    }
}

function _imageDetailPreviewUpdate(type,value1,value2) {

    var actualPercentage = Math.round( (value1/value2 * 100) );
    $('.vd-rangeSlider').val(actualPercentage);
    
    switch(type) {
        case 'rangeSlider':
            $('.valIndicator,.vd-bigPreview').show().delay(3500).fadeOut(100);
        break;
        case 'input':
            $('.vd-bigPreview').show().delay(3500).fadeOut(100);
        break;
    }
}

function _ajaxLabelTabs() {
    //tabs
    var currentVal = 0;
    var leng = $('.vd-tabs select').length;
    if ( $('.vd-tabs select').length > 0 ) {
        currentVal = $('.vd-tabs select').val();
    };

    $('.vd-tabs').find('.tab-content').hide().eq(currentVal).show();
    $('.vd-tabs').find('ul li').eq(0).addClass('current');

    //tabs with button
    $('.vd-tabs ul li').live('click',function(e){
        e.preventDefault();
        $('.vd-tabs ul li').removeClass('current');
        $(this).addClass('current');
        var currentTab = $('.vd-tabs ul li.current').index();
        $('.vd-tabs').find('.tab-content').hide().eq(currentTab).show();
    });


    //tabs with select
    $('.vd-tabs select').live('change',function(){
        currentVal = $(this).val();
        $('.vd-tabs').find('.tab-content').hide().eq(currentVal).show();
    });

}

function _itemSort() {
    $('.vd-sortbar').disableSelection();
    $('.vd-sortButtons li').live('click',function(){
        $('.vd-sortButtons li').removeClass('toggled').find('i').removeClass().addClass('icon-sort');

        var sortMethod = $(this).data('sort');

        if ($(this).hasClass('asc')) {
            $(this).addClass('toggled').addClass('desc').removeClass('asc').find('i').removeClass().addClass('icon-caret-up');
            $('.vd-content .vd-item').tsort({data:'mediatype',order:'asc'},{data:sortMethod,order:'desc'});
        } else {
            $(this).addClass('toggled').addClass('asc').removeClass('desc').find('i').removeClass().addClass('icon-caret-down');
            $('.vd-content .vd-item').tsort({data:'mediatype',order:'asc'},{data:sortMethod,order:'asc'});
        }
    });
}

function _js_init() {
    _ajaxUpateTooltip();
    //_ajaxUpateScrollBar();
    _ajaxUpateSortable();
    _itemSort();
    _ajaxImageDetail();
    _ajaxLabelTabs();

    
};


/* ------- */
/* PLUGINS */
/* ------- */

//rTooltip
;(function(e){var t={init:function(t){return this.each(function(){var n={position:"top",opacity:1,borderRadius:5,maxWidth:0,transition:"fade",speed:"250"};if(t){e.extend(n,t)}var r=this;var i=e(this);i.settings=n;var s=i.settings.position;var o=i.settings.opacity;var u=i.settings.borderRadius;var a=i.settings.maxWidth;var f=i.settings.transition;var l=i.settings.speed;this.createTp=function(){var t=i.attr("title");if(t!=null){i.attr("rel",t);i.removeAttr("title")}var n=i.attr("rel");e("body").append('<div class="tooltip">'+n+"</div>");e(".tooltip").css({display:"none",position:"absolute",opacity:o,"border-radius":u});if(a!=0){e(".tooltip").css({"max-width":a})}if(f=="fade"){e(".tooltip").delay(500).fadeIn(l)}else if(f=="none"){e(".tooltip").delay(500).show()}};this.destroyTp=function(){if(f=="fade"){e(".tooltip").remove()}else{e(".tooltip").remove()}};i.bind({mouseenter:function(){r.createTp()},mouseleave:function(){r.destroyTp()},mousemove:function(t){var n=t.pageX;var r=t.pageY;var i=0;var o=0;if(s=="top"){i=-40;o=-15}else if(s=="bottom"){i=+20;o}else if(s=="left"){var u=e(".tooltip").outerWidth();o=-u-10;i=-15}else if(s=="right"){o=+15;i=-15}e(".tooltip").css({top:r+i,left:n+o})}})})}};e.fn.tooltip=function(n){if(t[n]){return t[n].apply(this,Array.prototype.slice.call(arguments,1))}else if(typeof n==="object"||!n){return t.init.apply(this,arguments)}else{e.error("Method "+n+" does not exist on jQuery.tooltip")}}})(jQuery);
//scrollbar

//sorting
;(function(b){var a={init:function(c){var e={set_width:false,set_height:false,horizontalScroll:false,scrollInertia:550,scrollEasing:"easeOutCirc",mouseWheel:"pixels",mouseWheelPixels:60,autoDraggerLength:true,scrollButtons:{enable:false,scrollType:"continuous",scrollSpeed:20,scrollAmount:40},advanced:{updateOnBrowserResize:true,updateOnContentResize:false,autoExpandHorizontalScroll:false,autoScrollOnFocus:true},callbacks:{onScrollStart:function(){},onScroll:function(){},onTotalScroll:function(){},onTotalScrollBack:function(){},onTotalScrollOffset:0,whileScrolling:false,whileScrollingInterval:30}},c=b.extend(true,e,c);b(document).data("mCS-is-touch-device",false);if(d()){b(document).data("mCS-is-touch-device",true)}function d(){return !!("ontouchstart" in window)?1:0}return this.each(function(){var k=b(this);if(c.set_width){k.css("width",c.set_width)}if(c.set_height){k.css("height",c.set_height)}if(!b(document).data("mCustomScrollbar-index")){b(document).data("mCustomScrollbar-index","1")}else{var p=parseInt(b(document).data("mCustomScrollbar-index"));b(document).data("mCustomScrollbar-index",p+1)}k.wrapInner("<div class='mCustomScrollBox' id='mCSB_"+b(document).data("mCustomScrollbar-index")+"' style='position:relative; height:100%; overflow:hidden; max-width:100%;' />").addClass("mCustomScrollbar _mCS_"+b(document).data("mCustomScrollbar-index"));var f=k.children(".mCustomScrollBox");if(c.horizontalScroll){f.addClass("mCSB_horizontal").wrapInner("<div class='mCSB_h_wrapper' style='position:relative; left:0; width:999999px;' />");var i=f.children(".mCSB_h_wrapper");i.wrapInner("<div class='mCSB_container' style='position:absolute; left:0;' />").children(".mCSB_container").css({width:i.children().outerWidth(),position:"relative"}).unwrap()}else{f.wrapInner("<div class='mCSB_container' style='position:relative; top:0;' />")}var m=f.children(".mCSB_container");if(b(document).data("mCS-is-touch-device")){m.addClass("mCS_touch")}m.after("<div class='mCSB_scrollTools' style='position:absolute;'><div class='mCSB_draggerContainer' style='position:relative;'><div class='mCSB_dragger' style='position:absolute;'><div class='mCSB_dragger_bar' style='position:relative;'></div></div><div class='mCSB_draggerRail'></div></div></div>");var j=f.children(".mCSB_scrollTools"),g=j.children(".mCSB_draggerContainer"),o=g.children(".mCSB_dragger");if(c.horizontalScroll){o.data("minDraggerWidth",o.width())}else{o.data("minDraggerHeight",o.height())}if(c.scrollButtons.enable){if(c.horizontalScroll){j.prepend("<a class='mCSB_buttonLeft' style='display:block; position:relative;'></a>").append("<a class='mCSB_buttonRight' style='display:block; position:relative;'></a>")}else{j.prepend("<a class='mCSB_buttonUp' style='display:block; position:relative;'></a>").append("<a class='mCSB_buttonDown' style='display:block; position:relative;'></a>")}}f.bind("scroll",function(){if(!k.is(".mCS_disabled")){f.scrollTop(0).scrollLeft(0)}});k.data({mCS_Init:true,horizontalScroll:c.horizontalScroll,scrollInertia:c.scrollInertia,scrollEasing:c.scrollEasing,mouseWheel:c.mouseWheel,mouseWheelPixels:c.mouseWheelPixels,autoDraggerLength:c.autoDraggerLength,scrollButtons_enable:c.scrollButtons.enable,scrollButtons_scrollType:c.scrollButtons.scrollType,scrollButtons_scrollSpeed:c.scrollButtons.scrollSpeed,scrollButtons_scrollAmount:c.scrollButtons.scrollAmount,autoExpandHorizontalScroll:c.advanced.autoExpandHorizontalScroll,autoScrollOnFocus:c.advanced.autoScrollOnFocus,onScrollStart_Callback:c.callbacks.onScrollStart,onScroll_Callback:c.callbacks.onScroll,onTotalScroll_Callback:c.callbacks.onTotalScroll,onTotalScrollBack_Callback:c.callbacks.onTotalScrollBack,onTotalScroll_Offset:c.callbacks.onTotalScrollOffset,whileScrolling_Callback:c.callbacks.whileScrolling,whileScrolling_Interval:c.callbacks.whileScrollingInterval,bindEvent_scrollbar_click:false,bindEvent_mousewheel:false,bindEvent_focusin:false,bindEvent_buttonsContinuous_y:false,bindEvent_buttonsContinuous_x:false,bindEvent_buttonsPixels_y:false,bindEvent_buttonsPixels_x:false,bindEvent_scrollbar_touch:false,bindEvent_content_touch:false,mCSB_buttonScrollRight:false,mCSB_buttonScrollLeft:false,mCSB_buttonScrollDown:false,mCSB_buttonScrollUp:false,whileScrolling:false}).mCustomScrollbar("update");if(c.horizontalScroll){if(k.css("max-width")!=="none"){if(!c.advanced.updateOnContentResize){c.advanced.updateOnContentResize=true}k.data({mCS_maxWidth:parseInt(k.css("max-width")),mCS_maxWidth_Interval:setInterval(function(){if(m.outerWidth()>k.data("mCS_maxWidth")){clearInterval(k.data("mCS_maxWidth_Interval"));k.mCustomScrollbar("update")}},150)})}}else{if(k.css("max-height")!=="none"){k.data({mCS_maxHeight:parseInt(k.css("max-height")),mCS_maxHeight_Interval:setInterval(function(){f.css("max-height",k.data("mCS_maxHeight"));if(m.outerHeight()>k.data("mCS_maxHeight")){clearInterval(k.data("mCS_maxHeight_Interval"));k.mCustomScrollbar("update")}},150)})}}if(c.advanced.updateOnBrowserResize){var h;b(window).resize(function(){if(h){clearTimeout(h)}h=setTimeout(function(){if(!k.is(".mCS_disabled")&&!k.is(".mCS_destroyed")){k.mCustomScrollbar("update")}},150)})}if(c.advanced.updateOnContentResize){var n;if(c.horizontalScroll){var l=m.outerWidth()}else{var l=m.outerHeight()}n=setInterval(function(){if(c.horizontalScroll){if(c.advanced.autoExpandHorizontalScroll){m.css({position:"absolute",width:"auto"}).wrap("<div class='mCSB_h_wrapper' style='position:relative; left:0; width:999999px;' />").css({width:m.outerWidth(),position:"relative"}).unwrap()}var q=m.outerWidth()}else{var q=m.outerHeight()}if(q!=l){k.mCustomScrollbar("update");l=q}},300)}})},update:function(){var l=b(this),i=l.children(".mCustomScrollBox"),o=i.children(".mCSB_container");o.removeClass("mCS_no_scrollbar");l.removeClass("mCS_disabled mCS_destroyed");i.scrollTop(0).scrollLeft(0);var w=i.children(".mCSB_scrollTools"),m=w.children(".mCSB_draggerContainer"),k=m.children(".mCSB_dragger");if(l.data("horizontalScroll")){var y=w.children(".mCSB_buttonLeft"),r=w.children(".mCSB_buttonRight"),d=i.width();if(l.data("autoExpandHorizontalScroll")){o.css({position:"absolute",width:"auto"}).wrap("<div class='mCSB_h_wrapper' style='position:relative; left:0; width:999999px;' />").css({width:o.outerWidth(),position:"relative"}).unwrap()}var x=o.outerWidth()}else{var u=w.children(".mCSB_buttonUp"),e=w.children(".mCSB_buttonDown"),p=i.height(),g=o.outerHeight()}if(g>p&&!l.data("horizontalScroll")){w.css("display","block");var q=m.height();if(l.data("autoDraggerLength")){var s=Math.round(p/g*q),j=k.data("minDraggerHeight");if(s<=j){k.css({height:j})}else{if(s>=q-10){var n=q-10;k.css({height:n})}else{k.css({height:s})}}k.children(".mCSB_dragger_bar").css({"line-height":k.height()+"px"})}var z=k.height(),v=(g-p)/(q-z);l.data("scrollAmount",v).mCustomScrollbar("scrolling",i,o,m,k,u,e,y,r);var B=Math.abs(Math.round(o.position().top));l.mCustomScrollbar("scrollTo",B,{callback:false})}else{if(x>d&&l.data("horizontalScroll")){w.css("display","block");var f=m.width();if(l.data("autoDraggerLength")){var h=Math.round(d/x*f),A=k.data("minDraggerWidth");if(h<=A){k.css({width:A})}else{if(h>=f-10){var c=f-10;k.css({width:c})}else{k.css({width:h})}}}var t=k.width(),v=(x-d)/(f-t);l.data("scrollAmount",v).mCustomScrollbar("scrolling",i,o,m,k,u,e,y,r);var B=Math.abs(Math.round(o.position().left));l.mCustomScrollbar("scrollTo",B,{callback:false})}else{i.unbind("mousewheel focusin");if(l.data("horizontalScroll")){k.add(o).css("left",0)}else{k.add(o).css("top",0)}w.css("display","none");o.addClass("mCS_no_scrollbar");l.data({bindEvent_mousewheel:false,bindEvent_focusin:false})}}},scrolling:function(f,p,m,h,w,c,z,u){var l=b(this);l.mCustomScrollbar("callbacks","whileScrolling");if(!h.hasClass("ui-draggable")){if(l.data("horizontalScroll")){var g="x"}else{var g="y"}h.draggable({axis:g,containment:"parent",drag:function(x,y){l.mCustomScrollbar("scroll");h.addClass("mCSB_dragger_onDrag")},stop:function(x,y){h.removeClass("mCSB_dragger_onDrag")}})}if(!l.data("bindEvent_scrollbar_click")){m.bind("click",function(E){if(l.data("horizontalScroll")){var x=(E.pageX-m.offset().left);if(x<h.position().left||x>(h.position().left+h.width())){var y=x;if(y>=m.width()-h.width()){y=m.width()-h.width()}h.css("left",y);l.mCustomScrollbar("scroll")}}else{var x=(E.pageY-m.offset().top);if(x<h.position().top||x>(h.position().top+h.height())){var y=x;if(y>=m.height()-h.height()){y=m.height()-h.height()}h.css("top",y);l.mCustomScrollbar("scroll")}}});l.data({bindEvent_scrollbar_click:true})}if(l.data("mouseWheel")){var v=l.data("mouseWheel");if(l.data("mouseWheel")==="auto"){v=8;var o=navigator.userAgent;if(o.indexOf("Mac")!=-1&&o.indexOf("Safari")!=-1&&o.indexOf("AppleWebKit")!=-1&&o.indexOf("Chrome")==-1){v=1}}if(!l.data("bindEvent_mousewheel")){f.bind("mousewheel",function(F,L){F.preventDefault();var K=Math.abs(L*v);if(l.data("horizontalScroll")){if(l.data("mouseWheel")==="pixels"){if(L<0){L=-1}else{L=1}var I=Math.abs(Math.round(p.position().left))-(L*l.data("mouseWheelPixels"));l.mCustomScrollbar("scrollTo",I)}else{var E=h.position().left-(L*K);h.css("left",E);if(h.position().left<0){h.css("left",0)}var J=m.width(),H=h.width();if(h.position().left>J-H){h.css("left",J-H)}l.mCustomScrollbar("scroll")}}else{if(l.data("mouseWheel")==="pixels"){if(L<0){L=-1}else{L=1}var I=Math.abs(Math.round(p.position().top))-(L*l.data("mouseWheelPixels"));l.mCustomScrollbar("scrollTo",I)}else{var x=h.position().top-(L*K);h.css("top",x);if(h.position().top<0){h.css("top",0)}var G=m.height(),y=h.height();if(h.position().top>G-y){h.css("top",G-y)}l.mCustomScrollbar("scroll")}}});l.data({bindEvent_mousewheel:true})}}if(l.data("scrollButtons_enable")){if(l.data("scrollButtons_scrollType")==="pixels"){var C;if(b.browser.msie&&parseInt(b.browser.version)<9){l.data("scrollInertia",0)}if(l.data("horizontalScroll")){u.add(z).unbind("mousedown touchstart onmsgesturestart mouseup mouseout touchend onmsgestureend",i,e);l.data({bindEvent_buttonsContinuous_x:false});if(!l.data("bindEvent_buttonsPixels_x")){u.bind("click",function(x){x.preventDefault();if(!p.is(":animated")){C=Math.abs(p.position().left)+l.data("scrollButtons_scrollAmount");l.mCustomScrollbar("scrollTo",C)}});z.bind("click",function(x){x.preventDefault();if(!p.is(":animated")){C=Math.abs(p.position().left)-l.data("scrollButtons_scrollAmount");if(p.position().left>=-l.data("scrollButtons_scrollAmount")){C="left"}l.mCustomScrollbar("scrollTo",C)}});l.data({bindEvent_buttonsPixels_x:true})}}else{c.add(w).unbind("mousedown touchstart onmsgesturestart mouseup mouseout touchend onmsgestureend",i,e);l.data({bindEvent_buttonsContinuous_y:false});if(!l.data("bindEvent_buttonsPixels_y")){c.bind("click",function(x){x.preventDefault();if(!p.is(":animated")){C=Math.abs(p.position().top)+l.data("scrollButtons_scrollAmount");l.mCustomScrollbar("scrollTo",C)}});w.bind("click",function(x){x.preventDefault();if(!p.is(":animated")){C=Math.abs(p.position().top)-l.data("scrollButtons_scrollAmount");if(p.position().top>=-l.data("scrollButtons_scrollAmount")){C="top"}l.mCustomScrollbar("scrollTo",C)}});l.data({bindEvent_buttonsPixels_y:true})}}}else{if(l.data("horizontalScroll")){u.add(z).unbind("click");l.data({bindEvent_buttonsPixels_x:false});if(!l.data("bindEvent_buttonsContinuous_x")){u.bind("mousedown touchstart onmsgesturestart",function(x){x.preventDefault();x.stopPropagation();l.data({mCSB_buttonScrollRight:setInterval(function(){var y=Math.round((Math.abs(Math.round(p.position().left))+l.data("scrollButtons_scrollSpeed"))/l.data("scrollAmount"));l.mCustomScrollbar("scrollTo",y,{moveDragger:true})},30)})});var i=function(x){x.preventDefault();x.stopPropagation();clearInterval(l.data("mCSB_buttonScrollRight"))};u.bind("mouseup touchend onmsgestureend mouseout",i);z.bind("mousedown touchstart onmsgesturestart",function(x){x.preventDefault();x.stopPropagation();l.data({mCSB_buttonScrollLeft:setInterval(function(){var y=Math.round((Math.abs(Math.round(p.position().left))-l.data("scrollButtons_scrollSpeed"))/l.data("scrollAmount"));l.mCustomScrollbar("scrollTo",y,{moveDragger:true})},30)})});var e=function(x){x.preventDefault();x.stopPropagation();clearInterval(l.data("mCSB_buttonScrollLeft"))};z.bind("mouseup touchend onmsgestureend mouseout",e);l.data({bindEvent_buttonsContinuous_x:true})}}else{c.add(w).unbind("click");l.data({bindEvent_buttonsPixels_y:false});if(!l.data("bindEvent_buttonsContinuous_y")){c.bind("mousedown touchstart onmsgesturestart",function(x){x.preventDefault();x.stopPropagation();l.data({mCSB_buttonScrollDown:setInterval(function(){var y=Math.round((Math.abs(Math.round(p.position().top))+l.data("scrollButtons_scrollSpeed"))/l.data("scrollAmount"));l.mCustomScrollbar("scrollTo",y,{moveDragger:true})},30)})});var t=function(x){x.preventDefault();x.stopPropagation();clearInterval(l.data("mCSB_buttonScrollDown"))};c.bind("mouseup touchend onmsgestureend mouseout",t);w.bind("mousedown touchstart onmsgesturestart",function(x){x.preventDefault();x.stopPropagation();l.data({mCSB_buttonScrollUp:setInterval(function(){var y=Math.round((Math.abs(Math.round(p.position().top))-l.data("scrollButtons_scrollSpeed"))/l.data("scrollAmount"));l.mCustomScrollbar("scrollTo",y,{moveDragger:true})},30)})});var d=function(x){x.preventDefault();x.stopPropagation();clearInterval(l.data("mCSB_buttonScrollUp"))};w.bind("mouseup touchend onmsgestureend mouseout",d);l.data({bindEvent_buttonsContinuous_y:true})}}}}if(l.data("autoScrollOnFocus")){if(!l.data("bindEvent_focusin")){f.bind("focusin",function(){f.scrollTop(0).scrollLeft(0);var y=b(document.activeElement);if(y.is("input,textarea,select,button,a[tabindex],area,object")){if(l.data("horizontalScroll")){var K=p.position().left,H=y.position().left,F=f.width(),I=y.outerWidth();if(K+H>=0&&K+H<=F-I){}else{var L=H/l.data("scrollAmount");if(L>=m.width()-h.width()){L=m.width()-h.width()}h.css("left",L);l.mCustomScrollbar("scroll")}}else{var J=p.position().top,G=y.position().top,x=f.height(),E=y.outerHeight();if(J+G>=0&&J+G<=x-E){}else{var L=G/l.data("scrollAmount");if(L>=m.height()-h.height()){L=m.height()-h.height()}h.css("top",L);l.mCustomScrollbar("scroll")}}}});l.data({bindEvent_focusin:true})}}if(b(document).data("mCS-is-touch-device")){if(!l.data("bindEvent_scrollbar_touch")){var k,n;h.bind("touchstart onmsgesturestart",function(G){G.preventDefault();G.stopPropagation();var J=G.originalEvent.touches[0]||G.originalEvent.changedTouches[0],F=b(this),I=F.offset(),E=J.pageX-I.left,H=J.pageY-I.top;if(E<F.width()&&E>0&&H<F.height()&&H>0){k=H;n=E}});h.bind("touchmove onmsgesturechange",function(G){G.preventDefault();G.stopPropagation();var J=G.originalEvent.touches[0]||G.originalEvent.changedTouches[0],F=b(this),I=F.offset(),E=J.pageX-I.left,H=J.pageY-I.top;if(l.data("horizontalScroll")){l.mCustomScrollbar("scrollTo",(h.position().left-(n))+E,{moveDragger:true})}else{l.mCustomScrollbar("scrollTo",(h.position().top-(k))+H,{moveDragger:true})}});l.data({bindEvent_scrollbar_touch:true})}if(!l.data("bindEvent_content_touch")){var j,A,q,s,r,B,D;p.bind("touchstart onmsgesturestart",function(x){j=x.originalEvent.touches[0]||x.originalEvent.changedTouches[0];A=b(this);q=A.offset();s=j.pageX-q.left;r=j.pageY-q.top;B=r;D=s});p.bind("touchmove onmsgesturechange",function(x){x.preventDefault();x.stopPropagation();j=x.originalEvent.touches[0]||x.originalEvent.changedTouches[0];A=b(this).parent();q=A.offset();s=j.pageX-q.left;r=j.pageY-q.top;if(l.data("horizontalScroll")){l.mCustomScrollbar("scrollTo",D-s)}else{l.mCustomScrollbar("scrollTo",B-r)}});l.data({bindEvent_content_touch:true})}}},scroll:function(i){var k=b(this),p=k.find(".mCSB_dragger"),n=k.find(".mCSB_container"),e=k.find(".mCustomScrollBox");if(k.data("horizontalScroll")){var g=p.position().left,m=-g*k.data("scrollAmount"),o=n.position().left,d=Math.round(o-m)}else{var f=p.position().top,j=-f*k.data("scrollAmount"),l=n.position().top,c=Math.round(l-j)}if(b.browser.webkit){var q=(window.outerWidth-8)/window.innerWidth,h=(q<0.98||q>1.02)}if(k.data("scrollInertia")===0||h){if(!i){k.mCustomScrollbar("callbacks","onScrollStart")}if(k.data("horizontalScroll")){n.css("left",m)}else{n.css("top",j)}if(!i){if(k.data("whileScrolling")){k.data("whileScrolling_Callback").call()}k.mCustomScrollbar("callbacks","onScroll")}k.data({mCS_Init:false})}else{if(!i){k.mCustomScrollbar("callbacks","onScrollStart")}if(k.data("horizontalScroll")){n.stop().animate({left:"-="+d},k.data("scrollInertia"),k.data("scrollEasing"),function(){if(!i){k.mCustomScrollbar("callbacks","onScroll")}k.data({mCS_Init:false})})}else{n.stop().animate({top:"-="+c},k.data("scrollInertia"),k.data("scrollEasing"),function(){if(!i){k.mCustomScrollbar("callbacks","onScroll")}k.data({mCS_Init:false})})}}},scrollTo:function(g,m){var f={moveDragger:false,callback:true},m=b.extend(f,m),i=b(this),c,d=i.find(".mCustomScrollBox"),j=d.children(".mCSB_container"),e=i.find(".mCSB_draggerContainer"),k=e.children(".mCSB_dragger"),l;if(g||g===0){if(typeof(g)==="number"){if(m.moveDragger){c=g}else{l=g;c=Math.round(l/i.data("scrollAmount"))}}else{if(typeof(g)==="string"){var h;if(g==="top"){h=0}else{if(g==="bottom"&&!i.data("horizontalScroll")){h=j.outerHeight()-d.height()}else{if(g==="left"){h=0}else{if(g==="right"&&i.data("horizontalScroll")){h=j.outerWidth()-d.width()}else{if(g==="first"){h=i.find(".mCSB_container").find(":first")}else{if(g==="last"){h=i.find(".mCSB_container").find(":last")}else{h=i.find(g)}}}}}}if(h.length===1){if(i.data("horizontalScroll")){l=h.position().left}else{l=h.position().top}c=Math.ceil(l/i.data("scrollAmount"))}else{c=h}}}if(c<0){c=0}if(i.data("horizontalScroll")){if(c>=e.width()-k.width()){c=e.width()-k.width()}k.css("left",c)}else{if(c>=e.height()-k.height()){c=e.height()-k.height()}k.css("top",c)}if(m.callback){i.mCustomScrollbar("scroll",false)}else{i.mCustomScrollbar("scroll",true)}}},callbacks:function(h){var g=b(this),c=g.find(".mCustomScrollBox"),f=g.find(".mCSB_container");switch(h){case"onScrollStart":if(!f.is(":animated")){g.data("onScrollStart_Callback").call()}break;case"onScroll":if(g.data("horizontalScroll")){var e=Math.round(f.position().left);if(e<0&&e<=c.width()-f.outerWidth()+g.data("onTotalScroll_Offset")){g.data("onTotalScroll_Callback").call()}else{if(e>=-g.data("onTotalScroll_Offset")){g.data("onTotalScrollBack_Callback").call()}else{g.data("onScroll_Callback").call()}}}else{var d=Math.round(f.position().top);if(d<0&&d<=c.height()-f.outerHeight()+g.data("onTotalScroll_Offset")){g.data("onTotalScroll_Callback").call()}else{if(d>=-g.data("onTotalScroll_Offset")){g.data("onTotalScrollBack_Callback").call()}else{g.data("onScroll_Callback").call()}}}break;case"whileScrolling":if(g.data("whileScrolling_Callback")&&!g.data("whileScrolling")){g.data({whileScrolling:setInterval(function(){if(f.is(":animated")&&!g.data("mCS_Init")){g.data("whileScrolling_Callback").call()}},g.data("whileScrolling_Interval"))})}break}},disable:function(c){var h=b(this),d=h.children(".mCustomScrollBox"),f=d.children(".mCSB_container"),e=d.children(".mCSB_scrollTools"),g=e.find(".mCSB_dragger");d.unbind("mousewheel focusin");if(c){if(h.data("horizontalScroll")){g.add(f).css("left",0)}else{g.add(f).css("top",0)}}e.css("display","none");f.addClass("mCS_no_scrollbar");h.data({bindEvent_mousewheel:false,bindEvent_focusin:false}).addClass("mCS_disabled")},destroy:function(){var d=b(this),c=d.find(".mCSB_container").html();d.find(".mCustomScrollBox").remove();d.html(c).removeClass("mCustomScrollbar _mCS_"+b(document).data("mCustomScrollbar-index")).addClass("mCS_destroyed")}};b.fn.mCustomScrollbar=function(c){if(a[c]){return a[c].apply(this,Array.prototype.slice.call(arguments,1))}else{if(typeof c==="object"||!c){return a.init.apply(this,arguments)}else{b.error("Method "+c+" does not exist")}}}})(jQuery);var iOSVersion=iOSVersion();if(iOSVersion>=6){(function(m){var t={};var q={};var p=m.setTimeout;var o=m.setInterval;var l=m.clearTimeout;var r=m.clearInterval;if(!m.addEventListener){return false}function k(d,g,b){var e,c=b[0],a=(d===o);function f(){if(c){c.apply(m,arguments);if(!a){delete g[e];c=null}}}b[0]=f;e=d.apply(m,b);g[e]={args:b,created:Date.now(),cb:c,id:e};return e}function s(b,d,h,a,i){var g=h[a];if(!g){return}var f=(b===o);d(g.id);if(!f){var e=g.args[1];var c=Date.now()-g.created;if(c<0){c=0}e-=c;if(e<0){e=0}g.args[1]=e}function j(){if(g.cb){g.cb.apply(m,arguments);if(!f){delete h[a];g.cb=null}}}g.args[0]=j;g.created=Date.now();g.id=b.apply(m,g.args)}m.setTimeout=function(){return k(p,t,arguments)};m.setInterval=function(){return k(o,q,arguments)};m.clearTimeout=function(a){var b=t[a];if(b){delete t[a];l(b.id)}};m.clearInterval=function(a){var b=q[a];if(b){delete q[a];r(b.id)}};var n=m;while(n.location!=n.parent.location){n=n.parent}n.addEventListener("scroll",function(){var a;for(a in t){s(p,l,t,a)}for(a in q){s(o,r,q,a)}})}(window))}function iOSVersion(){var a=window.navigator.userAgent,b=a.indexOf("OS ");if((a.indexOf("iPhone")>-1||a.indexOf("iPad")>-1)&&b>-1){return window.Number(a.substr(b+3,3).replace("_","."))}return 0};
/*! Copyright (c) 2011 Brandon Aaron (http://brandonaaron.net)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 * Thanks to: Seamus Leahy for adding deltaX and deltaY
 *
 * Version: 3.0.6
 * 
 * Requires: 1.2.2+
 */
;(function(a){function d(b){var c=b||window.event,d=[].slice.call(arguments,1),e=0,f=!0,g=0,h=0;return b=a.event.fix(c),b.type="mousewheel",c.wheelDelta&&(e=c.wheelDelta/120),c.detail&&(e=-c.detail/3),h=e,c.axis!==undefined&&c.axis===c.HORIZONTAL_AXIS&&(h=0,g=-1*e),c.wheelDeltaY!==undefined&&(h=c.wheelDeltaY/120),c.wheelDeltaX!==undefined&&(g=-1*c.wheelDeltaX/120),d.unshift(b,e,g,h),(a.event.dispatch||a.event.handle).apply(this,d)}var b=["DOMMouseScroll","mousewheel"];if(a.event.fixHooks)for(var c=b.length;c;)a.event.fixHooks[b[--c]]=a.event.mouseHooks;a.event.special.mousewheel={setup:function(){if(this.addEventListener)for(var a=b.length;a;)this.addEventListener(b[--a],d,!1);else this.onmousewheel=d},teardown:function(){if(this.removeEventListener)for(var a=b.length;a;)this.removeEventListener(b[--a],d,!1);else this.onmousewheel=null}},a.fn.extend({mousewheel:function(a){return a?this.bind("mousewheel",a):this.trigger("mousewheel")},unmousewheel:function(a){return this.unbind("mousewheel",a)}})})(jQuery);


/* TinySort 1.5.2
* Copyright (c) 2008-2013 Ron Valstar http://tinysort.sjeiti.com/
*
* Dual licensed under the MIT and GPL licenses:
*   http://www.opensource.org/licenses/mit-license.php
*   http://www.gnu.org/licenses/gpl.html
*/
;(function(e,c){var h=!1,k=null,o=parseFloat,l=Math.min,m=/(-?\d+\.?\d*)$/g,g=/(\d+\.?\d*)$/g,i=[],f=[],b=function(p){return typeof p=="string"},n=Array.prototype.indexOf||function(r){var p=this.length,q=Number(arguments[1])||0;q=q<0?Math.ceil(q):Math.floor(q);if(q<0){q+=p}for(;q<p;q++){if(q in this&&this[q]===r){return q}}return -1};e.tinysort={id:"TinySort",version:"1.5.2",copyright:"Copyright (c) 2008-2013 Ron Valstar",uri:"http://tinysort.sjeiti.com/",licensed:{MIT:"http://www.opensource.org/licenses/mit-license.php",GPL:"http://www.gnu.org/licenses/gpl.html"},plugin:function(p,q){i.push(p);f.push(q)},defaults:{order:"asc",attr:k,data:k,useVal:h,place:"start",returns:h,cases:h,forceStrings:h,ignoreDashes:h,sortFunction:k}};e.fn.extend({tinysort:function(){var I,F,K=this,t=[],r=[],L=[],N=[],x=0,q,E=[],B=[],C=function(O){e.each(i,function(P,Q){Q.call(Q,O)})},y=function(Z,X){var O=0;if(x!==0){x=0}while(O===0&&x<q){var V=N[x],S=V.oSettings,W=S.ignoreDashes?g:m;C(S);if(S.sortFunction){O=S.sortFunction(Z,X)}else{if(S.order=="rand"){O=Math.random()<0.5?1:-1}else{var Y=h,R=!S.cases?a(Z.s[x]):Z.s[x],Q=!S.cases?a(X.s[x]):X.s[x];if(!u.forceStrings){var P=b(R)?R&&R.match(W):h,aa=b(Q)?Q&&Q.match(W):h;if(P&&aa){var U=R.substr(0,R.length-P[0].length),T=Q.substr(0,Q.length-aa[0].length);if(U==T){Y=!h;R=o(P[0]);Q=o(aa[0])}}}O=V.iAsc*(R<Q?-1:(R>Q?1:0))}}e.each(f,function(ab,ac){O=ac.call(ac,Y,R,Q,O)});if(O===0){x++}}return O};for(I=0,F=arguments.length;I<F;I++){var D=arguments[I];if(b(D)){if(E.push(D)-1>B.length){B.length=E.length-1}}else{if(B.push(D)>E.length){E.length=B.length}}}if(E.length>B.length){B.length=E.length}q=E.length;if(q===0){q=E.length=1;B.push({})}for(I=0,F=q;I<F;I++){var J=E[I],u=e.extend({},e.tinysort.defaults,B[I]),w=!(!J||J==""),p=w&&J[0]==":";N.push({sFind:J,oSettings:u,bFind:w,bAttr:!(u.attr===k||u.attr==""),bData:u.data!==k,bFilter:p,$Filter:p?K.filter(J):K,fnSort:u.sortFunction,iAsc:u.order=="asc"?1:-1})}K.each(function(V,O){var R=e(O),P=R.parent().get(0),Q,U=[];for(j=0;j<q;j++){var W=N[j],S=W.bFind?(W.bFilter?W.$Filter.filter(O):R.find(W.sFind)):R;U.push(W.bData?S.data(W.oSettings.data):(W.bAttr?S.attr(W.oSettings.attr):(W.oSettings.useVal?S.val():S.text())));if(Q===c){Q=S}}var T=n.call(L,P);if(T<0){T=L.push(P)-1;r[T]={s:[],n:[]}}if(Q.length>0){r[T].s.push({s:U,e:R,n:V})}else{r[T].n.push({e:R,n:V})}});for(j in r){r[j].s.sort(y)}for(j in r){var G=r[j],s=G.s.length,H=[],M=s,A=[0,0];switch(u.place){case"first":e.each(G.s,function(O,P){M=l(M,P.n)});break;case"org":e.each(G.s,function(O,P){H.push(P.n)});break;case"end":M=G.n.length;break;default:M=0}for(I=0;I<s;I++){var v=d(H,I)?!h:I>=M&&I<M+G.s.length,z=(v?G.s:G.n)[A[v?0:1]].e;z.parent().append(z);if(v||!u.returns){t.push(z.get(0))}A[v?0:1]++}}K.length=0;Array.prototype.push.apply(K,t);return K}});function a(p){return p&&p.toLowerCase?p.toLowerCase():p}function d(q,s){for(var r=0,p=q.length;r<p;r++){if(q[r]==s){return !h}}return h}e.fn.TinySort=e.fn.Tinysort=e.fn.tsort=e.fn.tinysort})(jQuery);


//range slider
;(function(e){e.fn.noUiSlider=function(t,n){function s(e,t,n){if(typeof e=="function"){e.call(t,n)}}function o(e,t,n){var r=t.data("setup"),i=r.handles,s=r.settings,o=r.pos;e=e<0?0:e>100?100:e;if(s.handles==2){if(n.is(":first-child")){var u=parseFloat(i[1][0].style[o])-s.margin;e=e>u?u:e}else{var u=parseFloat(i[0][0].style[o])+s.margin;e=e<u?u:e}}if(s.step){var a=f.from(s.range,s.step);e=Math.round(e/a)*a}return e}function u(e){return[e.clientX||e.originalEvent.clientX||e.originalEvent.touches[0].clientX,e.clientY||e.originalEvent.clientY||e.originalEvent.touches[0].clientY]}function a(e,t){return parseFloat(e[0].style[t])}var r=jQuery.fn.val;jQuery.fn.val=function(){return this.data("_isnS_")?methods.val.call(this,arguments[0]):r.apply(this,arguments)};var i=window.navigator.msPointerEnabled?2:"ontouchstart"in document.documentElement?3:1;var f={to:function(e,t){t=e[0]<0?t+Math.abs(e[0]):t-e[0];return t*100/this._length(e)},from:function(e,t){return t*100/this._length(e)},is:function(e,t){return t*this._length(e)/100+e[0]},_length:function(e){return e[0]>e[1]?e[0]-e[1]:e[1]-e[0]}};var l={handles:2,serialization:{to:["",""],resolution:.01}};methods={create:function(){return this.each(function(){function n(e,t,n){e.css(d,t+"%").data("input").val(f.is(r.range,t).toFixed(b))}var r=e.extend(l,t),c="<a><div></div></a>",h=e(this).data("_isnS_",true),p=[],d,v,m="",g=function(e){return!isNaN(parseFloat(e))&&isFinite(e)},y=(r.serialization.resolution=r.serialization.resolution||.01).toString().split("."),b=y[0]==1?0:y[1].length;r.start=g(r.start)?[r.start,0]:r.start;e.each(r,function(e,t){if(g(t)){r[e]=parseFloat(t)}var n=false;switch(e){case"range":case"start":n=t.length!=2||!g(t[0])||!g(t[1]);break;case"handles":n=t<1||t>2||!g(t);break;case"connect":n=t!="lower"&&t!="upper"&&typeof t!="boolean";break;case"orientation":n=t!="vertical"&&t!="horizontal";break;case"margin":case"step":n=typeof t!="undefined"&&!g(t);break;case"serialization":n=typeof t!="object"||!g(t.resolution)||typeof t.to=="object"&&t.to.length<r.handles;break;case"slide":n=typeof t!="function";break}if(n&&console){console.error("Bad input for "+e+" on slider:",h)}});r.margin=r.margin?f.from(r.range,r.margin):0;if(r.serialization.to instanceof jQuery||typeof r.serialization.to=="string"||r.serialization.to===false){r.serialization.to=[r.serialization.to]}if(r.orientation=="vertical"){m+="vertical";d="top";v=1}else{m+="horizontal";d="left";v=0}m+=r.connect?r.connect=="lower"?" connect lower":" connect":"";h.addClass(m);for(var w=0;w<r.handles;w++){p[w]=h.append(c).children(":last");p[w].css(d,f.to(r.range,r.start[w])+"%");var E=".noUiSlider";var S=(i===1?"mousedown":i===2?"MSPointerDown":"touchstart")+E+"X";var x=(i===1?"mousemove":i===2?"MSPointerMove":"touchmove")+E;var T=(i===1?"mouseup":i===2?"MSPointerUp":"touchend")+E;p[w].find("div").on(S,function(t){e("body").bind("selectstart"+E,function(){return false});if(!h.hasClass("disabled")){var n=e(this).addClass("active").parent().addClass("active");e("body").addClass("TOUCH");var i=n.add(e(document)).add("body");var a=parseFloat(n[0].style[d]);var l=u(t);var c=l;var m=false;e(document).on(x,function(e){e.preventDefault();var t=u(e);t[0]-=l[0];t[1]-=l[1];var i=[c[0]!=t[0],c[1]!=t[1]];var g=a+t[v]*100/(v?h.height():h.width());g=o(g,h,n);if(i[v]&&g!=m){n.css(d,g+"%").data("input").val(f.is(r.range,g).toFixed(b));s(r.slide,h.data("_n",true));m=g;n.css("z-index",p.length==2&&g==100&&n.is(":first-child")?2:1)}c=t});e(document).on(T+" mouseLeave"+E,function(){i.off(E);e("body").removeClass("TOUCH");h.find(".active").removeClass("active");if(h.data("_n")){h.data("_n",false).change()}})}}).on("click",function(e){e.stopPropagation()})}if(i==1){h.on("click",function(e){if(!h.hasClass("disabled")){var t=u(e),i=(t[v]-h.offset()[d])*100/(v?h.height():h.width());if(p.length>1){var a=t[v]<(p[0].offset()[d]+p[1].offset()[d])/2?p[0]:p[1]}else{var a=p[0]}n(a,o(i,h,a),h);s(r.slide,h);h.change()}})}for(var w=0;w<p.length;w++){var N=f.is(r.range,a(p[w],d)).toFixed(b);if(typeof r.serialization.to[w]=="string"){p[w].data("input",h.append('<input type="hidden" name="'+r.serialization.to[w]+'">').find("input:last").val(N).change(function(e){e.stopPropagation()}))}else if(r.serialization.to[w]==false){p[w].data("input",{val:function(e){if(typeof e!="undefined"){this.handle.data("noUiVal",e)}else{return this.handle.data("noUiVal")}},handle:p[w]})}else{p[w].data("input",r.serialization.to[w].data("handleNR",w).val(N).change(function(){var t=[null,null];t[e(this).data("handleNR")]=e(this).val();h.val(t)}))}}e(this).data("setup",{settings:r,handles:p,pos:d,res:b})})},val:function(){if(arguments[0]){var t=typeof arguments[0]=="number"?[arguments[0]]:arguments[0];return this.each(function(){var n=e(this).data("setup");for(var r=0;r<n.handles.length;r++){if(t[r]!=null){var i=o(f.to(n.settings.range,t[r]),e(this),n.handles[r]);n.handles[r].css(n.pos,i+"%").data("input").val(f.is(n.settings.range,i).toFixed(n.res))}}})}else{var n=e(this).data("setup").handles,r=[];for(var i=0;i<n.length;i++){r.push(parseFloat(n[i].data("input").val()))}return r.length==1?r[0]:r}},disabled:function(){return n?e(this).addClass("disabled"):e(this).removeClass("disabled")}};return t=="disabled"?methods.disabled.apply(this):methods.create.apply(this)}})(jQuery);

//jcrop
;(function(e){e.Jcrop=function(t,n){function a(e){return Math.round(e)+"px"}function f(e){return r.baseClass+"-"+e}function l(){return e.fx.step.hasOwnProperty("backgroundColor")}function c(t){var n=e(t).offset();return[n.left,n.top]}function h(e){return[e.pageX-i[0],e.pageY-i[1]]}function p(t){if(typeof t!=="object")t={};r=e.extend(r,t);e.each(["onChange","onSelect","onRelease","onDblClick"],function(e,t){if(typeof r[t]!=="function")r[t]=function(){}})}function d(e,t,n){i=c(A);nt.setCursor(e==="move"?e:e+"-resize");if(e==="move"){return nt.activateHandlers(m(t),E,n)}var r=Z.getFixed();var s=g(e);var o=Z.getCorner(g(s));Z.setPressed(Z.getCorner(s));Z.setCurrent(o);nt.activateHandlers(v(e,r),E,n)}function v(e,t){return function(n){if(!r.aspectRatio){switch(e){case"e":n[1]=t.y2;break;case"w":n[1]=t.y2;break;case"n":n[0]=t.x2;break;case"s":n[0]=t.x2;break}}else{switch(e){case"e":n[1]=t.y+1;break;case"w":n[1]=t.y+1;break;case"n":n[0]=t.x+1;break;case"s":n[0]=t.x+1;break}}Z.setCurrent(n);tt.update()}}function m(e){var t=e;rt.watchKeys();return function(e){Z.moveOffset([e[0]-t[0],e[1]-t[1]]);t=e;tt.update()}}function g(e){switch(e){case"n":return"sw";case"s":return"nw";case"e":return"nw";case"w":return"ne";case"ne":return"sw";case"nw":return"se";case"se":return"nw";case"sw":return"ne"}}function y(e){return function(t){if(r.disabled){return false}if(e==="move"&&!r.allowMove){return false}i=c(A);K=true;d(e,h(t));t.stopPropagation();t.preventDefault();return false}}function b(e,t,n){var r=e.width(),i=e.height();if(r>t&&t>0){r=t;i=t/e.width()*e.height()}if(i>n&&n>0){i=n;r=n/e.height()*e.width()}X=e.width()/r;V=e.height()/i;e.width(r).height(i)}function w(e){return{x:e.x*X,y:e.y*V,x2:e.x2*X,y2:e.y2*V,w:e.w*X,h:e.h*V}}function E(e){var t=Z.getFixed();if(t.w>r.minSelect[0]&&t.h>r.minSelect[1]){tt.enableHandles();tt.done()}else{tt.release()}nt.setCursor(r.allowSelect?"crosshair":"default")}function S(e){if(r.disabled){return false}if(!r.allowSelect){return false}K=true;i=c(A);tt.disableHandles();nt.setCursor("crosshair");var t=h(e);Z.setPressed(t);tt.update();nt.activateHandlers(x,E,e.type.substring(0,5)==="touch");rt.watchKeys();e.stopPropagation();e.preventDefault();return false}function x(e){Z.setCurrent(e);tt.update()}function T(){var t=e("<div></div>").addClass(f("tracker"));if(o){t.css({opacity:0,backgroundColor:"white"})}return t}function it(e){_.removeClass().addClass(f("holder")).addClass(e)}function st(e,t){function b(){window.setTimeout(w,c)}var n=e[0]/X,i=e[1]/V,s=e[2]/X,o=e[3]/V;if(Q){return}var u=Z.flipCoords(n,i,s,o),a=Z.getFixed(),f=[a.x,a.y,a.x2,a.y2],l=f,c=r.animationDelay,h=u[0]-f[0],p=u[1]-f[1],d=u[2]-f[2],v=u[3]-f[3],m=0,g=r.swingSpeed;n=l[0];i=l[1];s=l[2];o=l[3];tt.animMode(true);var y;var w=function(){return function(){m+=(100-m)/g;l[0]=Math.round(n+m/100*h);l[1]=Math.round(i+m/100*p);l[2]=Math.round(s+m/100*d);l[3]=Math.round(o+m/100*v);if(m>=99.8){m=100}if(m<100){ut(l);b()}else{tt.done();tt.animMode(false);if(typeof t==="function"){t.call(yt)}}}}();b()}function ot(e){ut([e[0]/X,e[1]/V,e[2]/X,e[3]/V]);r.onSelect.call(yt,w(Z.getFixed()));tt.enableHandles()}function ut(e){Z.setPressed([e[0],e[1]]);Z.setCurrent([e[2],e[3]]);tt.update()}function at(){return w(Z.getFixed())}function ft(){return Z.getFixed()}function lt(e){p(e);gt()}function ct(){r.disabled=true;tt.disableHandles();tt.setCursor("default");nt.setCursor("default")}function ht(){r.disabled=false;gt()}function pt(){tt.done();nt.activateHandlers(null,null)}function dt(){_.remove();C.show();C.css("visibility","visible");e(t).removeData("Jcrop")}function vt(e,t){tt.release();ct();var n=new Image;n.onload=function(){var i=n.width;var s=n.height;var o=r.boxWidth;var u=r.boxHeight;A.width(i).height(s);A.attr("src",e);D.attr("src",e);b(A,o,u);O=A.width();M=A.height();D.width(O).height(M);F.width(O+j*2).height(M+j*2);_.width(O).height(M);et.resize(O,M);ht();if(typeof t==="function"){t.call(yt)}};n.src=e}function mt(e,t,n){var i=t||r.bgColor;if(r.bgFade&&l()&&r.fadeTime&&!n){e.animate({backgroundColor:i},{queue:false,duration:r.fadeTime})}else{e.css("backgroundColor",i)}}function gt(e){if(r.allowResize){if(e){tt.enableOnly()}else{tt.enableHandles()}}else{tt.disableHandles()}nt.setCursor(r.allowSelect?"crosshair":"default");tt.setCursor(r.allowMove?"move":"default");if(r.hasOwnProperty("trueSize")){X=r.trueSize[0]/O;V=r.trueSize[1]/M}if(r.hasOwnProperty("setSelect")){ot(r.setSelect);tt.done();delete r.setSelect}et.refresh();if(r.bgColor!=I){mt(r.shade?et.getShades():_,r.shade?r.shadeColor||r.bgColor:r.bgColor);I=r.bgColor}if(q!=r.bgOpacity){q=r.bgOpacity;if(r.shade)et.refresh();else tt.setBgOpacity(q)}R=r.maxSize[0]||0;U=r.maxSize[1]||0;z=r.minSize[0]||0;W=r.minSize[1]||0;if(r.hasOwnProperty("outerImage")){A.attr("src",r.outerImage);delete r.outerImage}tt.refresh()}var r=e.extend({},e.Jcrop.defaults),i,s=navigator.userAgent.toLowerCase(),o=/msie/.test(s),u=/msie [1-6]\./.test(s);if(typeof t!=="object"){t=e(t)[0]}if(typeof n!=="object"){n={}}p(n);var N={border:"none",visibility:"visible",margin:0,padding:0,position:"absolute",top:0,left:0};var C=e(t),k=true;if(t.tagName=="IMG"){if(C[0].width!=0&&C[0].height!=0){C.width(C[0].width);C.height(C[0].height)}else{var L=new Image;L.src=C[0].src;C.width(L.width);C.height(L.height)}var A=C.clone().removeAttr("id").css(N).show();A.width(C.width());A.height(C.height());C.after(A).hide()}else{A=C.css(N).show();k=false;if(r.shade===null){r.shade=true}}b(A,r.boxWidth,r.boxHeight);var O=A.width(),M=A.height(),_=e("<div />").width(O).height(M).addClass(f("holder")).css({position:"absolute",left:"50%",top:"50%",marginLeft:-O/2,marginTop:-M/2,backgroundColor:r.bgColor}).insertAfter(C).append(A);if(r.addClass){_.addClass(r.addClass)}var D=e("<div />"),P=e("<div />").width("100%").height("100%").css({zIndex:310,position:"absolute",overflow:"hidden"}),H=e("<div />").width("100%").height("100%").css("zIndex",320),B=e("<div />").css({position:"absolute",zIndex:600}).dblclick(function(){var e=Z.getFixed();r.onDblClick.call(yt,e)}).insertBefore(A).append(P,H);if(k){D=e("<img />").attr("src",A.attr("src")).css(N).width(O).height(M),P.append(D)}if(u){B.css({overflowY:"hidden"})}var j=r.boundary;var F=T().width(O+j*2).height(M+j*2).css({position:"absolute",top:a(-j),left:a(-j),zIndex:290}).mousedown(S);var I=r.bgColor,q=r.bgOpacity,R,U,z,W,X,V,J=true,K,Q,G;i=c(A);var Y=function(){function e(){var e={},t=["touchstart","touchmove","touchend"],n=document.createElement("div"),r;try{for(r=0;r<t.length;r++){var i=t[r];i="on"+i;var s=i in n;if(!s){n.setAttribute(i,"return;");s=typeof n[i]=="function"}e[t[r]]=s}return e.touchstart&&e.touchend&&e.touchmove}catch(o){return false}}function t(){if(r.touchSupport===true||r.touchSupport===false)return r.touchSupport;else return e()}return{createDragger:function(e){return function(t){if(r.disabled){return false}if(e==="move"&&!r.allowMove){return false}i=c(A);K=true;d(e,h(Y.cfilter(t)),true);t.stopPropagation();t.preventDefault();return false}},newSelection:function(e){return S(Y.cfilter(e))},cfilter:function(e){e.pageX=e.originalEvent.changedTouches[0].pageX;e.pageY=e.originalEvent.changedTouches[0].pageY;return e},isSupported:e,support:t()}}();var Z=function(){function u(r){r=p(r);n=e=r[0];i=t=r[1]}function a(e){e=p(e);s=e[0]-n;o=e[1]-i;n=e[0];i=e[1]}function f(){return[s,o]}function l(r){var s=r[0],o=r[1];if(0>e+s){s-=s+e}if(0>t+o){o-=o+t}if(M<i+o){o+=M-(i+o)}if(O<n+s){s+=O-(n+s)}e+=s;n+=s;t+=o;i+=o}function c(e){var t=h();switch(e){case"ne":return[t.x2,t.y];case"nw":return[t.x,t.y];case"se":return[t.x2,t.y2];case"sw":return[t.x,t.y2]}}function h(){if(!r.aspectRatio){return v()}var s=r.aspectRatio,o=r.minSize[0]/X,u=r.maxSize[0]/X,a=r.maxSize[1]/V,f=n-e,l=i-t,c=Math.abs(f),h=Math.abs(l),p=c/h,g,y,b,w;if(u===0){u=O*10}if(a===0){a=M*10}if(p<s){y=i;b=h*s;g=f<0?e-b:b+e;if(g<0){g=0;w=Math.abs((g-e)/s);y=l<0?t-w:w+t}else if(g>O){g=O;w=Math.abs((g-e)/s);y=l<0?t-w:w+t}}else{g=n;w=c/s;y=l<0?t-w:t+w;if(y<0){y=0;b=Math.abs((y-t)*s);g=f<0?e-b:b+e}else if(y>M){y=M;b=Math.abs(y-t)*s;g=f<0?e-b:b+e}}if(g>e){if(g-e<o){g=e+o}else if(g-e>u){g=e+u}if(y>t){y=t+(g-e)/s}else{y=t-(g-e)/s}}else if(g<e){if(e-g<o){g=e-o}else if(e-g>u){g=e-u}if(y>t){y=t+(e-g)/s}else{y=t-(e-g)/s}}if(g<0){e-=g;g=0}else if(g>O){e-=g-O;g=O}if(y<0){t-=y;y=0}else if(y>M){t-=y-M;y=M}return m(d(e,t,g,y))}function p(e){if(e[0]<0)e[0]=0;if(e[1]<0)e[1]=0;if(e[0]>O)e[0]=O;if(e[1]>M)e[1]=M;return[Math.round(e[0]),Math.round(e[1])]}function d(e,t,n,r){var i=e,s=n,o=t,u=r;if(n<e){i=n;s=e}if(r<t){o=r;u=t}return[i,o,s,u]}function v(){var r=n-e,s=i-t,o;if(R&&Math.abs(r)>R){n=r>0?e+R:e-R}if(U&&Math.abs(s)>U){i=s>0?t+U:t-U}if(W/V&&Math.abs(s)<W/V){i=s>0?t+W/V:t-W/V}if(z/X&&Math.abs(r)<z/X){n=r>0?e+z/X:e-z/X}if(e<0){n-=e;e-=e}if(t<0){i-=t;t-=t}if(n<0){e-=n;n-=n}if(i<0){t-=i;i-=i}if(n>O){o=n-O;e-=o;n-=o}if(i>M){o=i-M;t-=o;i-=o}if(e>O){o=e-M;i-=o;t-=o}if(t>M){o=t-M;i-=o;t-=o}return m(d(e,t,n,i))}function m(e){return{x:e[0],y:e[1],x2:e[2],y2:e[3],w:e[2]-e[0],h:e[3]-e[1]}}var e=0,t=0,n=0,i=0,s,o;return{flipCoords:d,setPressed:u,setCurrent:a,getOffset:f,moveOffset:l,getCorner:c,getFixed:h}}();var et=function(){function s(e,t){i.left.css({height:a(t)});i.right.css({height:a(t)})}function o(){return u(Z.getFixed())}function u(e){i.top.css({left:a(e.x),width:a(e.w),height:a(e.y)});i.bottom.css({top:a(e.y2),left:a(e.x),width:a(e.w),height:a(M-e.y2)});i.right.css({left:a(e.x2),width:a(O-e.x2)});i.left.css({width:a(e.x)})}function f(){return e("<div />").css({position:"absolute",backgroundColor:r.shadeColor||r.bgColor}).appendTo(n)}function l(){if(!t){t=true;n.insertBefore(A);o();tt.setBgOpacity(1,0,1);D.hide();c(r.shadeColor||r.bgColor,1);if(tt.isAwake()){p(r.bgOpacity,1)}else p(1,1)}}function c(e,t){mt(v(),e,t)}function h(){if(t){n.remove();D.show();t=false;if(tt.isAwake()){tt.setBgOpacity(r.bgOpacity,1,1)}else{tt.setBgOpacity(1,1,1);tt.disableHandles()}mt(_,0,1)}}function p(e,i){if(t){if(r.bgFade&&!i){n.animate({opacity:1-e},{queue:false,duration:r.fadeTime})}else n.css({opacity:1-e})}}function d(){r.shade?l():h();if(tt.isAwake())p(r.bgOpacity)}function v(){return n.children()}var t=false,n=e("<div />").css({position:"absolute",zIndex:240,opacity:0}),i={top:f(),left:f().height(M),right:f().height(M),bottom:f()};return{update:o,updateRaw:u,getShades:v,setBgColor:c,enable:l,disable:h,resize:s,refresh:d,opacity:p}}();var tt=function(){function l(t){var n=e("<div />").css({position:"absolute",opacity:r.borderOpacity}).addClass(f(t));P.append(n);return n}function c(t,n){var r=e("<div />").mousedown(y(t)).css({cursor:t+"-resize",position:"absolute",zIndex:n}).addClass("ord-"+t);if(Y.support){r.bind("touchstart.jcrop",Y.createDragger(t))}H.append(r);return r}function h(e){var t=r.handleSize,i=c(e,n++).css({opacity:r.handleOpacity}).addClass(f("handle"));if(t){i.width(t).height(t)}return i}function p(e){return c(e,n++).addClass("jcrop-dragbar")}function d(e){var t;for(t=0;t<e.length;t++){o[e[t]]=p(e[t])}}function v(e){var t,n;for(n=0;n<e.length;n++){switch(e[n]){case"n":t="hline";break;case"s":t="hline bottom";break;case"e":t="vline right";break;case"w":t="vline";break}i[e[n]]=l(t)}}function m(e){var t;for(t=0;t<e.length;t++){s[e[t]]=h(e[t])}}function g(e,t){if(!r.shade){D.css({top:a(-t),left:a(-e)})}B.css({top:a(t),left:a(e)})}function b(e,t){B.width(Math.round(e)).height(Math.round(t))}function E(){var e=Z.getFixed();Z.setPressed([e.x,e.y]);Z.setCurrent([e.x2,e.y2]);S()}function S(e){if(t){return x(e)}}function x(e){var n=Z.getFixed();b(n.w,n.h);g(n.x,n.y);if(r.shade)et.updateRaw(n);t||C();if(e){r.onSelect.call(yt,w(n))}else{r.onChange.call(yt,w(n))}}function N(e,n,i){if(!t&&!n)return;if(r.bgFade&&!i){A.animate({opacity:e},{queue:false,duration:r.fadeTime})}else{A.css("opacity",e)}}function C(){B.show();if(r.shade)et.opacity(q);else N(q,true);t=true}function k(){M();B.hide();if(r.shade)et.opacity(1);else N(1);t=false;r.onRelease.call(yt)}function L(){if(u){H.show()}}function O(){u=true;if(r.allowResize){H.show();return true}}function M(){u=false;H.hide()}function _(e){if(e){Q=true;M()}else{Q=false;O()}}function j(){_(false);E()}var t,n=370,i={},s={},o={},u=false;if(r.dragEdges&&e.isArray(r.createDragbars))d(r.createDragbars);if(e.isArray(r.createHandles))m(r.createHandles);if(r.drawBorders&&e.isArray(r.createBorders))v(r.createBorders);e(document).bind("touchstart.jcrop-ios",function(t){if(e(t.currentTarget).hasClass("jcrop-tracker"))t.stopPropagation()});var F=T().mousedown(y("move")).css({cursor:"move",position:"absolute",zIndex:360});if(Y.support){F.bind("touchstart.jcrop",Y.createDragger("move"))}P.append(F);M();return{updateVisible:S,update:x,release:k,refresh:E,isAwake:function(){return t},setCursor:function(e){F.css("cursor",e)},enableHandles:O,enableOnly:function(){u=true},showHandles:L,disableHandles:M,animMode:_,setBgOpacity:N,done:j}}();var nt=function(){function s(t){F.css({zIndex:450});if(t)e(document).bind("touchmove.jcrop",l).bind("touchend.jcrop",c);else if(i)e(document).bind("mousemove.jcrop",u).bind("mouseup.jcrop",a)}function o(){F.css({zIndex:290});e(document).unbind(".jcrop")}function u(e){t(h(e));return false}function a(e){e.preventDefault();e.stopPropagation();if(K){K=false;n(h(e));if(tt.isAwake()){r.onSelect.call(yt,w(Z.getFixed()))}o();t=function(){};n=function(){}}return false}function f(e,r,i){K=true;t=e;n=r;s(i);return false}function l(e){t(h(Y.cfilter(e)));return false}function c(e){return a(Y.cfilter(e))}function p(e){F.css("cursor",e)}var t=function(){},n=function(){},i=r.trackDocument;if(!i){F.mousemove(u).mouseup(a).mouseout(a)}A.before(F);return{activateHandlers:f,setCursor:p}}();var rt=function(){function i(){if(r.keySupport){t.show();t.focus()}}function s(e){t.hide()}function o(e,t,n){if(r.allowMove){Z.moveOffset([t,n]);tt.updateVisible(true)}e.preventDefault();e.stopPropagation()}function a(e){if(e.ctrlKey||e.metaKey){return true}G=e.shiftKey?true:false;var t=G?10:1;switch(e.keyCode){case 37:o(e,-t,0);break;case 39:o(e,t,0);break;case 38:o(e,0,-t);break;case 40:o(e,0,t);break;case 27:if(r.allowSelect)tt.release();break;case 9:return true}return false}var t=e('<input type="radio" />').css({position:"fixed",left:"-120px",width:"12px"}).addClass("jcrop-keymgr"),n=e("<div />").css({position:"absolute",overflow:"hidden"}).append(t);if(r.keySupport){t.keydown(a).blur(s);if(u||!r.fixedSupport){t.css({position:"absolute",left:"-20px"});n.append(t).insertBefore(A)}else{t.insertBefore(A)}}return{watchKeys:i}}();if(Y.support)F.bind("touchstart.jcrop",Y.newSelection);H.hide();gt(true);var yt={setImage:vt,animateTo:st,setSelect:ot,setOptions:lt,tellSelect:at,tellScaled:ft,setClass:it,disable:ct,enable:ht,cancel:pt,release:tt.release,destroy:dt,focus:rt.watchKeys,getBounds:function(){return[O*X,M*V]},getWidgetSize:function(){return[O,M]},getScaleFactor:function(){return[X,V]},getOptions:function(){return r},ui:{holder:_,selection:B}};if(o)_.bind("selectstart",function(){return false});C.data("Jcrop",yt);return yt};e.fn.Jcrop=function(t,n){var r;this.each(function(){if(e(this).data("Jcrop")){if(t==="api")return e(this).data("Jcrop");else e(this).data("Jcrop").setOptions(t)}else{if(this.tagName=="IMG")e.Jcrop.Loader(this,function(){e(this).css({display:"block",visibility:"hidden"});r=e.Jcrop(this,t);if(e.isFunction(n))n.call(r)});else{e(this).css({display:"block",visibility:"hidden"});r=e.Jcrop(this,t);if(e.isFunction(n))n.call(r)}}});return this};e.Jcrop.Loader=function(t,n,r){function o(){if(s.complete){i.unbind(".jcloader");if(e.isFunction(n))n.call(s)}else window.setTimeout(o,50)}var i=e(t),s=i[0];i.bind("load.jcloader",o).bind("error.jcloader",function(t){i.unbind(".jcloader");if(e.isFunction(r))r.call(s)});if(s.complete&&e.isFunction(n)){i.unbind(".jcloader");n.call(s)}};e.Jcrop.defaults={allowSelect:true,allowMove:true,allowResize:true,trackDocument:true,baseClass:"jcrop",addClass:null,bgColor:"black",bgOpacity:.6,bgFade:false,borderOpacity:.4,handleOpacity:.5,handleSize:null,aspectRatio:0,keySupport:true,createHandles:["n","s","e","w","nw","ne","se","sw"],createDragbars:["n","s","e","w"],createBorders:["n","s","e","w"],drawBorders:true,dragEdges:true,fixedSupport:true,touchSupport:null,shade:null,boxWidth:0,boxHeight:0,boundary:2,fadeTime:400,animationDelay:20,swingSpeed:3,minSelect:[0,0],maxSize:[0,0],minSize:[0,0],onChange:function(){},onSelect:function(){},onDblClick:function(){},onRelease:function(){}}})(jQuery)
