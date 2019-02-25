(function(tinymce) {
    function initializeEditor(){
        NgRemoteMediaTranslations = window.NgRemoteMediaTranslations || {};

        RemoteMediaSettings = window.RemoteMediaSettings  || {
            'ez_contentobject_id': eZOeGlobalSettings.ez_contentobject_id,
            'ez_contentobject_version': eZOeGlobalSettings.ez_contentobject_version,
            'url_prefix': eZOeGlobalSettings.ez_extension_url.replace('/ezoe', '')
        };

        var View = NgRemoteMedia.views.EzOE;

        var loadRemoteMedia = function(){

            var textarea = this.getElement();
            var element = '.block';

            var options = {
                textEl: textarea,
                el: $(textarea).closest(element),
                tinymceEditor: this
            };

            new View(options);
        };

        tinymce.create('tinymce.plugins.RemotemediaPlugin', {
            init: function(ed) {

                // Register commands
                ed.addCommand('mceRemotemedia', loadRemoteMedia);

                // Register buttons
                ed.addButton('ngremotemedia', { title: 'NgRemoteMedia', cmd: 'mceRemotemedia' });

            },

            getInfo: function() {
                return {
                    longname: 'NgRemoteMedia',
                    author: 'Netgen',
                    authorurl: 'http://www.netgenlabs.com',
                    infourl: 'http://www.netgenlabs.com',
                    version: tinymce.majorVersion + "." + tinymce.minorVersion
                };
            }
        });

        // Register plugin
        tinymce.PluginManager.add('ngremotemedia', tinymce.plugins.RemotemediaPlugin);
    }

    setTimeout(initializeEditor, 0);
})(tinymce);
