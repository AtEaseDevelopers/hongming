<?php 

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LateDeliveryReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $lateTasks;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($lateTasks, $dateFrom, $dateTo)
    {
        
        $this->lateTasks = $lateTasks;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection()
    {

        return $this->lateTasks->map(function ($task) {
            return [
                'No' => $task->id,
                'DO Number' => $task->deliveryOrder->dono ?? 'N/A',
                'Customer' => $task->deliveryOrder->customer->company ?? 'N/A',
                'Driver' => $task->driver->name ?? 'N/A',
                'Product' => $task->deliveryOrder->product->name ?? 'N/A',
                'This Load' => $task->this_load,
                'Countdown' => $task->getCountdownFormatted() ?? 'N/A',
                'Time Taken' => $task->getTimeTakenFormatted() ?? 'N/A',
                'Start Time' => $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('d-m-Y H:i:s') : 'N/A',
                'End Time' => $task->end_time ? \Carbon\Carbon::parse($task->end_time)->format('d-m-Y H:i:s') : 'N/A',
                'Status' => $task->status ?? 'N/A',
                'Return Reason' => $task->return_reason ?? 'N/A',
                'Date' => $task->date,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'DO Number',
            'Customer',
            'Driver',
            'Product',
            'This Load',
            'Countdown',
            'Time Taken',
            'Start Time',
            'End Time',
            'Status',
            'Return Reason',
            'Date',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Add title with date range
        $sheet->insertNewRowBefore(1, 2); // Insert 2 rows at the top
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', 'Late Delivery Report (' . $this->dateFrom . ' to ' . $this->dateTo . ')');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        return [
            // Style the headers row (now row 3)
            3 => [
                'font' => [
                    'bold' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFE6E6FA', // Light lavender background
                    ],
                ],
            ],
            // Style all data rows with borders
            'A4:M' . ($this->lateTasks->count() + 3) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
}