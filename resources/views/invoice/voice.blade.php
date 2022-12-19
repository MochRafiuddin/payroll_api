<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>

<style>
    .left, .right{
    width: 50%;
}
.information{
    display: flex;

}
.mb-0{
    margin-bottom: 0 !important;
}
.text-right{
    text-align: right;
}
.rp{
    padding-right: 1rem;
    padding-left: 0.5rem;
    border-right: 0px !important;
}
.border-left-0{
    border-left: 0px !important;
}
.fw-b{
    font-weight: bold;
}
table{
    border-collapse: collapse;
}
table td{
    padding: 2px;
}
.text-center{
    text-align: center;
}
.w-100{
    width: 100%;
}
.salary table tr:first-child td{
    border: 1px solid black;
}
.salary table tr:last-child  {
    border-top: 1px solid black;
}
.salary table tr:last-child td {
    border-left: 0px solid black;
}
.salary table tr:last-child td:nth-child(2) {
    border-bottom: 1px solid black;
}
.salary table tr:last-child td:last-child {
    border-bottom: 1px solid black;
}
.salary table tr td{
    border-right: 1px;
    border-top: 0px;
    border-bottom: 0px;
    border-left: 1px;
    border-style: solid;
    border-color: black;
}
.salary table tr td:nth-child(2){
    border-right: 0px !important;
}
.mt-5{
    margin-top: 3rem;
}
.mb-1{
    margin-bottom: 0.5rem !important;
}
.mb-2{
    margin-bottom: 1rem;
}
.mr-2{
    margin-right: 2rem;
}
.m-0{
    margin: 0rem;
}
.total-all{
    padding: 0.5rem;
    border: 1px solid grey;
    margin-top: 0.5rem;
}
</style>
</head>
<body>
    <div class="page">
        <div class="header">
            <img src="{{ public_path('assets/images/lotte.png') }}" alt="" width="40%">
            <!-- <img src="{{ asset('assets/images/lotte.png') }}" alt="" width="40%"> -->
        </div>
        <div class="content">
            <center>
                <h2>Salary SLIP</h2>
            </center>
            <div class="information" style="">
                <table width="100%">
                    <tr>
                        <td width="50%" valign="top">
                            <table>
                                <tr>
                                    <td>Periode</td>
                                    <td>:</td>
                                    @php
                                        $hari=date("Y-m-d", strtotime($tgl->tahun."-".$tgl->bulan."-10"));
                                    @endphp
                                    <td>{{date('F 01', strtotime($hari))}} - {{date('t', strtotime($hari))}}</td>
                                </tr>
                                <tr>
                                    <td>Employee ID</td>
                                    <td>:</td>
                                    <td>{{$karyawan->nik}}</td>
                                </tr>
                                <tr>
                                    <td>Employee Name</td>
                                    <td>:</td>
                                    <td>{{ucwords($karyawan->nama_karyawan)}}</td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <h5 class="mb-1">ATTEDANCE AND OFFERTIME</h5>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Work Day</td>
                                    <td>:</td>
                                    <td>{{$gaji_p->hari_kerja_shift}} Days</td>
                                </tr>
                                <tr>
                                    <td>OT Holiday</td>
                                    <td>:</td>
                                    <td>{{$gaji_p->hari_lembur_holiday}} Days</td>
                                </tr>
                                <tr>
                                    <td>Permition/Sick/Leave</td>
                                    <td>:</td>
                                    <td>{{$gaji_p->hari_izin + $gaji_p->hari_tidak_hadir}} Days</td>
                                </tr>
                                <!-- <tr>
                                    <td></td>
                                    <td>:</td>
                                    <td> 0 Days</td>
                                </tr> -->
                            </table>
                        </td>
                        <td width="50%" valign="top">
                            <table>
                                <tr>
                                    <td>Dept/Section</td>
                                    <td>:</td>
                                    <td style='white-space: nowrap'>{{$departemen->nama_departemen}}</td>
                                </tr>
                                <tr>
                                    <td>Position</td>
                                    <td>:</td>
                                    <td style='white-space: nowrap'>{{$jabatan->nama_jabatan}}</td>
                                </tr>
                                <tr>
                                    <td>Issue Date</td>
                                    <td>:</td>
                                    <td style='white-space: nowrap'>{{date('d-F-Y',strtotime($gaji_p->created_date))}}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <h5 class="mb-1">A OVER TIME</h5>
                                    </td>
                                    <td colspan="3" style="text-align:right">
                                        <h5 class="mb-1">Actual</h5>
                                    </td>
                                    <td colspan="2">
                                        <h5 class="mb-1 text-right">Convertion</h5>
                                    </td>
                                </tr>
                                @php
                                $hari1=date('Y-m-d', strtotime('-1 month', strtotime( $hari )));
                                $hariawal=date('Y-m-d', strtotime('+1 days', strtotime( $hari1 )));

                                    $query = DB::table('t_gaji_karyawan_periode_lembur')
                                    ->select('index_tarif')
                                    ->selectRaw("SUM(jumlah_jam) as jam")
                                    ->where('deleted',1)
                                    ->whereBetween('tanggal', [$hariawal, $hari])
                                    ->where('id_gaji_karyawan_periode',$gaji_p->id)
                                    ->groupBy('index_tarif')->get();

                                    $gaji_pokok = DB::table('t_gaji_karyawan_periode_det')
                                    ->where('deleted',1)
                                    ->where('id_gaji_karyawan_periode',$gaji_p->id)
                                    ->where('id_gaji',1)
                                    ->first();

                                    $gaji_per_jam = $gaji_pokok->nominal / 173;
                                    $tot_a=0;
                                @endphp
                                @foreach($query as $aa)
                                <tr>
                                    <td>OT {{$aa->index_tarif}}</td>
                                    <td>:</td>
                                    <td class="text-right">
                                        @php
                                            $hsl=$aa->jam * $gaji_per_jam * $aa->index_tarif;
                                            $tot_a= $tot_a + $hsl;
                                            echo $aa->jam;
                                        @endphp
                                    </td>
                                    <td>hr(s)</td>
                                    <td class="rp">Rp</td>
                                    <td class="text-right">{{number_format($hsl)}}</td>
                                </tr>
                                @endforeach
                                <tr class="fw-b">
                                    <td style="border-top: 1px solid black;"></td>
                                    <td style="border-top: 1px solid black;"></td>
                                    <td style="border-top: 1px solid black;" colspan="2" class="text-right"><i>A. Total</i></td>
                                    <td style="border-top: 1px solid black;" class="rp">Rp</td>
                                    <td style="border-top: 1px solid black;">{{number_format($tot_a)}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="information mt-5">
                <table width="100%">
                    <tr>
                    <td width="50%" valign="top">
                         <h5 class="mb-1">B. BASIC SALARY AND ALLOWANCE(s)</h5>
                         <table class="w-100" style="border-collapse: collapse;">
                             <tr class="text-center">
                                 <td style="border: 1px solid black;">No.</td>
                                 <td style="border: 1px solid black;">Description</td>
                                 <td style="border: 1px solid black;" colspan="2">Amount</td>
                             </tr>
                             @php
                                $gaji_b = DB::table('t_gaji_karyawan_periode_det')
                                    ->where('deleted',1)
                                    ->where('id_gaji_karyawan_periode',$gaji_p->id)
                                    ->where('nominal','>',0)
                                    ->get();
                                $tot_b=0;
                                $tot_c=0;
                             @endphp
                             @foreach($gaji_b as $key=>$bb)
                             <tr>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" class="text-center">{{++$key}}</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;">{{$bb->nama_gaji}}</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" class="rp">Rp</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" class="text-right border-left-0">
                                @php
                                    $tot_b =$tot_b + $bb->nominal;
                                    echo number_format($bb->nominal);
                                @endphp
                                </td>
                             </tr>
                             @endforeach
                             <tr class="fw-b" style="">
                                 <td style="border-right: 1px solid black; border-top: 1px solid black;" colspan="2" class="text-right"><i>B. Total</i>&nbsp;</td>
                                 <td style="border-bottom: 1px solid black; border-top: 1px solid black;" class="rp">Rp</td>
                                 <td style="border: 1px solid black; border-top: 1px solid black;" class=" text-right border-left-0">{{number_format($tot_b)}}</td>
                             </tr>
                         </table>
                         
                    </td>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td width="50%" valign="top">
                       <h5 class="mb-1">C. DEDUCATION(s)</h5>
                         <table class="w-100" style="border-collapse: collapse;">
                             <tr class="text-center">
                                 <td style="border: 1px solid black;">No.</td>
                                 <td style="border: 1px solid black;">Description</td>
                                 <td style="border: 1px solid black;" colspan="2">Amount</td>
                             </tr>
                             <tr>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" class="text-center">1</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;">BPJS JHT</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" class="rp">Rp</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" class="text-right border-left-0">
                                 @php
                                    $tot_c=$tot_c+$gaji_p->jht_karyawan;
                                    echo number_format($gaji_p->jht_karyawan);
                                 @endphp
                                 </td>
                             </tr>
                             <tr>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;"  class="text-center">2</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" >BPJS Pensiun</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;"  class="rp">Rp</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;"  class="text-right border-left-0">
                                 @php
                                    $tot_c=$tot_c+$gaji_p->jpn_karyawan;
                                    echo number_format($gaji_p->jpn_karyawan);
                                 @endphp
                                </td>
                             </tr>
                             <tr>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;"  class="text-center">3</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" >BPJS Kesehatan</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;"  class="rp">Rp</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;"  class="text-right border-left-0">
                                 @php
                                    $tot_c=$tot_c+$gaji_p->jkn_karyawan;
                                    echo number_format($gaji_p->jkn_karyawan);
                                 @endphp
                                </td>
                             </tr>
                             <tr>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;"  class="text-center">4</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" >PPH 21</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;"  class="rp">Rp</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;"  class="text-right border-left-0">
                                 @php
                                    $tot_c=$tot_c+$gaji_p->pph21_sebulan;
                                    echo number_format($gaji_p->pph21_sebulan);
                                 @endphp                                 
                                </td>
                             </tr>
                             @php
                                $gaji_c= DB::table('t_gaji_karyawan_periode_det')
                                    ->where('deleted',1)
                                    ->where('id_gaji_karyawan_periode',$gaji_p->id)
                                    ->where('nominal','<',0)
                                    ->get();
                                $no=4;
                                
                             @endphp
                             @foreach($gaji_c as $cc)
                             <tr>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" class="text-center">{{++$no}}</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;">{{$cc->nama_gaji}}</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" class="rp">Rp</td>
                                 <td style="border: 1px solid black; border-bottom-width: 0px !important; border-top-width: 0px !important;" class="text-right border-left-0">
                                @php
                                    $nom = str_replace("-","",$cc->nominal);
                                    $tot_c =$tot_c + $nom;
                                    echo number_format($nom);
                                @endphp
                                </td>
                             </tr>
                             @endforeach
                             <tr class="fw-b" style="">
                                 <td style="border-right: 1px solid black; border-top: 1px solid black;" colspan="2" class="text-right"><i>C. Total</i>&nbsp;</td>
                                 <td style="border-bottom: 1px solid black; border-top: 1px solid black;" class="rp">Rp</td>
                                 <td style="border: 1px solid black;" class=" text-right border-left-0">{{number_format($tot_c)}}</td>
                             </tr>
                         </table>
                    </td>
                    </tr>
                    <tr>
                        <td>
                            <p><b>Note :</b></p>
                        </td>
                        <td></td>
                        <td>
                             <h5 class="mb-0 text-center">D. NETT SALARY (( A + B ) - C)</h5>
                             <div class="total-all text-center ">
                                 <h2 class="m-0">Rp. {{number_format($gaji_p->gaji_bersih)}}</h2>
                             </div>
                        </td>
                    </tr>
                </table>
                
            </div>
        </div>
    </div>
</body>

</html>