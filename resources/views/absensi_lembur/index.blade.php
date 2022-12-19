@extends('template')
@section('content')
<?php 
use App\Traits\Helper;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Data Absensi</h4><br>
                <div class="row mb-4">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="filterMonthYear">Tanggal Shift</label>
                            <input class="form-control" type="hidden" name="filterMonthYear" id="filterMonthYear" value="<?= date('d-m-Y') ?>">
                            <input class="form-control" type="text" name="tanggal" id="tanggal" value="{{$tanggal}}">

                        </div>
                        
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="filterKar">Karyawan</label>
                            <select class="form-control " name="filter_karyawan" id="filterKar">                               
                               <option value="0" selected>Semua</option>
                               @foreach($karyawan as $key)
                                    <option value="{{$key->id_karyawan}}">{{$key->nama_karyawan}}</option>
                               @endforeach
                           </select>
                        </div>
                    </div>
                    <div class="col text-right">
                        @if(Helper::can_akses('riwayat_absensi_lembur_export'))
                            <a id="myHref" class="btn btn-info" target="_blank">Export</a>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table w-100" id="table-data">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Emp No.</th>
                                        <th>No. ID</th>
                                        <th>Nama</th>
                                        <th>Tanggal</th>
                                        <th>Jam Kerja</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Pulang</th>
                                        <th>Scan Masuk</th>
                                        <th>Scan Pulang</th>
                                        <th>Absent</th>
                                        <th>Departemen</th>
                                        <th>Total Hour</th>
                                        <th>Reason Overtime</th>
                                        <th>KE-1</th>
                                        <th>KE-2</th>
                                        <th>KE-3</th>
                                        <th>KE-4</th>
                                        <th>Total Count</th>
                                        <th>Basic Salary</th>
                                        <th>PRICE PER HOUR</th>
                                        <th>TOTAL COST</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
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
<script src="{{ asset('/') }}assets/vendors/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function () {
        var date_range = $('#tanggal').val();
        var dates = date_range.split(" - ");
        read_data(dates[0],dates[1],$('#filterKar').val());

        $('input[name="tanggal"]').daterangepicker({
              autoUpdateInput: false,
              locale: {
                  cancelLabel: 'Clear',
                  format: 'MM-DD-YYYY'
              }
        });

        $('input[name="tanggal"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM-DD-YYYY') + ' - ' + picker.endDate.format('MM-DD-YYYY'));
            read_data(picker.startDate.format('MM-DD-YYYY'),picker.endDate.format('MM-DD-YYYY'),$('#filterKar').val());
        });

        $('input[name="tanggal"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        
         $('#filterKar').change(function(e){
            var date_range = $('#tanggal').val();
            var dates = date_range.split(" - ");
            read_data(dates[0],dates[1],$(this).val());
        })
        

        function read_data(tanggal_awal,tanggal_akhir,karyawan = 0) {
            $('#table-data').DataTable().destroy();
            $('#table-data').DataTable({
                processing: true,
                serverSide: true,

                "scrollX": true,
                language: {
                    searchPlaceholder: "All Karyawan"
                },
                ajax: {
                    url: '{{ url("absensi-lembur/data") }}/'+tanggal_awal+'/'+tanggal_akhir+'/'+karyawan,
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
                        data: 'employee_id',
                        name: 'm_karyawan.employee_id',
                    },
                    {
                        data: 'no_bpjs',
                        name: 'm_karyawan.no_bpjs',
                    },
                    {
                        data: 'nama_karyawan',
                        name: 'm_karyawan.nama_karyawan',
                        
                    },
                    {
                        data: 'tanggal',
                        name: 't_absensi.tanggal',
                        
                    },
                    {
                        data: 'nama_shift',
                        name: 'm_shift.nama_shift',
                        
                    },
                    {
                        data: 'jam_masuk_shift',
                        name: 't_absensi.jam_masuk_shift',
                        searchable: false
                        
                    },
                    {
                        data: 'jam_keluar_shift',
                        name: 't_absensi.jam_keluar_shift',
                        searchable: false
                        
                    },
                    {
                        data: 'tanggal_masuk',
                        name: 'tanggal_masuk',
                        searchable: false
                        
                    },
                    {
                        data: 'tanggal_keluar',
                        name: 'tanggal_keluar',
                        searchable: false
                        
                    },
                    {
                        data: 'absent',
                        searchable: false
                    },
                    {
                        data: 'nama_departemen',
                        name: 'm_departemen.nama_departemen',
                        
                    },
                    {
                        data: 'total_hour',
                        name: 'total_hour',
                        searchable: false
                        
                    },
                    {
                        data: 'reason',
                        name: 'reason',
                        searchable: false
                        
                    },
                    {
                        data: 'ke1',
                        name: 'ke1',
                        searchable: false
                        
                    },
                    {
                        data: 'ke2',
                        name: 'ke2',
                        searchable: false
                        
                    },
                    {
                        data: 'ke3',
                        name: 'ke3',
                        searchable: false
                        
                    },
                    {
                        data: 'ke4',
                        name: 'ke4',
                        searchable: false
                        
                    },
                    {
                        data: 'total_count',
                        name: 'total_count',
                        searchable: false
                        
                    },
                    {
                        data: 'basic_salary',
                        name: 'basic_salary',
                        searchable: false
                        
                    },
                    {
                        data: 'salary_hour',
                        name: 'salary_hour',
                        searchable: false
                        
                    },
                    {
                        data: 'total_cost',
                        name: 'total_cost',
                        searchable: false
                        
                    }
                ]
            });
        }
        $("#myHref").on('click', function() {
            var id_karyawan = $('#filterKar').val();
            var date_range = $('#tanggal').val();
            var dates = date_range.split(" - ");
            window.open("{{ url('absensi-lembur/export') }}?start="+dates[0]+"&akhir="+dates[1]+"&id_karyawan="+id_karyawan, "_blank");
        });

    });
</script>
@endpush