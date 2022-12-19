@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  

?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Import Absensi</h4><br>
                <h6 class="card-title">{{$titlePage}} Shift</h6>
                <a href="{{url('absen/unduh-format')}}" class="mb-3">Download format import</a>
                <div class="row mt-3">
                    <form action="#" id="form" method="post">
                        @csrf
                        <div class="form-group col">
                            <input type="file" name="file_excel" class="file-upload-default">
                            <div class="input-group col-xs-12" id="file">
                                <input type="text" class="form-control file-upload-info" disabled placeholder="Silahkan pilih File">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                                </span>
                                <span class="input-group-append ml-2">
                                    <button type="submit" id="priview" class="btn btn-danger" type="button">Preview</button>
                                </span>
                                <span class="input-group-append ml-2">
                                    <button id="load" class="btn btn-success" type="button" style="color:white">Load Data</button>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row mt-3">
                    <div class="form-group col-md-5">                        
                        <div class="form-check form-check-flat form-check-primary">
                            <label class="form-check-label">
                            <input type="checkbox" class="form-check-input salah-checkbox" name="data_salah">
                                Show Data Bermasalah
                            <i class="input-helper"></i></label>
                        </div>
                    </div>
                    <div class="col text-right">
                        <a href="javascript:;" class="btn btn-info" id="tambah_data">Tambah</a>
                    </div>
                </div>
                <br>
                <div class="loading" style="display: none;">
                    <div class="jumping-dots-loader">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col">
                        <div id="msg">

                        </div>
                        <div class="table-responsive" id="target-table">
                             
                         </div>
                    </div>
                </div>
                
                <ul class="text-warning">
                    <li>Pastikan Waktu Masuk dan Waktu Keluar Karyawan, sudah terisi semua.</li>
                    <li>Pastikan Shift Karyawan sudah di atur.</li>
                    <li>Untuk karyawan yang sama dan tanggal yang sama, lembur akan dihitung ulang.</li>
                </ul>
                <div class="d-flex justify-content-center">
                    <button type="button" id="save-import" class="btn btn-warning" disabled>
                        <div class="d-flex align-items-center">
                            <div id="spinner" class="spinner d-none" style="width: 20px; height:20px;"></div>
                            <span id="text-btn-import" class="ml-2">Submit Data Fingerprint</span>
                        </div>
                    </button>
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
  <div class="modal fade" id="formTambah" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel-3" aria-hidden="true">
    <div class="modal-dialog modal-md" style="margin-top:1%;" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel-3">Tambah</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
        <form action="{{url('/absen/add-log-absensi')}}" method="post">
            @csrf
          <label for="tanggal_shift"><small>Tanggal Shift</small></label>
          <input type="text" class="form-control pickerdate" name="tanggal_shift">
          <label for="id_karyawan" style="margin-top:2%;"><small>Karyawan</small></label>
          <select class="form-control js-example-basic-single" name="id_karyawan"
                  style="width:100%" data-maximum-selection-length="10" required>
                  <option value="">-- Pilih Karyawan --</option>
              @foreach($karyawan as $data)
                  <option value="{{$data->id_karyawan}}">{{$data->nama_karyawan}}</option>
              @endforeach
          </select>
          <label for="id_shift" style="margin-top:2%;"><small>Shift</small></label>
          <select class="form-control js-example-basic-single" name="id_shift"
                    style="width:100%" data-maximum-selection-length="10" required>
                  <option value="">-- Pilih Shift --</option>
              @foreach($shift as $data)
                  <option value="{{$data->id_shift}}">{{ucwords($data->nama_shift)}}</option>
              @endforeach
          </select>
          <label for="waktu_masuk" style="margin-top:2%;"><small>Waktu Masuk</small></label>
          <input type="text" class="form-control" data-inputmask="'alias': 'datetime'" name="waktu_masuk" required>
          <label for="waktu_keluar" style="margin-top:2%;"><small>Waktu Keluar</small></label>
          <input type="text" class="form-control" data-inputmask="'alias': 'datetime'" name="waktu_keluar" required>
        </div>
        <div class="modal-footer">
          <input type="submit" class="btn btn-success" value="Simpan" id="btn-save"/>
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal Ends -->

  <!-- Modal starts -->
  <div class="modal fade" id="formEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel-3" aria-hidden="true">
    <div class="modal-dialog modal-md" style="margin-top:1%;" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel-3">Edit</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
        <form action="" id="formUpdate" method="post">
            @csrf
          <input type="text" class="form-control" name="id" hidden>
          <label for="tanggal_shift"><small>Tanggal Shift</small></label>
          <input type="text" class="form-control pickerdate" name="edit_tanggal_shift">
          <label for="id_karyawan" style="margin-top:2%;"><small>Karyawan</small></label>
          <select class="form-control js-example-basic-single" name="edit_id_karyawan"
                  style="width:100%" data-maximum-selection-length="10" disabled>
                  <option value="">-- Pilih Karyawan --</option>
              @foreach($karyawan as $data)
                  <option value="{{$data->id_karyawan}}">{{$data->nama_karyawan}}</option>
              @endforeach
          </select>
          <label for="edit_id_shift" style="margin-top:2%;"><small>Shift</small></label>
          <select class="form-control js-example-basic-single" name="edit_id_shift"
                    style="width:100%" data-maximum-selection-length="10" required>
                  <option value="">-- Pilih Shift --</option>
              @foreach($shift as $data)
                  <option value="{{$data->id_shift}}">{{ucwords($data->nama_shift)}}</option>
              @endforeach
          </select>
          <label for="waktu_masuk" style="margin-top:2%;"><small>Waktu Masuk</small></label>
          <input type="text" class="form-control" data-inputmask="'alias': 'datetime'" name="edit_waktu_masuk" required>
          <label for="waktu_keluar" style="margin-top:2%;"><small>Waktu Keluar</small></label>
          <input type="text" class="form-control" data-inputmask="'alias': 'datetime'" name="edit_waktu_keluar" required>
        </div>
        <div class="modal-footer">
          <input type="button" class="btn btn-success" value="Simpan" id="save-update"/>
          <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
        </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal Ends -->

  <!-- Modal starts -->
  <div class="modal fade" id="filterTanggal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel-3" aria-hidden="true">
    <div class="modal-dialog modal-md" style="margin-top:1%;" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel-3">Pilih Tanggal</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control" name="id" hidden>
          <label for="waktu_masuk" style="margin-top:2%;"><small>Mulai Tanggal</small></label>
          <small style="color: red; display:none;" id="msg-start-date">Field wajib diisi.</small>
          <input type="text" class="form-control pickerdate" name="start_date" required>
          <label for="waktu_keluar" style="margin-top:2%;"><small>Sampai Tanggal</small></label>
          <small style="color: red; display:none;" id="msg-end-date">Field wajib diisi.</small>
          <input type="text" class="form-control pickerdate" name="end_date" required>
          <label for="id_karyawan" style="margin-top:2%;"><small>Karyawan</small></label>
          <select class="form-control js-example-single" name="load_id_karyawan" style="width:100%" data-maximum-selection-length="10">
                  <option value="0">-- Semua Karyawan --</option>
              @foreach($karyawan as $data)
                  <option value="{{$data->id_karyawan}}">{{$data->nama_karyawan}}</option>
              @endforeach
          </select>
          <label for="data" style="margin-top:2%;"><small>Data yang ditampilkan</small></label>
        <div class="form-check form-check-flat form-check-primary">
            <label class="form-check-label">
                <input type="radio" class="form-radio-input" name="data" value="0" checked>Hanya yang belum di import<i class="input-helper"></i>
            </label>
        </div>
        <div class="form-check form-check-flat form-check-primary">
            <label class="form-check-label">
                <input type="radio" class="form-radio-input" name="data" value="1">Semua data<i class="input-helper"></i>
            </label>
        </div>

        </div>
        <div class="modal-footer">
          <input type="submit" class="btn btn-success" value="Cari" id="btn-cari" style="color:white"/>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal Ends -->

