@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
$name[] = 'jam_ke';
$name[] = 'rate_hari_kerja';
$name[] = 'rate_hari_libur_1';
$name[] = 'rate_hari_libur_2';
$name[] = 'index_hari_libur_pendek';
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} Tarif Lembur</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1">Jam Ke</label>
                            <input type="text" class="form-control @error($name[0]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[0])}}" name="{{$name[0]}}" />
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1">Rate Hari Kerja</label>
                            <input type="text" class="form-control @error($name[1]) is-invalid @enderror"
                                value="{{str_replace('.', ',', Helper::showData($data,$name[1]))}}" name="{{$name[1]}}" onkeypress="return isNumber(event)" />
                            <small class="mt-3 text-info">Diisi dengan <i>N</i> kali 
                                upah sejam. Misalkan 2x upah sejam.</small>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1">Rate Hari Libur 1</label>
                            <input type="text" class="form-control numeric text-left @error($name[2]) is-invalid @enderror"
                                value="{{str_replace('.', ',', Helper::showData($data,$name[2]))}}" name="{{$name[2]}}" onkeypress="return isNumber(event)" />
                            <small class="mt-3 text-info">Diisi dengan <i>N</i> kali upah sejam. Misalkan 2x upah
                                sejam.<br>Untuk Karyawan 5 Hari Kerja.</small>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1">Rate Hari Libur 2</label>
                            <input type="text" class="form-control numeric text-left @error($name[3]) is-invalid @enderror"
                                value="{{str_replace('.', ',', Helper::showData($data,$name[3]))}}" name="{{$name[3]}}" onkeypress="return isNumber(event)" />
                            <small class="mt-3 text-info">Diisi dengan <i>N</i> kali  upah sejam. Misalkan 2x upah
                                sejam.<br>Untuk Karyawan 6 Hari Kerja.</small>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1">Rate Hari Libur Pendek</label>
                            <input type="text" class="form-control numeric text-left @error($name[4]) is-invalid @enderror"
                                value="{{str_replace('.', ',', Helper::showData($data,$name[4]))}}" name="{{$name[4]}}" onkeypress="return isNumber(event)" />
                            <small class="mt-3 text-info">Diisi dengan <i>N</i> kali  upah sejam. Misalkan 2x upah
                                sejam.<br>Untuk hari kerja terpendek (5 jam).</small>

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
function isNumber(evt) {
   var theEvent = evt || window.event;
   var key = theEvent.keyCode || theEvent.which;            
   var keyCode = key;
   key = String.fromCharCode(key);          
   if (key.length == 0) return;
   var regex = /^[0-9,\b]+$/;            
   if(keyCode == 188 || keyCode == 190){
      return;
   }else{
      if (!regex.test(key)) {
         theEvent.returnValue = false;                
         if (theEvent.preventDefault) theEvent.preventDefault();
      }
    }    
 }
</script>     
@endpush