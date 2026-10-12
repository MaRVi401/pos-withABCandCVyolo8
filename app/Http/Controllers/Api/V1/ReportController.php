<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReportController extends Controller
{
    /**
     * Mengambil Data JSON Laporan Penjualan & Ringkasan Statistik
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', now()->endOfDay()->toDateString());

        $transactions = Transaction::with([
            'admin:user_id,name',
            'payments.paymentMethod:payment_method_id,method_name',
            'items.product:product_id,product_name,sku'
        ])
        ->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
        ->orderBy('transaction_date', 'desc')
        ->get();

        $totalSales     = $transactions->sum('final_amount');
        $totalDiscount  = $transactions->sum('discount_amount');
        $totalCount     = $transactions->count();
        $totalItemsSold = $transactions->flatMap->items->sum('quantity');

        return response()->json([
            'success' => true,
            'summary' => [
                'start_date'       => $startDate,
                'end_date'         => $endDate,
                'total_sales'      => (float) $totalSales,
                'total_discount'   => (float) $totalDiscount,
                'total_count'      => $totalCount,
                'total_items_sold' => $totalItemsSold,
            ],
            'data' => $transactions
        ]);
    }

    /**
     * Mengunduh Dokumen PDF Rapi Menggunakan Dompdf (Paksa Attachment Download via Fetch/Blob)
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', now()->endOfDay()->toDateString());

        $transactions = Transaction::with(['admin:user_id,name', 'items.product:product_id,product_name'])
            ->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $summary = [
            'total_sales'    => $transactions->sum('final_amount'),
            'total_discount' => $transactions->sum('discount_amount'),
            'total_count'    => $transactions->count(),
        ];

        $fileName = "Laporan_Penjualan_{$startDate}_sd_{$endDate}.pdf";

        $pdf = Pdf::loadView('reports.pdf_template', compact('transactions', 'summary', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');
        if (ob_get_length()) {
            ob_end_clean();
        }

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Mengunduh File Excel (.xlsx) Rapi Menggunakan PhpSpreadsheet (Paksa Attachment Download via Fetch/Blob)
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', now()->endOfDay()->toDateString());

        $transactions = Transaction::with(['admin:user_id,name', 'items.product:product_id,product_name'])
            ->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Laporan
        $sheet->setCellValue('A1', 'LAPORAN PENJUALAN TOKO (POS)');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', "Periode: {$startDate} s/d {$endDate}");
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Tabel
        $headers = ['No. Invoice', 'Tanggal', 'Kasir', 'Detail Item Produk', 'Total Normal', 'Diskon', 'Total Bayar'];
        $sheet->fromArray($headers, NULL, 'A4');
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);

        // Fill Data
        $row = 5;
        foreach ($transactions as $t) {
            $itemList = $t->items->map(fn($i) => ($i->product->product_name ?? 'Produk') . ' (' . $i->quantity . 'x)')->implode(', ');

            $sheet->setCellValue('A' . $row, $t->invoice_number);
            $sheet->setCellValue('B' . $row, $t->transaction_date);
            $sheet->setCellValue('C' . $row, $t->admin->name ?? 'Kasir');
            $sheet->setCellValue('D' . $row, $itemList);
            $sheet->setCellValue('E' . $row, $t->total_amount);
            $sheet->setCellValue('F' . $row, $t->discount_amount);
            $sheet->setCellValue('G' . $row, $t->final_amount);
            $sheet->getStyle('E' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Auto width kolom
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = "Laporan_Penjualan_{$startDate}_sd_{$endDate}.xlsx";

        if (ob_get_length()) {
            ob_end_clean();
        }

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}