var menuState = false;

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

/**
 * Returns value stored in data attribute
 * @param string $value
 * @returns null|string
 */
function _getGlobalValueFromDataAttribute($attribName) {
    var $el = $('div[data-'+$attribName+']');
    var $value = null;
    if ($el) {
        $value = $el.data($attribName);
    }
    return $value;
 }

$(document).ready(function(){

    /**
     * Tree setup
     * jQuery initialization of admin tree structure
     */

    $("#tree").treeview({
            collapsed: true,
            animated: "medium",
            control:"#sidetreecontrol",
            /*prerendered: true,*/
            persist: "cookie"
    });
    // feature for extending the tree pane in case of wider page titles
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


    /**
     * Ajax setup
     * All links and forms woth class ajax will be ajaxified
     * form.mfu aws added
     * @see http://addons.nette.org/cs/jquery-ajax
     *
     */
    $("a.ajax").live("click", function (event) {
        event.preventDefault();
        $.get(this.href);
    });
    $("form.ajax, form.mfu").live("submit", function () {
        $(this).ajaxSubmit();
        return false;
    });
    $("form.ajax :submit").live("click", function () {
        $(this).ajaxSubmit();
        return false;
    });

    /**
     * Rel external helper script
     */
    $('a[rel=external]').live('click',function(ev){
        window.open(this.href);
        ev.preventDefault();
        return false;
    });


    /**
     * Setup jQuery accordion
     */
    $( ".accordion" ).accordion({
        collapsible: true,
        active: false
    });


    /**
     * Setup jQuery nice buttons
     */
    $("div.smooth_toolbar > a, form.form-with-smooth-buttons input:submit").button();
    $("a.smooth_button").livequery(function() {
        $(this).button();
    })

    /**
     * Setup datepicker and range picker
     */
    $( ".datepicker" ).datepicker();
    $( "input.j_date_from" ).datepicker({
        changeMonth: true,
        numberOfMonths: 3,
        dateFormat: "dd.mm.yy",
        onSelect: function( selectedDate ) {
            $( "input.j_date_to" ).datepicker( "option", "minDate", selectedDate );
        }
    });
    $( "input.j_date_to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3,
        dateFormat: "dd.mm.yy",
        onSelect: function( selectedDate ) {
            $( "input.j_date_from" ).datepicker( "option", "maxDate", selectedDate );
        }
    });

    /**
     * Handler for page name listener
     */
    $('input[class^="_page_name"]').live('change', function() {
        var $_this = $(this);
        var $lang = $_this.attr('class').substring(11);
        $('._page_name_listener_'+$lang).val($_this.val());
    })


    // extract global varialbles
    var $path = _getGlobalValueFromDataAttribute('basepath');

    /**
     * TinyMCE setup
     */
    $('textarea[class=wysiwyg]').tinymce({
        // Location of TinyMCE script
        script_url : $path+'/js/tiny_mce/tiny_mce.js',
        editor_deselector : "notiny",

        // General options
        theme : "advanced",
        plugins : "vd,autolink,lists,pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist,inlinepopups",

        // Theme options
        theme_advanced_buttons1 : "vd,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        //theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        //theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        relative_urls:false,
        height:'400',
        // Example content CSS (should be your site CSS)
        content_css : $path+"/css/tiny.css"
    });


    /**
     * Setup handler for inserting media files form virtual drive
     */
    $('a.insertMedia').live('click', function(e) {
        e.preventDefault();
        var $_this = $(this);
        $('#'+$_this.attr('rel')).val($_this.attr('media-id'));
        var $content = $('#'+$_this.attr('rel')).closest('div.media-container').find('div.media-container-content');
        $content.html('<img src="'+$_this.attr('media-thumb')+'">');
        $.colorbox.close();
     });


    /**
     * Change panrent feature
     *
     */
    $("#frmpageForm-parent").live("change", function() {
        var $parentsUrl = _getGlobalValueFromDataAttribute('parentsurl');
        var $parent = $(this).val();
        $.get($parentsUrl, { treeNodeId: $parent }, function($data) {
            if ( $data.parentUrls) {
                for (var $langCode in $data.parentUrls) {
                    $('#frmpageForm-'+$langCode+'-parent_url').val($data.parentUrls[$langCode].parent_url);
                }
            }
        });
    });

    // ??
    $("#frmaclForm-role").live("change", function() {
        var $aclLink = _getGlobalValueFromDataAttribute('acllink');
        var $role = $(this).val();

        if ($role != '') {
            window.location = $aclLink+'?role='+$role;
        }

    });

});
