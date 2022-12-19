<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\TAbsensi;

class ExportAbsensikaryawan implements FromView,WithEvents
{
       public function __construct(string $keyword,$keyword1)
    {
        $this->awal = $keyword;
        $this->akhir = $keyword1;
    }
        public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {                
                $columns = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z","AA", "AB", "AC", "AD", "AE", "AF", "AG",];
                foreach ($columns as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },

        ];

    }
    public function view(): View
    {
    	$hls = TAbsensi::where('deleted',1)->where('id_tipe_absensi',1)->distinct()->get('id_karyawan');

        return view('absensi_karyawan.export', [
            'hsl' => $hls, 'awal' => $this->awal,'akhir' => $this->akhir
        ]);
    }
}
