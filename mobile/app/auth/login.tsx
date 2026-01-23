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

export default function LoginScreen() {
    const router = useRouter();
    const colorScheme = useColorScheme();
    const isDark = colorScheme === 'dark';

    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');

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
        <KeyboardAvoidingView
            behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
            style={styles.container}
        >
            <View style={[styles.glassCard, isDark && styles.glassCardDark]}>
                <View style={styles.header}>
                    <View style={styles.logo}>
                        <FontAwesome6 name="piggy-bank" size={40} color="white" />
                    </View>
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

                    <TouchableOpacity style={styles.button} onPress={handleLogin}>
                        <Ionicons name="log-in-outline" size={22} color="white" style={{ marginRight: 8 }} />
                        <Text style={styles.buttonText}>Masuk</Text>
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
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        justifyContent: 'center',
        padding: 20,
    },
    glassCard: {
        backgroundColor: 'rgba(255, 255, 255, 0.9)',
        borderRadius: 24,
        padding: 30,
        borderWidth: 1,
        borderColor: 'rgba(255, 255, 255, 0.5)',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 8 },
        shadowOpacity: 0.15,
        shadowRadius: 24,
        elevation: 8,
    },
    glassCardDark: {
        backgroundColor: 'rgba(31, 41, 55, 0.8)',
        borderColor: 'rgba(255, 255, 255, 0.1)',
    },
    header: {
        alignItems: 'center',
        marginBottom: 32,
    },
    logo: {
        width: 70,
        height: 70,
        borderRadius: 18,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 16,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.4,
        shadowRadius: 10,
        elevation: 6,
    },
    title: {
        fontSize: 24,
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
        borderColor: 'rgba(0, 0, 0, 0.05)',
        borderRadius: 12,
        paddingHorizontal: 16,
    },
    inputWrapperDark: {
        backgroundColor: 'rgba(255, 255, 255, 0.05)',
        borderColor: 'rgba(255, 255, 255, 0.1)',
    },
    inputIcon: {
        marginRight: 10,
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
        backgroundColor: Colors.primary,
        height: 54,
        borderRadius: 12,
        justifyContent: 'center',
        alignItems: 'center',
        marginTop: 8,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 10,
        elevation: 4,
    },
    buttonText: {
        color: 'white',
        fontSize: 16,
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
        color: Colors.primary,
        fontWeight: '700',
    },
});
