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
use App\Traits\Helper;
use Carbon\Carbon;
use App\Models\MGaji;
use App\Models\TGajiKaryawanPeriode;

class ExportLaporanGajiKaryawan implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithStyles, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Helper;

    public $abjad = [];
    public $m_gaji_tetap = [];
    public $m_gaji_non_tetap = [];

	function __construct($periode) {
        $this->periode = $periode;
        $this->abjad = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'];
        $this->m_gaji_tetap = MGaji::whereIn('id_jenis_gaji',[1,2])->orWhere('id_gaji',9)->orderBy('id_gaji','asc')->where('deleted','1')->pluck('nama_gaji')->toArray();
        $this->m_gaji_non_tetap = MGaji::whereIn('id_jenis_gaji',[3,5])->orderBy('id_gaji','asc')->where('periode_hitung',2)->where('deleted','1')->pluck('nama_gaji')->toArray();
        // $this->shift_allowance = MGaji::where('id_gaji',8)->orderBy('id_gaji','asc')->where('deleted','1')->pluck('nama_gaji')->toArray();
        $this->shift_allowance = MGaji::whereIn('id_jenis_gaji',[3,5])->orderBy('id_gaji','asc')->where('periode_hitung','!=',2)->where('deleted','1')->where('id_gaji','!=',9)->pluck('nama_gaji')->toArray();
        $this->data = [];
        $this->rowCount = 6;

	}

    public function collection()
    {
        // dd($this->rowCount);
        return collect($this->data);
    }
    public function startCell(): string 
    {
        return "A6";
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
        // return ["Kode Grup karyawan","5/1/2022","5/2/2022","5/3/2022","5/4/2022","5/5/2022","5/6/2022","5/7/2022"];
        return [];
    }

    public function styles(Worksheet $sheet)
    {
            $sheet->getStyle('4')->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
            $sheet->getStyle('5')->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
            $sheet->getStyle('4')->getFont()->setSize(11)->setName('Times New Roman');
            $sheet->getStyle('5')->getFont()->setSize(11)->setName('Times New Roman');
            $sheet->getStyle($this->rowCount)->getFont()->setSize(11)->setName('Times New Roman');
            $sheet->getStyle('A'.$this->rowCount)->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
        // return [
            // Style the first row as bold text.
            // 1    => ['font' => ['bold' => true]],
            // 4 => [['alignment' => 'center']],
            // 5 => [['alignment' => 'center']],

            // Styling a specific cell by coordinate.
            // 'A1' => [    
            //     'font' => [
            //             'size'      =>  11,
            //             'bold'      =>  true,
            //         ],
            //     ],
            // 'A2' => [    
            //     'font' => [
            //             'size'      =>  8,
            //             'color' => array('rgb' => 'FF0000'),
            //         ],
            //     ],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        // ];
    }

    public function registerEvents(): array
    {
        $m_gaji_tetap = MGaji::whereIn('id_jenis_gaji',[1,2])->orWhere('id_gaji',9)->orderBy('id_gaji','asc')->where('deleted','1')->groupBy('id_jenis_gaji')->select('id_gaji','id_jenis_gaji')->get();
        $m_gaji_non_tetap = MGaji::whereIn('id_jenis_gaji',[3,5])->orderBy('id_gaji','asc')->where('periode_hitung',2)->where('deleted','1')->select('id_gaji','id_jenis_gaji')->get();
        // $tunjangan_harian = MGaji::whereIn('id_jenis_gaji',[3,5])->orderBy('id_gaji','asc')->where('periode_hitung',1)->where('deleted','1')->select('id_gaji','id_jenis_gaji')->get();
        $tunjangan_harian = MGaji::whereIn('id_jenis_gaji',[3,5])->orderBy('id_gaji','asc')->where('periode_hitung','!=',2)->where('deleted','1')->where('id_gaji','!=',9)->select('id_gaji','id_jenis_gaji')->get();

        $gaji_periode = TGajiKaryawanPeriode::with('details')->from('t_gaji_karyawan_periode as a')
                        ->leftJoin('m_karyawan as b','a.id_karyawan','=','b.id_karyawan')
                        ->leftJoin('m_departemen as c','b.id_departemen','=','c.id_departemen')
                        ->leftJoin('m_jabatan as d','b.id_jabatan','=','d.id_jabatan')
                        ->select('a.*','b.nik','b.nama_karyawan','b.jk','b.tanggal_masuk','c.nama_departemen','d.nama_jabatan')
                        ->where('a.id_periode',$this->periode['id_periode'])->where('a.deleted','1')->get();

        $data = [];
        $no=1;
        foreach ($gaji_periode as $key => $value) {
            $arr_data = [
                $no,
                // 'no',
                $value->nik,
                // 'nik',
                ucwords($value->nama_karyawan),
                // 'nama_karyawan',
                $value->jk,
                // 'jk',
                ($value->tanggal_masuk ? Carbon::createFromFormat('Y-m-d',$value->tanggal_masuk)->format('d-M-y') : null),
                // 'tgl_masuk',
                $value->nama_departemen,
                // 'nama_departemen',
                $value->nama_jabatan,
                // 'nama_jabatan',
            ];

            $num =1;
            // dd($m_gaji_tetap);
            foreach ($m_gaji_tetap as $key => $fix_salary) {    // fix salary
                $set = false;
                foreach ($value->details as $key => $detail) {
                    if ($fix_salary->id_jenis_gaji == $detail->id_jenis_gaji && $fix_salary->id_gaji == $detail->id_gaji) {
                        $arr_data[] = $detail->nominal == 0 ? '0' : $detail->nominal;
                        // $arr_data[] = 'fix '.$num++;
                        $set = true;
                    }
                }
                if (!$set) {
                    $arr_data[] = '0';
                    // $arr_data[] = 'fix '.$num++;
                }
                $num++;
            }

            // $arr_data[] = '0';    // position
            // $arr_data[] = 'position';    // position

            $num =1;
            foreach ($m_gaji_non_tetap as $key => $non_fix_salary) {    // non fix
                $set = false;
                foreach ($value->details as $key => $detail) {
                    if ($non_fix_salary->id_jenis_gaji == $detail->id_jenis_gaji && $detail->periode_hitung == 2 && $detail->id_gaji == $non_fix_salary->id_gaji) {
                        $arr_data[] = $detail->nominal == 0 ? '0' : $detail->nominal;
                        // $arr_data[] = 'non fix '.$num;
                        $set = true;
                    }
                }
                if (!$set) {
                    $arr_data[] = '0';
                    // $arr_data[] = 'non fix '.$num;
                }
                $num++;
            }

            // $arr_data[] = '0';    // tax allowance
            // $arr_data[] = 'tax allowance';    // tax allowance
            // $arr_data[] = '0';    // bpjs allowance
            // $arr_data[] = 'bpjs allowance';    // bpjs allowance

            // $shift_allowance = 0;
            $num =1;
            foreach ($tunjangan_harian as $key => $tunjangan) {    // shift allowance
                $set = false;
                foreach ($value->details as $key => $detail) {
                    if ($tunjangan->id_jenis_gaji == $detail->id_jenis_gaji && $detail->id_gaji == $tunjangan->id_gaji) {
                        // $shift_allowance += $detail->nominal;
                        $arr_data[] = $detail->nominal == 0 ? '0' : $detail->nominal;
                        // $arr_data[] = 'non fix '.$num;
                        $set = true;
                    }
                }
                if (!$set) {
                    $arr_data[] = '0';
                    // $arr_data[] = 'non fix '.$num;
                }
                $num++;
            }
            // dd($arr_data);
            // $arr_data[] = $shift_allowance == 0 ? '0' : $shift_allowance;    // shift allowance
            // $arr_data[] = "shift allowance";    // shift allowance

            $arr_data[] = $value->hari_kerja_shift == 0 ? '0' : $value->hari_kerja_shift;    // working day
            // $arr_data[] = "workingday";    // working day
            $arr_data[] = $value->hari_lembur_holiday == 0 ? '0' : $value->hari_lembur_holiday;    // holiday OT
            // $arr_data[] = "holiday";    // holiday OT
            $arr_data[] = ($value->hari_tidak_hadir+$value->hari_izin) == 0 ? '0' : ($value->hari_tidak_hadir+$value->hari_izin);    // absent leave/sick
            // $arr_data[] = "absent";    // absent leave/sick
            $arr_data[] = '0';    // WFH
            // $arr_data[] = 'wfh';    // WFH

            $trans = ($value->hari_kerja_shift - ($value->hari_tidak_hadir+$value->hari_izin));
            $arr_data[] = $trans == 0 ? '0' : $trans;    // trans
            // $arr_data[] = "trans";    // trans
            $arr_data[] = '0';    // meal
            // $arr_data[] = 'meal';    // meal
            $arr_data[] = $value->lembur == 0 ? '0' : $value->lembur;     // total OT
            // $arr_data[] = "OT";     // total OT

            $arr_data[] = '0';     // additional 1
            // $arr_data[] = 'additional 1';     // additional 1
            $arr_data[] = '0';     // additional 1
            // $arr_data[] = 'additional 2';     // additional 2
            $arr_data[] = '0';     // deduction 1
            // $arr_data[] = 'deduction 1';     // deduction 1
            $arr_data[] = '0';     // deduction 1
            // $arr_data[] = 'deduction 2';     // deduction 2

            $arr_data[] = $value->pph21_sebulan == 0 ? '0' : $value->pph21_sebulan;     // pph21
            // $arr_data[] = "pph21_sebulan";     // pph21
            $arr_data[] = $value->jht_karyawan == 0 ? '0' : $value->jht_karyawan;     // bpjs ket
            // $arr_data[] = "jht_perusahaan";     // bpjs ket
            $arr_data[] = $value->jpn_karyawan == 0 ? '0' : $value->jpn_karyawan;     // bpjs jp
            // $arr_data[] = "jpn_perusahaan";     // bpjs jp
            $arr_data[] = $value->jkn_karyawan == 0 ? '0' : $value->jkn_karyawan;     // bpjs ks
            // $arr_data[] = "jkn_perusahaan";     // bpjs ks

            $excess = 0;
            foreach ($value->details as $key => $detail) {
                if ($detail->id_gaji == 0 && strtolower($detail->nama_gaji_temp) == "hutang asuransi ekses") {
                    $excess += abs($detail->nominal);
                }
            }
            $arr_data[] = $excess == 0 ? '0' : $excess;     // deduction medical excess
            // $arr_data[] = "ekses";     // deduction medical excess

            $arr_data[] = $value->gaji_bersih == 0 ? '0' : $value->gaji_bersih;     // take home pay
            // $arr_data[] = "t homepay";     // take home pay

            $data[] = $arr_data;
            // dd($data);
            $no++;
        }

        $data_total = [];
        if (count($data) > 0) {     //set column total
            // dd(count($data[0]));
            for ($i=0; $i < (count($data[0])-7); $i++) { 
                $data_total[] = 0;
            }

            foreach ($data as $key => $value) {     // sum total
                for ($i=0; $i < 7; $i++) {  //unset index 1 - 6
                    unset($value[$i]);
                }
                $value = array_values($value);

                for ($i=0; $i < (count($data[0])-7); $i++) { 
                    $data_total[$i] += $value[$i];
                }
            }
        }

        $this->data = $data;

        return [
            // Handle by a closure.
            AfterSheet::class => function(AfterSheet $event) use($data,$data_total) {
                $this->rowCount = $this->rowCount + (count($data));
                $total_index = 0;

                $event->sheet->getDelegate()->getRowDimension('4')->setRowHeight(15);
                $event->sheet->getDelegate()->getRowDimension('5')->setRowHeight(15);

                $event->sheet->getParent()->getDefaultStyle()->getFont()->setSize(11)->setName('Times New Roman');

                $event->sheet->mergeCells('A1:E1');
                $event->sheet->setCellValue('A1', "PT. Lotte Chemical Engineering Plastics Indonesia");
                $event->sheet->setCellValue('A2', "Salary :");
                $event->sheet->setCellValue('B2', $this->convertBulanEn($this->periode['periode_bulan'])." ".$this->periode['periode_tahun']);
                $tanggal_start = Carbon::createFromDate($this->periode['periode_tahun'],$this->periode['periode_bulan'],11)->subMonths(1)->format('d F Y');
                $tanggal_end = Carbon::createFromDate($this->periode['periode_tahun'],$this->periode['periode_bulan'],10)->format('d F Y');
                $event->sheet->setCellValue('C2', "(".$tanggal_start." - ".$tanggal_end.")");

                $event->sheet->mergeCells('A4:A5');
                $event->sheet->setCellValue('A4', "No");
                $event->sheet->setCellValue('B4', "ID");
                $event->sheet->setCellValue('B5', "Number");

                $event->sheet->mergeCells('C4:C5');
                $event->sheet->setCellValue('C4', "Name");

                $event->sheet->mergeCells('D4:D5');

                $event->sheet->mergeCells('E4:E5');
                $event->sheet->setCellValue('E4', "Join Date");

                $event->sheet->mergeCells('F4:F5');
                $event->sheet->setCellValue('F4', "Department");

                $event->sheet->mergeCells('G4:G5');
                $event->sheet->setCellValue('G4', "Position");

                /*dynamic column gaji tetap*/
                $index = 7;
                foreach ($this->m_gaji_tetap as $key => $nama_gaji) {
                    $event->sheet->setCellValue($this->abjad[$index].'5', $nama_gaji);
                    $index++;
                }
                // $event->sheet->setCellValue($this->abjad[$index].'5', "Position");
                --$index;
                $event->sheet->mergeCells($this->abjad[7].'4:'.$this->abjad[$index].'4');
                $event->sheet->setCellValue($this->abjad[7].'4', "Fix Salary");
                /*dynamic column gaji tetap*/

                $start_index = $index+1;
                $index = $index+1;
                /*dynamic column gaji tdk tetap*/
                foreach ($this->m_gaji_non_tetap as $key => $nama_gaji) {
                    $event->sheet->setCellValue($this->abjad[$index].'5', $nama_gaji);
                    $index++;
                }
                $event->sheet->mergeCells($this->abjad[$start_index].'4:'.$this->abjad[$index-1].'4');
                $event->sheet->setCellValue($this->abjad[$start_index].'4', "Non Fix");
                /*dynamic column gaji tdk tetap*/

                // $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index].'5');
                // $event->sheet->setCellValue($this->abjad[$index].'4', "Tax Allowance");
                // $index = $index+1;

                // $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index].'5');
                // $event->sheet->setCellValue($this->abjad[$index].'4', "BPJS Allowance");
                // $index = $index+1;

                // $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index].'5');
                // $event->sheet->setCellValue($this->abjad[$index].'4', ucwords($this->shift_allowance[0]));
                // $index = $index+1;
                foreach ($this->shift_allowance as $key => $nama_gaji) {
                    $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index].'5');
                    $event->sheet->setCellValue($this->abjad[$index].'4', $nama_gaji);
                    // $event->sheet->setCellValue($this->abjad[$index].'4', ucwords($this->shift_allowance[0]));
                    $index++;
                }

                $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index].'5');
                $event->sheet->setCellValue($this->abjad[$index].'4', "Working Day");
                $index = $index+1;

                $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index].'5');
                $event->sheet->setCellValue($this->abjad[$index].'4', "Holiday OT");
                $index = $index+1;

                $event->sheet->setCellValue($this->abjad[$index].'4', "Absent");
                $event->sheet->setCellValue($this->abjad[$index].'5', "Leave/Sick");
                $index = $index+1;

                $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index].'5');
                $event->sheet->setCellValue($this->abjad[$index].'4', "WFH");
                $index = $index+1;

                $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index+1].'4');
                $event->sheet->setCellValue($this->abjad[$index].'4', "Day");
                $event->sheet->setCellValue($this->abjad[$index].'5', "Trans");
                $event->sheet->setCellValue($this->abjad[$index+1].'5', "Meal");
                $index = $index+2;

                $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index].'5');
                $event->sheet->setCellValue($this->abjad[$index].'4', "Total OT");
                $index = $index+1;

                $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index+1].'4');
                $event->sheet->setCellValue($this->abjad[$index].'4', "Additional");
                // $event->sheet->setCellValue($this->abjad[$index].'5', "Trans");
                // $event->sheet->setCellValue($this->abjad[$index+1].'5', "Meal");
                $index = $index+2;

                $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index+1].'4');
                $event->sheet->setCellValue($this->abjad[$index].'4', "Deduction");
                $event->sheet->setCellValue($this->abjad[$index].'5', "Day");
                $event->sheet->setCellValue($this->abjad[$index+1].'5', "(-)");
                $index = $index+2;

                $event->sheet->setCellValue($this->abjad[$index].'4', "PPh 21");
                $event->sheet->setCellValue($this->abjad[$index].'5', "(-)");
                $index = $index+1;

                $event->sheet->setCellValue($this->abjad[$index].'4', "BPJS KET (2%)");
                $event->sheet->setCellValue($this->abjad[$index].'5', "(-)");
                $index = $index+1;

                $event->sheet->setCellValue($this->abjad[$index].'4', "BPJS JP (1%)");
                $event->sheet->setCellValue($this->abjad[$index].'5', "(-)");
                $index = $index+1;

                $event->sheet->setCellValue($this->abjad[$index].'4', "BPJS KS (1%)");
                $event->sheet->setCellValue($this->abjad[$index].'5', "(-)");
                $index = $index+1;

                $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index].'5');
                $event->sheet->setCellValue($this->abjad[$index].'4', "Deduction for Excess medical");
                $index = $index+1;

                $event->sheet->mergeCells($this->abjad[$index].'4:'.$this->abjad[$index].'5');
                $event->sheet->setCellValue($this->abjad[$index].'4', "Take Home Pay");
                $index = $index+1;

                $event->sheet->mergeCells('A'.$this->rowCount.':G'.$this->rowCount);
                $event->sheet->setCellValue('A'.$this->rowCount, "Total");

                $event->sheet->getDelegate()->getStyle('A')
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->getDelegate()->getStyle('A1:A2')
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                $total_index_col = $index;
                $index_abjad = 7;
                /*dynamic column total*/
                $idx = 0;
                for ($i=7; $i < $total_index_col; $i++) { 
                    $event->sheet->setCellValue($this->abjad[$i].$this->rowCount, ($data_total[$idx] ?? '0'));
                    $idx++;
                }
                /*dynamic column total*/

                $event->sheet->getStyle('4:5')->applyFromArray([
                    'alignment' => ['wrapText' => true],
                ]);
                $event->sheet->getStyle('A4:'.$this->abjad[$i-1].$this->rowCount)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $event->sheet->mergeCells('B'.($this->rowCount+4).':C'.($this->rowCount+4));
                $event->sheet->mergeCells('B'.($this->rowCount+5).':C'.($this->rowCount+5));
                $event->sheet->mergeCells('B'.($this->rowCount+6).':C'.($this->rowCount+6));
                $event->sheet->setCellValue('B'.($this->rowCount+4), "BPJS Payment");
                $event->sheet->setCellValue('B'.($this->rowCount+5), "BPJS Ketenagakerjaan _ Before Tgl 25");
                $event->sheet->setCellValue('B'.($this->rowCount+6), "BPJS Kesehatan_ Before Tgl 10");

                $event->sheet->mergeCells('H'.($this->rowCount+4).':N'.($this->rowCount+4));
                $event->sheet->mergeCells('H'.($this->rowCount+6).':N'.($this->rowCount+6));
                $event->sheet->mergeCells('H'.($this->rowCount+7).':N'.($this->rowCount+7));
                $event->sheet->mergeCells('H'.($this->rowCount+9).':N'.($this->rowCount+9));
                $event->sheet->setCellValue('H'.($this->rowCount+4), "Not join BPJS JP (Jaminan Pensiun):");
                $event->sheet->setCellValue('H'.($this->rowCount+6), "- Employees (WNI) with age above 57yo. (New Regulation)");
                $event->sheet->setCellValue('H'.($this->rowCount+7), "- Calculation for Salary above 8mio only 8.939.700 (based on New BPJS regulation in March 2020)");
                $event->sheet->setCellValue('H'.($this->rowCount+9), "BPJS KS (Kesehatan) : Salary above 12mio, calculate only 12mio.");


                $event->sheet->mergeCells('AA'.($this->rowCount+4).':AB'.($this->rowCount+4));
                $event->sheet->mergeCells('AA'.($this->rowCount+8).':AB'.($this->rowCount+8));
                $date = Carbon::createFromFormat('m-Y', $this->periode['periode_bulan'].'-'.$this->periode['periode_tahun'])->format('F Y');
                $event->sheet->setCellValue('AA'.($this->rowCount+4), "Bekasi,  ______".$date);
                $event->sheet->setCellValue('AA'.($this->rowCount+8), "YOON IL SONG");

            },
            
        ];
    }


}
