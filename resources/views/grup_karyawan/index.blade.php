@extends('template')
@section('content')
<?php 
    use App\Traits\Helper;  
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Grup Karyawan</h4><br>
                <div class="row mb-4">
                    <div class="col text-right">
                        @if(Helper::can_akses('master_grup_karyawan_add'))
                        <a href="{{url('grup_karyawan/create')}}" class="btn btn-info">Tambah</a>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Grup</th>
                                        <th>Nama Grup</th>
                                        <th>Hari Kerja</th>
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
                ajax: {
                    url: '{{ url("grup_karyawan/data") }}',
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
                        data: 'kode_grup',
                        name: 'kode_grup',
                        width: 'auto'
                    },
                    {
                        data: 'nama_grup',
                        name: 'nama_grup',
                        width: 'auto'
                    },
                    {
                        data: 'hari_kerja_render',
                        name: 'hari_kerja_render',
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
        $(document).on('click', '.delete', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Kamu Yakin?',
                text: "Menghapus data ini",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = $(this).attr('href');
                }
            })
        })
    });
</script>
@endpush