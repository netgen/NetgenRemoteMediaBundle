const fs = require('fs-extra');

const isProd = process.env.NODE_ENV === 'production';

const fakeDevFiles = [
  '../bundle/Resources/public/js/remotemedia-vendors.js',
  '../bundle/Resources/public/css/remotemedia.css',
  '../bundle/Resources/public/css/remotemedia-vendors.css',
];

let moveFileMap = [];
if (isProd) {
  moveFileMap = [
    {
      source: 'dist/js/app.js',
      target: '../bundle/Resources/public/js/remotemedia.js',
    },
    {
      source: 'dist/js/chunk-vendors.js',
      target: '../bundle/Resources/public/js/remotemedia-vendors.js',
    },
    {
      source: 'dist/css/app.css',
      target: '../bundle/Resources/public/css/remotemedia.css',
    },
    {
      source: 'dist/css/chunk-vendors.css',
      target: '../bundle/Resources/public/css/remotemedia-vendors.css',
    },
  ];
} else {
  moveFileMap = [
    {
      source: 'dist/app.js',
      target: '../bundle/Resources/public/js/remotemedia.js',
    },
  ];
}

module.exports = {
  runtimeCompiler: true,
  filenameHashing: false,
  configureWebpack: {
    entry: {
      app: './src/main.js',
    },
    plugins: [{
      apply: (compiler) => {
        compiler.hooks.afterEmit.tap('AfterEmitPlugin', (compilation) => {
          moveFileMap.forEach(({source, target}) => {
            fs.copySync(source, target);
          });

          if (!isProd) {
            fakeDevFiles.forEach((filePath) => {
              fs.closeSync(fs.openSync(filePath, 'w'));
            })
          }
        });
      }
    }],
  }
};
