@extends('template')
@section('content')
<?php 
use App\Traits\Helper;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Data Absensi Karyawan</h4><br>
                <!-- <form action="{{url('absensi-karyawan/view-filter')}}" method="post"> -->
                    <div class="row">
                            <!-- @csrf -->
                            <div class="form-group col-md-5">
                                <label for="">Tanggal</label>
                                <input class="form-control" type="text" name="tanggal" id="tanggal" value="{{$tanggal}}">
                            </div>
                            <!-- <div class="form-group col-md-5">
                                <label for="">Tanggal Akhir</label>
                                <input class="form-control pickerdate" type="text" name="akhir" id="akhir" value="{{$akhir}}">
                            </div> -->
                            <div class="form-group col-md-2">
                                <label for=""></label>
                                <input type="submit" class="btn btn-success form-control savedata" value="Cari" />                    
                            </div>
                        </div>
                    <!-- </form>    -->
                <div class="row mb-4">
                    <div class="col text-right">
                        @if(Helper::can_akses('riwayat_absensi_karyawan_export'))
                            <a href="{{url('absensi-karyawan/export/'.date('m-d-Y', strtotime($awal)).'/'.date('m-d-Y', strtotime($akhir)))}}" class="btn btn-info" target="_blank">Export</a>
                        @endif
                    </div>
                </div>                 
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table w-100">
                                <thead>
                                    <tr>

                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-1">
                        <div style='width:25px; height:25px; background-color: yellow;'></div>
                    </div>
                    <div class="col-11">
                        <p>Terlambat 1 sampai 4 menit</p>
                    </div>
                    <div class="col-12">
                        <p></p>
                    </div>
                    <div class="col-1">
                        <div style='width:25px; height:25px; background-color: orange;'></div>
                    </div>
                    <div class="col-11">
                        <p>Terlambat 5 sampai 29 menit</p>
                    </div>
                    <div class="col-12">
                        <p></p>
                    </div>
                    <div class="col-1">
                        <div style='width:25px; height:25px; background-color: red;'></div>
                    </div>
                    <div class="col-11">
                        <p>Terlambat diatas 30 menit</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->

    <!-- content-wrapper ends -->
    <!-- partial:partials/_footer.html -->
    @include("partial.footer")
    <!-- partial -->
