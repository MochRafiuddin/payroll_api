<?php 
use App\Traits\Helper;  
?>
<table>
    <thead>
        <tr>
            <th>Emp No.</th>
            <th>No. ID</th>
            <th>Nama</th>
            <th>Tanggal</th>
            <th>Jam Kerja</th>
            <th>Jam Masuk</th>
            <th>Jam Pulang</th>
            <th>Scan Masuk</th>
            <th>Scan Pulang</th>
            <th>Absent</th>
            <th>Departemen</th>
            <th>Total Hour</th>
            <th>Reason Overtime</th>
            <th>KE-1</th>
            <th>KE-2</th>
            <th>KE-3</th>
            <th>KE-4</th>
            <th>Total Count</th>
            <th>Basic Salary</th>
            <th>PRICE PER HOUR</th>
            <th>TOTAL COST</th>
        </tr>
    </thead>
    <tbody>
        @foreach($hsl as $key => $aa)
        <tr>
            <td>{{$aa->employee_id}}</td>
            <td>{{$aa->no_bpjs}}</td>
            <td>{{$aa->nama_karyawan}}</td>
            <td>{{$aa->tanggal}}</td>
            <td>{{$aa->nama_shift}}</td>
            <td>{{$aa->jam_masuk_shift}}</td>
            <td>{{$aa->jam_keluar_shift}}</td>
            <td>{{date('H:i:s', strtotime($aa->tanggal_masuk))}}</td>
            <td>{{date('H:i:s', strtotime($aa->tanggal_keluar))}}</td>
            <td></td>
                @php
                    $ke1=\App\Http\Controllers\CAbsensiLembur::sum_total_jam($aa->id_karyawan,$aa->tanggal,"1.5");
                    $ke2=\App\Http\Controllers\CAbsensiLembur::sum_total_jam($aa->id_karyawan,$aa->tanggal,"2");
                    $ke3=\App\Http\Controllers\CAbsensiLembur::sum_total_jam($aa->id_karyawan,$aa->tanggal,"3");
                    $ke4=\App\Http\Controllers\CAbsensiLembur::sum_total_jam($aa->id_karyawan,$aa->tanggal,"4");
                    $salary=\App\Http\Controllers\CAbsensiLembur::salary($aa->id_karyawan,$aa->tanggal,"0");
                    $salary_hour=\App\Http\Controllers\CAbsensiLembur::salary($aa->id_karyawan,$aa->tanggal,"1");
                @endphp
            <td>{{ $aa->nama_departemen }}</td>
            <td>
                @php
                    $Hour = \App\Http\Controllers\CAbsensiLembur::sum_total_jam($aa->id_karyawan,$aa->tanggal,"0");
                    if($Hour==0){
                        $Hour="0";
                    }
                @endphp
                {{ $Hour }}
            </td>
            <td>
                @php
                    $data = DB::table('t_lembur')
                    ->where('id_karyawan',$aa->id_karyawan)
                    ->where('tanggal',$aa->tanggal)
                    ->where('approval',1)
                    ->where('approval2',1)
                    ->where('approval3',1)
                    ->where('deleted',1)
                    ->first();
                    if ($data) {
                        $html=$data->alasan_lembur;
                    }else {
                        $html="";
                    }
                    echo $html;
                @endphp
            </td>
            <td>
                @php
                    if($ke1==0){
                        $ke_1="0";
                    }else{
                        $ke_1=$ke1;
                    }
                @endphp
                {{ $ke_1 }}
            </td>
            <td>
                @php
                    if($ke2==0){
                        $ke_2="0";
                    }else{
                        $ke_2=$ke2;
                    }
                @endphp
                {{ $ke_2 }}
            </td>
            <td>
                @php
                    if($ke3==0){
                        $ke_3="0";
                    }else{
                        $ke_3=$ke3;
                    }
                @endphp
                {{ $ke_3 }}
            </td>
            <td>
                @php
                    if($ke4==0){
                        $ke_4="0";
                    }else{
                        $ke_4=$ke4;
                    }
                @endphp
                {{ $ke_4 }}
            </td>
            <td>
                @php
                    $count = ($ke4 * 4)+($ke3 * 3)+($ke2 * 2)+($ke1 * 1.5);
                    if($count==0){
                        $count="0";
                    }
                @endphp
                {{ $count }}
            </td>
            <td>
                @php
                    if($salary==0){
                        $salary="0";
                    }else{
                        $salary=Helper::ribuan(ceil($salary));
                    }
                @endphp
                {{ $salary }}
            </td>
            <td>
                @php
                    if($salary_hour==0){
                        $salaryH="0";
                    }else{
                        $salaryH=Helper::ribuan(ceil($salary_hour));
                    }
                @endphp
                {{ $salaryH}}
            </td>
            <td>
                @php
                    $COST = (($ke4 * 4)+($ke3 * 3)+($ke2 * 2)+($ke1 * 1.5)) * $salary_hour;
                    if($COST==0){
                        $COST="0";
                    }else{
                        $COST=Helper::ribuan(ceil($COST));
                    }
                @endphp
                {{ $COST }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>