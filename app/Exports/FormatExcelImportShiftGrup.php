<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class FormatExcelImportShiftGrup implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithStyles, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

	function __construct() {

	}

    public function collection()
    {
    
        return collect([
            [
                'kode_grup_karyawan' => 'A',
                'col1' => 'pagi',
                'col2' => 'malam',
                'col3' => 'siang',
                'col4' => 'siang',
                'col5' => 'libur',
                'col6' => 'siang',
                'col7' => 'siang',
            ],
            
        ]);
    }
    public function startCell(): string 
    {
        return "A4";
    }

    // public function startRow(): int
    // {
    //     return 4;
    // }

    // public function headingRow(): int
    // {
    //     return 3;
    // }

    public function headings(): array
    {
        return ["Kode Grup karyawan","5/1/2022","5/2/2022","5/3/2022","5/4/2022","5/5/2022","5/6/2022","5/7/2022"];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            // 1    => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            'A1' => [    
                'font' => [
                        'size'      =>  11,
                        'bold'      =>  true,
                    ],
                ],
            'A2' => [    
                'font' => [
                        'size'      =>  8,
                        'color' => array('rgb' => 'FF0000'),
                    ],
                ],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }


    public function registerEvents(): array
    {
        return [
            // Handle by a closure.
            AfterSheet::class    => function(AfterSheet $event) {

                $event->sheet->getDelegate()->getRowDimension('2')->setRowHeight(50);

                $event->sheet->setCellValue('A1', "Format import shift per grup karyawan");
                $event->sheet->setCellValue('A2', "* dibawah ini adalah contoh import shift untuk bulan mei 2022
* jika ingin import shift untuk bulan lain, silakan tanggalnya disesuaikan
* pagi/siang/malam adalah contoh kode shift, untuk kode shift bisa diatur di menu master -> shift
* baris 5,6,7 hanyalah contoh, silakan dihapus jika anda ingin import shift
");
                $event->sheet->mergeCells('A2:E2');

                $cellRange = 'A1:C1'; // All headers
                // $event->sheet->styleCells(
                // $cellRange,
                // [
                //     //Set border Style

                //     //Set font style
                    // 'font' => [
                    //     'name'      =>  'Calibri',
                    //     'size'      =>  12,
                    //     'bold'      =>  true,
                    // ],

                // ]
                // );

//                 $event->sheet->getDelegate()->getComment('C1')->getText()->createTextRun(
// '- Clean AAJI
// - Clean AAJI - Pernah di Industri
// - Terdaftar di Perusahaan Lain
// - Tengarai AAJI
// - BlackList AAJI');

             $event->sheet->mergeCells('A6:C6');

                // $event->sheet->styleCells(
                // 'A6:C6',
                // [
                //     //Set border Style

                //     //Set font style
                //     'font' => [
                //         'name'      =>  'Calibri',
                //         'size'      =>  10,
                //         'bold'      =>  false,
                //         'color' => ['argb' => 'FF0000'],
                //     ],

                // ]
                // );
#
            },
            
        ];
    }


}
