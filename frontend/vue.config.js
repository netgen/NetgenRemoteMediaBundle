module.exports = {
  runtimeCompiler: true,
  devServer: {
    proxy: {
      '^/ngadminui': {
        target: 'http://remote-media.dev6.netgen.biz',
        changeOrigin: true,
        headers: {
          cookie: `eZSESSID=${process.env.DEV_PROXY_SESSION_ID};`
        }
      }
    }
  },
  filenameHashing: false
};
