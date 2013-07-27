// flexibile slider resizing
$(window).resize(function() {

    var wWidth = $(window).width();
    if (wWidth > 1920) {
        wWidth = 1920;
    } else if (wWidth < 960) {
         wWidth = 960;
    };
    var sliderHeight = Math.round(wWidth/2.666);
    var controlsPos = Math.round(sliderHeight/2);

    $('#slideshow, #slideshow img').css({
        width:wWidth,
        height:sliderHeight
    });

    $('.slide-popup-outer').css({
        height:sliderHeight
    });

    $('#controls').css({
        top: (controlsPos - 60)
    });
    
});


// document ready
$(document).ready(function() {

    Shadowbox.init({
        "counterType": "skip",
        overlayOpacity: 0.3,
        skipSetup: true
    });


    
    $('.career-pos').click(function() {
        var cTitle = $(this).find('span.cTitle').text();
        var cText = $(this).find('span.cText').text();

        Shadowbox.open({
            content:    '<div class="career-position-text"><h2>'+cTitle+'</h2>'+cText+'</div>',
            player:     "html",
            width:      500,
            height:     250
        });

    });




    $('.carousel').each(function(){
        $( this ).lemmonSlider({

    'loop' : false

        });

    });





    var wWidth = $(window).width();
    var sliderHeight = Math.round(wWidth/2.666);
    var controlsPos = Math.round(sliderHeight/2);

    $('#slideshow, #slideshow img').css({
        width:wWidth,
        height:sliderHeight
    });

    $('#slideshow, .slide-popup-outer').css({
        height: sliderHeight
    });

    $('#controls').css({
        top: (controlsPos - 60)
    });


    //submenu
    $('.main-menu li, .lang-switcher li').hover(function() {
        $(this).find('.submenu, .lang-dropdown').stop(true,true).fadeIn("fast");
    }, function() {
        $(this).find('.submenu, .lang-dropdown').stop(true,true).delay(300).fadeOut("fast");
    });

    //slider
    $(function() {

        $('#slideshow').cycle({

            fx:      'fade',
            timeout:  4000,
            prev:    '#prev',
            next:    '#next',
            pager:   '#nav',
            pause:    1,
            speed: 300,
            pagerAnchorBuilder: pagerFactory
        });
        //pager
        function pagerFactory(idx, slide) {
            var s = idx > 5 ? ' style="display:none"' : '';
            return '<li'+s+' class="brand_logo brand_'+(idx+1)+'"><a href="#"></a></li>';

        };

    });
        

    //arrows fade
    $('#controls span').css({opacity:0.5});
    $('#controls span').hover(function() {
        $(this).stop(true,true).animate({opacity:1},200);
    }, function() {
        $(this).stop(true,true).animate({opacity:0.5},200);
    });

    //map plugin init
    $('img[usemap]').maphilight();

    //map tooltips
    $('.tooltips .tp').hide();
    $('.ar').hover(function() {
        var kraj = $(this).attr('id');
        $('.tp.'+kraj).stop(true,true).fadeToggle("fast");
    });

    //set width of address container
    var regionsCount = $('div.region').length;
    $('.shop-map-address').css({
        width: (960 * regionsCount)
    });

    //shop addresses scroll
    $('.ar').click(function(e) {
        e.preventDefault();

        var kraj = $(this).attr('id');
        var rel = $(this).attr('rel');

        var position = '-'+(960 * rel)+'px';
        $('.shop-map-address').stop(true,true).animate({
            left: position
        },200);

        var thisRegionCount = $('.region.r'+rel).find('.address-box').length;
        
        var thisRows = Math.ceil(thisRegionCount / 3);
        
        if (thisRows == 1) {
            var regionHeight = 190
        } else if (thisRows == 2) {
            var regionHeight = 290
        } else {
            var regionHeight = 390
        };

        $('.addresses-container').animate({
            height: regionHeight
        },250);



        
    });

    $('.newsletter-input').click(function(e) {
        e.stopPropagation();
    });

    $('html,body').live('click',function() {
        $('.newsletter-input').hide();
    });

    //scroll addresses to vision
    var offset = $('.shop-map-outer').offset();
    $('map .ar').click(function() {
        $('body,html').animate({ scrollTop: offset.top },"500");
    });

    //newsletter sign up
    $('.newsletter-button').find('a').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('.newsletter-input').toggle();
    });

    /* ----------
        gallery
    --------------*/
    //pocet obrazku
    var imgCount = $('div.thumb-box').length;
    //vypocitat pocet stranek
    var pages = Math.ceil(imgCount / 15)

    //vypsat stranky
    for (var i = 1; i <= pages; i++) {
        $('.thumbs-controls').append('<span rel="'+i+'" class="page">'+i+'</span>');
    };

    //nastavit active class na prvni stranku a obrazek
    $('.thumbs-controls .page[rel=1]').addClass("active");
    $('.thumb-box:nth-child(1)').addClass("active");
    //nastavit prvni obrazek
    getSrc('.thumb-box:nth-child(1)')

    $('.thumbs-controls .page').click(function() {
        //vypocitani top atrributu pro posun stranky
        if ( $(this).hasClass('active') == false ) {

            var pageNumber = $(this).attr('rel');
            var vpHeight = -635; //vyska viditelneho pasma s nahledy
            var cssTop = (pageNumber - 1) * vpHeight;
            $('.gallery-viewport').hide().css({
                top: cssTop
            }).fadeIn(300);

            //pridat active class na paginator
            if ( $(this).attr('rel') == pageNumber  ) {
                $('.thumbs-controls .page').removeClass("active");
                $(this).addClass("active");
            };

            var firstOnPage = (pageNumber * 15) - 14;
            $('.thumb-box').removeClass('active');
            $('.thumb-box:nth-child('+firstOnPage+')').addClass('active');
            var obr = $('.thumb-box.active');
            getSrc(obr);
                
        };

    });



    //zmenit obrazek po kliknuti
    $('.thumb-box').click(function(e){
        e.preventDefault();

        if ($(this).hasClass('active') == false) {
            $('.spinner').hide().delay(300).fadeIn(300);
            $('.thumb-box').removeClass('active');
            $(this).addClass('active');
            getSrc(this);
        };
        

        
    });

    //prepinani obrazku pomoci sipek
    $('.gallery-controls a').click(function(e) {
        
        var index = $('.thumb-box.active').index();


        var arrow = $(this).hasClass('left');
        if (arrow == true) { // prepnout stranku doleva
            $('.thumb-box.active').prev().addClass('active').next().removeClass('active');
            var obr = $('.thumb-box.active');
            getSrc(obr);
            var mod = index % 15

            //prepnout stranku kdyz jsme na konci
            if (mod == 0 && index != 0) {
                $('.thumbs-controls .page.active').prev().trigger('click');
                //prepnout na posledni obrazek na strance jdeme-li zpet
                var pageNumber = $('.thumbs-controls .page.active').attr('rel');
                var lastOnPage = (pageNumber * 15);
                var obr = $('.thumb-box.active');
                getSrc(obr);

                $('.thumb-box').removeClass('active');
                $('.thumb-box:nth-child('+lastOnPage+')').addClass('active');
                var obr = $('.thumb-box.active');
                getSrc(obr);
            };
            
        } else { // prepnout stranku doprava
            $('.thumb-box.active').next().addClass('active').prev().removeClass('active');
            var obr = $('.thumb-box.active');
            getSrc(obr);
            var mod = (index + 1) % 15
            //prepnout stranku kdyz jsme na konci
            if (mod == 0) {
                $('.thumbs-controls .page.active').next().trigger('click');
            };

            
        };


        e.preventDefault();
    });

    $('.popup-button').click(function() {
        $('.popup').fadeIn("fast");
        return false;
    });
 
    $('.close-button').click(function() {
        $('.popup').fadeOut("fast");
    });

    $('.input-box-form div.required').append('<span class="asterisk"></span>');


});// END document ready

