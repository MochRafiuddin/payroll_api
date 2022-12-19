@extends('template')
@php
    use Carbon\Carbon;
    use App\Traits\Helper;
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
            <div class="col-md-6 col-lg-4 grid-margin stretch-card">
                <div class="card bg-gradient-primary text-white text-center card-shadow-primary">
                    <div class="card-body">
                        <h6 class="font-weight-normal">TOTAL JAM LEMBUR PERIODE GAJI {{strtoupper($bulan)}}</h6>
                        <h2 class="mb-0">{{$jam_lembur}} Jam</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 grid-margin stretch-card">
                <div class="card bg-gradient-danger text-white text-center card-shadow-danger">
                    <div class="card-body">
                        <h6 class="font-weight-normal">NOMINAL LEMBUR PERIODE GAJI {{strtoupper($bulan)}}</h6>
                        <h2 class="mb-0">Rp. {{Helper::ribuan($nominal_lembur)}}</h2>
                    </div>
                </div>
            </div>            
            <div class="col-md-6 col-lg-4 grid-margin stretch-card">
                <div class="card bg-gradient-info text-white text-center card-shadow-info">
                    <div class="card-body">
                        <h6 class="font-weight-normal">NOMINAL POTONGAN GAJI PERIODE {{strtoupper($bulan)}}</h6>
                        <h2 class="mb-0">Rp. <?php echo Helper::ribuan($nominal_terlambat+$nominal_early_leave+$nominal_tidak_masuk); ?></h2>
                    </div>
                </div>
            </div>            
        </div>                
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4>Data Lembur Periode Gaji {{$bulan}}</h4>                        
                        <br><br>                            
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table w-100 tabel-lembur">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Jam Lembur</th>
                                                <th>Nominal</th>                                                
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
                        <h4>Data Terlambat Periode Gaji {{$bulan}}</h4><br>                                                
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table w-100 tabel-terlambat">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Shift</th>
                                                <th>Absensi</th>
                                                <th>Jam Terlambat</th>
                                                <th>Nominal</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <p>Total Jam Terlambat : <span style="color:black;font-weight: bold;">{{$jam_terlambat}} Jam</span></p>
                                <p>Total Denda Terlambat : <span style="color:black;font-weight: bold;">Rp. {{Helper::ribuan($nominal_terlambat)}}</span></p>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4>Data Early Leave Periode Gaji {{$bulan}}</h4>                        
                        <br>
                        <br>                            
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table w-100 tabel-leave">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Shift</th>
                                                <th>Absensi</th>
                                                <th>Jam Early Leave</th>
                                                <th>Nominal</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <p>Total Jam Early Leave : <span style="color:black;font-weight: bold;">{{$jam_early_leave}} Jam</span></p>
                                <p>Total Denda Early Leave : <span style="color:black;font-weight: bold;">Rp. {{Helper::ribuan($nominal_early_leave)}}</span></p>
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
                        <h4>Data Absensi Tidak Masuk Periode Gaji {{$bulan}}</h4>                        
                        <br><br>                            
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table w-100 tabel-tidak-masuk">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Nominal</th>                                                
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <p>Total Denda Tidak Masuk : <span style="color:black;font-weight: bold;">Rp. {{Helper::ribuan($nominal_tidak_masuk)}}</span></p>                                
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

</div>
@endsection

@push('js')
<script src="{{asset('/')}}assets/js/chart.js"></script>
<script src="{{ asset('/') }}assets/vendors/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function () {
        read_data_lembur();
        read_data_terlambat();
        read_data_early_leave();
        read_data_tidak_masuk();                
    });

    function read_data_lembur() {
        
        $('.tabel-lembur').DataTable().destroy();
        $('.tabel-lembur').DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'desc']],
            "scrollX": true,
            ajax: {
                url: '{{ url("dashboard/data-lembur-karyawan") }}',                
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
                    data: 'tanggal',
                    name: 'tanggal',
                },
                {
                    data: 'jam_lembur',
                    name: 'jam_lembur',
                },
                {
                    data: 'nominal',
                    name: 'nominal',
                },
            ]
        });
    }  

    function read_data_terlambat() {
        
        $('.tabel-terlambat').DataTable().destroy();
        $('.tabel-terlambat').DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'desc']],
            "scrollX": true,
            ajax: {
                url: '{{ url("dashboard/data-terlambat-karyawan") }}',                
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
                    data: 'tanggal',
                    name: 'tanggal',
                },
                {
                    data: 'shift',
                    name: 'shift',
                },
                {
                    data: 'absensi',
                    name: 'absensi',
                },
                {
                    data: 'jam_terlambat',
                    name: 'jam_terlambat',
                },
                {
                    data: 'nominal',
                    name: 'nominal',
                },
            ]
        });
    }
    function read_data_early_leave() {
        
        $('.tabel-leave').DataTable().destroy();
        $('.tabel-leave').DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'desc']],
            "scrollX": true,
            ajax: {
                url: '{{ url("dashboard/data-early-karyawan") }}',                
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
                    data: 'tanggal',
                    name: 'tanggal',
                },
                {
                    data: 'shift',
                    name: 'shift',
                },
                {
                    data: 'absensi',
                    name: 'absensi',
                },
                {
                    data: 'jam_early_leave',
                    name: 'jam_early_leave',
                },
                {
                    data: 'nominal',
                    name: 'nominal',
                },
            ]
        });
    }
    function read_data_tidak_masuk() {
        
        $('.tabel-tidak-masuk').DataTable().destroy();
        $('.tabel-tidak-masuk').DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'desc']],
            "scrollX": true,
            ajax: {
                url: '{{ url("dashboard/data-tidak-masuk-karyawan") }}',                
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
                    data: 'tanggal',
                    name: 'tanggal',
                },                
                {
                    data: 'nominal',
                    name: 'nominal',
                },
            ]
        });
    }  
</script>
@endpush