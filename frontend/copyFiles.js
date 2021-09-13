const fs = require('fs');

const mappings = [
  {
    source: 'dist/js/app.js',
    targets: [
      '../bundle/Resources/public/js/remotemedia.js'
    ]
  },
  {
    source: 'dist/js/chunk-vendors.js',
    targets: [
      '../bundle/Resources/public/js/remotemedia-vendors.js'
    ]
  },
  {
    source: 'dist/css/app.css',
    targets: [
      '../bundle/Resources/public/css/remotemedia.css'
    ]
  },
  {
    source: 'dist/css/chunk-vendors.css',
    targets: [
      '../bundle/Resources/public/css/remotemedia-vendors.css'
    ]
  }
];

const copyFile = source => destination => {
  fs.copyFile(source, destination, err => {
    if (err) throw err;
    console.log(`${source} copied to ${destination}`);
  });
};

mappings.forEach(map => {
  map.targets.forEach(copyFile(map.source));
});
