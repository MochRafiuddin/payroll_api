@extends('template')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Data Mark</h4>
                <div class="row mb-4">                    
                    <div class="form-group col-md-2">                        
                        <select name="data_load" id="data_load" class="form-control js-example-single">
                            <option value="0" selected>Data Dari</option>
                            <option value="1">Ditandai</option>
                            <option value="2">Absensi</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">                        
                        <input class="form-control" type="text" name="tanggal" id="tanggal" value="">                            
                    </div>
                    <div class="form-group col-md-2">                        
                        <select class="form-control js-example-single" name="departemen_load" id="departemen_load" style="width:100%">
                                <option value="0">Departemen</option>
                            @foreach($departemen as $data)
                                <option value="{{$data->id_departemen}}">{{$data->nama_departemen}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">                        
                        <select class="form-control js-example-single" name="grup_load" id="grup_load" style="width:100%">
                                <option value="0">Group</option>
                            @foreach($group as $data1)
                                <option value="{{$data1->id_grup_karyawan}}">{{$data1->nama_grup}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">                        
                        <select class="form-control js-example-single" name="shift_load" id="shift_load" style="width:100%">
                                <option value="0">Shift</option>
                            @foreach($shift as $data2)
                                <option value="{{$data2->id_shift}}">{{$data2->nama_shift}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class=" col-md-2">                        
                        <div class="col">
                            <button class="btn btn-info load">Load</button>
                        </div>
                    </div>                    
                    <div class="col-12">
                        <div id="msg1">
                            
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table w-100" data-page-length='50'>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Tanggal</th>
                                        <th>Shift</th>
                                        <th>Jam Masuk Shift</th>
                                        <th>Jam Keluar Shift</th>
                                        <th>Jam Masuk Absen</th>
                                        <th>Jam Keluar Absen</th>                                        
                                        <th><input type="checkbox" name="ceklis" id="ceklis"></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div><br> 
                <!-- <div class="d-flex justify-content-center">
                    <div class="form-group col-md-5">                        
                        <input class="form-control" type="text" name="tanggal" id="tanggal" value="">
                    </div>
                    <div class="form-group col-md-1 mr-3">                        
                        <div class="col">
                            <button class="btn btn-info load">Load</button>
                        </div>
                    </div>
                    <div class="form-group col-md-2">                        
                        <div class="col">
                            <button class="btn btn-info hapus">Hapus Terpilih</button>
                        </div>
                    </div>
                    <div class="form-group col-md-2">                        
                        <div class="col">
                            <button class="btn btn-info tambah">Tambah Mark</button>
                        </div>
                    </div>
                    <div class="form-group col-md-2">                        
                        <div class="col">
                            <button class="btn btn-info proses">Proses Mark</button>
                        </div>
                    </div>
                </div><br> -->
                <div class="d-flex justify-content-center">
                    <button class="btn btn-info proses mr-3">Proses Mark</button>
                    <button type="button" id="save-permanen" class="btn btn-warning">
                        <div class="d-flex align-items-center">
                            <div id="spinner" class="spinner d-none" style="width: 20px; height:20px;"></div>
                            <span id="text-btn-import" class="ml-2">Simpan Permanen</span>
                        </div>
                    </button>
                </div><br>
            </div>
        </div>
    </div>    
    @include("partial.footer")    
</div>

<div class="modal fade" id="formtambah" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel-3" aria-hidden="true">
    <div class="modal-dialog modal-md" style="margin-top:1%;" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel-3">Tambah Mark</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control" name="id" hidden>
          <label for="waktu_masuk" style="margin-top:2%;"><small>Range Tanggal</small></label>          
          <input type="text" class="form-control" name="add_tanggal" id="add_tanggal" required>
          <label for="id_karyawan" style="margin-top:2%;"><small>Departemen</small></label>
          <select class="form-control js-example-single" name="add_departemen" id="add_departemen" style="width:100%">
                  <option value="0">-- Pilih Departemen --</option>
              @foreach($departemen as $data)
                  <option value="{{$data->id_departemen}}">{{$data->nama_departemen}}</option>
              @endforeach
          </select>          
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="btn-add" style="color:white">Tambah</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
        </div>
      </div>
    </div>    
</div>
<div class="modal fade" id="formproses" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel-3" aria-hidden="true">
    <div class="modal-dialog modal-md" style="margin-top:1%;" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel-3">Jika tidak ada Perubahan, maka biarkan kosong 
              <!-- <span class="mdi mdi-information-outline pop" data-toggle="popover" title="Informasi" data-content="Untuk ubah jam masukan 07:45:00. Untuk Mengurangi jam dalam menit. Untuk menambah jam dalam menit" data-container=".modal-header"></span>               -->
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class='row'>
            <div class='col-12 mb-3'>
                <select class="form-control" name="p_shift" id="p_shift" style="width:100%">
                        <option value="0">-- Pilih Shift --</option>
                    @foreach($shift as $data)
                        <option value="{{$data->id_shift}}">{{$data->nama_shift}}</option>
                    @endforeach
                </select>          
            </div>
            <div class='col-2 mb-3'>
                <select class="form-control" name="p_o_shift_masuk" id="p_o_shift_masuk" style="width:100%">
                    <option value="0">=</option>
                    <option value="1">+</option>
                    <option value="2">-</option>
                </select>          
            </div>
            <div class='col-10 mb-3'>
                <input type="text" name="p_v_shift_masuk" id="p_v_shift_masuk" class='form-control' placeholder='Jam Masuk Shift'>
            </div>
            <div class='col-2 mb-3'>
                <select class="form-control" name="p_o_shift_keluar" id="p_o_shift_keluar" style="width:100%">
                    <option value="0">=</option>
                    <option value="1">+</option>
                    <option value="2">-</option>
                </select>          
            </div>
            <div class='col-10 mb-3'>
                <input type="text" name="p_v_shift_keluar" id="p_v_shift_keluar" class='form-control' placeholder='Jam Keluar Shift'>
            </div>
            <div class='col-2 mb-3'>
                <select class="form-control" name="p_o_absen_masuk" id="p_o_absen_masuk" style="width:100%">
                    <option value="0">=</option>
                    <option value="1">+</option>
                    <option value="2">-</option>
                </select>          
            </div>
            <div class='col-10 mb-3'>
                <input type="text" name="p_v_absen_masuk" id="p_v_absen_masuk" class='form-control' placeholder='Jam Masuk Absen'>
            </div>
            <div class='col-2 mb-3'>
                <select class="form-control" name="p_o_absen_keluar" id="p_o_absen_keluar" style="width:100%">
                    <option value="0">=</option>
                    <option value="1">+</option>
                    <option value="2">-</option>
                </select>          
            </div>
            <div class='col-10 mb-3'>
                <input type="text" name="p_v_absen_keluar" id="p_v_absen_keluar" class='form-control' placeholder='Jam Keluar Absen'>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="btn-proses" style="color:white">Proses</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
        </div>
      </div>
    </div>    
</div>
@endsection

@push('js')
<script src="{{asset('/')}}assets/js/select2.js"></script>
<script src="{{ asset('/') }}assets/vendors/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function () {                
        $(".table").DataTable(); 
        $('#departemen_load').hide();
        $('#grup_load').hide();
        $('#shift_load').hide();       
    });

    $('.tambah').click(function(e){
        $('#formtambah').modal('show');
    });

    $('#data_load').on('change', function() {        
        if (this.value == 2) {            
            $('#departemen_load').show();
            $('#grup_load').show();
            $('#shift_load').show();
        }else{
            $('#departemen_load').hide();
            $('#grup_load').hide();
            $('#shift_load').hide();
            $('#departemen_load').val('0');
            $('#grup_load').val('0');
            $('#shift_load').val('0');
        }
    });        

    $('.proses').click(function(e){
        $('#formproses').modal('show');
        const box = [];
        var checkboxes = $('.table').DataTable().$('input[type=checkbox]:checked');
        checkboxes.each(function(index,elem){
            var checkbox_value = $(elem).val();
            box.push(checkbox_value);
        });
        // console.log(box.length);
        if (box.length == 0) {
            $('#btn-proses').prop('disabled', true)
        }else{
            $('#btn-proses').prop('disabled', false)
        }
    });

    $(".load").click(function () {
        $(".table").DataTable().destroy();
        read_data();
    });

    $("#save-permanen").click(function () {
        const box = [];
        var checkboxes = $('.table').DataTable().$('input[type=checkbox]:checked');
        checkboxes.each(function(index,elem){
            var checkbox_value = $(elem).val();
            box.push(checkbox_value);
        });
        if (box.length != 0) {
            $("#save-permanen").attr('disabled','disabled');
            $("#spinner").removeClass('d-none');
            $("#text-btn-import").text('Sedang meng-upload data ...');
            $.ajax({
                type: "POST",
                url: "{{url('absen/save-import')}}",
                data: {json_import:box},
                dataType: "JSON",
                success: function (response) {
                    if(response.status){
                        $("#spinner").addClass('d-none');
                        $("#text-btn-import").text('Simpan Permanen');
                        $("#save-permanen").removeAttr('disabled');
                        
                        // $("#msg1").html("<div class='alert alert-success'> " +response.msg+"</div>");
                        $("#msg1").html("<div class='alert alert-success alert-block'>\
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>"+response.msg+"</div>");
                    }
                    $(".table").DataTable().destroy();
                    read_data();
                }
            });
        }else{
            $("#msg1").html("<div class='alert alert-danger alert-block'>\
            <button type='button' class='close' data-dismiss='alert'>&times;</button>Pilih Karyawan Terlebih Dahulu</div>");
        }
    });

    $('input[name="tanggal"]').daterangepicker({
        autoApply: true,
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        },            
    });
    $('input[name="tanggal"]').attr("placeholder","Range Tanggal");
    $('input[name="tanggal"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });
    $('input[name="tanggal"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    $('input[name="add_tanggal"]').daterangepicker({
        autoApply: true,
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        },            
    });    
    $('input[name="add_tanggal"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });
    $('input[name="add_tanggal"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    function read_data() {            
        $('.table').DataTable({
            processing: true,
            serverSide: true,
            "scrollX": true,
            pageLength: 50,
            language: {
                searchPlaceholder: " "
            },
            ajax: {
                url: '{{ url("marked/data") }}',
                type: 'GET',
                data:{
                    data : $("#data_load").val(),
                    tanggal : $("#tanggal").val(),
                    departemen : $("#departemen_load").val(),
                    grup : $("#grup_load").val(),
                    shift : $("#shift_load").val(),
                }
                
            },
            rowReorder: {
                selector: 'td:nth-child(1)'
            },
            "bStateSave": true,
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
                    data: 'tanggal_shift',
                    name: 'tanggal_shift',                        
                },
                {
                    data: 'nama_shift',
                    name: 'm_shift.nama_shift',                        
                },
                {
                    data: 'jam_masuk_shift',
                    name: 'jam_masuk_shift',
                },
                {
                    data: 'jam_keluar_shift',
                    name: 'jam_keluar_shift',
                },
                {
                    data: 'waktu_masuk',
                    name: 'waktu_masuk',
                },
                {
                    data: 'waktu_keluar',
                    name: 'waktu_keluar',
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
    $("#btn-proses").on('click', function() {                        
        var oTable = $('.table').dataTable();
        var rowcollection =  $('.table').DataTable().rows().data();
        const box = [];
        var checkboxes = $('.table').DataTable().$('input[type=checkbox]:checked');
        checkboxes.each(function(index,elem){
            var checkbox_value = $(elem).val();
            box.push(checkbox_value);
        });                    
        var shift = $("#p_shift").val();
        var op_shift_masuk = $("#p_o_shift_masuk").val();
        var val_shift_masuk = $("#p_v_shift_masuk").val();
        var op_shift_keluar = $("#p_o_shift_keluar").val();
        var val_shift_keluar = $("#p_v_shift_keluar").val();
        var op_absen_masuk = $("#p_o_absen_masuk").val();
        var val_absen_masuk = $("#p_v_absen_masuk").val();
        var op_absen_keluar = $("#p_o_absen_keluar").val();
        var val_absen_keluar = $("#p_v_absen_keluar").val();
        $.ajax({
            url: "{{url('marked/save-update')}}",
            type: "POST",
            dataType: 'json',
            data: {
                shift : shift,
                op_shift_masuk : op_shift_masuk,
                val_shift_masuk : val_shift_masuk,
                op_shift_keluar : op_shift_keluar,
                val_shift_keluar : val_shift_keluar,
                op_absen_masuk : op_absen_masuk,
                val_absen_masuk : val_absen_masuk,
                op_absen_keluar : op_absen_keluar,
                val_absen_keluar : val_absen_keluar,
                box : box,
            },            
            success: function(data){
                $('#formproses').modal('hide');
                $("#p_shift").val('');
                $("#p_o_shift_masuk").val('0');
                $("#p_v_shift_masuk").val('');
                $("#p_o_shift_keluar").val('0');
                $("#p_v_shift_keluar").val('');
                $("#p_o_absen_masuk").val('0');
                $("#p_v_absen_masuk").val('');
                $("#p_o_absen_keluar").val('0');
                $("#p_v_absen_keluar").val('');
                $("#msg1").html("<div class='alert alert-success alert-block'>\
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>"+data.msg+"</div>");
                $(".table").DataTable().destroy();
                read_data()
            }
        });
        
    });        
    $("#ceklis").click(function () {
        var cells = $('.table').DataTable().column(8).nodes(), // Cells from 1st column
            state = this.checked;
        // console.log(cells.length);
        for (var i = 0; i < cells.length; i += 1) {
            cells[i].querySelector("input[type='checkbox']").checked = state;
        }
    });
    $("#btn-add").on('click', function() {        
        var tanggal = $("#add_tanggal").val();
        var departemen = $("#add_departemen").val();
        $.ajax({
            url: "{{url('marked/add-bulk')}}",
            type: "POST",
            dataType: 'json',
            data: {
                tanggal : tanggal,
                departemen : departemen
            },            
            success: function(data){
                $('#formtambah').modal('hide');
                $("#msg1").html("<div class='alert alert-success alert-block'>\
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>"+data.msg+"</div>");
                $(".table").DataTable().destroy();
                read_data()
                $("#add_tanggal").val('');
                $("#add_departemen").val('0');
            }
        });
        
    });
    $(".hapus").on('click', function() {                        
        var oTable = $('.table').dataTable();
        var rowcollection =  $('.table').DataTable().rows().data();
        const box = [];
        var checkboxes = $('.table').DataTable().$('input[type=checkbox]:checked');
        checkboxes.each(function(index,elem){
            var checkbox_value = $(elem).attr('id_mark')
            box.push(checkbox_value);
        });
        // console.log(box);                    
        $.ajax({
            url: "{{url('marked/delete-bulk')}}",
            type: "GET",
            dataType: 'json',
            data: {                
                box : box,
            },            
            success: function(data){
                $("#msg1").html("<div class='alert alert-success alert-block'>\
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>"+data.msg+"</div>");
                $(".table").DataTable().destroy();
                read_data()
            }
        });
        
    });
</script>
@endpush