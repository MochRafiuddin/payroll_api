@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
$name[] = 'id_karyawan';
$name[] = 'id_tipe_absensi';
$name[] = 'tanggal_mulai';
$name[] = 'tanggal_selesai';
$name[] = 'alasan';
$i = 0;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} @lang('umum.izin')</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <input type="hidden" name="id_karyawan_filter" value="{{ $filterkar }}">
                    <input type="hidden" name="id_departement_filter" value="{{ $id_departement_filter }}">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>@lang('umum.karyawan')</label>
                            <select class="form-control js-example-basic-single" name="{{$name[$i]}}" id="{{$name[$i]}}"
                                     style="width:100%" data-maximum-selection-length="10">
                                 <option value="" selected disabled>@lang('umum.cari_karyawan')</option>                                 
                                 @foreach($karyawan as $dat)
                                    <option value="<?= $dat->{$name[$i]} ?>"
                                        {{(old($name[$i]) == $dat->{$name[$i]}) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[$i],$dat->{$name[$i]})}}>
                                        {{$dat->nama_karyawan}}
                                    </option>
                                @endforeach
                            @php $i++ @endphp
                             </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="{{ $name[$i] }}">@lang('umum.absensi')</label>
                            <select class="form-control @error($name[$i]) is-invalid @enderror" name="{{$name[$i]}}" id="{{$name[$i]}}">
                                <option value="" selected disabled> @lang('umum.pilih_absensi') </option>
                                @foreach($tipe_absensi as $key)
                                <option value="<?= $key->{$name[$i]} ?>"
                                    {{(old($name[$i]) == $key->{$name[$i]}) ? 'selected' : ''}}
                                    {{Helper::showDataSelected($data,$name[$i],$key->{$name[$i]})}}>
                                    {{$key->nama_tipe_absensi}}
                                </option>
                                @endforeach
                                @php $i++ @endphp
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                           <label for="{{$name[$i]}}">@lang('umum.tanggal_mulai')</label>
                                @php
                                    if($data != null){
                                        $hsl=date('d-m-Y',strtotime(Helper::showDataDate($data,$name[$i])));
                                    }else{
                                        $hsl='';
                                    }
                                @endphp
                           <input class="form-control pickerdate @error($name[$i]) is-invalid @enderror" type="text"
                               name="{{$name[$i]}}" id="{{$name[$i]}}"
                               value="{{$hsl}}">
                               @php $i++ @endphp
                        </div>
                        <div class="form-group col-md-6">
                           <label for="{{$name[$i]}}">@lang('umum.tanggal_akhir')</label>
                                @php
                                    if($data != null){
                                        $hsl1=date('d-m-Y',strtotime(Helper::showDataDate($data,$name[$i])));
                                    }else{
                                        $hsl1='';
                                    }
                                @endphp
                           <input class="form-control pickerdate @error($name[$i]) is-invalid @enderror" type="text"
                               name="{{$name[$i]}}" id="{{$name[$i]}}"
                               value="{{$hsl1}}">
                               @php $i++ @endphp
                        </div>
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1">@lang('umum.alasan')</label>
                            <textarea type="text" class="form-control @error($name[$i]) is-invalid @enderror" cols="5"
                                rows="6" value="" name="{{$name[$i]}}">{{Helper::showData($data,$name[$i])}}</textarea>
                                @php $i++ @endphp
                        </div>
                        <div class="col-12">
                            <p>@lang('umum.izin_sekarang') : <span id='ini'></span> <br> @lang('umum.izin_selanjutnya') : <span id='depan'></span> </p>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-success" value="@lang('umum.simpan')" />
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('js')
<script src="{{asset('/')}}assets/js/select2.js"></script>
@error($name[0]) 
<script>
    $($(".js-example-basic-single").select2("container")).addClass("is-invalid"); 
</script>
@enderror
<script>
    $('.js-example-basic-single').select2({
          placeholder: "Cari Karyawan",
          // maximumSelectionLength: 10
    });
      

    $('#id_tipe_absensi').on('change',function(){
        jQuery.ajax({
            type: 'GET',
            url: '{{ url("izin-cuti/get-tipe-absensi") }}/'+$('#id_tipe_absensi').val(),
            success: function(result) {
                $(".pickerdate").datepicker("destroy");
                if (result['akhir']==result['awal']) {
                    $(".pickerdate").datepicker( {
                        format: "dd-mm-yyyy",
                        startDate: result['awal'],
                    });      
                }else{
                    $(".pickerdate").datepicker( {
                        format: "dd-mm-yyyy",
                        startDate: result['awal'],
                        endDate: result['akhir'],
                    });      
                }
                
            }
        });
    }); 
    
    $("#id_karyawan").on('change', function(){
      getjmlgaji($(this).val());
    });

    function getjmlgaji(id_karyawan) {        
        var izin = $('#id_izin').val();
        jQuery.ajax({
            type: 'GET',
            url: '{{ url("izin-cuti/get-izin") }}/'+id_karyawan,
            success: function(result) {
                $('#ini').html(result);
                $('#depan').html('0');                
            }
        });
    }
</script>
@endpush