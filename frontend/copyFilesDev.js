const fs = require('fs');

const mappings = [
  {
    source: 'dist/app.js',
    targets: [
      '../bundle/Resources/public/js/remotemedia.js',
      '../bundle/ezpublish_legacy/ngremotemedia/design/standard/javascript/remotemedia.js'
    ]
  },
  {
    source: 'dist/editor_plugin.js',
    targets: [
      '../bundle/ezpublish_legacy/ngremotemedia/design/standard/javascript/plugins/ngremotemedia/editor_plugin.js'
    ]
  }
];

const fakeFiles = [
  {
    targets: [
      '../bundle/Resources/public/js/remotemedia-vendors.js',
      '../bundle/ezpublish_legacy/ngremotemedia/design/standard/javascript/remotemedia-vendors.js'
    ]
  },
  {
    targets: [
      '../bundle/Resources/public/css/remotemedia.css',
      '../bundle/ezpublish_legacy/ngremotemedia/design/standard/stylesheets/remotemedia.css'
    ]
  },
  {
    targets: [
      '../bundle/Resources/public/css/remotemedia-vendors.css',
      '../bundle/ezpublish_legacy/ngremotemedia/design/standard/stylesheets/remotemedia-vendors.css'
    ]
  },
];

const copyFile = source => destination => {
  fs.copyFile(source, destination, err => {
    if (err) throw err;
    console.log(`${source} copied to ${destination}`);
  });
};

const fakeFile = path => {
  fs.closeSync(fs.openSync(path, 'w'))
  console.log(`Faked ${path}`);
}

mappings.forEach(map => {
  map.targets.forEach(copyFile(map.source));
});

fakeFiles.forEach(fakes => {
  fakes.targets.forEach(fakeFile);
})
