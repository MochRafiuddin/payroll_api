<table>
    <thead>
        <tr>
            <td>No</td>
            <td>Nama Karyawan</td>
            @for($i = new DateTime($awal); $i <= new DateTime($akhir); $i->modify('+1 day'))
                <th>{{$i->format("d-F-Y")}}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach($hsl as $key => $aa)
            <tr>
                <td>{{++$key}}</td>
                @php
                    $result1 = DB::table('m_karyawan')
                        ->where('id_karyawan',$aa->id_karyawan)
                        ->first();
                @endphp
                <td>{{$result1->nama_karyawan}}</td>
                @for ($i = new DateTime($awal); $i <= new DateTime($akhir); $i->modify('+1 day'))
                    @php
                    $m = DB::table('t_absensi as a')
                        ->join('ref_tipe_absensi as b','a.id_tipe_absensi','=','b.id_tipe_absensi')
                        ->select('a.*','b.nama_tipe_absensi')
                        ->where('a.tanggal',$i->format("Y-m-d"))
                        ->where('a.id_karyawan',$result1->id_karyawan)
                        ->first();                                             
                    @endphp
                    @if($m)
                      @if($m->id_tipe_absensi == '1')
                        @if($m->menit_terlambat >= 1 && $m->menit_terlambat <= 4)
                            <td>
                                Masuk : {{$m->tanggal_masuk}}<br> Keluar : {{$m->tanggal_keluar}}<br> Terlambat : <a style="color: yellow"> {{$m->menit_terlambat}} Menit <br> Early Leave : {{$m->menit_early_leave}}</a>
                            </td>
                        @elseif($m->menit_terlambat >= 5 && $m->menit_terlambat <= 29)
                            <td>
                                Masuk : {{$m->tanggal_masuk}}<br> Keluar : {{$m->tanggal_keluar}}<br> Terlambat : <a style="color: orange"> {{$m->menit_terlambat}} Menit <br> Early Leave : {{$m->menit_early_leave}}</a>
                            </td>
                        @elseif($m->menit_terlambat >= 30)
                            <td>
                                <p>Masuk : {{$m->tanggal_masuk}}<br> Keluar : {{$m->tanggal_keluar}} <br> Terlambat : <a style='color: red;'> {{$m->menit_terlambat}} Menit</a> <br> Early Leave : {{$m->menit_early_leave}}</p>
                            </td>
                        @else
                            <td>
                                Masuk : {{$m->tanggal_masuk}}<br> Keluar : {{$m->tanggal_keluar}}<br> Terlambat : {{$m->menit_terlambat}} Menit <br> Early Leave : {{$m->menit_early_leave}}
                            </td>
                        @endif
                      @elseif($m->id_tipe_absensi == 3)  
                            <td style='color: green;'>
                                {{$m->nama_tipe_absensi}}
                            </td>
                      @else
                            <td style='color: orange;'>
                                {{$m->nama_tipe_absensi}}
                            </td>
                      @endif
                    @else
                    <td></td>
                    @endif
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>