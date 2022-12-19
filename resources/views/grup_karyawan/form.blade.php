@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
$name[] = 'nama_grup';
$name[] = 'kode_grup';
$name[] = 'hari_kerja';
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} Grup Karyawan</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="form-group col">
                            <label for="{{$name[0]}}">Nama Grup</label>
                            <input type="text" class="form-control @error($name[0]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[0])}}" name="{{$name[0]}}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="{{$name[1]}}">Kode Grup</label>
                            <input type="text" class="form-control @error($name[1]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[1])}}" name="{{$name[1]}}" />
                            @if ($errors->has($name[1]))
                                <span class="text-danger">{{ $errors->first($name[1]) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="{{$name[2]}}"
                                            id="optionsRadios1" value="1" {{Helper::showDataChecked($data,$name[2],1)}}>
                                        5 Hari Kerja
                                        <i class="input-helper"></i></label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="{{$name[2]}}"
                                            id="optionsRadios2" value="2" {{Helper::showDataChecked($data,$name[2],2)}}>
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