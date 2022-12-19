@extends('template')
@section('content')
<?php
    use App\Traits\Helper;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Data Asuransi Ekses</h4><br>
                <div class="row mb-4">
                    <div class="col text-right">
                        @if(Helper::can_akses('penggajian_asuransi_ekses_add'))
                        <a class="btn btn-info" href="{{route('create-asuransi')}}">Tambah Data</a>
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
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Limit</th>
                                        <th>Biaya RS</th>
                                        <th>Hutang</th>
                                        <th>Hutang Dibayar</th>
                                        <th>Sisa Hutang</th>
                                        <th>Opsi</th>
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
        read_data();

        function read_data() {
            $('table').DataTable({
                processing: true,
                serverSide: true,

                "scrollX": true,
                ajax: {
                    url: '{{ url("gaji_karyawan/data-asuransi-ekses") }}',
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
                        data: 'nik',
                        name: 'b.nik',
                        width: 'auto'
                    },
                    {
                        data: 'nama_karyawan',
                        name: 'b.nama_karyawan',
                        width: 'auto'
                    },
                    {
                        data: 'limit_asuransi',
                        name: 'b.limit_asuransi',
                        width: 'auto',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'biaya_rs',
                        name: 'a.biaya_rs',
                        width: 'auto',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'hutang',
                        name: 'a.hutang',
                        width: 'auto',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'hutang_bayar',
                        name: 'a.hutang_bayar',
                        width: 'auto',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'sisa_hutang',
                        name: 'a.sisa_hutang',
                        width: 'auto',
                        orderable: false,
                        searchable: false,
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
    
    $(document).on('click','.delete',function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Apakah yakin ingin tetap menghapus data ini ?',
            text: $(this).attr('notif'),
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
    });
</script>
@endpush