(function(tinymce)
{
    tinymce.create('tinymce.plugins.RemotemediaPlugin', {
        init : function(ed, url)
        {
            // Register commands
            ed.addCommand('mceRemotemedia', function()
            {
                var textarea = ed.getElement();
                new RemoteMedia.views.EzOE({textEl : textarea, tinymceEditor : ed});
            });

            // Register buttons
            ed.addButton('remotemedia', {title : 'Remotemedia', cmd : 'mceRemotemedia'});

            /*ed.onNodeChange.add(function(ed, cm, n)
             {
             cm.setActive('remotemedia', n.nodeName === 'SPAN');
             });*/
        },

        getInfo : function()
        {
            return {
                longname : 'RemoteMedia',
                author : 'Keyteq AS',
                authorurl : 'http://www.keyteq.no',
                infourl : 'http://www.keyteq.no',
                version : tinymce.majorVersion + "." + tinymce.minorVersion
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('remotemedia', tinymce.plugins.RemotemediaPlugin);
})(tinymce);
