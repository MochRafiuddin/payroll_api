@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
?>
<div class="main-panel">
    <div class="content-wrapper">        
        <div class="card">
            <div class="card-body">
                <h4>Cek Data Selfi</h4><br>                                
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
                </div>
                <div class="row mt-3">
                    <div class="form-group col-md-3">
                        <label>Data yang ditampilkan</label>
                        <div class="form-check form-check-flat form-check-primary">
                            <label class="form-check-label">
                            <input type="radio" class="form-check-input" name="tampil" id="tampil" value="0" checked>
                                Hanya Yang Belum di Submit
                            <i class="input-helper"></i></label>
                        </div>
                    </div>
                    <div class="form-group col-md-5">
                        <label>&nbsp;</label>
                        <div class="form-check form-check-flat form-check-primary">
                            <label class="form-check-label">
                            <input type="radio" class="form-check-input" name="tampil" id="tampil" value="1">
                                Semua Data
                            <i class="input-helper"></i></label>
                        </div>
                    </div>
                    <div class="col text-right mt-3">
                        <a href="javascript:;" class="btn btn-info" id="filter">Filter</a>
                    </div>                    
                </div>
                <!-- <div class="my-1" style="margin-left:-3%; margin-right:-3%; border-top:1px solid #060606 !important;"></div> -->
                <div class="row">
                    <div class="col" style="border-top:1px solid #060606 !important;"></div>
                </div>
                <br>                

                <div class="row">
                    <div class="col">
                        <div id="msg">

                        </div>
                        <div class="table-responsive" id="target-table">
                             <table class="table w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Karyawan</th>
                                        <th>Jam Selfi</th>
                                        <th>Tipe</th>
                                        <th>Submitted</th>                                        
                                    </tr>
                                </thead>
                            </table>
                         </div>
                    </div>
                </div>                
                <div class="d-flex justify-content-center">
                    <button type="button" id="save-import" class="btn btn-warning">
                        <div class="d-flex align-items-center">
                            <div id="spinner" class="spinner d-none" style="width: 20px; height:20px;"></div>
                            <span id="text-btn-import" class="ml-2">Submit</span>
                        </div>
                    </button>
                </div><br>
                <div class="row">
                    <div class="col">
                        <div id="msg1">
                            
                        </div>
                    </div>
                </div>
                <!-- <button type="button" id="delete-draft" class="btn btn-danger ">
                    <div class="d-flex align-items-center">
                        <div id="spinner" class="spinner d-none" style="width: 20px; height:20px;"></div>
                        <span id="text-btn-import" class="ml-2">Hapus Draft Absensi</span>
                    </div>
                </button> -->
            </div>
        </div>
    </div>
</div>

<!-- Modal starts -->

  <!-- Modal Ends -->

  <!-- Modal starts -->

  <!-- Modal Ends -->

  <!-- Modal starts -->
  
  <!-- Modal Ends -->

@endsection

@push('js')
<script src="{{asset('/')}}assets/js/select2.js"></script>
<script src="{{asset('/')}}assets/vendors/js/moment.js"></script>

