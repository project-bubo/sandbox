

(function() {
        
	tinymce.create('tinymce.plugins.BuboMenuPlugin', {
		init : function(ed, url) {
                },
                        
                createControl: function(n, cm) {
                    switch (n) {
                            case 'nsmenu':
                                    var c = cm.createMenuButton('nsmenu', {
                                            title : 'Netsars adictional functions',
                                            image : path+'/images/plugins.png',
                                            icons : false
                                    });

                                    c.onRenderMenu.add(function(c, m) {
                                        var args = {};
                                            $.ajax({
                                              url: nsmenuData,
                                              dataType: 'json',
                                              success: function(data){
                                                  $.each(data, function(key, val) {
                                                      var sub = m.addMenu({title : val.title});
                                                      $.each(val.submenu, function(i, value) {
                                                          sub.add({title : value.title, onclick : function() {
                                                             tinymce.extend(args, {
                                                                    id : value.command,
                                                                    'class': 'mceCMSBlock'
                                                             });
                                                             var el = tinyMCE.activeEditor.dom.createHTML('div', args, '<p style="text-align:center">'+val.title+'<br />'+value.title+'</p>');
                                                             var p = tinyMCE.activeEditor.dom.createHTML('p', '', el+'<br />');
                                                             tinyMCE.activeEditor.execCommand('mceInsertContent', false, p, {skip_undo : 1});
                                                                tinyMCE.activeEditor.execCommand('mceRepaint');
                                                                tinyMCE.activeEditor.focus();
                                                                tinyMCE.activeEditor.undoManager.add();
                                                          }});
                                                      });
                                                  });;
                                              }
                                            });
                                            
                                            /*var addMenuIthems = $.ajax({
                                              url: plugins,
                                              async: false
                                            }).responseText;
                                            */
                                            //sub.add({title : 'Recepty', onclick : function() { tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[**ns_msg(3)**]'); }});
                                            
                                    });

                                    
                                    // Return the new menu button instance
                                    return c;
                    }

                    return null;
                },
		

		getInfo : function() {
			return {
				longname : 'Bubo Menu plugin',
				author : 'NETSTARS s.r.o',
				authorurl : 'http://www.netstars.cz',
				infourl : '',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('nsmenu', tinymce.plugins.BuboMenuPlugin);
})();