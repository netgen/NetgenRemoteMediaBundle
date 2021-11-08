(function (global) {
  if (CKEDITOR.plugins.get('ngremotemedia')) {
    return;
  }

  var currentEditorInstance = null;

  function InsertMediaCallback(data, caption, align, cssClass) {
    let alignAttr = '';

    if (typeof align !== 'undefined' && align !== '') {
      alignAttr = ' data-ezalign="'+align+'"';
    }

    let html = '<div tabindex="-1" contenteditable="false" data-cke-widget-wrapper="1" data-cke-filter="off" ' +
      '             class="cke_widget_wrapper cke_widget_block cke_widget_ezcustomtag cke_widget_wrapper_ez-custom-tag--attributes-visible cke_widget_wrapper_ez-custom-tag" ' +
      '             data-cke-display-name="div" data-cke-widget-id="1" role="region" aria-label="div widget"' + alignAttr +
      '         >' +
      '             <div class="ez-custom-tag ez-custom-tag--attributes-visible cke_widget_element" data-ezelement="eztemplate" data-ezname="ngremotemedia"' +
      '                 data-cke-widget-keep-attr="0" data-widget="ezcustomtag" ' +
      '                 data-cke-widget-data="%7B%22name%22%3A%22customtag%22%2C%22content%22%3A%22%22%2C%22classes%22%3A%7B%22ez-custom-tag--attributes-visible%22%3A1%2C%22ez-custom-tag%22%3A1%7D%7D"' + alignAttr +
      '             >' +
      '                 <span data-ezelement="ezconfig">' +
      '                     <span data-ezelement="ezvalue" data-ezvalue-key="resourceId">' + data.resourceId + '</span>' +
      '                     <span data-ezelement="ezvalue" data-ezvalue-key="resourceType">' + data.type + '</span>' +
      '                     <span data-ezelement="ezvalue" data-ezvalue-key="coords">' + JSON.stringify(data.image_variations) + '</span>' +
      '                     <span data-ezelement="ezvalue" data-ezvalue-key="variation">' + data.selected_variation + '</span>' +
      '                     <span data-ezelement="ezvalue" data-ezvalue-key="caption">' + caption + '</span>' +
      '                     <span data-ezelement="ezvalue" data-ezvalue-key="cssclass">' + cssClass +'</span>' +
      '                 </span>' +
      '             </div>' +
      '         </div>';

    currentEditorInstance.insertHtml(html);
  }

  const InsertMedia = {
    exec: function (editor) {
      var data = {};
      window[`remoteMedia_ezrichtext`].setEditorInsertCallback(InsertMediaCallback);
      window[`remoteMedia_ezrichtext`].openEditorInsertModal(data);
      currentEditorInstance = editor;
    },
  };

  global.CKEDITOR.plugins.add('ngremotemedia', {
    init: (editor) => editor.addCommand('InsertMedia', InsertMedia),
  });
})(window);
