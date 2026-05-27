const { withAppBuildGradle } = require('@expo/config-plugins');

const ABI_SPLITS = `
    splits {
        abi {
            enable true
            reset()
            include "arm64-v8a"
            universalApk false
        }
    }
`;

function addAbiSplits(buildGradle) {
  if (buildGradle.includes('splits {') && buildGradle.includes('arm64-v8a')) {
    return buildGradle;
  }

  return buildGradle.replace(/android\s*\{/, (match) => `${match}\n${ABI_SPLITS}`);
}

module.exports = function withAndroidAbiSplits(config) {
  return withAppBuildGradle(config, (configWithGradle) => {
    if (configWithGradle.modResults.language === 'groovy') {
      configWithGradle.modResults.contents = addAbiSplits(configWithGradle.modResults.contents);
    }

    return configWithGradle;
  });
};