</div>
@endsection
@push('js')
<script src="{{asset('/')}}assets/js/select2.js"></script>
<script src="{{ asset('/') }}assets/vendors/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function () {
        var html_body_tabel2='';
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function isitable(tanggal,id_karyawan) {                        
        $.get('{{ url("absensi-karyawan/get-absensi") }}/'+tanggal+'/'+id_karyawan, function (result) {                                            
                    if (result) {
                        if (result.id_tipe_absensi == 1) {
                            $masuk=result.tanggal_masuk;
                            $keluar=result.tanggal_keluar;
                            if (result.menit_terlambat >= 1 && result.menit_terlambat <= 4) {
                                html_body_tabel2 += "<td><p style='white-space:nowrap;'>Masuk : "+$masuk+"<br> Keluar : "+$keluar+" <br> Terlambat : <span style='color: #dede07;'>"+result.menit_terlambat+" Menit</span><br> Early Leave : "+result.menit_early_leave+"</p></td>";
                            }else if (result.menit_terlambat >= 5 && result.menit_terlambat <= 29) {
                                html_body_tabel2 += "<td><p style='white-space:nowrap;'>Masuk : "+$masuk+"<br> Keluar : "+$keluar+" <br> Terlambat : <span style='color: orange;'>"+result.menit_terlambat+" Menit</span><br> Early Leave : "+result.menit_early_leave+"</p></td>";
                            }else if(result.menit_terlambat >= 30){
                                html_body_tabel2 += "<td><p style='white-space:nowrap;'>Masuk : "+$masuk+"<br> Keluar : "+$keluar+" <br> Terlambat : <span style='color: red;'>"+result.menit_terlambat+" Menit</span><br> Early Leave : "+result.menit_early_leave+"</p></td>";
                            }else{
                                html_body_tabel2 += "<td><p style='white-space:nowrap;'>Masuk : "+$masuk+"<br> Keluar : "+$keluar+" <br> Terlambat : "+result.menit_terlambat+" Menit<br> Early Leave : "+result.menit_early_leave+"</p></td>";
                            }
                        }else if (result.id_tipe_absensi == 3) {
                            html_body_tabel2 += "<td><div class='badge badge-outline-success badge-pill pointer'>"+result.nama_tipe_absensi+"</div></td>";
                        }else{
                            html_body_tabel2 += "<td><div class='badge badge-outline-primary badge-pill pointer'>"+result.nama_tipe_absensi+"</div></td>";
                        }
                    }else {
                        html_body_tabel2 += "<td><div class='badge badge-outline-danger badge-pill pointer'>-</div>";
                    }                    
            })            
        }

        function setTableShiftHeader()
        {
            html_tabel_header = '';

            html_tabel_header += '<th>No</th>';
            html_tabel_header += '<th>Nama Karyawan</th>';

            var filterMonthYear = $("#tanggal").val();
            var filterMonthYearSplit = filterMonthYear.split("-");
            var new_filterMonthYear = filterMonthYearSplit[1]+"-"+filterMonthYearSplit[0];

            var a = moment(filterMonthYearSplit[0]);
            var b = moment(filterMonthYearSplit[1]).add(1, 'days');

            // If you want an exclusive end date (half-open interval)
            for (var m = moment(a); m.isBefore(b); m.add(1, 'days')) {

                html_tabel_header += '<th>'+m.format('DD-MMMM-YYYY')+'</th>';
            }

            $(".table thead tr").html(html_tabel_header);

        }

        $('input[name="tanggal"]').daterangepicker({
              autoUpdateInput: false,
              locale: {
                  cancelLabel: 'Clear'
              },
              "maxSpan": {
                  "days": 31
              }
          });

          $('input[name="tanggal"]').on('apply.daterangepicker', function(ev, picker) {
              $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
          });

          $('input[name="tanggal"]').on('cancel.daterangepicker', function(ev, picker) {
              $(this).val('');
          });

          $(".savedata").on('click', function() {            
            var date_range = $('#tanggal').val();            
            window.location = "{{ url('absensi-karyawan/view-filter') }}?tanggal="+date_range;
        });

        read_data();

        function read_data() {
            $.ajax({
                url: '{{ url("absensi-karyawan/data") }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    start: $("#tanggal").val(),                    
                },           
            })
            .done(function(res) {
                console.log("success");                
                var data_karyawan = res.data.data_karyawan;
                var data_absensi_karyawan = res.data.data_absensi_karyawan;
                var filterMonthYear = $("#tanggal").val();
                var filterMonthYearSplit = filterMonthYear.split("-");
                var new_filterMonthYear = filterMonthYearSplit[1]+"-"+filterMonthYearSplit[0];

                var a = moment(filterMonthYearSplit[0]);
                var b = moment(filterMonthYearSplit[1]).add(1, 'days');

                html_body_tabel = '';
                for (var i = 0; i < data_karyawan.length; i++) {
                    no = i+1;
                    noabsen=0;
                    html_body_tabel2 = '';

                    for (var m = moment(a); m.isBefore(b); m.add(1, 'days')) {

                        var search_absen = $.grep(data_absensi_karyawan, function(absen){
                            return absen.tanggal == m.format('YYYY-MM-DD') && absen.id_karyawan == data_karyawan[i].id_karyawan;
                        });

                        if (search_absen.length > 0) {
                            if (search_absen[0].id_tipe_absensi == 1) {
                                var masuk=search_absen[0].tanggal_masuk;
                                var keluar=search_absen[0].tanggal_keluar;
                                if (search_absen[0].menit_terlambat >= 1 && search_absen[0].menit_terlambat <= 4 ) {
                                    html_body_tabel2 += "<td><div class='d-flex justify-content-end' style='margin-top:-8px;margin-right:-9px;margin-bottom:5px'><span class='mark badge badge-info' style='font-size: 8px' id_karyawan='"+search_absen[0].id_karyawan+"' tanggal='"+search_absen[0].tanggal+"'>Mark</span></div>\
                                    <p style='white-space:nowrap;'>Masuk : "+masuk+"<br> Keluar : "+keluar+" <br> Terlambat : <span style='color: #dede07;'>"+search_absen[0].menit_terlambat+" Menit</span><br>";
                                }else if (search_absen[0].menit_terlambat >= 5 && search_absen[0].menit_terlambat <= 29) {
                                    html_body_tabel2 += "<td><div class='d-flex justify-content-end' style='margin-top:-8px;margin-right:-9px;margin-bottom:5px'><span class='mark badge badge-info' style='font-size: 8px' id_karyawan='"+search_absen[0].id_karyawan+"' tanggal='"+search_absen[0].tanggal+"'>Mark</span></div>\
                                    <p style='white-space:nowrap;'>Masuk : "+masuk+"<br> Keluar : "+keluar+" <br> Terlambat : <span style='color: orange;'>"+search_absen[0].menit_terlambat+" Menit</span><br>";
                                }else if(search_absen[0].menit_terlambat >= 30){
                                    html_body_tabel2 += "<td><div class='d-flex justify-content-end' style='margin-top:-8px;margin-right:-9px;margin-bottom:5px'><span class='mark badge badge-info' style='font-size: 8px' id_karyawan='"+search_absen[0].id_karyawan+"' tanggal='"+search_absen[0].tanggal+"'>Mark</span></div>\
                                    <p style='white-space:nowrap;'>Masuk : "+masuk+"<br> Keluar : "+keluar+" <br> Terlambat : <span style='color: red;'>"+search_absen[0].menit_terlambat+" Menit</span><br>";
                                }else{
                                    html_body_tabel2 += "<td><div class='d-flex justify-content-end' style='margin-top:-8px;margin-right:-9px;margin-bottom:5px'><span class='mark badge badge-info' style='font-size: 8px' id_karyawan='"+search_absen[0].id_karyawan+"' tanggal='"+search_absen[0].tanggal+"'>Mark</span></div>\
                                    <p style='white-space:nowrap;'>Masuk : "+masuk+"<br> Keluar : "+keluar+" <br> Terlambat : "+search_absen[0].menit_terlambat+" Menit<br>";
                                }
                                if (search_absen[0].menit_early_leave >= 1 && search_absen[0].menit_early_leave <= 4 ) {
                                    html_body_tabel2 += "Early Leave : <span style='color: #dede07;'>"+search_absen[0].menit_early_leave+" Menit</span></p></td>";
                                }else if (search_absen[0].menit_early_leave >= 5 && search_absen[0].menit_early_leave <= 29) {
                                    html_body_tabel2 += "Early Leave : <span style='color: orange;'>"+search_absen[0].menit_early_leave+" Menit</span></p></td>";
                                }else if(search_absen[0].menit_early_leave >= 30){
                                    html_body_tabel2 += "Early Leave : <span style='color: red;'>"+search_absen[0].menit_early_leave+" Menit</span></p></td>";
                                }else{
                                    html_body_tabel2 += "Early Leave : "+search_absen[0].menit_early_leave+" Menit</p></td>";
                                }
                            }else if (search_absen[0].id_tipe_absensi == 3) {
                                html_body_tabel2 += "<td height='81px'><div class='d-flex justify-content-end' style='margin-top:-38px;margin-right:-9px;margin-bottom:15px'><span class='badge badge-info mark' style='font-size: 8px' id_karyawan='"+search_absen[0].id_karyawan+"' tanggal='"+m.format('YYYY-MM-DD')+"'>Mark</span></div>\
                                <div class='d-flex justify-content-center'><span class='badge badge-outline-success badge-pill pointer'>"+search_absen[0].nama_tipe_absensi+"</span></div></td>";
                            }else{
                                html_body_tabel2 += "<td height='81px'>\
                                <div class='d-flex justify-content-center'><span class='badge badge-outline-primary badge-pill pointer'>"+search_absen[0].nama_tipe_absensi+"</span></div></td>";
                            }                            
                        } else {
                            html_body_tabel2 += "<td height='81px'>\
                                <div class='d-flex justify-content-center'><span class='badge badge-outline-danger badge-pill pointer'>-</span></div></td>";
                        }

                        noabsen++;
                    }

                    html_body_tabel += '<tr>\
                        <td>'+no+'</td>\
                        <td>'+data_karyawan[i].nama_karyawan+'</td>\
                        '+html_body_tabel2+'\
                    </tr>';


                }
                           

                setTableShiftHeader();

                $(".table tbody").html(html_body_tabel);

                $('.table').DataTable();
                
                $(".dataTables_filter input").attr("placeholder", "All Karyawan");


            })
            .fail(function() {
                console.log("error");
            })
            .always(function() {
                console.log("complete");
            });
        }

        // $('#awal,#akhir').on('change', function(e) {
        //     $('.table').DataTable().destroy();
        //     read_data();
        // });
    });

    $(".pickerdate").datepicker( {
        format: "dd-mm-yyyy",
    });

    $(document).on('click','.mark', function (e){            
        let id_karyawan = $(this).attr('id_karyawan');
        let tanggal = $(this).attr('tanggal');     
        $.toast({
            heading: 'Success',
            text: 'Attendance data added successfully.',
            showHideTransition: 'slide',
            icon: 'success',
            loaderBg: '#f96868',
            position: 'bottom-right'
        });  
        $.ajax({
            url: "{{url('marked/save')}}",
            type: "POST",
            dataType: 'json',
            data: {
                    id_karyawan:id_karyawan,
                    tanggal:tanggal
                },            
            success: function(data){
                
            }
        });
    })        
</script>
@endpush