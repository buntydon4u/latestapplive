const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

config.transformer.minifierConfig = {
  compress: {
    drop_console: true,
    passes: 2
  },
  mangle: {
    keep_fnames: false
  },
  output: {
    comments: false
  }
};

module.exports = config;
