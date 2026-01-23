import React, { useEffect, useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, useColorScheme, RefreshControl, ScrollView } from 'react-native';
import { Colors } from '../../constants/Colors';
import { Ionicons, FontAwesome6 } from '@expo/vector-icons';
import { celenganService, Celengan } from '../../services/celenganService';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';

export default function CelenganScreen() {
    const router = useRouter();
    const colorScheme = useColorScheme();
    const isDark = colorScheme === 'dark';

    const [sortBy, setSortBy] = useState('newest'); // newest, progress, balance, target
    const [data, setData] = useState<Celengan[]>([]);
    const [refreshing, setRefreshing] = useState(false);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadData();
    }, []);

    const sortedData = React.useMemo(() => {
        let sorted = [...data];
        switch (sortBy) {
            case 'newest':
                sorted.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
                break;
            case 'progress':
                sorted.sort((a, b) => (b.total / b.target) - (a.total / a.target));
                break;
            case 'balance':
                sorted.sort((a, b) => b.total - a.total);
                break;
            case 'target':
                sorted.sort((a, b) => b.target - a.target);
                break;
        }
        return sorted;
    }, [data, sortBy]);

    const sortOptions = [
        { id: 'newest', label: 'Terbaru' },
        { id: 'progress', label: 'Progress' },
        { id: 'balance', label: 'Saldo Tertinggi' },
        { id: 'target', label: 'Target Terbesar' },
    ];

    const loadData = async () => {
        try {
            const result = await celenganService.getList();
            // The service already returns response.data, which is the full object { status: 'success', data: [...], summary: {...} }
            if (result.status === 'success') {
                setData(result.data || []);
            }
        } catch (error) {
            console.error(error);
            setData([]);
        } finally {
            setLoading(false);
        }
    };

    const onRefresh = useCallback(async () => {
        setRefreshing(true);
        await loadData();
        setRefreshing(false);
    }, []);

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
    };

    const renderItem = ({ item }: { item: Celengan }) => (
        <TouchableOpacity
            style={[styles.glassCard, isDark && styles.glassCardDark]}
            onPress={() => router.push(`/celengan/${item.id}`)}
        >
            <View style={styles.cardHeader}>
                <View style={[styles.iconContainer, { backgroundColor: 'rgba(102, 126, 234, 0.2)' }]}>
                    <FontAwesome6 name="gift" size={24} color="#667eea" />
                </View>
                <View style={{ flex: 1, marginLeft: 16 }}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                        <Text style={[styles.cardTitle, isDark && styles.cardTitleDark]}>{item.nama_celengan}</Text>
                        {item.is_pinned == 1 && (
                            <View style={styles.pinBadge}>
                                <Ionicons name="pin" size={10} color={Colors.gold} />
                                <Text style={styles.pinText}>PINNED</Text>
                            </View>
                        )}
                    </View>
                    <Text style={[styles.cardSubtitle, isDark && styles.cardSubtitleDark]}>
                        Est. {item.estimasi_hari || 0} hari
                    </Text>
                </View>
            </View>

            <View style={styles.progressContainer}>
                <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8 }}>
                    <Text style={[styles.progressText, isDark && styles.progressTextDark]}>{formatCurrency(item.total)}</Text>
                    <Text style={[styles.progressText, isDark && styles.progressTextDark]}>{formatCurrency(item.target)}</Text>
                </View>
                <View style={[styles.progressBarBg, isDark && styles.progressBarBgDark]}>
                    <LinearGradient
                        colors={['#667eea', '#764ba2']}
                        start={{ x: 0, y: 0 }}
                        end={{ x: 1, y: 0 }}
                        style={[styles.progressBarFill, { width: `${Math.min((item.total / item.target) * 100, 100)}%` }]}
                    />
                </View>
            </View>
        </TouchableOpacity>
    );

    return (
        <LinearGradient
            colors={isDark ? ['#1a1a2e', '#16213e'] : ['#667eea', '#764ba2']}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={{ flex: 1 }}
        >
            <View style={styles.header}>
                <Text style={[styles.headerTitle, isDark && styles.headerTitleDark]}>Daftar Celengan</Text>
                <TouchableOpacity style={[styles.addButton, isDark && styles.addButtonDark]}>
                    <Ionicons name="add" size={24} color={Colors.primary} />
                </TouchableOpacity>
            </View>

            <View style={styles.filterContainer}>
                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.filterContent}>
                    {sortOptions.map((option) => (
                        <TouchableOpacity
                            key={option.id}
                            style={[
                                styles.filterChip,
                                sortBy === option.id && styles.filterChipActive,
                                isDark && styles.filterChipDark,
                                sortBy === option.id && isDark && styles.filterChipActiveDark
                            ]}
                            onPress={() => setSortBy(option.id)}
                        >
                            <Text style={[
                                styles.filterText,
                                sortBy === option.id && styles.filterTextActive,
                                isDark && styles.filterTextDark,
                                sortBy === option.id && isDark && styles.filterTextActiveDark
                            ]}>
                                {option.label}
                            </Text>
                        </TouchableOpacity>
                    ))}
                </ScrollView>
            </View>

            <FlatList
                data={sortedData}
                renderItem={renderItem}
                keyExtractor={(item) => item.id.toString()}
                contentContainerStyle={styles.listContent}
                refreshControl={
                    <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={isDark ? 'white' : Colors.primary} />
                }
                ListEmptyComponent={
                    !loading ? (
                        <View style={styles.emptyState}>
                            <Ionicons name="documents-outline" size={64} color={isDark ? "#374151" : "#9CA3AF"} />
                            <Text style={[styles.emptyText, isDark && styles.emptyTextDark]}>Belum ada celengan</Text>
                            <Text style={[styles.emptySubtext, isDark && styles.emptySubtextDark]}>Mulai menabung dengan membuat celengan baru</Text>
                        </View>
                    ) : null
                }
            />
        </LinearGradient>
    );
}

