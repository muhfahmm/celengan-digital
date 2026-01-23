import api from './api';

export interface Celengan {
    id: number;
    judul: string;
    target: number;
    total: number;
    is_pinned: number;
    estimasi_hari: number;
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

export const celenganService = {
    async getList() {
        const response = await api.get<CelenganListResponse>('/data-celengan/api/api-list-celengan.php');
        return response.data;
    },
};
