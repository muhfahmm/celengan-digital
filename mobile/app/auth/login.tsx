import React, { useState } from 'react';
import {
    View,
    Text,
    TextInput,
    TouchableOpacity,
    StyleSheet,
    KeyboardAvoidingView,
    Platform,
    useColorScheme,
} from 'react-native';
import { Link, useRouter } from 'expo-router';
import { Colors } from '../../constants/Colors';
import { Ionicons, FontAwesome6 } from '@expo/vector-icons';
import { authService } from '../../services/authService';

import { LinearGradient } from 'expo-linear-gradient';
import { Appearance } from 'react-native';

export default function LoginScreen() {
    const router = useRouter();
    const colorScheme = useColorScheme();
    const isDark = colorScheme === 'dark';

    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');

    const toggleTheme = () => {
        const newScheme = isDark ? 'light' : 'dark';
        Appearance.setColorScheme(newScheme);
    };

    const handleLogin = async () => {
        try {
            const response = await authService.login(username, password);
            if (response.status === 'success') {
                await authService.setSession(response);
                router.replace('/main');
            } else {
                alert(response.message || 'Login gagal!');
            }
        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan koneksi');
        }
    };

    return (
        <LinearGradient
            colors={isDark ? ['#1a1a2e', '#16213e'] : ['#667eea', '#764ba2']}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.gradientContainer}
        >
            <TouchableOpacity onPress={toggleTheme} style={styles.themeToggle}>
                <Ionicons
                    name={isDark ? "sunny" : "moon"}
                    size={24}
                    color={isDark ? "#FDB813" : "#1F2937"}
                />
            </TouchableOpacity>

            <KeyboardAvoidingView
                behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
                style={styles.container}
            >
                <View style={[styles.glassCard, isDark && styles.glassCardDark]}>
                    <View style={styles.header}>
                        <LinearGradient
                            colors={['#667eea', '#764ba2']}
                            start={{ x: 0, y: 0 }}
                            end={{ x: 1, y: 1 }}
                            style={styles.logo}
                        >
                            <FontAwesome6 name="piggy-bank" size={32} color="white" />
                        </LinearGradient>
                        <Text style={[styles.title, isDark && styles.titleDark]}>Selamat Datang</Text>
                        <Text style={[styles.subtitle, isDark && styles.subtitleDark]}>
                            Masuk ke akun Celengan Digital Anda
                        </Text>
                    </View>

                    <View style={styles.form}>
                        <View style={styles.inputGroup}>
                            <Text style={[styles.label, isDark && styles.labelDark]}>Username</Text>
                            <View style={[styles.inputWrapper, isDark && styles.inputWrapperDark]}>
                                <Ionicons name="person-outline" size={20} color="#9CA3AF" style={styles.inputIcon} />
                                <TextInput
                                    style={[styles.input, isDark && styles.inputDark]}
                                    placeholder="Masukkan username"
                                    placeholderTextColor="#9CA3AF"
                                    value={username}
                                    onChangeText={setUsername}
                                    autoCapitalize="none"
                                />
                            </View>
                        </View>

                        <View style={styles.inputGroup}>
                            <Text style={[styles.label, isDark && styles.labelDark]}>Password</Text>
                            <View style={[styles.inputWrapper, isDark && styles.inputWrapperDark]}>
                                <Ionicons name="lock-closed-outline" size={20} color="#9CA3AF" style={styles.inputIcon} />
                                <TextInput
                                    style={[styles.input, isDark && styles.inputDark]}
                                    placeholder="Masukkan password"
                                    placeholderTextColor="#9CA3AF"
                                    value={password}
                                    onChangeText={setPassword}
                                    secureTextEntry
                                />
                            </View>
                        </View>

                        <TouchableOpacity activeOpacity={0.8} onPress={handleLogin}>
                            <LinearGradient
                                colors={['#667eea', '#764ba2']}
                                start={{ x: 0, y: 0 }}
                                end={{ x: 1, y: 1 }}
                                style={styles.button}
                            >
                                <Ionicons name="log-in-outline" size={22} color="white" style={{ marginRight: 8 }} />
                                <Text style={styles.buttonText}>Masuk</Text>
                            </LinearGradient>
                        </TouchableOpacity>
                    </View>

                    <View style={styles.footer}>
                        <Text style={[styles.footerText, isDark && styles.footerTextDark]}>
                            Belum punya akun?{' '}
                            <Link href="/auth/register" asChild>
                                <Text style={styles.link}>Daftar Sekarang</Text>
                            </Link>
                        </Text>
                    </View>
                </View>
            </KeyboardAvoidingView>
        </LinearGradient>
    );
}

