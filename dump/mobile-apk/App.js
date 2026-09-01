import { ActivityIndicator, Platform, StatusBar, StyleSheet, Text, View } from 'react-native';
import { WebView } from 'react-native-webview';
import WebPhoneFrame from './components/WebPhoneFrame';

const WEB_APP_URL = 'https://ubiquitous-dolphin-d5d38e.netlify.app';

export default function App() {
  return (
    <WebPhoneFrame>
      <MainApp />
    </WebPhoneFrame>
  );
}

function MainApp() {
  return (
    <View style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor="#ffffff" />
      <WebView
        source={{ uri: WEB_APP_URL }}
        style={styles.webview}
        cacheEnabled={false}
        cacheMode="LOAD_NO_CACHE"
        javaScriptEnabled
        domStorageEnabled
        sharedCookiesEnabled
        thirdPartyCookiesEnabled
        startInLoadingState
        originWhitelist={['*']}
        allowFileAccess
        allowFileAccessFromFileURLs
        allowUniversalAccessFromFileURLs
        mixedContentMode="never"
        renderLoading={() => (
          <View style={styles.loading}>
            <ActivityIndicator size="large" color="#b9273a" />
          </View>
        )}
        renderError={() => (
          <View style={styles.error}>
            <Text style={styles.errorTitle}>Unable to load app</Text>
            <Text style={styles.errorText}>The bundled React app could not be opened.</Text>
          </View>
        )}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#ffffff',
    paddingTop: Platform.OS === 'android' ? StatusBar.currentHeight || 0 : 0
  },
  webview: {
    flex: 1,
    backgroundColor: '#ffffff'
  },
  loading: {
    ...StyleSheet.absoluteFillObject,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#ffffff'
  },
  error: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
    backgroundColor: '#ffffff'
  },
  errorTitle: {
    color: '#161b26',
    fontSize: 18,
    fontWeight: '800',
    marginBottom: 8
  },
  errorText: {
    color: '#697185',
    textAlign: 'center'
  }
});
