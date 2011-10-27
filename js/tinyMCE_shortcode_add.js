(function() {

    tinymce.create('tinymce.plugins.easyReservations', {

        init : function(ed, url){		
			ed.addCommand('easyReservations', function() {
				ed.windowManager.open({
					file : url + '/window.php',
					width : 360,
					height : 190,
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

			// Register example button
			ed.addButton('easyReservations', {
				title : 'easyReservations',
				cmd : 'shtb_adv_insert_cmd',
                image: url + "/day.png",
				onclick : function() {
								ed.windowManager.open({
					file : url + '/tinyMCE_shortcode_add.php',
					width : 400,
					height : 250,
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
				});
				}
			});
        },

        getInfo : function() {
            return {
                longname : 'Add Reservation Form',
                author : 'Feryaz Beer',
                authorurl : 'http://www.feryaz.de',
                infourl : '',
                version : "1.2"
            };
        }
    });

    tinymce.PluginManager.add('easyReservations', tinymce.plugins.easyReservations);
    
})();