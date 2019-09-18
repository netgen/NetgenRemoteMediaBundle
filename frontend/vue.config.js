module.exports = {
  runtimeCompiler: true,
  devServer: {
    proxy: {
      '^/ngadminui': {
        target: 'http://media.site',
        changeOrigin: true,
        headers: {
          cookie: 'eZSESSID=md9u8ed2f63e3fq4kljb1scb2j;'
        }
      }
    }
  }
};
