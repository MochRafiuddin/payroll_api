@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
$name[] = 'nama_shift';
$name[] = 'hari_kerja';
$name[] = 'kode_shift';
$name[] = 'jam_masuk';
$name[] = 'jam_keluar';
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} Shift</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="form-group col">
                            <label for="exampleInputEmail1">Nama</label>
                            <input type="text" class="form-control @error($name[0]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[0])}}" name="{{$name[0]}}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="{{$name[2]}}">Kode</label>
                            <input type="text" class="form-control @error($name[2]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[2])}}" name="{{$name[2]}}" />
                            @if ($errors->has($name[2]))
                                <span class="text-danger">{{ $errors->first($name[2]) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="{{$name[3]}}">Jam Masuk</label>
                            <input type="time" class="form-control @error($name[3]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[3])}}" name="{{$name[3]}}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="{{$name[4]}}">Jam Keluar</label>
                            <input type="time" class="form-control @error($name[4]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[4])}}" name="{{$name[4]}}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="{{$name[1]}}"
                                            id="optionsRadios1" value="1"
                                            {{Helper::showDataChecked($data,$name[1],1)}}>
                                        5 Hari Kerja
                                        <i class="input-helper"></i></label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="{{$name[1]}}"
                                            id="optionsRadios2" value="2"
                                            {{Helper::showDataChecked($data,$name[1],2)}}>
                                        6 Hari Kerja
                                        <i class="input-helper"></i></label>
                                </div>
                            </div>
                           
                        </div>
                    </div>
                    <input type="submit" class="btn btn-success" value="Simpan" />
                </form>
            </div>
        </div>
    </div>
</div>

@endsection