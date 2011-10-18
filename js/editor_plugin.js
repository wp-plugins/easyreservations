/*
function tinyplugin() {
	var Helpbox = '';
    return "[reservations]";
}

(function() {

    tinymce.create('tinymce.plugins.tinyplugin', {

        init : function(ed, url){
            ed.addButton('tinyplugin', {
                title : 'Insert easyReservations Standard Form',
                onclick : function() {
                    ed.execCommand(
                        'mceInsertContent',
                        false,
                        tinyplugin()
                        );
                },
                image: url + "/addform.png"
            });
        },

        getInfo : function() {
            return {
                longname : 'Add Reservation Form',
                author : 'Feryaz Beer',
                authorurl : 'http://www.feryaz.de',
                infourl : '',
                version : "1.0"
            };
        }
    });

    tinymce.PluginManager.add('tinyplugin', tinymce.plugins.tinyplugin);
    
})();
*/

// closure to avoid namespace collision
(function(){
	// creates the plugin
	tinymce.create('tinymce.plugins.mygallery', {
		// creates control instances based on the control's id.
		// our button's id is "mygallery_button"
		createControl : function(id, controlManager) {
			if (id == 'mygallery_button') {
				// creates the button
				var button = controlManager.createButton('mygallery_button', {
					title : 'MyGallery Shortcode', // title of the button
					image : '../wp-includes/images/smilies/icon_mrgreen.gif',  // path to the button's image
					onclick : function() {
						// triggers the thickbox
						var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 360 < width ) ? 360 : width;
						W = W - 40;
						H = H - 84;
						tb_show( 'My Gallery Shortcode', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=mygallery-form' );
					}
				});
				return button;
			}
			return null;
		}
	});
	
	// registers the plugin. DON'T MISS THIS STEP!!!
	tinymce.PluginManager.add('mygallery', tinymce.plugins.mygallery);
	
	// executes this when the DOM is ready
	jQuery(function(){
		// creates a form to be displayed everytime the button is clicked
		// you should achieve this using AJAX instead of direct html code like this
		var form = jQuery('<div id="mygallery-form">\
		<table id="mygallery-table" class="form-table">\
			<tr>\
				<th><label for="mygallery-type">Type</label></th>\
				<td><select name="type" id="mygallery-type">\
					<option value="reservations">Form</option>\
					<option value="reservationcalendar">Calendar</option>\
					<option value="editreservation">Edit Reservation</option>\
				</select><br /></td>\
			</tr>\
			<tr>\
				<th><label for="mygallery-id">Post ID</label></th>\
				<td><input type="text" name="id" id="mygallery-id" value="" /></td><br />\
			</tr>\
		</table>\
		<p class="submit">\
			<input type="button" id="mygallery-submit" class="button-primary" value="Insert Shortcode" name="submit" />\
		</p>\
		</div>');
		
		var table = form.find('table');
		form.appendTo('body').hide();
		
		// handles the click event of the submit button
		form.find('#mygallery-submit').click(function(){
			// defines the options and their default values
			// again, this is not the most elegant way to do this
			// but well, this gets the job done nonetheless
			var options = { 
				'type'    : '3',
				'id'         : '',
				};
			var shortcode = '[';
			
			for( var index in options) {
				var value = table.find('#mygallery-' + index).val();
				
				// attaches the attribute to the shortcode only if it's different from the default value
				if ( value !== options[index] )
					shortcode += value + ' ';
			}
			
			shortcode += ']';
			
			// inserts the shortcode into the active editor
			tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
			
			// closes Thickbox
			tb_remove();
		});
	});
})()