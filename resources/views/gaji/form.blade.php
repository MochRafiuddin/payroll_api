@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
$name[0] = 'nama_gaji';
$name[1] = 'periode_hitung';
$name[2] = 'id_jenis_gaji';

?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} Gaji</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="form-group col">
                            <label for="exampleInputEmail1">Nama</label>
                            <input type="text" class="form-control @error($name[0]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[0])}}" name="{{$name[0]}}" />
                        </div>
                        <div class="form-group col">
                            <label for="exampleInputEmail1">Periode Hitung</label>
                            <select class="form-control @error($name[1]) is-invalid @enderror" name="{{$name[1]}}">
                                <option value="" selected disabled> Pilih Periode Hitung </option>

                                <option value="1" {{(old($name[1]) == 1) ? 'selected' : ''}}
                                    {{Helper::showDataSelected($data,$name[1],1)}}>
                                    Bulan
                                </option>
                                <option value="2" {{(old($name[1]) == 2) ? 'selected' : ''}}
                                    {{Helper::showDataSelected($data,$name[1],2)}}>
                                    Hari
                                </option>
                                <option value="3" {{(old($name[1]) == 3) ? 'selected' : ''}}
                                    {{Helper::showDataSelected($data,$name[1],3)}}>
                                    Masuk hari libur
                                </option>
                            </select>
                        </div>
                         <div class="form-group col">
                             <label for="exampleInputEmail1">Jenis Gaji</label>
                             <select class="form-control @error($name[2]) is-invalid @enderror" name="{{$name[2]}}">
                                 <option value="" selected disabled> Pilih Jenis Gaji</option>
                                 @foreach($jenis_gaji as $key)
                                 <option value="<?= $key->{$name[2]} ?>"
                                     {{(old($name[2]) == $key->{$name[2]}) ? 'selected' : ''}}
                                     {{Helper::showDataSelected($data,$name[2],$key->{$name[2]})}}>
                                     {{$key->nama_jenis_gaji}}
                                 </option>
                                 @endforeach
                             </select>
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
$(document).ready(function () {
    $("select[name=id_jenis_gaji]").change(function (e) {
        e.preventDefault();
        var el = $('select[name=periode_hitung]');
        console.log(parseInt($(this).val()));
        if(parseInt($(this).val()) == 2){
            el.prop("selectedIndex",1);
            el.attr("disabled","disabled");
        }else{
            el.removeAttr("disabled");
        }
    });
});
</script>
@endpush