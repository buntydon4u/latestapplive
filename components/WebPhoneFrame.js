import { Platform, SafeAreaView, StyleSheet, useWindowDimensions, View } from 'react-native';

export default function WebPhoneFrame({ children }) {
  const { width, height } = useWindowDimensions();

  if (Platform.OS !== 'web') {
    return children;
  }

  const frameWidth = Math.min(390, Math.max(0, width - 24));
  const frameHeight = Math.min(844, Math.max(0, height - 24));

  return (
    <SafeAreaView style={styles.page}>
      <View style={[styles.frame, { width: frameWidth, height: frameHeight }]}>
        <View style={styles.notch} />
        <View style={styles.screen}>{children}</View>
      </View>
    </SafeAreaView>
  );
}

const styles = Platform.OS === 'web'
  ? StyleSheet.create({
      page: {
        flex: 1,
        minHeight: '100vh',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 12,
        backgroundColor: '#eef1f6'
      },
      frame: {
        position: 'relative',
        padding: 10,
        borderRadius: 46,
        backgroundColor: '#111318',
        borderWidth: 1,
        borderColor: '#2c3038',
        shadowColor: '#101828',
        shadowOffset: { width: 0, height: 28 },
        shadowOpacity: 0.32,
        shadowRadius: 48,
        boxShadow: '0 28px 70px rgba(16, 24, 40, 0.32), 0 4px 16px rgba(16, 24, 40, 0.22)'
      },
      notch: {
        position: 'absolute',
        top: 18,
        left: '50%',
        width: 118,
        height: 32,
        marginLeft: -59,
        borderRadius: 18,
        backgroundColor: '#050608',
        zIndex: 2
      },
      screen: {
        flex: 1,
        overflow: 'hidden',
        borderRadius: 36,
        backgroundColor: '#ffffff'
      }
    })
  : StyleSheet.create({});
