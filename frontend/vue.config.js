module.exports = {
  runtimeCompiler: true,
  devServer: {
    proxy: {
      '^/ngadminui': {
        target: 'http://remote-media.dev6.netgen.biz',
        changeOrigin: true,
        headers: {
          cookie: 'eZSESSID=o7oaf2s3aroc8reislknrvqc4b;'
        }
      }
    }
  },
  filenameHashing: false
};
