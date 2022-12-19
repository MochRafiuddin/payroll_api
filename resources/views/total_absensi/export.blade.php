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
                    $m = DB::table('t_absensi')
                        ->where('tanggal',$i->format("Y-m-d"))
                        ->where('id_karyawan',$result1->id_karyawan)
                        ->where('id_tipe_absensi',1)
                        ->first();
                    @endphp
                    @if($m)
                        @if($m->menit_terlambat >= 1 && $m->menit_terlambat <= 4)
                            <td>
                                <p style="color: yellow;">Masuk : {{$m->tanggal_masuk}}<br> Keluar : {{$m->tanggal_keluar}}<br> Terlambat : {{$m->menit_terlambat}} Menit</p>
                            </td>
                        @elseif($m->menit_terlambat >= 5 && $m->menit_terlambat <= 29)
                            <td>
                                <p style="color: orange;">Masuk : {{$m->tanggal_masuk}}<br> Keluar : {{$m->tanggal_keluar}}<br> Terlambat : {{$m->menit_terlambat}} Menit</p>
                            </td>
                        @elseif($m->menit_terlambat >= 30)
                            <td>
                                <p style="color: red;">Masuk : {{$m->tanggal_masuk}}<br> Keluar : {{$m->tanggal_keluar}}<br> Terlambat : {{$m->menit_terlambat}} Menit</p>
                            </td>
                        @else
                            <td>
                                <p>Masuk : {{$m->tanggal_masuk}}<br> Keluar : {{$m->tanggal_keluar}}<br> Terlambat : {{$m->menit_terlambat}} Menit</p>
                            </td>
                        @endif
                    @else
                    <td style="background-color: black;">-</td>
                    @endif
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>