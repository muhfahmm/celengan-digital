import { View, Text, StyleSheet, ScrollView, useColorScheme, TouchableOpacity } from 'react-native';
import { Colors } from '../../constants/Colors';
import { Ionicons } from '@expo/vector-icons';
import { useEffect, useState } from 'react';
import { authService } from '../../services/authService';
import { useRouter } from 'expo-router';

export default function Dashboard() {
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

    return (
        <ScrollView
            contentContainerStyle={styles.scrollContainer}
            showsVerticalScrollIndicator={false}
        >
            <View style={styles.header}>
                <View>
                    <Text style={[styles.welcomeText, isDark && styles.welcomeTextDark]}>Halo,</Text>
                    <Text style={[styles.usernameText, isDark && styles.usernameTextDark]}>{username} 👋</Text>
                </View>
                <TouchableOpacity style={[styles.profileButton, isDark && styles.profileButtonDark]}>
                    <Ionicons name="notifications-outline" size={22} color={isDark ? "white" : Colors.text} />
                </TouchableOpacity>
            </View>

            <View style={[styles.card, isDark && styles.cardDark]}>
                <Text style={[styles.cardLabel, isDark && styles.cardLabelDark]}>Total Tabungan</Text>
                <Text style={[styles.cardValue, isDark && styles.cardValueDark]}>Rp 2.500.000</Text>
                <Text style={[styles.cardSubtext, isDark && styles.cardSubtextDark]}>Target: Rp 5.000.000 (50%)</Text>

                <View style={styles.progressBarBg}>
                    <View style={[styles.progressBarFill, { width: '50%' }]} />
                </View>
            </View>

            <View style={styles.sectionHeader}>
                <Text style={[styles.sectionTitle, isDark && styles.sectionTitleDark]}>Celengan Favorit</Text>
            </View>

            {/* Placeholder content */}
            <View style={[styles.glassCard, isDark && styles.glassCardDark]}>
                <View style={styles.celenganIcon}>
                    <Ionicons name="car" size={24} color="white" />
                </View>
                <View style={{ flex: 1 }}>
                    <Text style={[styles.celenganTitle, isDark && styles.celenganTitleDark]}>Beli Mobil</Text>
                    <Text style={[styles.celenganSubtitle, isDark && styles.celenganSubtitleDark]}>Rp 1.000.000 / Rp 100.000.000</Text>
                </View>
            </View>

        </ScrollView>
    );
}

const styles = StyleSheet.create({
    scrollContainer: {
        padding: 20,
        paddingTop: 60,
        paddingBottom: 100,
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 24,
    },
    welcomeText: {
        fontSize: 16,
        color: Colors.textSecondary,
        fontWeight: '500',
    },
    welcomeTextDark: {
        color: 'rgba(255,255,255,0.6)',
    },
    usernameText: {
        fontSize: 24,
        color: Colors.text,
        fontWeight: '800',
    },
    usernameTextDark: {
        color: 'white',
    },
    profileButton: {
        padding: 10,
        backgroundColor: 'rgba(255,255,255,0.8)',
        borderRadius: 12,
    },
    profileButtonDark: {
        backgroundColor: 'rgba(255,255,255,0.1)',
    },
    card: {
        backgroundColor: 'rgba(255, 255, 255, 0.9)',
        borderRadius: 24,
        padding: 24,
        marginBottom: 32,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 8 },
        shadowOpacity: 0.2,
        shadowRadius: 16,
        elevation: 8,
    },
    cardDark: {
        backgroundColor: 'rgba(31, 41, 55, 0.8)',
        borderColor: 'rgba(255, 255, 255, 0.1)',
        borderWidth: 1,
    },
    cardLabel: {
        fontSize: 14,
        color: Colors.textSecondary,
        marginBottom: 8,
        textTransform: 'uppercase',
        fontWeight: '600',
    },
    cardLabelDark: {
        color: 'rgba(255,255,255,0.6)',
    },
    cardValue: {
        fontSize: 32,
        color: Colors.text,
        fontWeight: '800',
        marginBottom: 8,
    },
    cardValueDark: {
        color: 'white',
    },
    cardSubtext: {
        fontSize: 14,
        color: Colors.textSecondary,
        marginBottom: 16,
    },
    cardSubtextDark: {
        color: 'rgba(255,255,255,0.6)',
    },
    progressBarBg: {
        height: 10,
        backgroundColor: 'rgba(0,0,0,0.05)',
        borderRadius: 5,
        overflow: 'hidden',
    },
    progressBarFill: {
        height: '100%',
        backgroundColor: Colors.success,
        borderRadius: 5,
    },
    sectionHeader: {
        marginBottom: 16,
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: '700',
        color: Colors.text,
    },
    sectionTitleDark: {
        color: 'white',
    },
    glassCard: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(255, 255, 255, 0.6)',
        borderRadius: 16,
        padding: 16,
        borderWidth: 1,
        borderColor: 'rgba(255, 255, 255, 0.4)',
        gap: 16,
    },
    glassCardDark: {
        backgroundColor: 'rgba(31, 41, 55, 0.6)',
        borderColor: 'rgba(255, 255, 255, 0.1)',
    },
    celenganIcon: {
        width: 48,
        height: 48,
        borderRadius: 12,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
    },
    celenganTitle: {
        fontSize: 16,
        fontWeight: '700',
        color: Colors.text,
    },
    celenganTitleDark: {
        color: 'white',
    },
    celenganSubtitle: {
        fontSize: 13,
        color: Colors.textSecondary,
        marginTop: 4,
    },
    celenganSubtitleDark: {
        color: 'rgba(255,255,255,0.6)',
    },
});
