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
                    'ngremotemedia/jcrop': {
                        'jquery': 'jquery-safe'
                    }
                },
                paths: {
                    'ngremotemedia': '/extension/ngremotemedia/design/ezexceed/javascript',
                    'ngremotemedia/jcrop': '/extension/ngremotemedia/design/standard/javascript/libs/jquery.jcrop'
                }
            });


            require(['ngremotemedia/views/ezoe'], function(View){
                new View(options);
            });
        }else{
            new NgRemoteMedia.views.EzOE(options);
        }
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

})(tinymce);