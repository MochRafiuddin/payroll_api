@extends('template')
@php
    use Carbon\Carbon;
    $option_tanggal = [
        'today' => date('Y-m-d'),
        'today_slashed' => Carbon::createFromFormat('Y-m-d',date('Y-m-d'))->format('d/m/Y'),
        'subday1' => Carbon::createFromFormat('Y-m-d',date('Y-m-d'))->subDays(1)->format('Y-m-d'),
        'subday1_slashed' => Carbon::createFromFormat('Y-m-d',date('Y-m-d'))->subDays(1)->format('d/m/Y'),
        'subday2' => Carbon::createFromFormat('Y-m-d',date('Y-m-d'))->subDays(2)->format('Y-m-d'),
        'subday2_slashed' => Carbon::createFromFormat('Y-m-d',date('Y-m-d'))->subDays(2)->format('d/m/Y'),
    ];
@endphp
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-6 col-lg-3 grid-margin stretch-card">
                <div class="card bg-gradient-primary text-white text-center card-shadow-primary">
                    <div class="card-body">
                        <h6 class="font-weight-normal">@lang('umum.total_karyawan')</h6>
                        <h2 class="mb-0">{{$karyawan}}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 grid-margin stretch-card">
                <div class="card bg-gradient-danger text-white text-center card-shadow-danger">
                    <div class="card-body">
                        <h6 class="font-weight-normal">@lang('umum.total_gaji')</h6>
                        <h2 class="mb-0">{{number_format($gaji)}}</h2>
                    </div>
                </div>
            </div>            
            <div class="col-md-6 col-lg-3 grid-margin stretch-card">
                <div class="card bg-gradient-info text-white text-center card-shadow-info">
                    <div class="card-body">
                        <h6 class="font-weight-normal">@lang('umum.total_tidak_masuk')</h6>
                        <h2 class="mb-0">{{$tmasuk}}</h2>
                    </div>
                </div>
            </div>
            @foreach($izin as $iz)
            <div class="col-md-6 col-lg-3 grid-margin stretch-card">
                <div class="card bg-gradient-warning text-white text-center card-shadow-warning">
                    <div class="card-body">
                        <h6 class="font-weight-normal">@lang('umum.total_hari') {{$iz->nama_tipe_absensi}} @lang('umum.bulan_ini')</h6>
                        <h2 class="mb-0">{{$iz->total}}</h2>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">@lang('umum.grafik_gaji')</h4>                        
                        <div id="js-legend" class="chartjs-legend mt-4 mb-5 gaji-legend"></div>
                        <div class="demo-chart">
                            <canvas id="dashboard-gaji"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">@lang('umum.grafik_absensi')</h4>
                        <div class="card-descridivtion">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="RadioAb1" name="RadioAb" class="custom-control-input" value="1" checked>
                                <label class="custom-control-label" for="RadioAb1">01 - {{(date('t-M'))}}</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="RadioAb2" name="RadioAb" class="custom-control-input" value="2">
                                <label class="custom-control-label" for="RadioAb2">11 {{date('M',strtotime('-1 month',strtotime(date('Y-m-d'))))}} - 10 {{(date('M'))}}</label>
                            </div>
                        </div>
                        <div id="js-legend" class="chartjs-legend mt-4 mb-5 analytics-legend"></div>
                        <div class="demo-chart">
                            <canvas id="dashboard-monthly-analytics"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">@lang('umum.grafik_absensi_per_shift')</h4>
                        <label for="tanggal_grafik_shift">@lang('umum.tanggal')</label>
                        <select class="form-control" name="tanggal_grafik_shift" id="tanggal_grafik_shift" style="width:30%;">
                            <option value="{{$option_tanggal['today']}}">{{$option_tanggal['today_slashed']}}</option>
                            <option value="{{$option_tanggal['subday1']}}">{{$option_tanggal['subday1_slashed']}}</option>
                            <option value="{{$option_tanggal['subday2']}}">{{$option_tanggal['subday2_slashed']}}</option>
                            <!-- <option value="2022-06-02">2022-06-02</option>
                            <option value="2022-06-01">2022-06-01</option>
                            <option value="2022-05-30">2022-05-30</option> -->
                        </select>
                        <div class="row" id="target-chart-content">

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4>@lang('umum.data_izin')</h4>
                        <a href="{{url('absensi/izin-cuti')}}">Link Approval</a>
                        <br><br>
                            <div class="row">
                                <div class="form-group col-md-2">
                                    <label>Status</label>
                                    <div class="form-check form-check-flat form-check-primary">
                                      <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input izcu-checkbox" checked name="izcu_status_menunggu">
                                        @lang('umum.menunggu')
                                      <i class="input-helper"></i></label>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Status</label>
                                    <div class="form-check form-check-flat form-check-primary">
                                      <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input izcu-checkbox" name="izcu_status_disetujui">
                                        @lang('umum.setuju')
                                      <i class="input-helper"></i></label>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Status</label>
                                    <div class="form-check form-check-flat form-check-primary">
                                      <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input izcu-checkbox" name="izcu_status_ditolak">
                                        @lang('umum.tolak')
                                      <i class="input-helper"></i></label>
                                    </div>
                                </div>
                                <div class="form-group col-3">
                                    <label for="filterMonthYear">@lang('umum.tanggal')</label>                                    
                                    <input class="form-control" type="text" name="tanggal_izcu" id="tanggal_izcu" value="{{$tanggal}}">
                                </div>                    
                            </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table w-100 tabel-izin-cuti">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>@lang('umum.nama_karyawan')</th>
                                                <th>@lang('umum.tipe_absen')</th>
                                                <th>@lang('umum.tanggal_mulai')</th>
                                                <th>@lang('umum.tanggal_akhir')</th>
                                                <th>@lang('umum.approval')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4>@lang('umum.lembur_terbaru')</h4>
                        <a href="{{url('/absensi/over-time')}}">Link Approval</a>
                        <br>
                        <br>
                            <div class="row">
                                <div class="form-group col-md-2">
                                    <label>Status</label>
                                    <div class="form-check form-check-flat form-check-primary">
                                      <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input lembur-checkbox" checked name="lembur_status_menunggu">
                                        @lang('umum.menunggu')
                                      <i class="input-helper"></i></label>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Status</label>
                                    <div class="form-check form-check-flat form-check-primary">
                                      <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input lembur-checkbox" name="lembur_status_disetujui">
                                        @lang('umum.setuju')
                                      <i class="input-helper"></i></label>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Status</label>
                                    <div class="form-check form-check-flat form-check-primary">
                                      <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input lembur-checkbox" name="lembur_status_ditolak">
                                        @lang('umum.tolak')
                                      <i class="input-helper"></i></label>
                                    </div>
                                </div>                                
                                <div class="form-group col-3">
                                    <label for="filterMonthYear">@lang('umum.tanggal')</label>                                    
                                    <input class="form-control" type="text" name="tanggal_lembur" id="tanggal_lembur" value="{{$tanggal}}">
                                </div>
                            </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table w-100 tabel-lembur">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>@lang('umum.nama_karyawan')</th>
                                                <th>@lang('umum.tanggal')</th>
                                                <th>@lang('umum.jumlah_jam')</th>
                                                <th>@lang('umum.approval')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4>@lang('umum.karyawan_tidak_masuk')</h4><br>
                        <div class="row">
                            <div class="form-group col-3">
                                <label for="filterMonthYear">@lang('umum.tanggal')</label>                                    
                                <input class="form-control" type="text" name="tanggal_tidak_masuk" id="tanggal_tidak_masuk" value="{{$tanggal}}">
                            </div>
                        </div>
                        
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table w-100 tabel-tidak-masuk">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NIK</th>
                                                <th>@lang('umum.nama_karyawan')</th>
                                                <th>@lang('umum.tanggal')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        
    </div>
    <!-- content-wrapper ends -->
    <!-- partial:partials/_footer.html -->
    <footer class="footer">
        <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2022 <a
                    href="https://www.aptikma.co.id" target="_blank">Aptikma.co.id</a>. All rights
                reserved.</span>
            <!-- <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i
                    class="mdi mdi-heart text-danger"></i></span> -->
        </div>
    </footer>
    <!-- partial -->

    <!-- Modal starts -->
      <div class="modal fade" id="modalChartShift" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel-3" aria-hidden="true">
        <div class="modal-dialog modal-md" style="margin-top:5%;" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="titleModal"></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body" style="height:340px; overflow-y: scroll;">

                <div class="card-descridivtion">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="RadioShf1" name="RadioShf" class="custom-control-input" value="" checked>
                        <label class="custom-control-label" for="RadioShf1" id="labelRadioShf1">Text</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="RadioShf2" name="RadioShf" class="custom-control-input" value="">
                        <label class="custom-control-label" for="RadioShf2" id="labelRadioShf2">Text</label>
                    </div>
                </div><br>

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="tabel_detail_shift" class="table table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>@lang('umum.nama_karyawan')</th>
                                    </tr>
                                </thead>
                                <tbody id="detailShiftRow">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <!-- <div class="modal-footer">
              <input type="submit" class="btn btn-success" value="Simpan" id="btn-save"/>
              <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
            </form> -->
            </div>
          </div>
        </div>
      </div>
      <!-- Modal Ends -->

