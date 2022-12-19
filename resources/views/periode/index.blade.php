@extends('template')
@section('content')
<?php
    use App\Traits\Helper;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Data Periode</h4><br>
                <div class="row mb-4">
                    <div class="col text-right">
                        @if(Helper::can_akses('penggajian_periode_add'))
                            <a href="{{url('periode/create')}}" class="btn btn-info">Tambah</a>
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
                                        <th>Bulan</th>
                                        <th>Tahun</th>
                                        <th>Aktif</th>
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
                    url: '{{ url("periode/data") }}',
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
                        data: 'bulan',
                        name: 'bulan',
                        width: 'auto'
                    },
                    {
                        data: 'tahun',
                        name: 'tahun',
                        width: 'auto'
                    },
                    {
                        data: 'aktif_render',
                        name: 'aktif_render',
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
        });
        $(document).on('click','.aktif-check',function(e){
    
            var value = $(this).val();
            var status = $(this).is(':checked');
            if(status){
                status = false;
            }else{
                status = true;
            }
            Swal.fire({
                title: 'Kamu Yakin?',
                text: "Apakah anda yakin ingin mengubah periode?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
                }).then((result) => {
                if (result.isConfirmed) {
                    aktifkanPeriode(value);
                }else{
                    $(this).prop('checked',status);
                }
            })
        });
        function aktifkanPeriode(value){
            $.ajax({
                type: "get",
                url: "{{url('periode/actived-periode')}}/"+value,
                dataType: "JSON",
                success: function (response) {
                    if(response.status){
                        $('table').DataTable().destroy();
                        read_data();
                    }
                }
            });
        }
    });
</script>
@endpush