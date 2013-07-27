

(function() {
        function findParentLayer(node) {
		do {
			if (node.className && node.className.indexOf('mceGallery') != -1) {
				return node;
			}
		} while (node = node.parentNode);
                return false;
	};
        var Event = tinymce.dom.Event;
	tinymce.create('tinymce.plugins.VirtualDrivePlugin', {
		init : function(ed, url) {
                        var node;
                        function isVDItem(node) {
				return node && (node.nodeName === 'IMG' || findParentLayer(node));
			};
			// Register commands
			ed.addCommand('mceVD', function() {
				// Internal image object like a flash placeholder
				if (ed.dom.getAttrib(ed.selection.getNode(), 'class', '').indexOf('mceItem') != -1)
					return;

				ed.windowManager.open({
					file : vdlink,
					width : 1024 + parseInt(ed.getLang('vd.delta_width', 0)),
					height : 635 + parseInt(ed.getLang('vd.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('vd', {
				title : 'Virtualni disk (Ctrl+Shift+D)',
				cmd : 'mceVD',
                                 image : url + '/img/drive_green.png'
			});
			ed.addShortcut('ctrl+shift+d', 'advlink.advlink_desc', 'mceVD');
                        ed.onMouseUp.add(function(ed, e) {
				var layer = findParentLayer(e.target);
	
				if (layer) {
					ed.dom.setAttrib(layer, 'data-mce-style', '');
				}
			});
                        ed.onContextMenu.addToTop(function(ed, e) {
                            Event.cancel(e);
                        });
                        ed.onPaste.addToTop(function(ed, e) {
                            var k = e.keyCode;
                            if (this.node && !((k > 32 && k < 41) || (k > 111 && k < 124))) {
                                Event.cancel(e);
                            }
                        });
                        ed.onKeyPress.addToTop(function(ed, e) {
                            var k = e.keyCode;
                            if (this.node && !((k > 32 && k < 41) || (k > 111 && k < 124))) {
                                Event.cancel(e);
                            }
                        });
                        ed.onKeyUp.addToTop(function(ed, e) {
                            var k = e.keyCode;
                            if (this.node && !((k > 32 && k < 41) || (k > 111 && k < 124))) {
                                Event.cancel(e);
                            }
                        });
                        ed.onKeyDown.addToTop(function(ed, e) {
                            var k = e.keyCode;
                            if ((k == 46 || k == 8) && this.node) {
                                ed.dom.remove(this.node);
				ed.execCommand('mceRepaint');
                            }else if(k == 13 && this.node){
                                var br = ed.dom.create('br');
                                ed.dom.insertAfter(br, this.node);
                                ed.execCommand('mceRepaint');
                                ed.selection.select(br);
                                return Event.cancel(e);
                            }else if(this.node && !((k > 32 && k < 41) || (k > 111 && k < 124))){
                                return Event.cancel(e);
                            }
                            return true;
                        });
			// Fixes edit focus issues with layers on Gecko
			// This will enable designMode while inside a layer and disable it when outside
			ed.onMouseDown.add(function(ed, e) {
				var node = e.target, doc = ed.getDoc(), parent;
                                node = findParentLayer(node);
                                if(node){
                                    if(this.node) ed.dom.removeClass(this.node,'mceGallerySelected');
                                    ed.dom.addClass(node,'mceGallerySelected');
                                }else{
                                    ed.dom.removeClass(this.node,'mceGallerySelected');
                                }
                                this.node = node;
				if (tinymce.isGecko) {
                                        
					if (node) {
						if (doc.designMode !== 'on') {
							doc.designMode = 'on';
                                                        
							// Repaint caret
							node = doc.body;
							parent = node.parentNode;
							parent.removeChild(node);
							parent.appendChild(node);
                                                        
						}
					} else if (doc.designMode == 'on') {
						doc.designMode = 'off';
					}
				}
			});
                        ed.onNodeChange.add(function(ed, cm, node) {
				cm.setActive('vd', isVDItem(node));
			});
		},

		getInfo : function() {
			return {
				longname : 'Virtual Drive',
				author : 'NETSTARS s.r.o',
				authorurl : 'http://www.netstars.cz',
				infourl : '',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('vd', tinymce.plugins.VirtualDrivePlugin);
})();