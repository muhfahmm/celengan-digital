import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, useColorScheme, Alert } from 'react-native';
import { Colors } from '../../constants/Colors';
import { Ionicons } from '@expo/vector-icons';
import { authService } from '../../services/authService';
import { useRouter } from 'expo-router';

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
        <View style={styles.container}>
            <View style={styles.header}>
                <Text style={[styles.headerTitle, isDark && styles.headerTitleDark]}>Profil Saya</Text>
            </View>

            <View style={styles.content}>
                <View style={[styles.profileCard, isDark && styles.profileCardDark]}>
                    <View style={styles.avatar}>
                        <Ionicons name="person" size={40} color="white" />
                    </View>
                    <View>
                        <Text style={[styles.profileName, isDark && styles.profileNameDark]}>{username}</Text>
                        <Text style={styles.profileRole}>Member</Text>
                    </View>
                </View>

                <TouchableOpacity style={[styles.menuItem, isDark && styles.menuItemDark]}>
                    <View style={[styles.menuIcon, { backgroundColor: 'rgba(59, 130, 246, 0.1)' }]}>
                        <Ionicons name="settings-outline" size={20} color="#3B82F6" />
                    </View>
                    <Text style={[styles.menuLabel, isDark && styles.menuLabelDark]}>Pengaturan Akun</Text>
                    <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
                </TouchableOpacity>

                <TouchableOpacity style={[styles.menuItem, isDark && styles.menuItemDark]}>
                    <View style={[styles.menuIcon, { backgroundColor: 'rgba(16, 185, 129, 0.1)' }]}>
                        <Ionicons name="shield-checkmark-outline" size={20} color="#10B981" />
                    </View>
                    <Text style={[styles.menuLabel, isDark && styles.menuLabelDark]}>Keamanan</Text>
                    <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
                </TouchableOpacity>

                <TouchableOpacity
                    style={[styles.menuItem, isDark && styles.menuItemDark, { marginTop: 20 }]}
                    onPress={handleLogout}
                >
                    <View style={[styles.menuIcon, { backgroundColor: 'rgba(239, 68, 68, 0.1)' }]}>
                        <Ionicons name="log-out-outline" size={20} color="#EF4444" />
                    </View>
                    <Text style={[styles.menuLabel, { color: '#EF4444' }]}>Keluar</Text>
                </TouchableOpacity>
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
    },
    header: {
        paddingTop: 60,
        paddingHorizontal: 20,
        paddingBottom: 20,
    },
    headerTitle: {
        fontSize: 24,
        fontWeight: '800',
        color: Colors.text,
    },
    headerTitleDark: {
        color: 'white',
    },
    content: {
        padding: 20,
        gap: 12,
    },
    profileCard: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(255, 255, 255, 0.8)',
        padding: 20,
        borderRadius: 20,
        marginBottom: 20,
        borderWidth: 1,
        borderColor: 'rgba(255, 255, 255, 0.5)',
    },
    profileCardDark: {
        backgroundColor: 'rgba(31, 41, 55, 0.6)',
        borderColor: 'rgba(255, 255, 255, 0.1)',
    },
    avatar: {
        width: 60,
        height: 60,
        borderRadius: 30,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 16,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 4,
    },
    profileName: {
        fontSize: 20,
        fontWeight: '700',
        color: Colors.text,
    },
    profileNameDark: {
        color: 'white',
    },
    profileRole: {
        fontSize: 14,
        color: Colors.textSecondary,
    },
    menuItem: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(255, 255, 255, 0.8)',
        padding: 16,
        borderRadius: 16,
        borderWidth: 1,
        borderColor: 'rgba(255, 255, 255, 0.5)',
    },
    menuItemDark: {
        backgroundColor: 'rgba(31, 41, 55, 0.6)',
        borderColor: 'rgba(255, 255, 255, 0.1)',
    },
    menuIcon: {
        width: 40,
        height: 40,
        borderRadius: 12,
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
