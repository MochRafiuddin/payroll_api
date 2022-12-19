@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
$name[] = 'id_karyawan';
$name[] = 'name';
$name[] = 'username';
$name[] = 'email';
$name[] = 'password';
$name[] = 'id_role';
$i = 0;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} User</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1">Nama Karyawan</label>
                            <select class="form-control js-example-basic-single" name="{{$name[$i]}}" id="{{$name[$i]}}"
                                     style="width:100%" data-maximum-selection-length="10">
                                 <option value="" selected disabled>Cari Karyawan</option>                                 
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
                            <label for="exampleInputEmail1">Nama</label>
                            <input type="text" class="form-control @error($name[$i]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[$i])}}" name="{{$name[$i]}}" id="nama" />
                            @if($departemen)
                                <input type="hidden" name="id_departemen" id="id_departemen" value="{{$departemen->id_departemen}}">
                            @else
                                <input type="hidden" name="id_departemen" id="id_departemen" value="">
                            @endif
                            
                            @php $i++ @endphp
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1">Username</label>
                            <input type="text" class="form-control @error($name[$i]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[$i])}}" name="{{$name[$i]}}" />
                                @php $i++ @endphp
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1">Email</label>
                            <input type="text" class="form-control @error($name[$i]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[$i])}}" name="{{$name[$i]}}" />
                                @php $i++ @endphp
                        </div>
                        @if($data)
                            @php $i++ @endphp
                        @else
                            <div class="form-group col-md-6">
                                <label for="exampleInputEmail1">Password</label>
                                <input type="text" class="form-control @error($name[$i]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[$i])}}" name="{{$name[$i]}}" />
                                @php $i++ @endphp
                            </div>
                        @endif
                        <div class="form-group col-md-6">
                            <label for="{{ $name[$i] }}">Role</label>
                            <select class="form-control @error($name[$i]) is-invalid @enderror" name="{{$name[$i]}}" id="{{$name[$i]}}">
                                <option value="" selected disabled> Pilih Role </option>
                                @foreach($role as $key)
                                <option value="<?= $key->{$name[$i]} ?>"
                                    {{(old($name[$i]) == $key->{$name[$i]}) ? 'selected' : ''}}
                                    {{Helper::showDataSelected($data,$name[$i],$key->{$name[$i]})}}>
                                    {{$key->nama_role}}
                                </option>
                                @endforeach
                                @php $i++ @endphp
                            </select>
                        </div>                        
                            <div class="form-group col-md-6" id="inp_multi_anggota" style="display:none">
                                <label>Anggota</label>
                                <select class="js-example-basic-multiple" multiple="multiple" style="width:100%" name="multi_anggota[]" id="multi_anggota">
                                    
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
<script src="{{asset('/')}}assets/js/select2.js"></script>
@error($name[0]) 
<script>
    $($(".js-example-basic-single").select2("container")).addClass("is-invalid"); 
</script>
@enderror
<script>
    $( document ).ready(function() {
        getAnggota();
    });

    $('.js-example-basic-single').select2({
          placeholder: "Cari Karyawan",
          // maximumSelectionLength: 10
    });

    $(".pickerdate").datepicker( {
        format: "dd-mm-yyyy",
    });

    $('#id_karyawan').on('change',function(){
        jQuery.ajax({
            type: 'GET',
            url: '{{ url("user/get-karyawan") }}/'+$('#id_karyawan').val(),
            success: function(result) {
                $('#nama').val(result.nama_karyawan);
                $('#id_departemen').val(result.id_departemen);
            }
        });
    });

    $('#id_role').change(function(e){            
        jQuery.ajax({
            type: 'GET',
            url: '{{ url("user/get-role") }}/'+$(this).val()+'/'+$('#id_departemen').val(),
            success: function(result) {                  
                if (result.data['role'].kode_role == 'leader') {
                    $("#inp_multi_anggota").css('display','block');
                    $('#multi_anggota').html(result.data['karyawan']);
                }else{
                    $("#inp_multi_anggota").css('display','none');                    
                }                
            }
        });
    })

    function getAnggota(){
        $.ajax({
            url: '{{ url("user/get-anggota") }}/'+$('#id_karyawan').val()+'/'+$('#id_departemen').val()+'/'+$('#id_role').val(),
            type: "GET",
            contentType: false,
            cache: false,
            processData:false,
            success: function(result){                
                if (result.error == 0) {                        
                    if (result.data['role'].kode_role == 'leader') {
                        $("#inp_multi_anggota").css('display','block');
                        $('#multi_anggota').html(result.data['karyawan']);
                    }else{
                        $("#inp_multi_anggota").css('display','none');                    
                    }
                }else{
                    $("#inp_multi_anggota").css('display','none');                    
                }
            }
        });
    }
</script>
@endpush