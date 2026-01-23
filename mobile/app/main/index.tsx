import { View, Text, StyleSheet, ScrollView, useColorScheme, TouchableOpacity, ImageBackground, Appearance } from 'react-native';
import { Colors } from '../../constants/Colors';
import { Ionicons, FontAwesome6 } from '@expo/vector-icons';
import { useEffect, useState } from 'react';
import { authService } from '../../services/authService';
import { celenganService } from '../../services/celenganService';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';

export default function Dashboard() {
    const router = useRouter();
    const colorScheme = useColorScheme();
    const isDark = colorScheme === 'dark';
    const [username, setUsername] = useState('User');
    const [summary, setSummary] = useState({ total_tabungan: 0, total_target: 0 });
    const [pinnedCelengan, setPinnedCelengan] = useState<any[]>([]);

    useEffect(() => {
        loadUser();
        loadData();
    }, []);

    const toggleTheme = () => {
        const newScheme = isDark ? 'light' : 'dark';
        Appearance.setColorScheme(newScheme);
    };

    const loadUser = async () => {
        const session = await authService.getSession();
        if (session && session.user) {
            setUsername(session.user.username);
        }
    };

    const loadData = async () => {
        try {
            const data = await celenganService.getList();
            if (data.status === 'success') {
                setSummary(data.summary);
                // Filter only pinned or take top 3 if none pinned
                const pinned = data.data.filter(c => c.is_pinned == 1);
                setPinnedCelengan(pinned.length > 0 ? pinned : data.data.slice(0, 3));
            }
        } catch (error) {
            console.error(error);
        }
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
    };

    const progress = summary.total_target > 0 ? (summary.total_tabungan / summary.total_target) * 100 : 0;

    return (
        <LinearGradient
            colors={isDark ? ['#1a1a2e', '#16213e'] : ['#667eea', '#764ba2']}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={{ flex: 1 }}
        >
            <ScrollView
                contentContainerStyle={styles.scrollContainer}
                showsVerticalScrollIndicator={false}
            >
                <View style={styles.header}>
                    <View>
                        <Text style={[styles.welcomeText, isDark && styles.welcomeTextDark]}>Halo,</Text>
                        <Text style={[styles.usernameText, isDark && styles.usernameTextDark]}>{username} 👋</Text>
                    </View>
                    <View style={{ flexDirection: 'row', gap: 12 }}>
                        <TouchableOpacity onPress={toggleTheme} style={[styles.profileButton, isDark && styles.profileButtonDark]}>
                            <Ionicons name={isDark ? "sunny" : "moon"} size={22} color={isDark ? "#FDB813" : Colors.text} />
                        </TouchableOpacity>
                        <TouchableOpacity style={[styles.profileButton, isDark && styles.profileButtonDark]}>
                            <Ionicons name="notifications-outline" size={22} color={isDark ? "white" : Colors.text} />
                        </TouchableOpacity>
                    </View>
                </View>

                {/* Stat Card (Glassmorphism) */}
                <View style={[styles.glassCard, styles.mainStatCard, isDark && styles.glassCardDark]}>
                    <View style={styles.statHeader}>
                        <View style={[styles.iconBox, { backgroundColor: 'rgba(59, 130, 246, 0.2)' }]}>
                            <Ionicons name="wallet" size={24} color="#3B82F6" />
                        </View>
                        <View>
                            <Text style={[styles.cardLabel, isDark && styles.cardLabelDark]}>TOTAL TABUNGAN</Text>
                            <Text style={[styles.cardValue, isDark && styles.cardValueDark]}>{formatCurrency(summary.total_tabungan)}</Text>
                        </View>
                    </View>

                    <Text style={[styles.cardSubtext, isDark && styles.cardSubtextDark]}>
                        Target: {formatCurrency(summary.total_target)} ({progress.toFixed(1)}%)
                    </Text>

                    <View style={[styles.progressBarBg, isDark && styles.progressBarBgDark]}>
                        <LinearGradient
                            colors={['#10B981', '#34D399']}
                            start={{ x: 0, y: 0 }}
                            end={{ x: 1, y: 0 }}
                            style={[styles.progressBarFill, { width: `${Math.min(progress, 100)}%` }]}
                        />
                    </View>
                </View>

                <View style={styles.sectionHeader}>
                    <Text style={[styles.sectionTitle, isDark && styles.sectionTitleDark]}>Celengan Favorit</Text>
                </View>

                {/* Vertical List (Column Layout) */}
                <View style={styles.verticalList}>
                    {pinnedCelengan.length === 0 ? (
                        <Text style={{ textAlign: 'center', color: isDark ? '#9CA3AF' : '#6B7280', marginTop: 20 }}>Belum ada celengan.</Text>
                    ) : (
                        pinnedCelengan.map((item) => {
                            const itemProgress = item.target > 0 ? (item.total / item.target) * 100 : 0;
                            return (
                                <TouchableOpacity
                                    key={item.id}
                                    style={[styles.glassCard, styles.itemCard, isDark && styles.glassCardDark]}
                                    onPress={() => router.push(`/celengan/${item.id}`)}
                                >
                                    <View style={styles.itemHeader}>
                                        <View style={[styles.itemIcon, { backgroundColor: 'rgba(102, 126, 234, 0.2)' }]}>
                                            <FontAwesome6 name="gift" size={20} color="#667eea" />
                                        </View>
                                        <View style={{ flex: 1 }}>
                                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                                                <Text style={[styles.itemTitle, isDark && styles.itemTitleDark]}>{item.nama_celengan}</Text>
                                                {item.is_pinned == 1 && (
                                                    <View style={styles.pinBadge}>
                                                        <Ionicons name="pin" size={10} color="white" />
                                                        <Text style={styles.pinText}>PINNED</Text>
                                                    </View>
                                                )}
                                            </View>
                                            <Text style={[styles.itemSubtitle, isDark && styles.itemSubtitleDark]}>
                                                Est. {item.estimasi_hari || 0} hari
                                            </Text>
                                        </View>
                                    </View>

                                    <View style={[styles.progressBarBg, { height: 8, marginTop: 12 }, isDark && styles.progressBarBgDark]}>
                                        <LinearGradient
                                            colors={['#667eea', '#764ba2']}
                                            start={{ x: 0, y: 0 }}
                                            end={{ x: 1, y: 0 }}
                                            style={[styles.progressBarFill, { width: `${Math.min(itemProgress, 100)}%` }]}
                                        />
                                    </View>

                                    <View style={styles.itemStats}>
                                        <Text style={[styles.statMin, isDark && styles.statMinDark]}>{formatCurrency(item.total)}</Text>
                                        <Text style={[styles.statMax, isDark && styles.statMaxDark]}>{formatCurrency(item.target)}</Text>
                                    </View>
                                </TouchableOpacity>
                            );
                        })
                    )}
                </View>
            </ScrollView>
        </LinearGradient>
    );
}

