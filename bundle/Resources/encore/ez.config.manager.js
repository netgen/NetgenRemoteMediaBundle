const path = require('path');

module.exports = (eZConfig, eZConfigManager) => {
  eZConfigManager.add({
    eZConfig,
    entryName: 'ezplatform-admin-ui-alloyeditor-js',
    newItems: [
      path.resolve(__dirname, '../public/js/alloyeditor/buttons/ngremotemedia.js'),
      path.resolve(__dirname, '../public/js/alloyeditor/plugins/ngremotemedia.js'),
    ]
  });
};
