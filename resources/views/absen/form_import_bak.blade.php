@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  

?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Import Absesi</h4><br>
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
                                    <button type="submit" id="priview" class="btn btn-danger" type="button">Priview</button>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
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
                    <li>Pastikan Waktu Masuk dan Waktu Keluar Karyawan, sudah terisi semua</li>
                    <li>Pastikan Shift Karyawan sudah di atur</li>
                </ul>
                <button type="button" id="save-import" class="btn btn-info " disabled>
                    <div class="d-flex align-items-center">
                        <div id="spinner" class="spinner d-none" style="width: 20px; height:20px;"></div>
                        <span id="text-btn-import" class="ml-2">Import Data Fingerprint</span>
                        
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    $(document).ready(function () {
        $("#save-import").attr('disabled','disabled');
        $("#table-priview").DataTable();
        cekFileUpload();
        var _JSON = [];
        var karyawan = <?= $karyawan ?>;
        var shift_karyawan = <?= $shift_karyawan ?>;
        var shift = <?= $shift ?>;
        var cek_karyawan = true;
        var btnImport = true;
  
        generateHtml();
        $("#form").on('submit',(function(e) {
            e.preventDefault();
            // _JSON = [];
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
                        // let index = 0;
                        res.data.forEach(function(dataExcel){
                            var datakaryawan = cariKaryawan(dataExcel.kode);
                            
                            var dataShift = getShiftKaryawan(datakaryawan.id_karyawan,dataExcel.tanggal_conv);
                            dataExcel.id_shift = dataShift.id_shift;
                            dataExcel.nik = datakaryawan.nik;

                            if(dataExcel.tanggal == "" || dataExcel.tanggal == null){
                                btnImport = false;
                            }

                        let index = _JSON.findIndex(
                            element => element.kode == dataExcel.kode && element.id_shift == dataShift.id_shift && element.tanggal_shift == dataExcel.tanggal_conv
                        );

                        if(_JSON.length == 0){ //jika data tidak ada di array tampung
                                pushJson(datakaryawan,dataExcel,dataShift);
                            console.log(dataExcel)
                        }else{
                            if (dataShift.kode_shift == 'malam' && dataExcel.status.toLowerCase() == "c/keluar") {
                                let date = new Date(dataExcel.tanggal_conv);
                                let past_date = formatDate(date.setDate(date.getDate() - 1));
                                dataExcel.tanggal_conv = past_date;
                                index = _JSON.findIndex(
                                            element => element.kode == dataExcel.kode && element.id_shift == dataShift.id_shift && element.tanggal_shift == dataExcel.tanggal_conv
                                        );
                            console.log(dataExcel)
                            }else{
                                index = _JSON.findIndex(
                                            element => element.kode == dataExcel.kode && element.id_shift == dataShift.id_shift && element.tanggal_shift == dataExcel.tanggal_conv
                                        );
                            console.log(dataExcel)
                            }

                            if (index >= 0) {
                                let status = dataExcel.status;
                                if(status.toLowerCase() == "c/masuk"){
                                    _JSON[index].waktu_masuk = dataExcel.tanggal;
                                }else if(status.toLowerCase() == "c/keluar"){
                                    _JSON[index].waktu_keluar = dataExcel.tanggal;
                                }
                            }else{
                                pushJson(datakaryawan,dataExcel,dataShift)
                            }
                        }

                        });
                        
                        generateHtml();
                        cekJamKosong();


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
            $("#msg").html("");
            $("#save-import").attr('disabled','disabled');
            $("#spinner").removeClass('d-none');
            $("#text-btn-import").text('Sedang meng-upload data ...');
            $.ajax({
                type: "POST",
                url: "{{url('absen/save-import')}}",
                data: {json_import:_JSON},
                dataType: "JSON",
                success: function (response) {
                    if(response.status){
                        $("#spinner").addClass('d-none');
                        $("#text-btn-import").text('Import Data Fingerprint');
                        $("#save-import").removeAttr('disabled');
                        
                        $("#msg").html("<div class='alert alert-success'> " +response.msg+"</div>");

                    }

                },
                error: function(error){
                    $("#spinner").addClass('d-none');
                    $("#text-btn-import").text('Import Data Fingerprint');
                    $("#save-import").removeAttr('disabled');
                }
            });
        }
        function pushJson(datakaryawan,dataExcel,dataShift){
            let status = dataExcel.status;
            var tanggalMasuk = "";
            var tanggalKeluar = "";
            if(status.toLowerCase() == "c/masuk"){
                tanggalMasuk = dataExcel.tanggal;
            }else if(status.toLowerCase() == "c/keluar"){
                tanggalKeluar = dataExcel.tanggal;
                
            }

            _JSON.push({
                id_karyawan:datakaryawan.id_karyawan,
                nama_karyawan:datakaryawan.nama_karyawan,
                nik:datakaryawan.nik,
                kode:datakaryawan.kode_fingerprint,
                id_shift:dataShift.id_shift,
                tanggal_shift:dataExcel.tanggal_conv,
                jam_masuk_shift:dataShift.jam_masuk,
                jam_keluar_shift:dataShift.jam_keluar,
                waktu_masuk:tanggalMasuk,
                waktu_keluar:tanggalKeluar
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
        function cekJamKosong(){
            _JSON.forEach(function(data){
                if(data.waktu_masuk == "" || data.waktu_keluar == ""){
                    btnImport = false;
                }else{
                    btnImport = true;
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
        function generateHtml(){
            var html = '<table class="table mb-3" id="table-priview">';
                html += '<thead>';
                html += '<tr>';
                html += '<th>NIK</th>';
                html += '<th>Nama Karyawan</th>';
                html += '<th>Tanggal Shift</th>';
                html += '<th>Jam Masuk Shift</th>';
                html += '<th>Jam Keluar Shift</th>';
                html += '<th>Waktu Masuk</th>';
                html += '<th>Waktu Keluar</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';
            // console.log(html);
            _JSON.forEach(function(data){
                html += "<tr>";
                    html += "<td>"+data.nik+"</td>";
                    html += "<td>"+data.nama_karyawan+"</td>";
                    html += "<td>"+data.tanggal_shift+"</td>";
                    html += "<td>"+data.jam_masuk_shift+"</td>";
                    html += "<td>"+data.jam_keluar_shift+"</td>";
                    html += "<td>"+data.waktu_masuk+"</td>";
                    html += "<td>"+data.waktu_keluar+"</td>";
                html += "</tr>";
            });
            html += '</tbody>';
            html += '</table>';
            // console.log(html);
            $("#target-table").html(html);
            $("#table-priview").DataTable().destroy();
            $("#table-priview").DataTable();

        }
    });
</script>
@endpush