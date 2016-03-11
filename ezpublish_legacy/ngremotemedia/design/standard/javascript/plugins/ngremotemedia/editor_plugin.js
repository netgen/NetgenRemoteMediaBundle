(function(tinymce) {

        var View;

        (function (factory) {

            if (typeof require === 'function') {

                require.config({
                    map: {
                        'ngremotemedia/jcrop': {
                            'jquery': 'jquery-safe'
                        }
                    },
                    paths: {
                        'ngremotemedia': '/extension/ngremotemedia/design/ezexceed/javascript',
                        'shared': '/extension/ngremotemedia/design/standard/javascript/ngremotemedia/shared',
                        'ngremotemedia/jcrop': '/extension/ngremotemedia/design/standard/javascript/libs/jquery.jcrop'
                    }
                });


                if(!window.NgRemoteMediaShared){
                    require([
                        'handlebars',
                        'shared/templates',
                        'shared/models',
                        'shared/tagger',
                        'shared/browser',
                        'shared/upload',
                        'shared/scaled_version',
                        'shared/scaler',
                        'shared/ezoe',
                    ], function(){
                        require(['handlebars', 'ngremotemedia/views/ezoe'], factory)
                    });
                }else{
                    require(['handlebars', 'ngremotemedia/views/ezoe'], factory)
                }

            } else {
                factory(null, NgRemoteMedia.views.EzOE);
            }
        }(function (hbs, v) {
            hbs && _.extend(Handlebars.helpers, hbs.helpers); //Take ezExceed handlebars helpers
            View = v;
        }));      

        var loadRemoteMedia = function(){

            var textarea = this.getElement();
            //.block class is For administration
            var element = NgRemoteMediaShared.config().is_admin ? '.block' : '.eze-object-attribute';

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

})(tinymce);