@extends('template')
@section('content')
<?php 
    use App\Traits\Helper;  
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Data Shift</h4><br>
                <div class="row mb-4">
                    <div class="col text-right">
                        @if(Helper::can_akses('master_shift_add'))
                        <a href="{{url('shift/create')}}" class="btn btn-info">Tambah</a>
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
                                        <th>Nama</th>
                                        <th>Kode Shift</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
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
                    url: '{{ url("shift/data") }}',
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
                        data: 'nama_shift',
                        name: 'nama_shift',
                        width: 'auto'
                    },
                    {
                        data: 'kode_shift',
                        name: 'kode_shift',
                        width: 'auto'
                    },
                    {
                        data: 'jam_masuk',
                        name: 'jam_masuk',
                        width: 'auto'
                    },
                    {
                        data: 'jam_keluar',
                        name: 'jam_keluar',
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