@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  

?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} Periode</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="form-group col">
                            <label for="exampleInputEmail1">Date</label>
                       
                                <div id="datepicker-popup" class="input-group date datepicker p-0">
                                    <input type="text" name="tanggal_periode"
                                        class="form-control @error('tanggal_periode') is-invalid @enderror"
                                        value="{{($data != null) ? $data->bulan.'-'.$data->tahun : '' }}">
                                    <span class="input-group-addon input-group-append border-left">
                                        <span class="mdi mdi-calendar input-group-text"></span>
                                    </span>
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
@push("js")
<script>
    $('#datepicker-popup').datepicker({
      enableOnReadonly: true,
      todayHighlight: true,
      format: "mm-yyyy",
      startView: "months",
      minViewMode: "months"
    });
</script>
@endpush