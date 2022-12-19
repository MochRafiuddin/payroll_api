@extends('template')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Gaji Karyawan Periode</h4><br>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Periode</th>
                                        <th>Action</th>
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
        read_data();

        function read_data() {
            $('table').DataTable({
                processing: true,
                serverSide: true,

                "scrollX": true,
                language: {
                    searchPlaceholder: "All Karyawan"
                },
                ajax: {
                    url: '{{ url("gaji_karyawan/data-periode") }}',
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
                        name: 'nik',
                        width: 'auto'
                    },
                    {
                        data: 'nama_karyawan',
                        name: 'm_karyawan.nama_karyawan',
                        width: 'auto'
                    },
                    {
                        data: 'nama_jabatan',
                        name: 'm_jabatan.nama_jabatan',
                        width: 'auto'
                    },
                    {
                        data: 'periode',
                        name: 'periode',
                        width: 'auto'
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
    });
</script>
@endpush