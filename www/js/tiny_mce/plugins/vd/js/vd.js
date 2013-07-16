var VirtualDrive = {
	preInit : function() {
            $(document).bind('contextmenu',function(event){
                event.preventDefault();
            });

            var ed = tinyMCEPopup.editor;
            var n = ed.selection.getNode();


            //http://localhost/choice/www/admin/media/?media-navBar-navBarItem-gallery_2-galleryId=2&media-folderId=2&media-section=galleries&media-fileId=9&media-trigger=page&media-content-actions=gallery&do=media-navBar-navBarItem-gallery_2-enterGallery

            //alert(n.nodeName);

		/*var url;

		tinyMCEPopup.requireLangPack();

		if (url = tinyMCEPopup.getParam("external_image_list_url"))
			document.write('<script language="javascript" type="text/javascript" src="' + tinyMCEPopup.editor.documentBaseURI.toAbsolute(url) + '"></script>');
                */
	},

    getSelection: function(){
        var ed = tinyMCEPopup.editor,
        sel = ed.selection.getContent({format : 'text'})
        if(sel){
            $('#link_title').val(sel);
            $('#frm_img').val(0);
            $('.th').hide();
            $('#insert_link').show();
        }else{
            $('#link_title').val('odkaz');
        }

    },

	init : function(ed) {
		var f = document.forms[0],
                //nl = f.elements,
                ed = tinyMCEPopup.editor,
                dom = ed.dom,
                n = ed.selection.getNode(),
                fl = tinyMCEPopup.getParam('external_image_list', 'tinyMCEImageList');


//        alert('/choice/www/admin/tiny/');

//        ed.windowManager.open({
//            file : '/choice/www/admin/tiny/',
//                width : 1024 + parseInt(ed.getLang('vd.delta_width', 0)),
//                height : 635 + parseInt(ed.getLang('vd.delta_height', 0)),
//                inline : 1
//        });

		tinyMCEPopup.resizeToInnerSize();

		if (n.nodeName == 'IMG') {

		}

		// Setup browse button
		//document.getElementById('srcbrowsercontainer').innerHTML = getBrowserHTML('srcbrowser','src','image','theme_advanced_image');
		if (isVisible('srcbrowser'))
			document.getElementById('src').style.width = '260px';

	},

	insert : function(file, title) {
            var t = this;
            /*
		var ed = tinyMCEPopup.editor, t = this, f = document.forms[0];

		if (f.src.value === '') {
			if (ed.selection.getNode().nodeName == 'IMG') {
				ed.dom.remove(ed.selection.getNode());
				ed.execCommand('mceRepaint');
			}

			tinyMCEPopup.close();
			return;
		}

		if (tinyMCEPopup.getParam("accessibility_warnings", 1)) {
			if (!f.alt.value) {
				tinyMCEPopup.confirm(tinyMCEPopup.getLang('advimage_dlg.missing_alt'), function(s) {
					if (s)
						t.insertAndClose();
				});

				return;
			}
		}
            */

		//t.insertAndClose();
        t.insertImage();
	},

    insertImage: function(params) {

        var ed = tinyMCEPopup.editor;
        ed.execCommand('mceInsertContent', false, params.html, {skip_undo : 1});
        ed.undoManager.add();
        tinyMCEPopup.close();

    },

    insertDiv: function() {
       var ed = tinyMCEPopup.editor,
                f = document.forms[1],
                nl = f.elements,
                args = {},
                el;

		tinyMCEPopup.restoreSelection();

		// Fixes crash in Safari
		if (tinymce.isWebKit)
			ed.getWin().focus();
                tinymce.extend(args, {
                        id : nl.id.value,
                        'class': 'mceCMSFile'
                });
		el = ed.selection.getNode();

		if (el && el.nodeName == 'DIV') {
			ed.dom.setAttribs(el, args);
		} else {
            tinymce.each(args, function(value, name) {
                if (value === "") {
                    delete args[name];
                }
            });

            var thumb = tinyMCEPopup.editor.dom.createHTML('IMG',{src:tinyMCEPopup.editor.baseURI.path+'/plugins/vd/img/icons/'+nl.icon.value+'_icon.png'});
            var div = tinyMCEPopup.editor.dom.createHTML('div', args, nl.name.value+'<p style="text-align:center">'+thumb+'</p>');
			ed.execCommand('mceInsertContent', false, tinyMCEPopup.editor.dom.createHTML('p','',div), {skip_undo : 1});
			ed.undoManager.add();
		}

		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.editor.focus();
		tinyMCEPopup.close();
    },

	insertAndClose : function() {

		var ed = tinyMCEPopup.editor,
                f = document.forms[0],
                nl = f.elements,
                v,
                args = {},
                el;

		tinyMCEPopup.restoreSelection();

		// Fixes crash in Safari
		if (tinymce.isWebKit)
			ed.getWin().focus();
/*
		if (!ed.settings.inline_styles) {
			args = {
				vspace : nl.vspace.value,
				hspace : nl.hspace.value,
				border : nl.border.value,
				align : getSelectValue(f, 'align')
			};
		} else {
			// Remove deprecated values
			args = {
				vspace : '',
				hspace : '',
				border : '',
				align : ''
			};
		}

		tinymce.extend(args, {
			src : nl.src.value.replace(/ /g, '%20'),
			width : nl.width.value,
			height : nl.height.value,
			alt : nl.alt.value,
			title : nl.title.value,
			'class' : getSelectValue(f, 'class_list'),
			style : nl.style.value,
			id : nl.id.value,
			dir : nl.dir.value,
			lang : nl.lang.value,
			usemap : nl.usemap.value,
			longdesc : nl.longdesc.value
		});

		args.onmouseover = args.onmouseout = '';

		if (f.onmousemovecheck.checked) {
			if (nl.onmouseoversrc.value)
				args.onmouseover = "this.src='" + nl.onmouseoversrc.value + "';";

			if (nl.onmouseoutsrc.value)
				args.onmouseout = "this.src='" + nl.onmouseoutsrc.value + "';";
		}
*/

                if(nl.image && nl.image.value == '1'){
                    tinymce.extend(args, {
                            src : nl.src.value/*.replace(/ /g, '%20')+"&width="+nl.width.value*/,
                            width : nl.width.value,
                            height : nl.height.value,
                            'class': nl.cssclass.value
                    });
                    el = ed.selection.getNode();

                    if (el && el.nodeName == 'IMG') {
                            ed.dom.setAttribs(el, args);
                    } else {
                            tinymce.each(args, function(value, name) {
                                    if (value === "") {
                                            delete args[name];
                                    }
                            });

                            ed.execCommand('mceInsertContent', false, ed.dom.createHTML('img', args), {skip_undo : 1});
                            ed.undoManager.add();
                    }
                }else{
                    tinymce.extend(args, {
                            href : nl.src.value.replace(/ /g, '%20')
                    });
                    el = ed.selection.getNode();

                    if (el && el.nodeName == 'A') {
                            ed.dom.setAttribs(el, args);
                    } else {
                            tinymce.each(args, function(value, name) {
                                    if (value === "") {
                                            delete args[name];
                                    }
                            });
                            var $text = ed.selection.getContent({format : 'text'}) ? ed.selection.getContent({format : 'text'}) : (nl.link_title ? nl.link_title.value : 'odkaz');
                            ed.execCommand('mceInsertContent', false, ed.dom.createHTML('A', args, $text), {skip_undo : 1});
                            ed.undoManager.add();
                    }
                }
		ed.execCommand('mceRepaint');
		ed.focus();
		tinyMCEPopup.close();
	},

        insertGallery : function() {

		var ed = tinyMCEPopup.editor,
                f = document.forms[0],
                nl = f.elements,
                args = {},
                el;

		tinyMCEPopup.restoreSelection();

		// Fixes crash in Safari
		if (tinymce.isWebKit)
			ed.getWin().focus();
                tinymce.extend(args, {
			id : nl.gallery.value,
                        'class': 'mceGallery'
                });
		el = ed.selection.getNode();

		if (el && el.nodeName == 'DIV') {
			ed.dom.setAttribs(el, args);
		} else {
			tinymce.each(args, function(value, name) {
				if (value === "") {
					delete args[name];
				}
			});

                        var thumb = tinyMCEPopup.editor.dom.createHTML('IMG',{src:nl.thumb.value});
                        var div = tinyMCEPopup.editor.dom.createHTML('div', args, nl.name.value+'<p style="text-align:center">'+thumb+'</p>');
			ed.execCommand('mceInsertContent', false, tinyMCEPopup.editor.dom.createHTML('p','',div), {skip_undo : 1});
			ed.undoManager.add();
		}

		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.editor.focus();
		tinyMCEPopup.close();
	},

	getAttrib : function(e, at) {
		var ed = tinyMCEPopup.editor, dom = ed.dom, v, v2;

		if (ed.settings.inline_styles) {
			switch (at) {
				case 'align':
					if (v = dom.getStyle(e, 'float'))
						return v;

					if (v = dom.getStyle(e, 'vertical-align'))
						return v;

					break;

				case 'hspace':
					v = dom.getStyle(e, 'margin-left')
					v2 = dom.getStyle(e, 'margin-right');

					if (v && v == v2)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;

				case 'vspace':
					v = dom.getStyle(e, 'margin-top')
					v2 = dom.getStyle(e, 'margin-bottom');
					if (v && v == v2)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;

				case 'border':
					v = 0;

					tinymce.each(['top', 'right', 'bottom', 'left'], function(sv) {
						sv = dom.getStyle(e, 'border-' + sv + '-width');

						// False or not the same as prev
						if (!sv || (sv != v && v !== 0)) {
							v = 0;
							return false;
						}

						if (sv)
							v = sv;
					});

					if (v)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;
			}
		}

		if (v = dom.getAttrib(e, at))
			return v;

		return '';
	},

	setSwapImage : function(st) {
		var f = document.forms[0];

		f.onmousemovecheck.checked = st;
		setBrowserDisabled('overbrowser', !st);
		setBrowserDisabled('outbrowser', !st);

		if (f.over_list)
			f.over_list.disabled = !st;

		if (f.out_list)
			f.out_list.disabled = !st;

		f.onmouseoversrc.disabled = !st;
		f.onmouseoutsrc.disabled  = !st;
	},


	resetImageData : function() {
		var f = document.forms[0];

		f.elements.width.value = f.elements.height.value = '';
	},

	updateImageData : function(img, st) {
		var f = document.forms[0];

		if (!st) {
			f.elements.width.value = img.width;
			f.elements.height.value = img.height;
		}

		this.preloadImg = img;
	},

	changeAppearance : function() {
		var ed = tinyMCEPopup.editor, f = document.forms[0], img = document.getElementById('alignSampleImg');

		if (img) {
			if (ed.getParam('inline_styles')) {
				ed.dom.setAttrib(img, 'style', f.style.value);
			} else {
				img.align = f.align.value;
				img.border = f.border.value;
				img.hspace = f.hspace.value;
				img.vspace = f.vspace.value;
			}
		}
	},

	changeHeight : function() {
		var f = document.forms[0], tp, t = this;

		if (!f.constrain.checked || !t.preloadImg) {
			return;
		}

		if (f.width.value == "" || f.height.value == "")
			return;

		tp = (parseInt(f.width.value) / parseInt(t.preloadImg.width)) * t.preloadImg.height;
		f.height.value = tp.toFixed(0);
	},

	changeWidth : function() {
		var f = document.forms[0], tp, t = this;

		if (!f.constrain.checked || !t.preloadImg) {
			return;
		}

		if (f.width.value == "" || f.height.value == "")
			return;

		tp = (parseInt(f.height.value) / parseInt(t.preloadImg.height)) * t.preloadImg.width;
		f.width.value = tp.toFixed(0);
	},

	updateStyle : function(ty) {
		var dom = tinyMCEPopup.dom, b, bStyle, bColor, v, isIE = tinymce.isIE, f = document.forms[0], img = dom.create('img', {style : dom.get('style').value});

		if (tinyMCEPopup.editor.settings.inline_styles) {
			// Handle align
			if (ty == 'align') {
				dom.setStyle(img, 'float', '');
				dom.setStyle(img, 'vertical-align', '');

				v = getSelectValue(f, 'align');
				if (v) {
					if (v == 'left' || v == 'right')
						dom.setStyle(img, 'float', v);
					else
						img.style.verticalAlign = v;
				}
			}

			// Handle border
			if (ty == 'border') {
				b = img.style.border ? img.style.border.split(' ') : [];
				bStyle = dom.getStyle(img, 'border-style');
				bColor = dom.getStyle(img, 'border-color');

				dom.setStyle(img, 'border', '');

				v = f.border.value;
				if (v || v == '0') {
					if (v == '0')
						img.style.border = isIE ? '0' : '0 none none';
					else {
						if (b.length == 3 && b[isIE ? 2 : 1])
							bStyle = b[isIE ? 2 : 1];
						else if (!bStyle || bStyle == 'none')
							bStyle = 'solid';
						if (b.length == 3 && b[isIE ? 0 : 2])
							bColor = b[isIE ? 0 : 2];
						else if (!bColor || bColor == 'none')
							bColor = 'black';
						img.style.border = v + 'px ' + bStyle + ' ' + bColor;
					}
				}
			}

			// Handle hspace
			if (ty == 'hspace') {
				dom.setStyle(img, 'marginLeft', '');
				dom.setStyle(img, 'marginRight', '');

				v = f.hspace.value;
				if (v) {
					img.style.marginLeft = v + 'px';
					img.style.marginRight = v + 'px';
				}
			}

			// Handle vspace
			if (ty == 'vspace') {
				dom.setStyle(img, 'marginTop', '');
				dom.setStyle(img, 'marginBottom', '');

				v = f.vspace.value;
				if (v) {
					img.style.marginTop = v + 'px';
					img.style.marginBottom = v + 'px';
				}
			}

			// Merge
			dom.get('style').value = dom.serializeStyle(dom.parseStyle(img.style.cssText), 'img');
		}
	},

	changeMouseMove : function() {
	},

	showPreviewImage : function(u, st) {
		if (!u) {
			tinyMCEPopup.dom.setHTML('prev', '');
			return;
		}

		if (!st && tinyMCEPopup.getParam("advimage_update_dimensions_onchange", true))
			this.resetImageData();

		u = tinyMCEPopup.editor.documentBaseURI.toAbsolute(u);

		if (!st)
			tinyMCEPopup.dom.setHTML('prev', '<img id="previewImg" src="' + u + '" border="0" onload="VirtualDrive.updateImageData(this);" onerror="VirtualDrive.resetImageData();" />');
		else
			tinyMCEPopup.dom.setHTML('prev', '<img id="previewImg" src="' + u + '" border="0" onload="VirtualDrive.updateImageData(this, 1);" />');
	}
};

VirtualDrive.preInit();
tinyMCEPopup.onInit.add(VirtualDrive.init, VirtualDrive);
