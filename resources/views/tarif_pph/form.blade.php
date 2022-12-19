@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
$name[] = 'batas_atas';
$name[] = 'batas_bawah';
$name[] = 'tarif';
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} Tarif PPH</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="form-group col">
                            <label for="exampleInputEmail1">Batas Bawah (Rupiah)</label>
                            <input type="text" class="form-control numeric @error($name[1]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[1])}}" name="{{$name[1]}}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="exampleInputEmail1">Batas Atas (Rupiah)</label>
                            <input type="text" class="form-control numeric @error($name[0]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[0])}}" name="{{$name[0]}}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="exampleInputEmail1">Tarif (%)</label>
                            <input type="text" class="form-control @error($name[2]) is-invalid @enderror"
                                value="{{ str_replace('.', ',', Helper::showData($data,$name[2]))}}" name="{{$name[2]}}" onkeypress="return isNumber(event)"/>
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