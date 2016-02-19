(function(tinymce) {
    tinymce.create('tinymce.plugins.RemotemediaPlugin', {
        init: function(ed /*, url*/ ) {
            
            // Register commands
            ed.addCommand('mceRemotemedia', function() {
                var textarea = ed.getElement();
                new RemoteMedia.views.EzOE({
                    textEl: textarea,
                    el: $(textarea).closest('.block'), //.block class is For administration
                    tinymceEditor: ed
                });
            });

            // Register buttons
            ed.addButton('remotemedia', { title: 'RemoteMedia', cmd: 'mceRemotemedia' });

        },

        getInfo: function() {
            return {
                longname: 'RemoteMedia',
                author: 'Netgen',
                authorurl: 'http://www.netgenlabs.com',
                infourl: 'http://www.netgenlabs.com',
                version: tinymce.majorVersion + "." + tinymce.minorVersion
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('remotemedia', tinymce.plugins.RemotemediaPlugin);

})(tinymce);