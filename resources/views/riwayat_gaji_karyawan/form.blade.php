@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  

?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Gaji Karyawan</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <div class="form-check form-check-danger">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" value="1" name="ganti_gaji_periode">
                            Apakah anda ingin mengubah juga, gaji periode {{Helper::convertBulan(Session::get('periode_bulan'))." ".Session::get('periode_tahun')}} 
                            ?
                            <i class="input-helper"></i></label>
                    </div>
                    <div class="row">
                         <div class="form-group col-md-6">
                            <label for="exampleInputEmail1">NIK</label>
                            <input type="text" class="form-control"
                                value="{{$karyawan->nik}}"  disabled/>
                        </div>
                         <div class="form-group col-md-6">
                            <label for="exampleInputEmail1">Nama Karyawan</label>
                            <input type="text" class="form-control"
                                value="{{$karyawan->nama_karyawan}}" disabled/>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($data as $key)
                        <div class="form-group col-md-4">
                            <label for="exampleInputEmail1">{{$key->nama_gaji}} (Rp)</label>
                            <input type="text" class="form-control numeric"
                                value="{{($key->nominal == null) ? 0 : $key->nominal}}" name="gaji_{{$key->id_gaji}}" />
                        </div>
                        @endforeach
                    </div>
                    <input type="submit" class="btn btn-success" value="Simpan" />
                </form>
            </div>
        </div>
    </div>
</div>

@endsection