<script>
    $(document).ready(function () {
        $('.js-example-basic').select2({
            placeholder: "Cari Data",
            tags: true,
            dropdownParent: $("#formTambah")            
        });
        $('.js-example-basic-single').select2({
            placeholder: "Cari Data",            
        });
        $(".pickerdate").datepicker( {
            format: "dd-mm-yyyy",
            orientation: "bottom",
            autoclose: true
        });

        var _JSON = [];
        get_data();

        $('#filter').on('click', function(e) {
            get_data();
            // var tampil = $('input[type=radio][name=tampil]:checked').val();
            // console.log(tampil);
        });

        function get_data() {
            var tampil = $('input[type=radio][name=tampil]:checked').val();
            var karyawan = $('#id_karyawan').val();
            var mulai = $('#tanggal_mulai').val();
            var akhir = $('#tanggal_akhir').val();
            _JSON = [];
            $.ajax({
                url: '{{ url("selfi/data-submit") }}',
                type: 'GET',
                data: {
                    tampil : tampil,
                    karyawan : karyawan,
                    mulai : mulai,
                    akhir : akhir,
                },
                success: function (res) {
                    res.data.forEach(function(dataExcel){
                        pushJson(dataExcel);
                    });
                    // console.log(_JSON);
                    read_data();    
                }                
            });            
        }
        function read_data() {            
            var tampil = $('input[type=radio][name=tampil]:checked').val();
            var karyawan = $('#id_karyawan').val();
            var mulai = $('#tanggal_mulai').val();
            var akhir = $('#tanggal_akhir').val();
            $('table').DataTable().destroy();
            $('table').DataTable({

                processing: true,
                serverSide: true,

                "scrollX": true,
                ajax: {
                    url: '{{ url("selfi/data-submit") }}',
                    type: 'GET',
                    data: {
                        tampil : tampil,
                        karyawan : karyawan,
                        mulai : mulai,
                        akhir : akhir,
                    }
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
                        data: 'submitted',
                        name: 'submitted',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        }
        $(document).on('click','.delete',function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Kamu Yakin?',
                text: "Menghapus data ini",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    url = $(this).attr('href');
                    $.get(url, function (data) {
                        $('table').DataTable().draw();
                    })
                }
            })
        })

        $("#save-import").click(function (e) {
            e.preventDefault();
            let param = [];
            _JSON.forEach(function(data){
              if (data.id != 0) {
                param.push(data.id);
              }
            });

            $("#msg").html("");
            $("#save-import").attr('disabled','disabled');
            $("#spinner").removeClass('d-none');
            $("#text-btn-import").text('Sedang meng-upload data ...');

            $.ajax({
            type: "POST",
            url: "{{url('selfi/get-selfi-data')}}",
            data: {json_import:param},
            dataType: "JSON",
            success: function (response) {
                if(response.status){
                    $("#spinner").addClass('d-none');
                    $("#text-btn-import").text('Submit');
                    $("#save-import").removeAttr('disabled');    
                    $("#msg").html("<div class='alert alert-success'> " +response.msg+"</div>");
                    if (response.msg1.length > 0) {
                        $("#msg1").html("<div class='alert alert-warning alert-block'>\
                                    <button type='button' class='close' data-dismiss='alert'>&times;</button>\
                                    <h4>Peringatan!!</h4>\
                                    Data sukses disubmit kecuali untuk karyawan : <br>"+response.msg1.join("")+"<br>karena shift karyawan masih kosong\
                                </div>");
                    }
                }
                $('table').DataTable().draw();                              
            },
            error: function (data) {
                $("#spinner").addClass('d-none');
                $("#text-btn-import").text('Submit');
                $("#save-import").removeAttr('disabled');
            }
        });
        });

        function pushJson(dataExcel){
            _JSON.push({
                id:dataExcel.id,
                userid:dataExcel.id_karyawan,
                checktime:dataExcel.jam_selfi,
                checktype:dataExcel.type,
            });
        }

    $('#tambah_data').click(function(e){
        $('#postForm').trigger("reset");
        $('#formTambah').modal('show');
        $('#id_karyawan_f').val("0").change();
    });
    
    $('#btn-save').click(function (e) {
        e.preventDefault();
        $(this).html('Sending..');
        // console.log($('#id').val());
        if ( $('#id').val().length != 0) {
            var url = "{{ url('absen/update-fingerprint') }}";
        }else{
            var url = "{{ url('absen/add-fingerprint') }}";
        }
    
        $.ajax({
          data: $('#postForm').serialize(),
          url: url,
          type: "POST",
          dataType: 'json',
          success: function (data) {
     
              $('#postForm').trigger("reset");
              $('#btn-save').html('Simpan');
              $('#formTambah').modal('hide');
              $('table').DataTable().destroy();  
              get_data()            

          },
          error: function (data) {
              console.log('Error:', data);
              $('#btn-save').html('Simpan');
          }
      });
    });

    $('body').on('click', '.editUser', function () {
      var id = $(this).data('id');
      $.get("{{ url('absen/get-fingerprint') }}" +'/'+ id, function (data) {
          $('#postForm').trigger("reset");
          $('#modal-title').html("Edit");
          $('#formTambah').modal('show');
          $('#id').val(data['data'].id);
          $('#id_karyawan_f').val(data['data'].userid).change();
          $('#checktime').val(data['checktime']);
          $('#checktype').val(data['data'].checktype).change();
      })
    });
});
</script>
@endpush