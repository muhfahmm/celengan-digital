import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, useColorScheme, ActivityIndicator } from 'react-native';
import { useLocalSearchParams, useRouter, Stack } from 'expo-router';
import { Colors } from '../../constants/Colors';
import { Ionicons, FontAwesome6 } from '@expo/vector-icons';
import { celenganService, Celengan, Transaction } from '../../services/celenganService';
import { format } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';

export default function DetailCelengan() {
    const { id } = useLocalSearchParams();
    const router = useRouter();
    const colorScheme = useColorScheme();
    const isDark = colorScheme === 'dark';

    const [celengan, setCelengan] = useState<Celengan | null>(null);
    const [transactions, setTransactions] = useState<Transaction[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (id) {
            loadDetail();
        }
    }, [id]);

    const loadDetail = async () => {
        try {
            const response = await celenganService.getDetail(Number(id));
            if (response.status === 'success') {
                setCelengan(response.data);
                setTransactions(response.transactions);
            }
        } catch (error) {
            console.error(error);
            alert('Gagal memuat detail celengan');
        } finally {
            setLoading(false);
        }
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
    };

    const formatDate = (dateString: string) => {
        return format(new Date(dateString), 'd MMMM yyyy, HH:mm', { locale: idLocale });
    };

    if (loading) {
        return (
            <View style={[styles.container, styles.center, isDark && styles.containerDark]}>
                <ActivityIndicator size="large" color={Colors.primary} />
            </View>
        );
    }

    if (!celengan) {
        return (
            <View style={[styles.container, styles.center, isDark && styles.containerDark]}>
                <Text style={{ color: isDark ? 'white' : 'black' }}>Celengan tidak ditemukan</Text>
            </View>
        );
    }

    const progress = (celengan.total / celengan.target) * 100;

    return (
        <>
            <Stack.Screen options={{ headerShown: false }} />
            <View style={[styles.container, isDark && styles.containerDark]}>
                {/* Header with Back Button */}
                <View style={styles.header}>
                    <TouchableOpacity onPress={() => router.back()} style={[styles.backButton, isDark && styles.backButtonDark]}>
                        <Ionicons name="arrow-back" size={24} color={isDark ? 'white' : 'black'} />
                    </TouchableOpacity>
                    <Text style={[styles.headerTitle, isDark && styles.headerTitleDark]}>Detail Celengan</Text>
                    <View style={{ width: 40 }} />
                </View>

                <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.content}>

                    <View style={[styles.mainCard, isDark && styles.mainCardDark]}>
                        <View style={styles.iconLarge}>
                            <FontAwesome6 name="gift" size={32} color="white" />
                        </View>
                        <Text style={[styles.title, isDark && styles.titleDark]}>{celengan.nama_celengan}</Text>

                        <View style={styles.amountContainer}>
                            <Text style={[styles.amountLabel, isDark && styles.amountLabelDark]}>Terkumpul</Text>
                            <Text style={[styles.amountValue, isDark && styles.amountValueDark]}>{formatCurrency(celengan.total)}</Text>
                            <Text style={[styles.targetText, isDark && styles.targetTextDark]}>dari target {formatCurrency(celengan.target)}</Text>
                        </View>

                        <View style={styles.progressBarBg}>
                            <View style={[styles.progressBarFill, { width: `${Math.min(progress, 100)}%` }]} />
                        </View>
                        <Text style={[styles.percentage, isDark && styles.percentageDark]}>{progress.toFixed(1)}%</Text>
                    </View>

                    <View style={styles.actionContainer}>
                        <TouchableOpacity style={styles.actionButton}>
                            <View style={[styles.actionIcon, { backgroundColor: 'rgba(16, 185, 129, 0.1)' }]}>
                                <Ionicons name="add" size={24} color={Colors.success} />
                            </View>
                            <Text style={[styles.actionLabel, isDark && styles.actionLabelDark]}>Tabung</Text>
                        </TouchableOpacity>

                        <TouchableOpacity style={styles.actionButton}>
                            <View style={[styles.actionIcon, { backgroundColor: 'rgba(59, 130, 246, 0.1)' }]}>
                                <Ionicons name="pencil" size={24} color={Colors.primary} />
                            </View>
                            <Text style={[styles.actionLabel, isDark && styles.actionLabelDark]}>Edit</Text>
                        </TouchableOpacity>

                        <TouchableOpacity style={styles.actionButton}>
                            <View style={[styles.actionIcon, { backgroundColor: 'rgba(239, 68, 68, 0.1)' }]}>
                                <Ionicons name="trash-outline" size={24} color="#EF4444" />
                            </View>
                            <Text style={[styles.actionLabel, isDark && styles.actionLabelDark]}>Hapus</Text>
                        </TouchableOpacity>
                    </View>

                    <Text style={[styles.sectionTitle, isDark && styles.sectionTitleDark]}>Riwayat Transaksi</Text>

                    {transactions.length === 0 ? (
                        <Text style={{ textAlign: 'center', color: '#9CA3AF', marginTop: 20 }}>Belum ada transaksi</Text>
                    ) : (
                        transactions.map((t) => (
                            <View key={t.id} style={[styles.transactionItem, isDark && styles.transactionItemDark]}>
                                <View style={[styles.transactionIcon, { backgroundColor: t.jumlah > 0 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }]}>
                                    <Ionicons
                                        name={t.jumlah > 0 ? "arrow-down" : "arrow-up"}
                                        size={20}
                                        color={t.jumlah > 0 ? Colors.success : "#EF4444"}
                                    />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={[styles.transactionDate, isDark && styles.transactionDateDark]}>{formatDate(t.date)}</Text>
                                    <Text style={[styles.transactionLabel, isDark && styles.transactionLabelDark]}>
                                        {t.jumlah > 0 ? 'Menabung' : 'Penarikan'}
                                    </Text>
                                </View>
                                <Text style={[
                                    styles.transactionAmount,
                                    { color: t.jumlah > 0 ? Colors.success : "#EF4444" }
                                ]}>
                                    {t.jumlah > 0 ? '+' : ''}{formatCurrency(t.jumlah)}
                                </Text>
                            </View>
                        ))
                    )}

                </ScrollView>
            </View>
        </>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#F3F4F6',
    },
    containerDark: {
        backgroundColor: '#111827',
    },
    center: {
        justifyContent: 'center',
        alignItems: 'center',
    },
    header: {
        paddingTop: 50,
        paddingHorizontal: 20,
        paddingBottom: 20,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
    },
    backButton: {
        width: 40,
        height: 40,
        borderRadius: 12,
        backgroundColor: 'white',
        justifyContent: 'center',
        alignItems: 'center',
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 4,
        elevation: 2,
    },
    backButtonDark: {
        backgroundColor: '#1F2937',
    },
    headerTitle: {
        fontSize: 18,
        fontWeight: '700',
        color: Colors.text,
    },
    headerTitleDark: {
        color: 'white',
    },
    content: {
        padding: 20,
        paddingTop: 0,
    },
    mainCard: {
        backgroundColor: 'white',
        borderRadius: 24,
        padding: 24,
        alignItems: 'center',
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1,
        shadowRadius: 12,
        elevation: 5,
        marginBottom: 24,
    },
    mainCardDark: {
        backgroundColor: '#1F2937',
    },
    iconLarge: {
        width: 64,
        height: 64,
        borderRadius: 20,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 16,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 6,
    },
    title: {
        fontSize: 22,
        fontWeight: '800',
        color: Colors.text,
        marginBottom: 20,
        textAlign: 'center',
    },
    titleDark: {
        color: 'white',
    },
    amountContainer: {
        alignItems: 'center',
        marginBottom: 20,
    },
    amountLabel: {
        fontSize: 14,
        color: Colors.textSecondary,
        marginBottom: 4,
        textTransform: 'uppercase',
        fontWeight: '600',
    },
    amountLabelDark: {
        color: '#9CA3AF',
    },
    amountValue: {
        fontSize: 32,
        fontWeight: '800',
        color: Colors.text,
        marginBottom: 4,
    },
    amountValueDark: {
        color: 'white',
    },
    targetText: {
        fontSize: 14,
        color: Colors.textSecondary,
    },
    targetTextDark: {
        color: '#9CA3AF',
    },
    progressBarBg: {
        width: '100%',
        height: 12,
        backgroundColor: '#F3F4F6',
        borderRadius: 6,
        overflow: 'hidden',
        marginBottom: 8,
    },
    progressBarFill: {
        height: '100%',
        backgroundColor: Colors.success,
        borderRadius: 6,
    },
    percentage: {
        fontSize: 14,
        fontWeight: '700',
        color: Colors.success,
    },
    percentageDark: {
        color: Colors.success,
    },
    actionContainer: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 30,
    },
    actionButton: {
        alignItems: 'center',
        flex: 1,
    },
    actionIcon: {
        width: 56,
        height: 56,
        borderRadius: 18,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 8,
    },
    actionLabel: {
        fontSize: 13,
        fontWeight: '600',
        color: Colors.text,
    },
    actionLabelDark: {
        color: 'white',
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: '700',
        color: Colors.text,
        marginBottom: 16,
    },
    sectionTitleDark: {
        color: 'white',
    },
    transactionItem: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'white',
        padding: 16,
        borderRadius: 16,
        marginBottom: 12,
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 4,
        elevation: 2,
    },
    transactionItemDark: {
        backgroundColor: '#1F2937',
    },
    transactionIcon: {
        width: 40,
        height: 40,
        borderRadius: 12,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 16,
    },
    transactionDate: {
        fontSize: 12,
        color: Colors.textSecondary,
        marginBottom: 2,
    },
    transactionDateDark: {
        color: '#9CA3AF',
    },
    transactionLabel: {
        fontSize: 15,
        fontWeight: '600',
        color: Colors.text,
    },
    transactionLabelDark: {
        color: 'white',
    },
    transactionAmount: {
        fontSize: 16,
        fontWeight: '700',
    },
});