const styles = StyleSheet.create({
    gradientContainer: {
        flex: 1,
    },
    themeToggle: {
        position: 'absolute',
        top: 50,
        right: 20,
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: 'rgba(255, 255, 255, 0.9)',
        justifyContent: 'center',
        alignItems: 'center',
        zIndex: 10,
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 4,
        elevation: 4,
    },
    container: {
        flex: 1,
        justifyContent: 'center',
        padding: 20,
    },
    glassCard: {
        backgroundColor: 'rgba(255, 255, 255, 0.95)',
        borderRadius: 24,
        padding: 40,
        borderWidth: 1,
        borderColor: 'rgba(255, 255, 255, 0.6)',
        shadowColor: '#1f2687',
        shadowOffset: { width: 0, height: 8 },
        shadowOpacity: 0.2,
        shadowRadius: 32,
        elevation: 10,
    },
    glassCardDark: {
        backgroundColor: 'rgba(31, 41, 55, 0.4)',
        borderColor: 'rgba(255, 255, 255, 0.1)',
    },
    header: {
        alignItems: 'center',
        marginBottom: 32,
    },
    logo: {
        width: 64,
        height: 64,
        borderRadius: 16,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 16,
        shadowColor: '#667eea',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.4,
        shadowRadius: 16,
        elevation: 6,
    },
    title: {
        fontSize: 28,
        fontWeight: '800',
        color: '#1F2937',
        marginBottom: 8,
    },
    titleDark: {
        color: '#F3F4F6',
    },
    subtitle: {
        fontSize: 14,
        color: '#6B7280',
        textAlign: 'center',
        fontWeight: '500',
    },
    subtitleDark: {
        color: 'rgba(255, 255, 255, 0.7)',
    },
    form: {
        gap: 20,
    },
    inputGroup: {
        gap: 8,
    },
    label: {
        fontSize: 13,
        fontWeight: '600',
        color: '#374151',
        textTransform: 'uppercase',
        letterSpacing: 0.5,
    },
    labelDark: {
        color: 'rgba(255, 255, 255, 0.8)',
    },
    inputWrapper: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(255, 255, 255, 0.8)',
        borderWidth: 2,
        borderColor: 'rgba(0, 0, 0, 0.1)',
        borderRadius: 12,
        paddingHorizontal: 16,
    },
    inputWrapperDark: {
        backgroundColor: 'rgba(255, 255, 255, 0.05)',
        borderColor: 'rgba(255, 255, 255, 0.1)',
    },
    inputIcon: {
        marginRight: 12,
    },
    input: {
        flex: 1,
        height: 48,
        fontSize: 15,
        color: '#1F2937',
        fontWeight: '500',
    },
    inputDark: {
        color: '#F3F4F6',
    },
    button: {
        flexDirection: 'row',
        height: 52,
        borderRadius: 12,
        justifyContent: 'center',
        alignItems: 'center',
        marginTop: 8,
        shadowColor: '#667eea',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.4,
        shadowRadius: 20,
        elevation: 5,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.2)',
    },
    buttonText: {
        color: 'white',
        fontSize: 15,
        fontWeight: '700',
    },
    footer: {
        marginTop: 24,
        alignItems: 'center',
    },
    footerText: {
        fontSize: 14,
        color: '#6B7280',
    },
    footerTextDark: {
        color: 'rgba(255, 255, 255, 0.7)',
    },
    link: {
        color: '#667eea',
        fontWeight: '600',
    },
});
