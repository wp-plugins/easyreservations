function tinyplugin() {
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
