@extends('template')
@section('content')
<?php 
use App\Traits\Helper;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Riwayat Gaji Karyawan</h4><br>
                <div class="row mb-4">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="filterKar">Periode Gaji</label>
                            <select class="form-control " name="filter_periode" id="filterPer">
                               @foreach($periode as $a)
                                    <option value="{{$a->id_periode}}" {{ ($a->id_periode == $id_per) ? 'selected' : '' }}>{{Helper::convertBulan($a->bulan)}} - {{$a->tahun}}</option>
                               @endforeach
                           </select>
                        </div>
                    </div>
                    <div class="col text-right">
                        @if(Helper::can_akses('riwayat_penggajian_export'))
                        <a class="btn btn-success" href="{{route('export-gaji')}}">Export Gaji</a>
                        @endif
                        @if(Helper::can_akses('riwayat_penggajian_calculate'))
                        <a class="btn btn-warning" href="{{route('calculate-gaji')}}">Calculate</a>
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
                                        <th>Periode</th>
                                        <th>Nama</th>
                                        <th>NIK</th>
                                        <th>Gaji Bersih</th>
                                        <th>Slip Gaji</th>
                                    </tr>
                                </thead>
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
<script>
    $(document).ready(function () {
        read_data($('#filterPer').val());

        $('#filterPer').change(function(e){
            read_data($(this).val());
        })

        function read_data(periode) {
            $('#table-data').DataTable().destroy();
            $('#table-data').DataTable({
                processing: true,
                serverSide: true,

                "scrollX": true,
                language: {
                    searchPlaceholder: "All Karyawan"
                },
                ajax: {
                    url: '{{ url("gaji_karyawan/data-riwayat") }}/'+periode,
                },
                rowReorder: {
                    selector: 'td:nth-child(1)'
                },

                responsive: true,
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '4%',
                        className: 'text-center'
                    },
                    {
                        data: 'periode',
                        name: 'periode',
                        width: 'auto',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'nama_karyawan',
                        name: 'a.nama_karyawan',
                        width: 'auto'
                    },
                    {
                        data: 'nik',
                        name: 'a..nik',
                        width: 'auto'
                    },
                    {
                        data: 'gaji_bersih',
                        name: 'gaji_bersih',
                        width: 'auto',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'slip_gaji',
                        name: 'slip_gaji',
                        width: 'auto',
                        orderable: false,
                        searchable: false,
                    },
                    // {
                    //     data: 'nama_jabatan',
                    //     name: 'nama_jabatan',
                    //     width: 'auto'
                    // },
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false
                    // },
                ]
            });
        }
    });
</script>
@endpush