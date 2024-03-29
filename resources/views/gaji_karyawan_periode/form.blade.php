@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  

?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Gaji Karyawan Periode</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    
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
                            <label for="exampleInputEmail1">{{ucwords($key->nama_gaji)}} (Rp)<br><small for="exampleInputEmail1">{{ucwords($key->nama_jenis_gaji)}} {{ucwords($key->periode_hitung)}}</small></label>
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