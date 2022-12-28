@extends('template')
@section('content')
<?php 
    use App\Traits\Helper;  
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Data Absensi Selfi</h4><br>
                <div class="row mt-3">
                    <div class="form-group col-3">
                        <label for="filterMonthYear">Tanggal Mulai</label>                                    
                        <input class="form-control pickerdate" type="text" name="tanggal_mulai" id="tanggal_mulai" value="{{$mulai}}">
                    </div>
                    <div class="form-group col-3">
                        <label for="filterMonthYear">Tanggal Akhir</label>
                        <input class="form-control pickerdate" type="text" name="tanggal_akhir" id="tanggal_akhir" value="{{$akhir}}">
                    </div>
                    <div class="form-group col-3">
                        <label for="filterMonthYear">Karyawan</label>
                        <select class="form-control js-example-basic-single" name="id_karyawan" id="id_karyawan" style="width:100%" data-maximum-selection-length="10">
                            <option value="0">Semua Karyawan</option>
                            @foreach($karyawan as $data)
                                <option value="{{$data->id_karyawan}}">{{$data->nama_karyawan}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-3">
                        <div class="row">
                            <div class="col-12">
                                <label for="filterMonthYear"></label>
                            </div>
                            <div class="col-12">                                
                                <a href="javascript:;" class="btn btn-info" id="filter">Filter</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Karyawan</th>
                                        <th>Jam Selfi</th>
                                        <th>Tipe</th>                                        
                                        <th>status</th>                                        
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Detail</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class='row'>
              <input type="hidden" name="id" id="id">
              <div class="col-8">
                  <label for="">Cari lokasi</label>
                  <div id="map" style="width:100%;height:400px;"></div><br>
              </div>
              <div class="col-4">
                  <label for="">Gambar</label>
                  <div class="d-flex justify-content-center">
                    <img  alt="" id='gambar' width="80%">
                  </div><br>
              </div>
              <div class="col-6">
                  <div class="form-group">
                      <label>Latitude</label>
                      <input type="text" name="latitude" id="latitude" class="form-control" placeholder="Latitude" value="" readonly/>
                  </div>
              </div>
              <div class="col-6">
                  <div class="form-group">
                      <label>Longitude</label>
                      <input type="text" name="longitude" id="longitude" class="form-control" placeholder="Longitude" value="" readonly/>
                  </div>
              </div>  
          </div>
      </div>
      <div class="modal-footer">
        <div class="col-12">
              <button type="button" class="btn btn-success float-left" id="setuju">Setuju</button>
              <button type="button" class="btn btn-danger float-right" id="tolak">Tolak</button>
          </div>
      </div>
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
        read_data();

        $(".pickerdate").datepicker( {
            format: "dd-mm-yyyy",
            orientation: "bottom",
            autoclose: true
        });

    });
    $('#filter').on('click', function(e) {
        read_data();            
    });
    function read_data() {
        var karyawan = $('#id_karyawan').val();
        var mulai = $('#tanggal_mulai').val();
        var akhir = $('#tanggal_akhir').val();
        $('.table').DataTable().destroy();
        $('.table').DataTable({
            processing: true,
            serverSide: true,
            "scrollX": true,
            ajax: {
                url: '{{ url("selfi/data") }}',
                type: 'GET',
                data: {
                    karyawan : karyawan,
                    mulai : mulai,
                    akhir : akhir,
                },
            },
            rowReorder: {
                selector: 'td:nth-child(1)'
            },
            responsive: true,
            columns: [{
                    "data": 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    width: '4%',
                    className: 'text-center'
                },
                {
                    data: 'nama_karyawan',
                    name: 'nama_karyawan',
                },
                {
                    data: 'jam_selfi',
                    name: 'jam_selfi',
                },
                {
                    data: 'tipe',
                    name: 'tipe',
                },
                {
                    data: 'status_selfi',
                    name: 'status_selfi',
                },                
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
    }
    $('body').on('click', '.editPass', function () {
        var id = $(this).data('id');    
        var latitude = $(this).data('latitude');    
        var longitude = $(this).data('longitude');
        var gambar = $(this).data('gambar');
        $('#exampleModal').modal('show');
        $('#id').val(id);
        $('#latitude').val(latitude);
        $('#longitude').val(longitude);
        // $('#gambar').val(gambar);
        $('#gambar').attr('src', '{{asset("upload/foto")}}/'+gambar);
        initAutocomplete();
    });
    $('#setuju').click(function (e) {
        $.ajax({
            data: {
                id: $('#id').val(),
                status: 1
            },
            url: "{{ url('selfi/set-status') }}",
            type: "POST",
            dataType: 'json',
            success: function (data) {
                $('#postForm').trigger("reset");
                $('#exampleModal').modal('hide');
                read_data();
            }
        });
    });
    $('#tolak').click(function (e) {
        $.ajax({
            data: {
                id: $('#id').val(),
                status: 2
            },
            url: "{{ url('selfi/set-status') }}",
            type: "POST",
            dataType: 'json',
            success: function (data) {
                $('#postForm').trigger("reset");
                $('#exampleModal').modal('hide');
                read_data();
            }
        });
    });
</script>
<script>
    function initAutocomplete() {
        // var lati = document.getElementById('latitude').value;
        // var long = document.getElementById('longitude').value;
        var lati = $('#latitude').val();
        var long = $('#longitude').val();
        // console.log(lati, long);
      var latlng = new google.maps.LatLng(lati, long);
        var map = new google.maps.Map(document.getElementById('map'), {
            center: latlng,
            zoom: 18,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        var marker1 = new google.maps.Marker({
            position: latlng,
            map: map,
            title: '',
            draggable: false
        })
        google.maps.event.addListener(marker1, 'dragend', function(marker1) {
            var latLng = marker1.latLng;
            document.getElementById('latitude').value = latLng.lat();
            document.getElementById('longitude').value = latLng.lng();
        });
      var input = document.getElementById('pac-input');
    //   map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

        
    }
</script>    
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB-W-RsTAPM3gMXac5yEMIxNbip9mSEVuo&callback=initAutocomplete&libraries=places&v=weekly" defer></script>
@endpush