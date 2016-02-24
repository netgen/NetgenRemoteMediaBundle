(function(tinymce) {

    var loadRemoteMedia =  function(){
        var textarea = this.getElement();
        //.block class is For administration
        var element = RemoteMediaShared.config().is_admin ? '.block' : '.eze-object-attribute';

        var options = {
            textEl: textarea,
            el: $(textarea).closest(element), 
            tinymceEditor: this
        };

        if (typeof require === 'function') {        

            require.config({
                map: {
                    'remotemedia/jcrop': {
                        'jquery': 'jquery-safe'
                    }
                },
                paths: {
                    'remotemedia': '/extension/ngremotemedia/design/ezexceed/javascript',
                    'remotemedia/jcrop': '/extension/ngremotemedia/design/standard/javascript/libs/jquery.jcrop'
                }
            });


            require(['remotemedia/views/ezoe'], function(View){
                new View(options);
            });
        }else{
            new RemoteMedia.views.EzOE(options);
        }
    };

    tinymce.create('tinymce.plugins.RemotemediaPlugin', {
        init: function(ed) {
            
            // Register commands
            ed.addCommand('mceRemotemedia', loadRemoteMedia);

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