@extends('template')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
              <h4>@lang('umum.edit_lembur')</h4><br>
                <div class="row">
                    <div class="form-group col-md-2">
                        <label for="">@lang('umum.tanggal_shift'):</label><br>
                        <label for="">{{$data->tanggal_shift}}</label>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="">@lang('umum.shift_masuk'):</label><br>
                        <label for="">{{$data->shift_masuk}}</label>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="">@lang('umum.shift_keluar'):</label><br>
                        <label for="">{{$data->shift_keluar}}</label>
                    </div>  
                    <div class="form-group col-md-2">
                        <label for="">@lang('umum.waktu_masuk'):</label><br>
                        <label for="">{{$data->waktu_masuk}}</label>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="">@lang('umum.waktu_keluar'):</label><br>
                        <label for="">{{$data->waktu_keluar}}</label>
                    </div>                    
                </div>
                <hr><br>
                <div class="row">
                    <div class="col-4">
                        @lang('umum.tarif')
                    </div>
                    <div class="col-4">
                        @lang('umum.jumlah_jam')
                    </div>
                </div><br>
                <form id="form" action="{{url('/lembur/update-lembur')}}" method="POST">
                  <div id="content">
                  @csrf
                  <input type="text" class="form-control" name="id_karyawan_filter" value="{{$id_karyawan_filter}}" hidden>

                  <input type="text" class="form-control" name="id_karyawan" value="{{$data->id_karyawan}}" hidden>
                  <input type="text" class="form-control" name="tanggal_shift" value="{{$data->def_tanggal_shift}}" hidden>
                  <input type="text" class="form-control" name="id_shift" value="{{$data->id_shift}}" hidden>
                  @foreach($data->data_lembur as $data)
                    <div class="sub-content">
                      <div class="row">
                          <div class="col-4">
                              <input type="text" class="form-control numeric" name="index_tarif[]" value="{{$data['index_tarif']}}">
                          </div>
                          <div class="col-4">
                              <input type="text" class="form-control numeric" name="jumlah_jam[]" value="{{$data['jumlah_jam']}}">
                          </div>
                          <div class="col-4">
                              <input type="button" class="btn btn-danger hapus" value="Hapus">
                          </div>
                      </div><br>
                    </div>
                  @endforeach
                  </div>
                  @error('index_tarif')
                    <small class="text-danger">
                        Tariff index and Total Hours is required.
                    </small>
                  @enderror  
                  <hr><br>
                    <div class="form-group">
                        <label for="exampleFormControlTextarea1">@lang('umum.alasan_lembur')</label>
                        <textarea class="form-control" name="alasan" id="exampleFormControlTextarea1" rows="3">{{$alasan}}</textarea>
                    </div>
                  <div class="row">
                      <div class="col-4">
                          <input type="button" class="btn btn-info" value="@lang('umum.tambah')" id="btn-tambah">
                      </div>
                      <div class="col-8 text-right">
                          <input type="submit" class="btn btn-success" value="@lang('umum.simpan')">
                      </div>
                  </div><br>
                </form>
            </div>
        </div>
    </div>

    <!-- content-wrapper ends -->
    <!-- partial:partials/_footer.html -->
    @include("partial.footer")
    <!-- partial -->
</div>
@endsection
@push('js')
<script>
    $(document).ready(function () {

      $(".numeric").autoNumeric('init', {
          aPad: false,
          aDec: ',',
          aSep: '.'
      });

      $('#content').on('click', 'input.hapus', function(events){
          events.preventDefault();
          let idx = $(this).closest('.sub-content').index();
          $(this).parent().parent().parent().remove();
      });

      $("#btn-tambah").click(function(e){

          let html = '<div class="sub-content">\
                        <div class="row">\
                            <div class="col-4">\
                                <input type="text" class="form-control numeric" name="index_tarif[]" value="0">\
                            </div>\
                            <div class="col-4">\
                                <input type="text" class="form-control numeric" name="jumlah_jam[]" value="0">\
                            </div>\
                            <div class="col-4">\
                                <input type="button" class="btn btn-danger hapus" value="Hapus">\
                            </div>\
                        </div><br>\
                      </div>';
          $('#content').append(html);
          $(".numeric").autoNumeric('init', {
              aPad: false,
              aDec: ',',
              aSep: '.'
          });
          
      });

    });


</script>
@endpush