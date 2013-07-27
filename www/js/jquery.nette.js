/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * @copyright   Copyright (c) 2009 Jan Marek
 * @license     MIT
 * @link        http://nettephp.com/cs/extras/jquery-ajax
 * @version     0.2
 */


if (typeof String.prototype.startsWith != 'function') {
    String.prototype.startsWith = function (str){
        return this.indexOf(str) == 0;
    };
}

function _extractUrl(payload, lang) {
    $parentUrl = payload.state['urlEditor_'+lang+'-parentUrl'];
    if ($parentUrl != null && $parentUrl.length > 1) {
        $parentUrl = $parentUrl + '/';
    } else {
        $parentUrl = '/';
    }
    return $parentUrl + payload.state['urlEditor_'+lang+'-url'];
}

jQuery.extend({
    nette: {
        updateSnippet: function (id, html, isLastSnippet) {
            $("#" + id).html(html);

        //$.nette.afterLastSnippetUpdate();


        //            if (id == 'snippet--structureManager') {
        //                $("#" + id).html(html);
        //                if (isLastSnippet) {
        //                    $.nette.afterLastSnippetUpdate();
        //                }
        //            } else {
        //                if (isLastSnippet) {
        //                    $("#" + id).fadeTo("fast", 0.3, function () {
        //                        $(this).html(html).fadeTo("fast", 1, function () {
        //                            $.nette.afterLastSnippetUpdate();
        //                        });
        //                    });
        //
        //                } else {
        //                    $("#" + id).fadeTo("fast", 0.3, function () {
        //                        $(this).html(html).fadeTo("fast", 1);
        //                    });
        //                }
        //
        //            }
        //			$.nette.registerAfterUpdate();
        },

        afterLastSnippetUpdate: function() {
            //            $('.vd-content').mCustomScrollbar({
            //                set_width: 750,
            //                set_height:470,
            //                mouseWheelPixels: 154,
            //                scrollInertia: 170,
            //                advanced: {
            //                    updateOnContentResize: true
            //                }
            //            });
            //
            //            $('.vd-content').disableSelection();
            //            $('.vd-content').sortable({
            //                items: '.vd-sortMe',
            //                cancel: '.deleteButton',
            //                revert: 200,
            //                scroll: true,
            //                tolerance: "pointer",
            //                forcePlaceholderSize: true
            //            });
            //            $('.tp').tooltip({
            //                position:'top',
            //                opacity:1,
            //                borderRadius: 3,
            //                transition: 'fade',
            //                speed: 300
            //            });
            //initOrAjaxUpdate();

            //$('.vd-content').mCustomScrollbar('update');
            Nette.refreshValidaton();
        },

        registerAfterUpdate: function() {

        },

        success: function (payload) {
            // redirect
            if (payload.redirect) {
                window.location.href = payload.redirect;
                return;
            }


            if (payload.insertMedia) {
                $('#'+payload.insertMedia.cid).val(payload.insertMedia.mediaid);
                var $container = $('#'+payload.insertMedia.cid).closest('div.media-container');
                var $content = $container.find('div.media-container-content');

                $content.show();
                $('a.remove-media-item', $container).show();
                $content.html('<img src="'+payload.insertMedia.mediathumb+'">');
                $.colorbox.close();
            }


            if (payload.tinyControl) {

                switch (payload.tinyControl.command) {
                    case 'insertImage':
                        VirtualDrive.insertImage(payload.tinyControl.args);
                        break;
                }

            }

            if (payload.mediaContent) {
                //alert(payload.mediaContent.currentOffset);
                mediaJsConfig.offset = payload.mediaContent.currentOffset;
                $( ".mCSB_container" ).append(payload.mediaContent.html);
                //alert(payload.mediaContent.html);
                $( ".vd-content" ).mCustomScrollbar("update");
            }


            // snippets
            if (payload.snippets) {

                //alert(payload.snippets.size);

                var $numberOfSnippets = Object.keys(payload.snippets).length;
                var $snippetCounter = 0;
                var mediaData = null;

                for (var i in payload.snippets) {
                    $snippetCounter++;

                    jQuery.nette.updateSnippet(i, payload.snippets[i], $snippetCounter == $numberOfSnippets);

                    if (payload.mediaData) {
                        mediaData = payload.mediaData;
                    }
                    _ajaxUpdateSnippet(i, payload.state, mediaData);

                    switch (i) {
                        case 'snippet--structureManager':
                            $("#tree").treeview({
                                collapsed: true,
                                animated: "medium",
                                control:"#sidetreecontrol",
                                /*prerendered: true,*/
                                persist: "cookie"
                            });
                            getWidth();
                            break;

                        case 'snippet--flashMessages':
                            $('.flash').delay(5000).fadeOut('slow');
                            break;

                        case 'snippet-media-content-popUp':
                            openDialog();
                            break;
                    }

                }


                for (var i in payload.snippets) {

                    switch (i) {
                        case 'snippet--structureManager':
                            $("#tree").treeview({
                                collapsed: true,
                                animated: "medium",
                                control:"#sidetreecontrol",
                                /*prerendered: true,*/
                                persist: "cookie"
                            });
                            getWidth();
                            break;

                        case 'snippet--flashMessages':
                            $('.flash').delay(5000).fadeOut('slow');
                            break;

                        case 'snippet-media-content-popUp':
                            openDialog();
                            break;
                    }

                }

            }

        }
    }
});

jQuery.ajaxSetup({
    success: jQuery.nette.success,
    dataType: "json"
});

/**
 * AJAX form plugin for jQuery
 *
 * @copyright  Copyright (c) 2009 Jan Marek
 * @license    MIT
 * @link       http://nettephp.com/cs/extras/ajax-form
 * @version    0.1
 */

jQuery.fn.extend({
    ajaxSubmit: function (callback) {
        var form;
        var sendValues = {};

        // submit button
        if (this.is(":submit")) {
            form = this.parents("form");
            sendValues[this.attr("name")] = this.val() || "";

        // form
        } else if (this.is("form")) {
            form = this;

        // invalid element, do nothing
        } else {
            return null;
        }

        // validation
        if (form.get(0).onsubmit && !form.get(0).onsubmit()) return null;

        // get values
        var values = form.serializeArray();

        for (var i = 0; i < values.length; i++) {
            var name = values[i].name;

            // multi
            if (name in sendValues) {
                var val = sendValues[name];

                if (!(val instanceof Array)) {
                    val = [val];
                }

                val.push(values[i].value);
                sendValues[name] = val;
            } else {
                sendValues[name] = values[i].value;
            }
        }

        // send ajax request
        var ajaxOptions = {
            url: form.attr("action"),
            data: sendValues,
            type: form.attr("method") || "get"
        };

        if (callback) {
            ajaxOptions.success = callback;
        }

        return jQuery.ajax(ajaxOptions);
    }
});
