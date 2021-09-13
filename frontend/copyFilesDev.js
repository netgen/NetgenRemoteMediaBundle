const fs = require('fs');

const mappings = [
  {
    source: 'dist/app.js',
    targets: [
      '../bundle/Resources/public/js/remotemedia.js'
    ]
  }
];

const fakeFiles = [
  {
    targets: [
      '../bundle/Resources/public/js/remotemedia-vendors.js'
    ]
  },
  {
    targets: [
      '../bundle/Resources/public/css/remotemedia.css'
    ]
  },
  {
    targets: [
      '../bundle/Resources/public/css/remotemedia-vendors.css'
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
