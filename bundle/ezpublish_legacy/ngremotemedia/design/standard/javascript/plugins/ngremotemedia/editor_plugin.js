(function(tinymce) {
    tinymce.PluginManager.add('ngremotemedia', function(editor) {
        // Add a button that opens a modal
        editor.addButton('ngremotemedia', {
            title: 'Insert remote media',
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
})(tinymce);