const styles = StyleSheet.create({
    header: {
        paddingTop: 60,
        paddingHorizontal: 24,
        paddingBottom: 24,
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
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
    addButton: {
        width: 48,
        height: 48,
        borderRadius: 16,
        backgroundColor: Colors.glass,
        justifyContent: 'center',
        alignItems: 'center',
        borderWidth: 1,
        borderColor: Colors.border,
    },
    addButtonDark: {
        backgroundColor: Colors.glassDark,
        borderColor: Colors.borderDark,
    },
    listContent: {
        padding: 24,
        paddingTop: 0,
        gap: 20,
        paddingBottom: 100,
    },
    filterContainer: {
        marginBottom: 16,
    },
    filterContent: {
        paddingHorizontal: 24,
        gap: 12,
        paddingBottom: 8,
    },
    filterChip: {
        paddingHorizontal: 16,
        paddingVertical: 10,
        backgroundColor: Colors.glass,
        borderRadius: 20,
        borderWidth: 1,
        borderColor: Colors.border,
        justifyContent: 'center',
        alignItems: 'center',
    },
    filterChipDark: {
        backgroundColor: Colors.glassDark,
        borderColor: Colors.borderDark,
    },
    filterChipActive: {
        backgroundColor: Colors.primary,
        borderColor: Colors.primary,
        borderWidth: 0,
    },
    filterChipActiveDark: {
        backgroundColor: Colors.primary,
        borderColor: Colors.primary,
    },
    filterText: {
        fontSize: 14,
        fontWeight: '600',
        color: Colors.textSecondary,
    },
    filterTextDark: {
        color: Colors.textSecondaryDark,
    },
    filterTextActive: {
        color: 'white',
        fontWeight: '700',
    },
    filterTextActiveDark: {
        color: 'white',
    },
    glassCard: {
        backgroundColor: Colors.glass,
        borderRadius: 24,
        padding: 24,
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
    cardHeader: {
        flexDirection: 'row',
        marginBottom: 20,
    },
    iconContainer: {
        width: 56,
        height: 56,
        borderRadius: 18,
        justifyContent: 'center',
        alignItems: 'center',
    },
    cardTitle: {
        fontSize: 18,
        fontWeight: '700',
        color: Colors.text,
        letterSpacing: -0.5,
    },
    cardTitleDark: {
        color: 'white',
    },
    pinBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(245, 158, 11, 0.2)',
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
    cardSubtitle: {
        fontSize: 14,
        color: Colors.textSecondary,
        marginTop: 4,
        fontWeight: '500',
    },
    cardSubtitleDark: {
        color: Colors.textSecondaryDark,
    },
    progressContainer: {
        marginTop: 4,
    },
    progressText: {
        fontSize: 13,
        fontWeight: '600',
        color: Colors.text,
    },
    progressTextDark: {
        color: 'white',
    },
    progressBarBg: {
        height: 10,
        backgroundColor: 'rgba(0, 0, 0, 0.05)',
        borderRadius: 10,
        overflow: 'hidden',
    },
    progressBarBgDark: {
        backgroundColor: 'rgba(255, 255, 255, 0.08)',
    },
    progressBarFill: {
        height: '100%',
        borderRadius: 10,
    },
    emptyState: {
        alignItems: 'center',
        justifyContent: 'center',
        paddingTop: 100,
        gap: 16,
    },
    emptyText: {
        fontSize: 18,
        fontWeight: '700',
        color: Colors.text,
    },
    emptyTextDark: {
        color: 'white',
    },
    emptySubtext: {
        fontSize: 14,
        color: Colors.textSecondary,
        textAlign: 'center',
        maxWidth: 250,
    },
    emptySubtextDark: {
        color: Colors.textSecondaryDark,
    },
});
