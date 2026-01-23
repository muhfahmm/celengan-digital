import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, useColorScheme, Alert } from 'react-native';
import { Colors } from '../../constants/Colors';
import { Ionicons } from '@expo/vector-icons';
import { authService } from '../../services/authService';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';

export default function ProfileScreen() {
    const router = useRouter();
    const colorScheme = useColorScheme();
    const isDark = colorScheme === 'dark';
    const [username, setUsername] = useState('User');

    useEffect(() => {
        loadUser();
    }, []);

    const loadUser = async () => {
        const session = await authService.getSession();
        if (session && session.user) {
            setUsername(session.user.username);
        }
    };

    const handleLogout = () => {
        Alert.alert(
            'Konfirmasi',
            'Yakin ingin keluar dari aplikasi?',
            [
                { text: 'Batal', style: 'cancel' },
                {
                    text: 'Keluar',
                    style: 'destructive',
                    onPress: async () => {
                        await authService.logout();
                        router.replace('/auth/login');
                    }
                }
            ]
        );
    };

    return (
        <LinearGradient
            colors={isDark ? ['#1a1a2e', '#16213e'] : ['#667eea', '#764ba2']}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={{ flex: 1 }}
        >
            <View style={styles.header}>
                <Text style={[styles.headerTitle, isDark && styles.headerTitleDark]}>Profil Saya</Text>
            </View>

            <View style={styles.content}>
                <View style={[styles.glassCard, isDark && styles.glassCardDark, { flexDirection: 'row', alignItems: 'center', marginBottom: 24 }]}>
                    <View style={styles.avatar}>
                        <Ionicons name="person" size={32} color="white" />
                    </View>
                    <View>
                        <Text style={[styles.profileName, isDark && styles.profileNameDark]}>{username}</Text>
                        <Text style={[styles.profileRole, isDark && styles.profileRoleDark]}>Member</Text>
                    </View>
                </View>

                <TouchableOpacity style={[styles.glassCard, styles.menuItem, isDark && styles.glassCardDark]}>
                    <View style={[styles.menuIcon, { backgroundColor: 'rgba(59, 130, 246, 0.1)' }]}>
                        <Ionicons name="settings-outline" size={22} color="#3B82F6" />
                    </View>
                    <Text style={[styles.menuLabel, isDark && styles.menuLabelDark]}>Pengaturan Akun</Text>
                    <Ionicons name="chevron-forward" size={20} color={isDark ? Colors.textSecondaryDark : "#9CA3AF"} />
                </TouchableOpacity>

                <TouchableOpacity style={[styles.glassCard, styles.menuItem, isDark && styles.glassCardDark]}>
                    <View style={[styles.menuIcon, { backgroundColor: 'rgba(16, 185, 129, 0.1)' }]}>
                        <Ionicons name="shield-checkmark-outline" size={22} color="#10B981" />
                    </View>
                    <Text style={[styles.menuLabel, isDark && styles.menuLabelDark]}>Keamanan</Text>
                    <Ionicons name="chevron-forward" size={20} color={isDark ? Colors.textSecondaryDark : "#9CA3AF"} />
                </TouchableOpacity>

                <TouchableOpacity
                    style={[styles.glassCard, styles.menuItem, isDark && styles.glassCardDark, { marginTop: 24, borderColor: 'rgba(239, 68, 68, 0.3)' }]}
                    onPress={handleLogout}
                >
                    <View style={[styles.menuIcon, { backgroundColor: 'rgba(239, 68, 68, 0.1)' }]}>
                        <Ionicons name="log-out-outline" size={22} color="#EF4444" />
                    </View>
                    <Text style={[styles.menuLabel, { color: '#EF4444' }]}>Keluar</Text>
                </TouchableOpacity>
            </View>
        </LinearGradient>
    );
}

const styles = StyleSheet.create({
    header: {
        paddingTop: 60,
        paddingHorizontal: 24,
        paddingBottom: 24,
    },
    headerTitle: {
        fontSize: 28,
        fontWeight: '800',
        color: Colors.text,
        letterSpacing: -0.5,
    },
    headerTitleDark: {
        color: 'white',
    },
    content: {
        padding: 24,
        paddingTop: 0,
        gap: 16,
    },
    glassCard: {
        backgroundColor: Colors.glass,
        borderRadius: 20,
        padding: 20,
        borderWidth: 1,
        borderColor: Colors.border,
        shadowColor: '#1f2687',
        shadowOffset: { width: 0, height: 8 },
        shadowOpacity: 0.1,
        shadowRadius: 16,
        elevation: 6,
    },
    glassCardDark: {
        backgroundColor: Colors.glassDark,
        borderColor: Colors.borderDark,
        shadowColor: 'black',
        shadowOpacity: 0.3,
    },
    avatar: {
        width: 60,
        height: 60,
        borderRadius: 20,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 16,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.4,
        shadowRadius: 10,
        elevation: 6,
    },
    profileName: {
        fontSize: 20,
        fontWeight: '800',
        color: Colors.text,
        letterSpacing: -0.5,
    },
    profileNameDark: {
        color: 'white',
    },
    profileRole: {
        fontSize: 14,
        color: Colors.textSecondary,
        marginTop: 2,
        fontWeight: '500',
    },
    profileRoleDark: {
        color: Colors.textSecondaryDark,
    },
    menuItem: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 16,
    },
    menuIcon: {
        width: 44,
        height: 44,
        borderRadius: 14,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 16,
    },
    menuLabel: {
        fontSize: 16,
        fontWeight: '600',
        color: Colors.text,
        flex: 1,
    },
    menuLabelDark: {
        color: 'white',
    },
});