</div>
@endsection

@push('js')
<script src="{{asset('/')}}assets/js/chart.js"></script>
<script src="{{ asset('/') }}assets/vendors/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function () {
        read_data();
        read_gaji_data();
        read_data_izcu();
        read_data_lembur();
        read_data_tidak_masuk();
        read_data_grafik_absensi();


        $('input[name="tanggal_lembur"]').daterangepicker({
              autoUpdateInput: false,
              locale: {
                  cancelLabel: 'Clear',
                  format: 'MM-DD-YYYY'
              }
        });

        $('input[name="tanggal_lembur"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM-DD-YYYY') + ' - ' + picker.endDate.format('MM-DD-YYYY'));
            read_data_lembur();
        });

        $('input[name="tanggal_lembur"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        $('input[name="tanggal_tidak_masuk"]').daterangepicker({
              autoUpdateInput: false,
              locale: {
                  cancelLabel: 'Clear',
                  format: 'MM-DD-YYYY'
              }
        });

        $('input[name="tanggal_tidak_masuk"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM-DD-YYYY') + ' - ' + picker.endDate.format('MM-DD-YYYY'));            
            read_data_tidak_masuk();
        });

        $('input[name="tanggal_tidak_masuk"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        $('input[name="tanggal_izcu"]').daterangepicker({
              autoUpdateInput: false,
              locale: {
                  cancelLabel: 'Clear',
                  format: 'MM-DD-YYYY'
              }
        });

        $('input[name="tanggal_izcu"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM-DD-YYYY') + ' - ' + picker.endDate.format('MM-DD-YYYY'));            
            read_data_izcu();
        });

        $('input[name="tanggal_izcu"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });

    var myChart;

        function read_data() {
            var inp = document.querySelector('input[name="RadioAb"]:checked').value;
            
            $.ajax({
                url : "{{ url('/dashboard/chart') }}",
                data : {'tgl' : inp},
                type : 'GET',
                dataType : 'json',
                success : function(result){

                    var ctx = document.getElementById('dashboard-monthly-analytics').getContext("2d");
                        myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                                labels:  result.dric_title,
                                datasets: [
                                    {
                                        label: "@lang('umum.izin')",
                                        data: result.dric['izin'],
                                        borderColor: 'rgba(77, 124, 255, 0.8)',
                                        backgroundColor: 'rgba(77, 124, 255, 0.8)',
                                        pointRadius: 3,
                                        fill: true,
                                        borderWidth: 3,
                                        fillColor: "rgba(77, 124, 255,0.1)",
                                    },
                                    {
                                        label: "@lang('umum.tidak_masuk')",
                                        data: result.dric['tidak_masuk'],
                                        borderColor: 'rgba(235, 105, 143, .8)',
                                        backgroundColor: 'rgba(235, 105, 143, .8)',
                                        pointRadius: 3,
                                        fill: true,
                                        borderWidth: 3,
                                        fillColor: "rgba(235, 105, 143,0.1)",
                                    },
                                ]
                              },
                        options: {
                            multiTooltipTemplate: "<%= datasetLabel %>: <%= value %>",
                            responsive: true,
                            interaction: {
                              mode: 'index',
                              intersect: false,
                            },
                            stacked: false,
                            maintainAspectRatio: false,
                            legend: {
                                display: true,
                                position: "top"
                            },
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                      display: true,
                                      text: '@lang("umum.hari")'
                                    }
                                },
                                y: {
                                  type: 'linear',
                                  position: 'left',
                                  beginAtZero: true,
                                  display: true,
                                  title: {
                                    display: true,
                                    text: '@lang("umum.jumlah")'
                                  },
                                  ticks: {
                                      beginAtZero: true
                                  }
                                },
                            },
                        },
                    });

                }
            });        
        }

        var myChart1;

        function read_gaji_data() {
            // var inp = document.querySelector('input[name="RadioAb"]:checked').value;
            
            $.ajax({
                url : "{{ url('/dashboard/chart-gaji') }}",
                // data : {'tgl' : inp},
                type : 'GET',
                dataType : 'json',
                success : function(result){

                    var ctx = document.getElementById('dashboard-gaji').getContext("2d");
                        myChart1 = new Chart(ctx, {
                        type: 'line',
                        data: {
                                labels:  result.dric_title,
                                datasets: [
                                    {
                                        label: '@lang("umum.jumlah")',
                                        data: result.dric['tgaji'],
                                        borderColor: 'rgba(77, 124, 255, 0.8)',
                                        backgroundColor: 'rgba(77, 124, 255, 0.8)',
                                        pointRadius: 3,
                                        fill: true,
                                        borderWidth: 3,
                                        fillColor: "rgba(77, 124, 255,0.1)",
                                    }
                                ]
                              },
                        options: {
                            multiTooltipTemplate: "<%= datasetLabel %>: <%= value %>",
                            responsive: true,
                            interaction: {
                              mode: 'index',
                              intersect: false,
                            },
                            stacked: false,
                            maintainAspectRatio: false,
                            legend: {
                                display: true,
                                position: "top"
                            },
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                      display: true,
                                      text: '@lang("umum.bulan")'
                                    }
                                },
                                y: {
                                  type: 'linear',
                                  position: 'left',
                                  beginAtZero: true,
                                  display: true,
                                  title: {
                                    display: true,
                                    text: '@lang("umum.jumlah")'
                                  },
                                  ticks: {
                                      beginAtZero: true
                                  }
                                },
                            },
                        },
                    });

                }
            });        
        }

    $('input[name="RadioAb"]').on('click', function(e) {
        myChart.destroy();
        read_data();
    });



    $('.izcu-checkbox').on('click', function(e) {
        read_data_izcu();
    });
    function read_data_izcu() {
        let status_menunggu = $('input[name="izcu_status_menunggu"]:checked').val();
        let status_disetujui = $('input[name="izcu_status_disetujui"]:checked').val();
        let status_ditolak = $('input[name="izcu_status_ditolak"]:checked').val();
        var date_range_izcu = $('#tanggal_izcu').val();
        var dates_izcu = date_range_izcu.split(" - ");  

        $('.tabel-izin-cuti').DataTable().destroy();
        $('.tabel-izin-cuti').DataTable({
            processing: true,
            serverSide: true,

            "scrollX": true,
            ajax: {
                url: '{{ url("dashboard/data-izcu") }}',
                type: 'GET',
                data: {
                    status_menunggu : status_menunggu,
                    status_disetujui : status_disetujui,
                    status_ditolak : status_ditolak,
                    awal : dates_izcu[0],
                    akhir : dates_izcu[1],
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
                    name: 'm_karyawan.nama_karyawan',
                },
                {
                    data: 'nama_tipe_absensi',
                    name: 'ref_tipe_absensi.nama_tipe_absensi',
                },
                {
                    data: 'tanggal_mulai',
                    name: 't_izin.tanggal_mulai',
                },
                {
                    data: 'tanggal_selesai',
                    name: 't_izin.tanggal_selesai',
                },
                {
                    data: 'approval',
                    name: 'approval',
                    orderable: false,
                    searchable: false,
                },
            ]
        });
    }  


    $('.lembur-checkbox').on('click', function(e) {
        read_data_lembur();
    });
    function read_data_lembur() {
        let status_menunggu = $('input[name="lembur_status_menunggu"]:checked').val();
        let status_disetujui = $('input[name="lembur_status_disetujui"]:checked').val();
        let status_ditolak = $('input[name="lembur_status_ditolak"]:checked').val();
        var date_range_lembur = $('#tanggal_lembur').val();
        var dates_lembur = date_range_lembur.split(" - ");  

        $('.tabel-lembur').DataTable().destroy();
        $('.tabel-lembur').DataTable({
            processing: true,
            serverSide: true,
            order: [[2, 'desc']],
            "scrollX": true,
            ajax: {
                url: '{{ url("dashboard/data-lembur") }}',
                type: 'GET',
                data: {
                    status_menunggu : status_menunggu,
                    status_disetujui : status_disetujui,
                    status_ditolak : status_ditolak,
                    awal : dates_lembur[0],
                    akhir : dates_lembur[1],
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
                    name: 'm_karyawan.nama_karyawan',
                },
                {
                    data: 'tanggal',
                    name: 't_lembur.tanggal',                    
                    
                },
                {
                    data: 'total_jam',
                    name: 't_lembur.total_jam',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'approval',
                    name: 'approval',
                    orderable: false,
                    searchable: false,
                },
            ]
        });
    }  


    function read_data_tidak_masuk() {
        var date_range_tidak_masuk = $('#tanggal_tidak_masuk').val();
        var dates_tidak_masuk = date_range_tidak_masuk.split(" - ");
        
        $('.tabel-tidak-masuk').DataTable().destroy();
        $('.tabel-tidak-masuk').DataTable({
            processing: true,
            serverSide: true,
            order: [[3, 'desc']],
            "scrollX": true,
            ajax: {
                url: '{{ url("dashboard/data-tidak-masuk") }}',
                type: 'GET',
                data: {                    
                    awal : dates_tidak_masuk[0],
                    akhir : dates_tidak_masuk[1],
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
                    data: 'nik',
                    name: 'c.nik',
                },
                {
                    data: 'nama_karyawan',
                    name: 'c.nama_karyawan',
                },
                {
                    data: 'tanggal',
                    name: 'b.tanggal',
                },
            ]
        });
    }

    var array_chart_pershift = [];
    var json_shift = [];
    var json_detail_shift = [];
    var tanggal_shift_detail;
    var id_shift_detail;
    var list_shift_detail;

    function read_data_grafik_absensi() {
        tanggal_shift_detail = document.querySelector('select[name="tanggal_grafik_shift"]').value;
        
        $.ajax({
            url : "{{ url('/dashboard/grafik-absensi') }}",
            data : {'tanggal' : tanggal_shift_detail},
            type : 'GET',
            dataType : 'json',
            success : function(result){
                json_shift = result.data
                generatePieChart(json_shift);
            }
        });        
    }

    $('#target-chart-content').on('click', 'a', function(events){
        events.preventDefault();
        let title = $(this).attr('nama_shift');
        id_shift_detail = parseInt($(this).attr('id_shift'));
        list_shift_detail = $(this).attr('list');

        if (list_shift_detail == 'list_masuk') {
            $('#RadioShf1').val('belum_masuk');
            $('#labelRadioShf1').text('@lang("umum.belum_masuk")');
            $('#RadioShf2').val('sudah_masuk');
            $('#labelRadioShf2').text('@lang("umum.sudah_masuk")');
        }else{
            $('#RadioShf1').val('belum_pulang');
            $('#labelRadioShf1').text('@lang("umum.belum_keluar")');
            $('#RadioShf2').val('sudakeluar');
            $('#labelRadioShf2').text('@lang("umum.sudah_keluar")');
        }
        $('#RadioShf1').prop('checked',true);
        let kategori = document.querySelector('input[name="RadioShf"]:checked').value;

        $.ajax({
            url : "{{ url('/dashboard/detail-list-pershift') }}",
            data : {'id_shift' : id_shift_detail,'kategori' : kategori,'list' : list_shift_detail,'tanggal' : tanggal_shift_detail},
            type : 'GET',
            dataType : 'json',
            success : function(result){
                json_detail_shift = result.data
                generateShiftRow(json_detail_shift);

            }
        }); 

        $('#titleModal').text('Detail Shift '+title);
        $('#modalChartShift').modal('show');
    });

    $('input[name="RadioShf"]').change(function(){
        let kategori = document.querySelector('input[name="RadioShf"]:checked').value;
        $.ajax({
            url : "{{ url('/dashboard/detail-list-pershift') }}",
            data : {'id_shift' : id_shift_detail,'kategori' : kategori,'list' : list_shift_detail,'tanggal' : tanggal_shift_detail},
            type : 'GET',
            dataType : 'json',
            success : function(result){
                json_detail_shift = result.data
                generateShiftRow(json_detail_shift);
            }
        }); 
    });

    $('select[name="tanggal_grafik_shift"]').change(function(){
        $.each(array_chart_pershift , function(key, data) {
            data.destroy();
        });
        read_data_grafik_absensi();
    });

    function generateShiftRow(datas){
        $("#detailShiftRow").empty();
        let html = '';
        $.each(datas , function(key, data) {
            html += "<tr>\
                        <td class='text-center'>"+data.no+"</td>\
                        <td class='text-left'>"+data.nama_karyawan+"</td>\
                    </tr>";
        });
        $("#detailShiftRow").html(html);
    }

    function generatePieChart(datas){
        let html = '';
        $.each(datas , function(key, data) {
            html += "<div class='col-lg-6 grid-margin grid-margin-lg-0 stretch-card'>\
                      <div class='card'>\
                        <div class='card-body'>\
                          <h2 class='card-title text-center'>"+data.nama_shift+"</h2>\
                          <div class='row'>\
                            <div class='col-md-6'>\
                                <h6 class='card-title text-center'><a href='javascript:;' nama_shift='"+data.nama_shift+" - Masuk' id_shift='"+data.id_shift+"' list='list_masuk'>@lang('umum.masuk')</a></h6>\
                                <canvas id='pieChart1-id-"+data.id_shift+"'></canvas>\
                            </div>\
                            <div class='col-md-6'>\
                                <h6 class='card-title text-center'><a href='javascript:;' nama_shift='"+data.nama_shift+" - Pulang' id_shift='"+data.id_shift+"' list='list_pulang'>@lang('umum.keluar')</a></h6>\
                                <canvas id='pieChart2-id-"+data.id_shift+"'></canvas>\
                            </div>\
                          </div>\
                        </div>\
                      </div>\
                    </div>";
        });
        $("#target-chart-content").html(html);
        $.each(datas , function(key, data) {
            var doughnutPieData1 = {
                datasets: [{
                  data: [data.masuk, (data.total_karyawan - data.masuk)],
                  backgroundColor: [
                    'rgba(62, 224, 47, 0.5)',
                    'rgba(255, 99, 132, 0.5)',
                  ],
                  borderColor: [
                    'rgba(62, 224, 47, 1)',
                    'rgba(255,99,132,1)',
                  ],
                }],

                labels: [
                  '@lang("umum.sudah_masuk")',
                  '@lang("umum.belum_masuk")',
                ]
            };
            var doughnutPieData2 = {
                datasets: [{
                  data: [data.pulang, (data.total_karyawan - data.pulang)],
                  backgroundColor: [
                    'rgba(62, 224, 47, 0.5)',
                    'rgba(255, 99, 132, 0.5)',
                  ],
                  borderColor: [
                    'rgba(62, 224, 47, 1)',
                    'rgba(255,99,132,1)',
                  ],
                }],

                labels: [
                  '@lang("umum.sudah_keluar")',
                  '@lang("umum.belum_keluar")',
                ]
            };
            var doughnutPieOptions = {
              responsive: true,
              animation: {
                animateScale: true,
                animateRotate: true
              }
            };
            
            if ($("#pieChart1-id-"+data.id_shift).length) {
                var pieChartCanvas = $("#pieChart1-id-"+data.id_shift).get(0).getContext("2d");
                var pieChart = new Chart(pieChartCanvas, {
                  type: 'pie',
                  data: doughnutPieData1,
                  options: doughnutPieOptions
                });
                array_chart_pershift.push(pieChart);
            }
            if ($("#pieChart2-id-"+data.id_shift).length) {
                var pieChartCanvas = $("#pieChart2-id-"+data.id_shift).get(0).getContext("2d");
                var pieChart = new Chart(pieChartCanvas, {
                  type: 'pie',
                  data: doughnutPieData2,
                  options: doughnutPieOptions
                });
                array_chart_pershift.push(pieChart);
            }
        });

    }

</script>
@endpush