import { Stack } from 'expo-router';
import { useColorScheme, View, StyleSheet } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Colors } from '../constants/Colors';
import { StatusBar } from 'expo-status-bar';

export default function RootLayout() {
    const colorScheme = useColorScheme();
    const isDark = colorScheme === 'dark';
    const bgColors = isDark ? Colors.backgroundDark : Colors.background;

    return (
        <View style={styles.container}>
            <LinearGradient
                colors={bgColors as [string, string, ...string[]]}
                style={StyleSheet.absoluteFill}
            />
            <StatusBar style={isDark ? 'light' : 'dark'} />
            <Stack
                screenOptions={{
                    headerShown: false,
                    contentStyle: { backgroundColor: 'transparent' },
                    animation: 'fade',
                }}
            />
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
    },
});
