var loadRemoteMedia =  function(textarea, ed, bookmark)
{
    require.config({
        shim : {
            jcrop : {
                deps : ['jquery-safe'],
                exports : 'jQuery.fn.Jcrop'
            }
        },
        paths : {
            'jcrop' : '/extension/ngremotemedia/design/standard/javascript/libs/jquery.jcrop.min',
            'remotemedia' : '/extension/ngremotemedia/design/ezexceed/javascript'
        }
    });
    require(['remotemedia/views/ezoe', 'remotemedia/templates'], function(View)
    {
        var view = new View({
            textEl : textarea,
            tinymceEditor : ed,
            bookmark : bookmark
        });
    });
}

tinymce.create('tinymce.plugins.RemotemediaPlugin', {
    init : function(ed, url)
    {
        // Register commands
        ed.addCommand('mceRemotemedia', function()
        {
            var bookmark = ed.selection.getBookmark();
            loadRemoteMedia(ed.getElement(), ed, bookmark);
        });

        // Register buttons
        ed.addButton('remotemedia', {title : 'Remotemedia', cmd : 'mceRemotemedia'});
    },

    getInfo : function()
    {
        return {
            longname : 'Remotemedia',
            author : 'Keyteq AS',
            authorurl : 'http://www.keyteq.no',
            infourl : 'http://www.keyteq.no',
            version : tinymce.majorVersion + "." + tinymce.minorVersion
        };
    }
});

// Register plugin
tinymce.PluginManager.add('remotemedia', tinymce.plugins.RemotemediaPlugin);