//prepinani pomoci klavesnice
$(document).keydown(function(e) {
    var left = 37;
    var up = 38;
    var right = 39;
    var down = 40;
    
    var imgCount = $('div.thumb-box').length;

    switch(e.which) {
        case left:
        $('.gallery-controls .arrow.left').trigger('click');
        $('.controls .prev-page').trigger('click');
        break;

        case up:
        var i = $('.thumb-box.active').prev().index() -2;
        if (i < 0) {
            i = 0;
        };
        $('.thumb-box').eq(i).trigger('click');
        var mod = (i) % 15
            //prepnout stranku kdyz jsme na konci
            if (mod >= 12 && mod <= 14) {
                $('.thumbs-controls .page.active').prev().trigger('click');
                var pageNumber = $('.thumbs-controls .page.active').attr('rel');
                var lastOnPage = (pageNumber * 15);
                switch (mod) {
                    case 12:
                    lastOnPage = lastOnPage - 2
                    break;
                    case 13:
                    lastOnPage = lastOnPage - 1
                    break;
                    case 14:
                    lastOnPage
                    break;
                    default: return;
                }
                
                $('.thumb-box:nth-child('+lastOnPage+')').trigger('click');
            };
            
        break;

        case right:
        $('.gallery-controls .arrow.right').trigger('click');
        $('.controls .next-page').trigger('click');
        break;

        case down:
        var i = $('.thumb-box.active').next().index() +2;
        if (i > imgCount) {
            i = imgCount;
        };
         $('.thumb-box').eq(i).trigger('click');
         var mod = (i) % 15
            //prepnout stranku kdyz jsme na konci
            
            if (mod >= 0 && mod <= 2) {
                $('.thumbs-controls .page.active').next().trigger('click');
                var pageNumber = $('.thumbs-controls .page.active').attr('rel');
                var firstOnPage = (pageNumber * 15) - 14;
                
                switch (mod) {
                    case 0:
                    firstOnPage
                    break;
                    case 1:
                    firstOnPage = firstOnPage + 1
                    break;
                    case 2:
                    firstOnPage = firstOnPage + 2
                    break;
                    default: return;
                }
                $('.thumb-box:nth-child('+firstOnPage+')').trigger('click');
                
            };
            
        break;

        default: return;
    }
    e.preventDefault();
    
});


function getSrc(what) {
 
        //get href attribut
        var file = $(what).find('a').attr('href');
 
        $('.gallery-main').find('img').stop(true,false).fadeOut(200, function() {
            $(this).attr('src', file).delay(500).fadeIn(200);
        });
 
        var imgCount = $('div.thumb-box').length;
        var currentImg = $('div.thumb-box.active').index() + 1;
 
        //zobrazit popisek
         var title = $(what).find('a').attr('title');
            if (title == null) {
                $('.gallery-controls .name').text("");
            } else {
                $('.gallery-controls .name').html(title+"<br/><span class='gallery-count'>"+currentImg+"/"+imgCount+"</span>");
            };
}



