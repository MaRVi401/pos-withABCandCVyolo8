<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; font-size: 16pt; color: #1e293b; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 9pt; }
        
        .summary-box { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .summary-box td { padding: 10px; background: #f8fafc; border: 1px solid #cbd5e1; text-align: center; width: 33.33%; }
        .summary-title { font-size: 8pt; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .summary-value { font-size: 13pt; font-weight: bold; margin-top: 4px; color: #0f172a; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #94a3b8; padding: 7px 9px; text-align: left; }
        table.data-table th { background-color: #f1f5f9; text-transform: uppercase; font-size: 8pt; color: #475569; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .invoice-col { color: #4338ca; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Penjualan Toko</h2>
        <p>Periode Transaksi: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                <div class="summary-title">Total Omset</div>
                <div class="summary-value">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-title">Jumlah Transaksi</div>
                <div class="summary-value">{{ $summary['total_count'] }} Nota</div>
            </td>
            <td>
                <div class="summary-title">Total Diskon Diberikan</div>
                <div class="summary-value">Rp {{ number_format($summary['total_discount'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">No. Invoice</th>
                <th width="15%">Tanggal</th>
                <th width="12%">Kasir</th>
                <th>Detail Item Produk</th>
                <th width="12%" class="text-right">Subtotal</th>
                <th width="10%" class="text-right">Diskon</th>
                <th width="12%" class="text-right">Total Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td class="invoice-col">{{ $t->invoice_number }}</td>
                    <td>{{ $t->transaction_date }}</td>
                    <td>{{ $t->admin->name ?? 'Kasir' }}</td>
                    <td>
                        @foreach($t->items as $item)
                            • {{ $item->product->product_name ?? 'Produk' }} ({{ $item->quantity }}x)<br>
                        @endforeach
                    </td>
                    <td class="text-right">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #dc2626;">Rp {{ number_format($t->discount_amount, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($t->final_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data transaksi pada rentang tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>