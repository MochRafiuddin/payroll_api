@extends('template',['title'=>$title])
@section('content')
<?php 
    use App\Traits\Helper;  
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Data Karyawan</h4><br>
                <div class="row">
                    <div class="form-group col-md-3">
                        <label for="">@lang('umum.departemen_label')</label>
                        <select class="form-control js-example-basic-single" name="id_departemen" id="id_departemen" style="width:100%" data-maximum-selection-length="10">
                            <option value="0" selected>Semua</option>
                            @foreach($departemen as $data)
                                <option value="{{$data->id_departemen}}" {{(Session::get('state') == $data->id_departemen) ? 'selected' : ''}}>{{$data->nama_departemen}}</option>
                            @endforeach
                        </select>                  
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col text-right">
                        @if(Helper::can_akses('master_karyawan_add'))
                        <a href="{{url('karyawan/create')}}" class="btn btn-info">Tambah</a>
                        <input type="hidden" id="state" value="{{ Session::get('state') }}">
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table w-100 tabelMKaryawan">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Grup Karyawan</th>
                                        <th>Kode Fingerprint</th>
                                        <th>@lang('umum.departemen_proses')</th>
                                        <th>@lang('umum.departemen_label')</th>
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
<script src="{{asset('/')}}assets/js/select2.js"></script>
<script>    
    $(document).ready(function () {

        read_data();
        if ($('#state').val()=='') {
            $('.tabelMKaryawan').DataTable().state.clear();
            $('.tabelMKaryawan').DataTable().draw();
        }

        function read_data() {
            let departement = $('#id_departemen').val();
            $('.tabelMKaryawan').DataTable({
                processing: true,
                serverSide: true,

                "scrollX": true,
                language: {
                    searchPlaceholder: "All Karyawan"
                },
                ajax: {
                    url: '{{ url("karyawan/data") }}',
                    type: 'GET',
                    data: {
                        departement : departement,                        
                    }
                },
                rowReorder: {
                    selector: 'td:nth-child(1)'
                },
                "bStateSave": true,
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
                        name: 'm_karyawan.nik',
                        width: 'auto'
                    },
                    {
                        data: 'nama_karyawan',
                        name: 'm_karyawan.nama_karyawan',
                        width: 'auto'
                    },
                    {
                        data: 'nama_grup_karyawan',
                        name: 'm_grup_karyawan.nama_grup',
                        width: 'auto'
                    },
                    {
                        data: 'kode_fingerprint',
                        name: 'kode_fingerpirnt',
                        width: 'auto',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_departemen',
                        name: 'm_departemen.nama_departemen',
                        width: 'auto'
                    },
                    {
                        data: 'nama_departemen_label',
                        name: 'nama_departemen_label',
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
                    $(".numeric").autoNumeric('init',{aPad:false});
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

        $('#id_departemen').on('change',function(){
            let departement = $('#id_departemen').val();
            let table = $(".tabelMKaryawan").DataTable();
            table.destroy();
            read_data();
            $('.tabelMKaryawan').DataTable().state.clear();
            $('.tabelMKaryawan').DataTable().draw();
        });

    });
</script>
@endpush