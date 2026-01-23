import React, { useEffect, useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, useColorScheme, RefreshControl } from 'react-native';
import { Colors } from '../../constants/Colors';
import { Ionicons, FontAwesome6 } from '@expo/vector-icons';
import { celenganService, Celengan } from '../../services/celenganService';
import { useRouter } from 'expo-router';

export default function CelenganScreen() {
    const router = useRouter();
    const colorScheme = useColorScheme();
    const isDark = colorScheme === 'dark';

    const [data, setData] = useState<Celengan[]>([]);
    const [refreshing, setRefreshing] = useState(false);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadData();
    }, []);

    const loadData = async () => {
        try {
            const response = await celenganService.getList();
            if (response.status === 'success') {
                setData(response.data);
            }
        } catch (error) {
            console.error(error);
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
            style={[styles.card, isDark && styles.cardDark]}
            onPress={() => console.log('Open detail', item.id)}
        >
            <View style={styles.cardHeader}>
                <View style={styles.iconContainer}>
                    <FontAwesome6 name="gift" size={20} color="white" />
                </View>
                <View style={{ flex: 1, marginLeft: 12 }}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                        <Text style={[styles.cardTitle, isDark && styles.cardTitleDark]}>{item.judul}</Text>
                        {item.is_pinned == 1 && <Ionicons name="pin" size={14} color={Colors.primary} />}
                    </View>
                    <Text style={[styles.cardSubtitle, isDark && styles.cardSubtitleDark]}>
                        Est. {item.estimasi_hari} hari
                    </Text>
                </View>
            </View>

            <View style={styles.progressContainer}>
                <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 }}>
                    <Text style={[styles.progressText, isDark && styles.progressTextDark]}>{formatCurrency(item.total)}</Text>
                    <Text style={[styles.progressText, isDark && styles.progressTextDark]}>{formatCurrency(item.target)}</Text>
                </View>
                <View style={styles.progressBarBg}>
                    <View style={[styles.progressBarFill, { width: `${Math.min((item.total / item.target) * 100, 100)}%` }]} />
                </View>
            </View>
        </TouchableOpacity>
    );

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <Text style={[styles.headerTitle, isDark && styles.headerTitleDark]}>Daftar Celengan</Text>
                <TouchableOpacity style={[styles.addButton, isDark && styles.addButtonDark]}>
                    <Ionicons name="add" size={24} color="white" />
                </TouchableOpacity>
            </View>

            <FlatList
                data={data}
                renderItem={renderItem}
                keyExtractor={(item) => item.id.toString()}
                contentContainerStyle={styles.listContent}
                refreshControl={
                    <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
                }
                ListEmptyComponent={
                    !loading ? (
                        <View style={styles.emptyState}>
                            <Ionicons name="documents-outline" size={48} color="#9CA3AF" />
                            <Text style={styles.emptyText}>Belum ada celengan</Text>
                            <Text style={styles.emptySubtext}>Mulai menabung dengan membuat celengan baru</Text>
                        </View>
                    ) : null
                }
            />
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
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    headerTitle: {
        fontSize: 24,
        fontWeight: '800',
        color: Colors.text,
    },
    headerTitleDark: {
        color: 'white',
    },
    addButton: {
        width: 40,
        height: 40,
        borderRadius: 12,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 4,
    },
    addButtonDark: {
        shadowOpacity: 0.5,
    },
    listContent: {
        padding: 20,
        paddingTop: 0,
        gap: 16,
    },
    card: {
        backgroundColor: 'rgba(255, 255, 255, 0.9)',
        borderRadius: 20,
        padding: 20,
        borderWidth: 1,
        borderColor: 'rgba(255, 255, 255, 0.6)',
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1,
        shadowRadius: 12,
        elevation: 3,
    },
    cardDark: {
        backgroundColor: 'rgba(31, 41, 55, 0.6)',
        borderColor: 'rgba(255, 255, 255, 0.1)',
    },
    cardHeader: {
        flexDirection: 'row',
        marginBottom: 16,
    },
    iconContainer: {
        width: 44,
        height: 44,
        borderRadius: 12,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
    },
    cardTitle: {
        fontSize: 16,
        fontWeight: '700',
        color: Colors.text,
    },
    cardTitleDark: {
        color: 'white',
    },
    cardSubtitle: {
        fontSize: 13,
        color: Colors.textSecondary,
        marginTop: 2,
    },
    cardSubtitleDark: {
        color: 'rgba(255, 255, 255, 0.6)',
    },
    progressContainer: {
        marginTop: 4,
    },
    progressText: {
        fontSize: 12,
        fontWeight: '600',
        color: Colors.text,
    },
    progressTextDark: {
        color: 'white',
    },
    progressBarBg: {
        height: 8,
        backgroundColor: 'rgba(0, 0, 0, 0.05)',
        borderRadius: 4,
        overflow: 'hidden',
    },
    progressBarFill: {
        height: '100%',
        backgroundColor: Colors.success,
        borderRadius: 4,
    },
    emptyState: {
        alignItems: 'center',
        justifyContent: 'center',
        paddingTop: 100,
        gap: 12,
    },
    emptyText: {
        fontSize: 16,
        fontWeight: '600',
        color: Colors.textSecondary,
    },
    emptySubtext: {
        fontSize: 13,
        color: '#9CA3AF',
        textAlign: 'center',
    },
});
