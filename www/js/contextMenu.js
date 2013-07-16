(function($){  
     $.fn.contextMenu = function(options) {  
         
        var defaults = {  
           contextId : null,
           submenu: false,  
           showContentMenuSignal: "",
           parentName:"",
           name:"",
           contextMenuClassName: "",
           contextMenuSelector: "vmenu",
           contextMenuSnippetName: "",
           contextMenuListenerId:"default-context-menu-listener"
        };  

        var options = $.extend(defaults, options);  

        this.die();

        this.live('mouseenter',function(){
            $(this).css({borderBottom: '1px dotted #ccc'});
        });
        this.live('mouseleave',function(){
            $(this).css({borderBottom: 'none'});
        });
                
        this.live('contextmenu',function(e){
            options.contextId = $(this).attr('menu_id'); //id prvku = pid
            //if(options.parentName == "") options.parentName = options.name; ????????????????????????????
            
            var json = { };

            var cmlOffset = $('#'+options.contextMenuListenerId).offset();

            json['contextMenuClassName'] = options.contextMenuClassName;
            json['contextMenuSelector'] = options.contextMenuSelector;
            json['contextMenuSnippetName'] = options.contextMenuSnippetName;
            json['contextMenuX'] = e.pageX - Math.round(cmlOffset.left);
            json['contextMenuY'] = e.pageY - Math.round(cmlOffset.top);
            json['contextMenuParams'] = [options.contextId, options.contextMenuSelector];
            
            $.get(options.showContentMenuSignal, json);
            $('<div class="overlay"></div>').css({ left : '0px', top : '0px',position: 'absolute', width: '100%', height: '100%'}).mousedown(function() {
                    $('#'+options.contextMenuSelector).hide();
                    $(this).remove(); 
            }).bind('contextmenu' , function(){return false;}).appendTo(document.body);
            return false;
        });

        $('.first_li',$('#'+options.contextMenuSelector)).live('click',function() {
            if( $(this).children().size() == 1 ) {
                    $('#'+options.contextMenuSelector).hide();
                    $('.overlay').hide();
            }
        });

        $(' .inner_li a',$('#'+options.contextMenuSelector)).live('click',function() {
                    $('#'+options.contextMenuSelector).hide();
                    $('.overlay').hide();
        });

        $(" .inner_li",$('#'+options.contextMenuSelector)).live({
            mouseenter:
                function () {
                    if ( $(this).children().size() > 0){
                        options.submenu = true;
                        $(this).find('.inner_li').show();
                    }
                },
            mouseleave:
                function () {
                    if(!options.submenu){
                        $('.inner_li').hide();
                    }
                    options.submenu = false;
                }
        });
        $(" .first_li",$('#'+options.contextMenuSelector)).live({
            mouseenter:
                function () {
                    if ( $(this).children().size() > 0){
                        $(this).find('.inner_li').show();
                    }
                },
            mouseleave:
                function () {
                    if(!options.submenu){
                        $('.inner_li').hide();
                    }
                    options.submenu = false;
                }
        });

    };  
    $.fn.contextMenu.mouseEnter = function(obj, first){
        if ( $(obj).children().size() > 0){
            if(first) options.submenu = true;
            $(obj).find('.inner_li').show();
        }
    };
    $.fn.contextMenu.mouseLeave = function(){
        if(!options.submenu){
            $('.inner_li').hide();
        }
        options.submenu = false;
    };
})(jQuery);  


