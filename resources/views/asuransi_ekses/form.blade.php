@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Tambah Asuransi Ekses</h4><br>
                <div class="row mb-4">
                    <div class="col-md-2" style="margin-top:1.3%;">
                        Karyawan
                    </div>
                    <div class="col-md-4">
                        <select class="form-control js-example-basic-single" name="id_karyawan"
                                style="width:100%" data-maximum-selection-length="10">
                            <option value="">Cari Karyawan</option>
                            @foreach($m_karyawan as $data)
                                <option value="{{$data->id_karyawan}}">{{$data->nama_karyawan}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2" style="margin-top:1.3%;">
                        Gaji Pokok
                    </div>
                    <div class="col-md-4">
                        <input class="form-control numeric" name="gaji_pokok" readonly>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-2" style="margin-top:1.3%;">
                        Limit Asuransi
                    </div>
                    <div class="col-md-4">
                        <input class="form-control numeric" name="limit_asuransi" readonly>
                    </div>
                    <div class="col-md-2" style="margin-top:1.3%;">
                        30% Gaji Pokok
                    </div>
                    <div class="col-md-4">
                        <input class="form-control numeric" name="gaji_30" readonly>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-2" style="margin-top:1.3%;">
                        Biaya Rumah Sakit
                    </div>
                    <div class="col-md-4">
                        <input class="form-control numeric" name="biaya_rs">
                    </div>
                    <div class="col-md-2" style="margin-top:1.3%;">
                        Periode Aktif
                    </div>
                    <div class="col-md-4">
                        <input class="form-control" name="periode_aktif" readonly>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-2" style="margin-top:1.3%;">
                        Hutang Karyawan
                    </div>
                    <div class="col-md-4">
                        <input class="form-control numeric" name="hutang_karyawan" readonly>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4 style="margin-top:5%;">Potongan Gaji Karyawan Per Periode</h4>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-warning" style="margin-top:18%;" id="btn_generate_cicilan" disabled>Generate Otomatis</button>
                        <button class="btn btn-info" style="margin-top:18%;" id="btn_tambah_cicilan" disabled>Tambah</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="tabel_cicilan" class="table table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Potongan Gaji</th>
                                        <th>Periode</th>
                                        <th>Sudah Bayar</th>
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mb-4" style="margin-top:3.5%;">
                    <div class="col-md-3" style="margin-top:0%;">
                        Total Potongan Gaji
                    </div>
                    <div class="col-md-4" name="total_potongan_gaji">
                        0
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-3" style="margin-top:0%;">
                        Hutang Karyawan
                    </div>
                    <div class="col-md-4" name="hutang_karyawan">
                        0
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-3" style="margin-top:0%;">
                        Sisa
                    </div>
                    <div class="col-md-4" name="sisa">
                        0
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-12 text-right">
                        <button id="btn_submit" class="btn btn-success" disabled>Submit</button>
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
<script src="{{asset('/')}}assets/js/select2.js"></script>

<script>
    $(document).ready(function () {
        // read_data();
        $('.js-example-basic-single').select2({
            placeholder: "Cari Karyawan",
            // maximumSelectionLength: 10
        });
        let id_karyawan = $("select[name=id_karyawan]").val();
        let biaya_rs = $("input[name=biaya_rs]").val() ?? 0;
        getDataKaryawan(id_karyawan,biaya_rs);
    });

    $("select[name=id_karyawan],input[name=biaya_rs]").on('change',function(e){
        let id_karyawan = $("select[name=id_karyawan]").val();
        let biaya_rs = $("input[name=biaya_rs]").val() ?? 0;
        getDataKaryawan(id_karyawan,biaya_rs);
    });
    $("#btn_generate_cicilan").click(function(e){
        let id_karyawan = $("select[name=id_karyawan]").val();
        let biaya_rs = $("input[name=biaya_rs]").val() ?? 0;
        getDataKaryawan(id_karyawan,biaya_rs,is_generate=1);
    });

    var num = 0;
    var total_potongan = 0;
    var sisa = 0;
    var index_element = 0;
    var index_bulan = parseInt('{{$bulan_aktif}}');
    var array_cicilan = [];
    var bulan_aktif = parseInt('{{$bulan_aktif}}');
    var tahun_aktif = parseInt('{{$tahun_aktif}}');
    var array_bulan = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $("#btn_tambah_cicilan").click(function(e){

        // let element = $('#tabel_cicilan').find('tbody').find('tr').eq(index_element);
        let no = num+1;
        let potongan_gaji = 0;
        let bulan = index_bulan;
        let tahun = tahun_aktif;
        let sudah_bayar = 'Belum';
        if (bulan > 12) {
            index_bulan = 1;
            bulan = index_bulan;
            tahun_aktif = tahun_aktif+1;
            tahun = tahun_aktif;
        }
        obj_cicilan = {
                'no' : no,
                'potongan_gaji' : potongan_gaji,
                'bulan' : bulan,
                'tahun' : tahun,
                'sudah_bayar' : sudah_bayar,
            };
        array_cicilan.push(obj_cicilan);
        console.log(array_cicilan)

        $('#tabel_cicilan').find('tbody').empty();
        $.each(array_cicilan, function(index, value) {
            $('#tabel_cicilan').find('tbody').append('<tr><td name="no">'+value.no+'</td><td name="potongan_gaji"><input class="generate-cicilan form-control numeric" style="width:150px;height:5%;" name="cicilan[]" value="'+value.potongan_gaji+'"></td><td name="periode">'+array_bulan[value.bulan-1]+' '+value.tahun+'</td><td name="sudah_bayar">'+value.sudah_bayar+'</td><td><a class="hapus" href="javascript:;">'+'Hapus'+'</a></td></tr>');
        });

        $(".numeric").autoNumeric('init', {
            aPad: false,
            aDec: ',',
            aSep: '.'
        });


        num++;
        index_bulan++;
        
    });
    
    $('#tabel_cicilan').find('tbody').on('click', 'a.hapus', function(events){
        events.preventDefault();
        let idx = $(this).closest('tr').index();
        $(this).parent().parent().remove();
        array_cicilan.splice(idx,1);
        // console.log(array_cicilan)
        reset_cicilan(idx);
    });

    function reset_cicilan(idx){
        // num = 0;
        total_potongan = 0;
        sisa = 0;
        console.log(index_bulan, tahun_aktif)

        for (var i = idx; i < array_cicilan.length; i++) {
            if ((array_cicilan[i].bulan-1) < 1) {
                array_cicilan[i].bulan = 13;
                array_cicilan[i].tahun = array_cicilan[i].tahun-1;
                tahun_aktif = tahun_aktif-1;
            }
            array_cicilan[i].no = array_cicilan[i].no-1;
            array_cicilan[i].tahun = tahun_aktif;
            array_cicilan[i].bulan = array_cicilan[i].bulan-1;
            array_cicilan[i].potongan_gaji = array_cicilan[i].potongan_gaji;
            array_cicilan[i].sudah_bayar = array_cicilan[i].sudah_bayar;
            index_bulan = array_cicilan[i].bulan+1;
        };

        $('#tabel_cicilan').find('tbody').empty();
        $.each(array_cicilan, function(index, value) {
            $('#tabel_cicilan').find('tbody').append('<tr><td name="no">'+value.no+'</td><td name="potongan_gaji"><input class="generate-cicilan form-control numeric" style="width:150px;height:5%;" name="cicilan[]" value="'+value.potongan_gaji+'"></td><td name="periode">'+array_bulan[value.bulan-1]+' '+value.tahun+'</td><td name="sudah_bayar">'+value.sudah_bayar+'</td><td><a class="hapus" href="javascript:;">'+'Hapus'+'</a></td></tr>');
        });
        // index_bulan--;
        console.log(array_cicilan)

        hitung_total_potongan(array_cicilan);
    }

    $("#btn_submit").click(function(e){
        e.preventDefault();

        let id_karyawan = $("select[name=id_karyawan]").val();
        let limit_asuransi = $("input[name=limit_asuransi]").val() ?? 0;
        let biaya_rs = $("input[name=biaya_rs]").val() ?? 0;
        let hutang = $("div[name=hutang_karyawan]").text();
        let hutang_bayar = 0;
        let sisa_hutang = $("div[name=sisa]").text();

        $.ajax({
            type: "POST",
            url: "{{route('submit-asuransi')}}",
            data: {
                id_karyawan:id_karyawan,
                limit_asuransi:numberFormat(limit_asuransi),
                biaya_rs:numberFormat(biaya_rs),
                hutang:numberFormat(hutang),

                data_cicilan:array_cicilan,
            },
            dataType: "JSON",
            success: function (response) {
                window.location = "{{route('asuransi-ekses-index')}}";
            },
            error: function(error){
            }
        });
    });

    $('#tabel_cicilan').find('tbody').on('keyup','input',function(e){
        let idx = $(this).closest('tr').index();
        let value = $(this).closest('tr').find('td').find('input').val();
        array_cicilan[idx].potongan_gaji = parseFloat(numberFormat(value));
        // hitung_ulang_cicilan();
        hitung_total_potongan(array_cicilan);
    });
    
    function cek_enable_submit(){
        if (numberFormat($("div[name=total_potongan_gaji]").text()) == numberFormat($("div[name=hutang_karyawan]").text()) && numberFormat($("div[name=sisa]").text()) == 0) {
            $('#btn_submit').removeAttr('disabled');
        }else{
            $('#btn_submit').attr('disabled',true);
        }
    }

    function hitung_total_potongan(array_data){
        total_potongan = 0;
        $.each(array_data, function(index, value) {
            total_potongan += value.potongan_gaji;
        });
        $("div[name=total_potongan_gaji]").text(numericFormat(total_potongan));
        let sisa = total_potongan - numberFormat($("div[name=hutang_karyawan]").text());
        if (sisa > 0) {
            $("div[name=sisa]").text(numericFormat(sisa));
        }else{
            $("div[name=sisa]").text(numericFormat(0));
        }
        cek_enable_submit();
    }

    function getDataKaryawan(id_karyawan,biaya_rs,is_generate=0){
        // $("#msg").html("");
        // $("#save-import").attr('disabled','disabled');
        // $("#spinner").removeClass('d-none');
        // $("#text-btn-import").text('Sedang meng-upload data ...');
        $.ajax({
            type: "GET",
            url: "{{url('gaji_karyawan/get-detail-asuransi-ekses')}}",
            data: {
                id_karyawan:id_karyawan,
                biaya_rs:biaya_rs,
                is_generate:is_generate,
            },
            dataType: "JSON",
            success: function (response) {
                if (response) {
                    $("input[name=limit_asuransi]").val(numericFormat(response.limit_asuransi));
                    $("input[name=biaya_rs]").val(numericFormat(response.biaya_rs));
                    $("input[name=gaji_pokok]").val(numericFormat(response.gaji_pokok ?? 0));
                    $("input[name=periode_aktif]").val(response.periode_aktif);
                    $("input[name=gaji_30]").val(numericFormat(response.gaji_30));
                    $("input[name=hutang_karyawan]").val(numericFormat(response.hutang_karyawan));
                    $("div[name=hutang_karyawan]").text(numericFormat(response.hutang_karyawan));
                    $("div[name=total_potongan_gaji]").text(numericFormat(response.total_potongan_gaji));

                    $('#tabel_cicilan').find('tbody').empty();
                    index_element = 0;
                    index_bulan = bulan_aktif;
                    if (response.data_cicilan.length > 0) {
                        array_cicilan=[];
                        num=0;
                        $.each(response.data_cicilan, function(index, value) {
                            if (value.periode_bulan > 12) {
                                value.periode_bulan = 1;
                                value.periode_tahun = value.periode_tahun+1;
                            }
                            $('#tabel_cicilan').find('tbody').append('<tr><td name="no">'+value.no+'</td><td name="potongan_gaji"><input class="generate-cicilan form-control numeric" style="width:150px;height:5%;" name="cicilan[]" value="'+value.potongan_gaji+'"></td><td name="periode">'+array_bulan[value.periode_bulan-1]+' '+value.periode_tahun+'</td><td name="sudah_bayar">'+value.sudah_bayar+'</td><td><a class="hapus" href="javascript:;">'+'Hapus'+'</a></td></tr>');

                            obj_cicilan = {
                                    'no' : value.no,
                                    'potongan_gaji' : value.potongan_gaji,
                                    'bulan' : value.periode_bulan,
                                    'tahun' : value.periode_tahun,
                                    'sudah_bayar' : value.sudah_bayar,
                                };
                            array_cicilan.push(obj_cicilan);

                            num++;
                            index_element++;
                            index_bulan++;

                        });
                    }
                    console.log(array_cicilan)
                    $(".numeric").autoNumeric('init', {
                        aPad: false,
                        aDec: ',',
                        aSep: '.'
                    });

                    hitung_total_potongan(array_cicilan);
                    cek_enable_submit();
                    // index_element++;
                    // console.log(index_element)
                }else if(response == 0){
                    $("input[name=limit_asuransi]").val('');
                    $("input[name=biaya_rs]").val('');
                    $("input[name=gaji_pokok]").val('');
                    $("input[name=periode_aktif]").val('');
                    $("input[name=gaji_30]").val('');
                    $("input[name=hutang_karyawan]").val('');
                    $("div[name=hutang_karyawan]").text('0');
                    $("div[name=total_potongan_gaji]").text('0');
                    cek_enable_submit();
                }
                $('#btn_generate_cicilan').removeAttr('disabled');
                $('#btn_tambah_cicilan').removeAttr('disabled');
            },
            error: function(error){
                // $("#spinner").addClass('d-none');
            }
        });
    }
</script>
@endpush