<?php

namespace App\Imports;

use App\Models\MGrupKaryawan;
use App\Models\MShiftGrup;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ImportShift implements ToCollection, WithStartRow
{
    protected $mGrupKaryawan;
    protected $mShift;
    protected $msg = "";
    protected $data = [];
    protected $error = 0;
    protected $dayInMonth = 0;
    protected $idUser = 0;
    protected $allTanggal = [];
    protected $deletedMShiftGrup = [];
    public function  __construct($grupKarywan,$shift)
    {
        $this->idUser = Auth::user()->id_user;
        $this->mGrupKaryawan = $grupKarywan;
        $this->mShift = $shift;
    }
    private $rows = 0;
    protected $dataGrup = [];
    public function collection(Collection $rows)
    {
        $idGrupKaryawan = 0;
        $idShift = 0;
        $dataGrup = [];
        // $this->dayInMonth = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        $this->dayInMonth = count($rows[0]) - 1;
        $arrayGrupKaryawan = $this->mGrupKaryawan->pluck('kode_grup')->toArray();
        // dd($this->dayInMonth);
        $no = 0;
        foreach ($rows as $row) 
        {
            // dd(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[1])->format("d-m-Y"));
            // dd(date("Y-m-d",strtotime($row[0])));
            //cek row awal harus kumpulkan tanggal
            if($no == 0){
                ///kumpulkan tanggal
                for($n = 1; $n < ($this->dayInMonth+1); $n++){
                    array_push($this->allTanggal,\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$n])->format("Y-m-d"));
                // dd($this->allTanggal);
                }
            }else{
                

                //cek grup karyawan
                // echo $row[0];
                for($i = 0; $i < count($this->mGrupKaryawan); $i++){
                    if(!in_array(strtoupper($row[0]),array_map('strtoupper', $arrayGrupKaryawan))){
                        $this->error = 1;
                        $this->msg = "kode grup karyawan <b>{$row[0]}</b> tidak ditemukan, silakan cek di menu master -> grup karyawan";
                    }
                    if($row[0] == $this->mGrupKaryawan[$i]->kode_grup){
                        $idGrupKaryawan = $this->mGrupKaryawan[$i]->id_grup_karyawan;
                        if(!in_array($idGrupKaryawan,$this->dataGrup)){
                            array_push($this->dataGrup,$idGrupKaryawan);
                        }
                        
                    }
                }
                ///////////
                //cek shift
                $arraySHift = $this->mShift->pluck('kode_shift')->toArray();
                
                for($j = 1; $j < ($this->dayInMonth+1); $j++){
                    // dd($this->mShift);
                    if($row[$j] != ""){
                        if(!in_array(strtolower($row[$j]),array_map('strtolower', $arraySHift))){
                            $this->error = 1;
                            $this->msg = "Kode shift {$row[$j]} tidak ditemukan, silakan cek di menu master -> shift";
                        }else{
                            for($k = 0; $k < count($this->mShift); $k++){
                                
                                if(strtolower($this->mShift[$k]->kode_shift) == strtolower($row[$j])){
                                    $idShift = $this->mShift[$k]->id_shift;
                                }
                            }
                            if($this->error == 0){
                                // MShiftGrup::deleteRow($idGrupKaryawan,$this->allTanggal[$j-1]);
                                $this->deletedMShiftGrup[] = ['id_grup_karyawan'=>$idGrupKaryawan,'tanggal'=>$this->allTanggal[$j-1]];
                                array_push($this->data,[
                                        'id_grup_karyawan' => $idGrupKaryawan,
                                        'id_shift' => $idShift,
                                        'tanggal' => $this->allTanggal[$j-1],
                                        'created_by'=>$this->idUser,
                                        'updated_by'=>$this->idUser
                                    ]);
                            }
                        }
                    }
                }
                ////////////
                
            }

            
            
            
            $no++;
        }

        
    }
    public function startRow(): int
    {
        return 4;
    } 
    public function outPut()
    {
        return "tidak";
    }
      
    
    public function data()
    {
        return $this->data;
    }
    public function message()
    {
        return $this->msg;
    }
    public function error()
    {
        return $this->error;
    }
    public function getRowCount(): int
    {
        return $this->rows;
    }
    public function dataGrup()
    {
        return $this->dataGrup;
    }
    public function getAllTanggal()
    {
        return $this->allTanggal;
    }
}