@endsection

@push('js')
<script src="{{asset('/')}}assets/js/select2.js"></script>
<script src="{{asset('/')}}assets/vendors/js/moment.js"></script>

<script>
    $(document).ready(function () {
        $('.js-example-basic-single').select2({
            placeholder: "Cari Data",
            dropdownParent: $('#formTambah')
        });
        $(".pickerdate").datepicker( {
            format: "dd-mm-yyyy",
            orientation: "bottom",
            autoclose: true
        });

        $("#save-import").attr('disabled','disabled');
        $("#table-priview").DataTable();
        cekFileUpload();
        var _JSON = [];
        var karyawan = <?= $karyawan ?>;
        var shift_karyawan = <?= $shift_karyawan ?>;
        var shift = <?= $shift ?>;
        var cek_karyawan = true;
        var btnImport = true;

        $('#btn-cari').click(function(e){
            let start_date = $('input[name="start_date"]').val();
            let end_date = $('input[name="end_date"]').val();
            let karyawan = $('select[name="load_id_karyawan"]').val();
            let data = $('input[name="data"]:checked').val();

            if (start_date == "") {
                $('#msg-start-date').show();
                return;
            }
            if (end_date == "") {
                $('#msg-end-date').show();
                return;
            }

            $('#filterTanggal').modal('hide');
            e.preventDefault();
            _JSON = [];
            generateHtml();
            $(".loading").css('display','block');

            $.ajax({
                url: "{{url('absen/get-log-absensi')}}",
                type: "GET",
                data : {
                    start_date : start_date,
                    end_date : end_date,
                    karyawan : karyawan,
                    data : data
                },
                success: function(res){
                    showForm();
                    res.data.forEach(function(dataExcel){
                        pushJson(dataExcel);
                    });
                    
                    cekJamKosong();
                    generateHtml();


                    if(btnImport){
                        $("#save-import").removeAttr('disabled');
                    }else{
                        $("#save-import").attr('disabled','disabled');
                    }
                    $('#file').removeClass('border-danger');
                }
            });
        });

        $("#save-update").on("click", function(e){
           e.preventDefault();

           let url = $('#formUpdate').attr('action');
           let id = $('input[name="id"]').val();
           let tanggal_shift = $('input[name="edit_tanggal_shift"]').val();
           let id_shift = $('select[name="edit_id_shift"]').val();
           let waktu_masuk = $('input[name="edit_waktu_masuk"]').val();
           let waktu_keluar = $('input[name="edit_waktu_keluar"]').val();

           $('#formEdit').modal('show');
           _JSON = [];
           generateHtml();
           $(".loading").css('display','block');

           $.ajax({
               url: url,
               type: "POST",
               data : {
                   id : id,
                   edit_tanggal_shift : tanggal_shift,
                   edit_id_shift : id_shift,
                   edit_waktu_masuk : waktu_masuk,
                   edit_waktu_keluar : waktu_keluar
               },
               success: function(res){
                    $('#formEdit').modal('hide');
                    let start_date = $('input[name="start_date"]').val();
                    let end_date = $('input[name="end_date"]').val();
                    $.ajax({
                        url: "{{url('absen/get-log-absensi')}}",
                        type: "GET",
                        data : {
                            start_date : start_date,
                            end_date : end_date
                        },
                        success: function(res){
                            showForm();
                            res.data.forEach(function(dataExcel){
                                pushJson(dataExcel);
                            });
                            
                            cekJamKosong();
                            generateHtml();

                            if(btnImport){
                                $("#save-import").removeAttr('disabled');
                            }else{
                                $("#save-import").attr('disabled','disabled');
                            }
                            $('#file').removeClass('border-danger');
                        }
                    });
                   
               }
           });
        });
  
        generateHtml();
        $("#form").on('submit',(function(e) {
            e.preventDefault();
            _JSON = [];
            generateHtml();
            btnImport = false;
            $("#msg").html("");
            $(".loading").css('display','block');
            $('#file').css('display','none');
            
            $.ajax({
                url: "{{url('absen/priview-import')}}",
                type: "POST",
                data:  new FormData(this),
                contentType: false,
                cache: false,
                processData:false,
                success: function(res){
                        // console.log(shift_karyawan)
                    if(res.status){
                        showForm();
                        res.data.forEach(function(dataExcel){
                            pushJson(dataExcel);
                        });
                        
                        cekJamKosong();
                        generateHtml();


                        if(btnImport){
                            $("#save-import").removeAttr('disabled');
                        }else{
                            $("#save-import").attr('disabled','disabled');
                        }
                        $('#file').removeClass('border-danger');

                    }else{
                        showForm();
                        $("#msg").html("<div class='alert alert-danger'> " +res.msg+"</div>");

                    }

                
                }
            });
                    
            }
        ));
        $("#save-import").click(function(e){
            saveFingerPrint();
        });
        function showForm(){
             $('#file').css('display','inline-flex');
             $(".loading").css('display','none');
        }
        function saveFingerPrint(){
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
                url: "{{url('absen/save-import')}}",
                data: {json_import:param},
                dataType: "JSON",
                success: function (response) {
                    if(response.status){
                        $("#spinner").addClass('d-none');
                        $("#text-btn-import").text('Import Data Fingerprint');
                        $("#save-import").removeAttr('disabled');
                        
                        $("#msg").html("<div class='alert alert-success'> " +response.msg+"</div>");

                    }

                    location.reload(); 
                },
                error: function(error){
                    $("#spinner").addClass('d-none');
                    $("#text-btn-import").text('Import Data Fingerprint');
                    $("#save-import").removeAttr('disabled');
                }
            });
        }
        function pushJson(dataExcel){
            _JSON.push({
                id:dataExcel.id,
                id_karyawan:dataExcel.id_karyawan,
                nama_karyawan:dataExcel.nama_karyawan,
                nik:dataExcel.nik,
                kode:dataExcel.kode,
                id_shift:dataExcel.id_shift,
                tanggal_shift:dataExcel.tanggal_shift,
                jam_masuk_shift:dataExcel.jam_masuk_shift,
                jam_keluar_shift:dataExcel.jam_keluar_shift,
                waktu_masuk:dataExcel.waktu_masuk,
                waktu_keluar:dataExcel.waktu_keluar,
                id_tipe_absensi:dataExcel.id_tipe_absensi,
                nama_tipe_absensi:dataExcel.nama_tipe_absensi,
                imported:dataExcel.imported,
            });
        }
        function formatDate(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) 
                month = '0' + month;
            if (day.length < 2) 
                day = '0' + day;

            return [year, month, day].join('-');
        }
        function formatDateDMY(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) 
                month = '0' + month;
            if (day.length < 2) 
                day = '0' + day;

            return [day, month, year].join('-');
        }
        function formatDatetimeDMY(date) {
            let time = date.substring(11,19);
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) 
                month = '0' + month;
            if (day.length < 2) 
                day = '0' + day;

            return [day, month, year].join('-')+' '+time;
        }
        function cekJamKosong(){
            _JSON.forEach(function(data){
              if (data.id != 0) {
                if(data.waktu_masuk == "" || data.waktu_keluar == "" || data.waktu_masuk == null || data.waktu_keluar == null){
                    btnImport = false;
                }else{
                    btnImport = true;
                }
              }
            })
        }
        function cariKaryawan(kode_fingerprint){
            var result = 0;
            karyawan.forEach(function(data){
                
                if(parseInt(data.kode_fingerprint) == parseInt(kode_fingerprint)){
                    result = data;
                }
            });
            return result;
        }
        function cekKaryawanInJson(id_karyawan){
            var result = null;
            _JSON.forEach(function(i, index){
                
                if(parseInt(i.id_karyawan) == parseInt(id_karyawan)){
                    return result = index;
                }
            });
            return result;
        }
        function getShiftKaryawan(id_karyawan,tanggal_shift){
            var result = null;
            shift_karyawan.forEach(function(data,index){
                if(parseInt(data.id_karyawan) == parseInt(id_karyawan) && data.tanggal == tanggal_shift){
                    // if(data.id_karyawan == 1){
                        // console.log(data);
                    // }
                // console.log(getShift(data.id_shift))
                    return result = getShift(data.id_shift);
                }
            })
            return result;
        }
        function getShift(id_shift){
            var result = 0;
            shift.forEach(function(data,index){
                if(parseInt(data.id_shift) == parseInt(id_shift)){
                    return result = data;
                }
            })
            return result;
        }
        function formatDateOnly(date){
            let tanggal = new Date(date);
            tanggal = tanggal.getFullYear()+'-' + (tanggal.getMonth()+1) + '-'+tanggal.getDate();

            return tanggal;
        }

        $('.salah-checkbox').on('click', function(e) {
            showForm();
            cekJamKosong();
            generateHtml();
            
        });

        function generateHtml(){
            let salah = $('input[name="data_salah"]:checked').val();
            var html = '<table class="table mb-3" id="table-priview">';
                html += '<thead>';
                html += '<tr>';
                html += '<th>NIK</th>';
                html += '<th>Nama Karyawan</th>';
                html += '<th>Tanggal Shift</th>';
                html += '<th>Jam Masuk Shift</th>';
                html += '<th>Jam Keluar Shift</th>';
                html += '<th width="17%">Waktu Masuk</th>';
                html += '<th width="17%">Waktu Keluar</th>';
                html += '<th>Import</th>';
                html += '<th width="">Opsi</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';
            // console.log(html);
            _JSON.forEach(function(data){
              let start  = data.waktu_masuk;
              let end = data.waktu_keluar;
              let interval = moment.utc(moment(end,"YYYY-MM-DD HH:mm:ss").diff(moment(start,"YYYY-MM-DD HH:mm:ss"))).format("HH:mm:ss");
              let tanggal_shift = moment(new Date(data.tanggal_shift)).format('DD/MM/YYYY');
              let waktu_masuk = data.waktu_masuk ? moment(new Date(data.waktu_masuk)).format('DD/MM HH:mm:ss') : '';
              let waktu_keluar = data.waktu_keluar ? moment(new Date(data.waktu_keluar)).format('DD/MM HH:mm:ss') : '';
            if (salah != 'on') {                      
              if (data.id != 0) {
                if (data.waktu_masuk == null || data.waktu_keluar == null || data.waktu_masuk > data.waktu_keluar || (data.waktu_masuk < data.waktu_keluar && interval < "01:00:00")) {
                    btnImport = false;
                    html += "<tr class='table-danger'>";
                        html += "<td>"+data.nik+"</td>";
                        html += "<td>"+data.nama_karyawan+"</td>";
                        html += "<td>"+tanggal_shift+"</td>";
                        html += "<td style='white-space: nowrap'>"+(data.jam_masuk_shift ?? '<div class="badge badge-outline-success">Libur</div>')+"</td>";
                        html += "<td style='white-space: nowrap'>"+(data.jam_keluar_shift ?? '<div class="badge badge-outline-success">Libur</div>')+"</td>";
                        html += "<td style='white-space: nowrap'>"+(waktu_masuk ?? '')+"</td>";
                        html += "<td style='white-space: nowrap'>"+(waktu_keluar ?? '')+"</td>";
                        if(data.imported==1){
                            html += "<td>Imported</td>";
                        }else{
                            html += "<td></td>";
                        }
                        html += "<td><a href='javascript:;' link='{{url('/absen/edit-log-absensi?id=')}}"+data.id+"' class='text-warning edit mr-2'><span class='mdi mdi-pen'></span></a><a href='javascript:;' link='{{url('/absen/delete-log-absensi?id=')}}"+data.id+"' class='text-danger delete mr-2'><span class='mdi mdi-delete'></span></a></td>";
                    html += "</tr>";
                }else{
                    html += "<tr>";
                        html += "<td>"+data.nik+"</td>";
                        html += "<td>"+data.nama_karyawan+"</td>";
                        html += "<td>"+tanggal_shift+"</td>";
                        html += "<td style='white-space: nowrap'>"+(data.jam_masuk_shift ?? '<div class="badge badge-outline-success">Libur</div>')+"</td>";
                        html += "<td style='white-space: nowrap'>"+(data.jam_keluar_shift ?? '<div class="badge badge-outline-success">Libur</div>')+"</td>";
                        html += "<td style='white-space: nowrap'>"+(waktu_masuk ?? '')+"</td>";
                        html += "<td style='white-space: nowrap'>"+(waktu_keluar ?? '')+"</td>";
                        if(data.imported==1){
                            html += "<td>Imported</td>";
                        }else{
                            html += "<td></td>";
                        }
                        html += "<td><a href='javascript:;' link='{{url('/absen/edit-log-absensi?id=')}}"+data.id+"' class='text-warning edit mr-2'><span class='mdi mdi-pen'></span></a><a href='javascript:;' link='{{url('/absen/delete-log-absensi?id=')}}"+data.id+"' class='text-danger delete mr-2'><span class='mdi mdi-delete'></span></a></td>";
                    html += "</tr>";
                }
              }else{
                if (data.id_tipe_absensi == '3') {
                  html += "<tr class=''>";
                      html += "<td>"+data.nik+"</td>";
                      html += "<td>"+data.nama_karyawan+"</td>";
                      html += "<td>"+tanggal_shift+"</td>";
                      html += "<td style='white-space: nowrap'><div class='badge badge-outline-success'>"+data.nama_tipe_absensi+"</div></td>";
                      html += "<td style='white-space: nowrap'><div class='badge badge-outline-success'>"+data.nama_tipe_absensi+"</div></td>";
                      html += "<td style='white-space: nowrap'><div class='badge badge-outline-success'>"+data.nama_tipe_absensi+"</div></td>";
                      html += "<td style='white-space: nowrap'><div class='badge badge-outline-success'>"+data.nama_tipe_absensi+"</div></td>";
                      html += "<td style='white-space: nowrap'><div class='badge badge-outline-success'>"+data.nama_tipe_absensi+"</div></td>";
                      html += "<td></td>";
                  html += "</tr>";
                }else{
                  html += "<tr class=''>";
                      html += "<td>"+data.nik+"</td>";
                      html += "<td>"+data.nama_karyawan+"</td>";
                      html += "<td>"+tanggal_shift+"</td>";
                      html += "<td style='white-space: nowrap'><div class='badge badge-outline-primary'>"+data.nama_tipe_absensi+"</div></td>";
                      html += "<td style='white-space: nowrap'><div class='badge badge-outline-primary'>"+data.nama_tipe_absensi+"</div></td>";
                      html += "<td style='white-space: nowrap'><div class='badge badge-outline-primary'>"+data.nama_tipe_absensi+"</div></td>";
                      html += "<td style='white-space: nowrap'><div class='badge badge-outline-primary'>"+data.nama_tipe_absensi+"</div></td>";
                      html += "<td style='white-space: nowrap'><div class='badge badge-outline-primary'>"+data.nama_tipe_absensi+"</div></td>";
                      html += "<td></td>";
                  html += "</tr>";
                }
              }
            }else{
              if (data.id != 0) {   
                if (data.waktu_masuk == null || data.waktu_keluar == null || data.waktu_masuk > data.waktu_keluar || (data.waktu_masuk < data.waktu_keluar && interval < "01:00:00" )) {
                    btnImport = false;
                    html += "<tr class='table-danger'>";
                        html += "<td>"+data.nik+"</td>";
                        html += "<td>"+data.nama_karyawan+"</td>";
                        html += "<td>"+tanggal_shift+"</td>";
                        html += "<td style='white-space: nowrap'>"+(data.jam_masuk_shift ?? '<div class="badge badge-outline-success">Libur</div>')+"</td>";
                        html += "<td style='white-space: nowrap'>"+(data.jam_keluar_shift ?? '<div class="badge badge-outline-success">Libur</div>')+"</td>";
                        html += "<td style='white-space: nowrap'>"+(waktu_masuk ?? '')+"</td>";
                        html += "<td style='white-space: nowrap'>"+(waktu_keluar ?? '')+"</td>";
                        html += "<td></td>";
                        html += "<td><a href='javascript:;' link='{{url('/absen/edit-log-absensi?id=')}}"+data.id+"' class='text-warning edit mr-2'><span class='mdi mdi-pen'></span></a><a href='javascript:;' link='{{url('/absen/delete-log-absensi?id=')}}"+data.id+"' class='text-danger delete mr-2'><span class='mdi mdi-delete'></span></a></td>";
                    html += "</tr>";
                }else{
                    return;
                }
              }else{
                return;
              }
            }
        });
            html += '</tbody>';
            html += '</table>';
            // console.log(html);
            $("#target-table").html(html);
            $("#table-priview").DataTable().destroy();
            $("#table-priview").DataTable();

            $("#table-priview").on("click", "a.edit", function(){
               let url = $(this).attr('link');
               $.ajax({
                   url: url,
                   type: "GET",
                   success: function(res){
                        let url = "{{url('/absen/update-log-absensi')}}"
                       $('input[name="id"]').val(res.data.id);
                       $('input[name="edit_tanggal_shift"]').val(res.data.tanggal_shift);
                       $('input[name="edit_waktu_masuk"]').val(res.data.waktu_masuk);
                       $('input[name="edit_waktu_keluar"]').val(res.data.waktu_keluar);
                       $('select[name="edit_id_shift"]').select2().val(res.data.id_shift).trigger('change');
                       $('select[name="edit_id_karyawan"]').select2().val(res.data.id_karyawan).trigger('change');
                       $('#formUpdate').attr('action',url);
                       $('#formEdit').modal('show');
                   }
               });
            });
            $("#table-priview").on("click", "a.delete", function(){
               let url = $(this).attr('link');
               _JSON = [];
               generateHtml();
               $(".loading").css('display','block');
               $.ajax({
                   url: url,
                   type: "GET",
                   success: function(res){
                        let start_date = $('input[name="start_date"]').val();
                        let end_date = $('input[name="end_date"]').val();
                        $.ajax({
                            url: "{{url('absen/get-log-absensi')}}",
                            type: "GET",
                            data : {
                                start_date : start_date,
                                end_date : end_date
                            },
                            success: function(res){
                                showForm();
                                res.data.forEach(function(dataExcel){
                                    pushJson(dataExcel);
                                });
                                
                                cekJamKosong();
                                generateHtml();

                                if(btnImport){
                                    $("#save-import").removeAttr('disabled');
                                }else{
                                    $("#save-import").attr('disabled','disabled');
                                }
                                $('#file').removeClass('border-danger');
                            }
                        });
                   }
               });
            });

        }
    });
</script>
<script type="text/javascript">
    $('#tambah_data').click(function(e){
        $('#formTambah').modal('show');
    });

    $('#load').click(function(e){
        $('#filterTanggal').modal('show');
    });
    
    $('input[name="waktu_masuk"]').on('change',function(e){
        let time = $('input[name="waktu_masuk"]').val();
        time = moment(time, "DD/MM/YYYY HH:mm").format("DD/MM/YYYY HH:mm");
        if (time == 'Invalid date') {
            $('#btn-save').attr('disabled',true);
        }else{
            $('input[name="waktu_masuk"]').val(time);
            $('#btn-save').attr('disabled',false);
        }
    });
    $('input[name="waktu_keluar"]').on('change',function(e){
        let time = $('input[name="waktu_keluar"]').val();
        time = moment(time, "DD/MM/YYYY HH:mm").format("DD/MM/YYYY HH:mm");
        if (time == 'Invalid date') {
            $('#btn-save').attr('disabled',true);
        }else{
            $('input[name="waktu_keluar"]').val(time);
            $('#btn-save').attr('disabled',false);
        }
    });
</script>
@endpush