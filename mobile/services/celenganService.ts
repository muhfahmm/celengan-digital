import api from './api';

export interface Celengan {
    id: number;
    nama_celengan: string;
    target: number;
    total: number;
    is_pinned: number;
    pengisian: string;
    estimasi_hari?: number; // Optional as not in DB directly
    created_at: string;
}

export interface CelenganSummary {
    total_tabungan: number;
    total_target: number;
}

export interface CelenganListResponse {
    status: string;
    data: Celengan[];
    summary: CelenganSummary;
}

export interface Transaction {
    id: number;
    celengan_id: number;
    jumlah: number;
    date: string;
}

export interface CelenganDetailResponse {
    status: string;
    data: Celengan;
    transactions: Transaction[];
}

export const celenganService = {
    async getList() {
        const response = await api.get<CelenganListResponse>('/data-celengan/api/api-list-celengan.php');
        return response.data;
    },

    async getDetail(id: number) {
        const response = await api.get<CelenganDetailResponse>(`/data-celengan/api/api-detail-celengan.php?id=${id}`);
        return response.data;
    },
};
