tinymce.PluginManager.add('ngremotemedia', function(editor) {
    // Add a button that opens a modal
    editor.addButton('ngremotemedia', {
        text: false,
        icon: 'mce-ico mce-i-media',
        tooltip: 'Netgen remote media',
        onclick: function() {
            var fieldId = $(editor.targetElm).data('fieldid');
            window['remoteMedia'+fieldId].handleEditorInsertClicked();
        }
    });

    // Adds a menu item to the tools menu
    editor.addMenuItem('ngremotemedia', {
        text: 'Netgen remote media',
        icon: 'mce-ico mce-i-media',
        context: 'insert',
        onclick: function() {
            var fieldId = $(editor.targetElm).data('fieldid');
            window['remoteMedia'+fieldId].handleEditorInsertClicked();
        }
    });

    return {
        getMetadata: function () {
            return  {
                name: "Netgen remote media",
                url: "https://github.com/netgen/NetgenRemoteMediaBundle"
            };
        }
    };
});
