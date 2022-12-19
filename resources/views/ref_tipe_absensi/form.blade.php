@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
$name[] = 'nama_tipe_absensi';
$name[] = 'tipe_batas_waktu';
$name[] = 'batas_waktu';
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} Tipe Abensi</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="form-group col">
                            <label for="exampleInputEmail1">Nama Tipe Absensi</label>
                            <input type="text" class="form-control @error($name[0]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[0])}}" name="{{$name[0]}}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="exampleInputEmail1">Tipe Batas Waktu</label>
                            <select class="form-control @error($name[1]) is-invalid @enderror" name="{{$name[1]}}" id="{{$name[1]}}"
                                     style="width:100%" data-maximum-selection-length="10">
                                <option value="" selected disabled>Pilih Batas Waktu</option>                                 
                                <option value="1"
                                    {{(old($name[1]) == 1) ? 'selected' : ''}}
                                    {{Helper::showDataSelected($data,$name[1],1)}}>
                                        H -
                                </option>
                                <option value="2"
                                    {{(old($name[1]) == 2) ? 'selected' : ''}}
                                    {{Helper::showDataSelected($data,$name[1],2)}}>
                                    H +
                                </option>
                             </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="exampleInputEmail1">Batas Waktu(Hari)</label>
                            <input type="text" class="form-control @error($name[2]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[2])}}" name="{{$name[2]}}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <p class="text-warning">* misal hari ini tanggal 13 juli, hanya bisa input sampai h-5 kecuali sabtu minggu, yaitu tanggal 6,7,8,11,12</p>
                            <p class="text-warning" >* misal hari ini tgl 13 juli, hanya bisa input mulai h+7 yaitu : mulai tanggal 21 juli,22 juli dst</p>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-success" value="Simpan" />
                </form>
            </div>
        </div>
    </div>
</div>

@endsection