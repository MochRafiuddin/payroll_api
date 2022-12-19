@extends('template')
@section('content')
<?php
    use App\Traits\Helper;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Data Tarif Lembur</h4><br>
                <div class="row mb-4">
                    <div class="col text-right">
                        @if (Helper::can_akses('konfigurasi_tarif_lembur_add'))
                            <a href="{{url('tarif_lembur/create')}}" class="btn btn-info">Tambah</a>
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
                                        <th>Jam Ke</th>
                                        <th>Rate Hari Kerja (Rp)</th>
                                        <th>Rate Hari Libur 1 (%)</th>
                                        <th>Rate Hari Libur 2 (%)</th>
                                        <th>Rate Hari Libur Pendek (%)</th>
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
                    url: '{{ url("tarif_lembur/data") }}',
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
                        data: 'jam_ke',
                        name: 'jam_ke',
                        width: 'auto'
                    },
                    {
                        data: 'rate_hari_kerja',
                        name: 'rate_hari_kerja',
                        className:'numeric',
                        width: 'auto'
                    },
                    {
                        data: 'rate_hari_libur_1',
                        name: 'rate_hari_libur_1',
                        className:'numeric',
                        width: 'auto'
                    },
                    {
                        data: 'rate_hari_libur_2',
                        name: 'rate_hari_libur_2',
                        className:'numeric',
                        width: 'auto'
                    },
                    {
                        data: 'index_hari_libur_pendek',
                        name: 'index_hari_libur_pendek',
                        className:'numeric',
                        width: 'auto'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                "drawCallback": function( settings ) {
                    $(".numeric").autoNumeric('init',{aPad:false, aDec: ',', aSep: '.'});
                }

            });
        }
         $(document).on('click','.delete',function(e){
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