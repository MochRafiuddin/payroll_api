@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
$name[0] = 'nama_shift';
$name[2] = 'kode_shift';
$name[3] = 'jam_masuk';
$name[4] = 'jam_keluar';
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
                            <input type="text" class="form-control timepicker @error($name[3]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[3])}}" name="{{$name[3]}}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="{{$name[4]}}">Jam Keluar</label>
                            <input type="text" class="form-control timepicker @error($name[4]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[4])}}" name="{{$name[4]}}" />
                        </div>
                    </div>
                    <input type="submit" class="btn btn-success" value="Simpan" />
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('js')
<script>
$('.timepicker').timepicker({
    timeFormat: 'HH:mm',
    dynamic: false,
    dropdown: false,
    scrollbar: false
});
</script>
@endpush