const styles = StyleSheet.create({
    scrollContainer: {
        padding: 24, // More breathing room
        paddingTop: 60,
        paddingBottom: 100,
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 32,
    },
    welcomeText: {
        fontSize: 16,
        color: Colors.textSecondary,
        fontWeight: '500',
        letterSpacing: 0.5,
    },
    welcomeTextDark: { color: Colors.textSecondaryDark },
    usernameText: {
        fontSize: 28, // Larger for modern look
        color: Colors.text,
        fontWeight: '800',
        letterSpacing: -0.5,
    },
    usernameTextDark: { color: Colors.white },
    profileButton: {
        width: 44,
        height: 44,
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: Colors.glass,
        borderRadius: 14,
        borderWidth: 1,
        borderColor: Colors.border,
    },
    profileButtonDark: {
        backgroundColor: Colors.glassDark,
        borderColor: Colors.borderDark,
    },

    // Glassmorphism Common Styles
    glassCard: {
        backgroundColor: Colors.glass,
        borderRadius: 24,
        borderWidth: 1,
        borderColor: Colors.border,
        shadowColor: '#1f2687',
        shadowOffset: { width: 0, height: 8 },
        shadowOpacity: 0.1,
        shadowRadius: 24,
        elevation: 8,
    },
    glassCardDark: {
        backgroundColor: Colors.glassDark,
        borderColor: Colors.borderDark,
        shadowColor: 'black',
        shadowOpacity: 0.3,
    },

    // Main Stat Card
    mainStatCard: {
        padding: 28,
        marginBottom: 40,
    },
    statHeader: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 24,
        gap: 16,
    },
    iconBox: {
        width: 60,
        height: 60,
        borderRadius: 20,
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: 'rgba(6, 182, 212, 0.1)', // Light Cyan BG
    },
    cardLabel: {
        fontSize: 12,
        color: Colors.textSecondary,
        marginBottom: 4,
        textTransform: 'uppercase',
        fontWeight: '700',
        letterSpacing: 1,
    },
    cardLabelDark: { color: Colors.textSecondaryDark },
    cardValue: {
        fontSize: 32,
        color: Colors.text,
        fontWeight: '800',
        letterSpacing: -1,
    },
    cardValueDark: { color: Colors.white },
    cardSubtext: {
        fontSize: 14,
        color: Colors.textSecondary,
        marginBottom: 16,
        fontWeight: '500',
    },
    cardSubtextDark: { color: Colors.textSecondaryDark },
    progressBarBg: {
        height: 8,
        backgroundColor: 'rgba(0,0,0,0.05)',
        borderRadius: 8,
        overflow: 'hidden',
    },
    progressBarBgDark: { backgroundColor: 'rgba(255,255,255,0.08)' },
    progressBarFill: {
        height: '100%',
        borderRadius: 8,
    },

    sectionHeader: { marginBottom: 20 },
    sectionTitle: {
        fontSize: 20,
        fontWeight: '700',
        color: Colors.text,
        letterSpacing: -0.5,
    },
    sectionTitleDark: { color: Colors.white },

    // Item Cards
    verticalList: {
        gap: 20,
    },
    itemCard: {
        padding: 20,
    },
    itemHeader: {
        flexDirection: 'row',
        gap: 16,
    },
    itemIcon: {
        width: 52,
        height: 52,
        borderRadius: 16,
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: 'rgba(102, 126, 234, 0.1)',
    },
    itemTitle: {
        fontSize: 17,
        fontWeight: '700',
        color: Colors.text,
    },
    itemTitleDark: { color: Colors.white },
    itemSubtitle: {
        fontSize: 13,
        color: Colors.textSecondary,
        marginTop: 4,
        fontWeight: '500',
    },
    itemSubtitleDark: { color: Colors.textSecondaryDark },
    pinBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(245, 158, 11, 0.2)', // Gold transparent
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 8,
        gap: 4,
        borderWidth: 1,
        borderColor: 'rgba(245, 158, 11, 0.3)',
    },
    pinText: {
        color: Colors.gold,
        fontSize: 10,
        fontWeight: '800',
        letterSpacing: 0.5,
    },
    itemStats: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginTop: 8,
    },
    statMin: {
        fontSize: 13,
        fontWeight: '600',
        color: Colors.textSecondary,
    },
    statMinDark: { color: Colors.textSecondaryDark },
    statMax: {
        fontSize: 13,
        fontWeight: '700',
        color: Colors.text,
    },
    statMaxDark: { color: Colors.white },
});
