(function (global) {
  if (CKEDITOR.plugins.get('ngremotemedia')) {
    return;
  }

  function InsertMediaCallback(data, caption, cssClass) {

  }

  const InsertMedia = {
    exec: function (editor) {
      var data = {};
      window[`remoteMediaezrichtext`].setEditorInsertCallback(InsertMediaCallback);
      window[`remoteMediaezrichtext`].openEditorInsertModal(data);
    },
  };

  global.CKEDITOR.plugins.add('ngremotemedia', {
    init: (editor) => editor.addCommand('InsertMedia', InsertMedia),
  });
})(window);
