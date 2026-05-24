<?php

if (!function_exists('prepareChartData')) {
    function prepareChartData(array $transaksi_all): array
    {
        $total = 0;
        $ath = 0;
        $labels = [];
        $saldo_awal = [];
        $saldo_akhir = [];
        $colors = [];

        foreach ($transaksi_all as $t) {
            $labels[] = $t['tanggal'];
            $saldo_awal[] = $total;

            $nominal = (float)$t['nominal'];
            if (strtolower($t['tipe']) === 'masuk') {
                $total += $nominal;
                $colors[] = '#10B981';
            } else {
                $total -= $nominal;
                $colors[] = '#EF4444';
            }

            $saldo_akhir[] = $total;

            if ($total > $ath) {
                $ath = $total;
            }
        }

        return [
            'labels' => $labels,
            'saldo_awal' => $saldo_awal,
            'saldo_akhir' => $saldo_akhir,
            'colors' => $colors,
            'ath' => $ath,
        ];
    }
}
