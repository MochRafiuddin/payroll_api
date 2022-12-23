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
                            <input class="form-control" type="text" name="filterMonthYear" id="filterMonthYear" value="<?= date('d-m-Y') ?>">
                        </div>
                        
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="filterKar">Karyawan</label>
                            <select class="form-control " name="filter_karyawan" id="filterKar">
                               <!-- <option value="" selected disabled>Pilih Karyawan</option> -->
                               <option value="0" selected>Semua</option>
                               @foreach($karyawan as $key)
                                    <option value="{{$key->id_karyawan}}">{{$key->nama_karyawan}}</option>
                               @endforeach
                           </select>
                        </div>
                    </div>
                    <div class="col text-right">
                        @if(Helper::can_akses('absensi_data_fingerprint_import'))
                            <a class="btn btn-warning" href="{{url('absen/fingerprint')}}">Import</a>
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
                                        <th>Nama Karyawan</th>
                                        <th>NIK</th>
                                        <th>Tanggal Shift</th>
                                        <th>Nama Shift</th>
                                        <th>Waktu Masuk</th>
                                        <th>Waktu Keluar</th>
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
<script>
    $(document).ready(function () {
        read_data($("#filterMonthYear").val(),$('#filterKar').val());
        function getDaysByMonth() {
            var filterMonthYear = $("#filterMonthYear").val();
            var filterMonthYearSplit = filterMonthYear.split("-");
            var new_filterMonthYear = filterMonthYearSplit[1]+"-"+filterMonthYearSplit[0];

          const daysInMonth = moment(new_filterMonthYear).daysInMonth();
          return Array.from({length: daysInMonth}, (v, k) => k + 1)
        };

        $("#filterMonthYear").datepicker( {
            format: "dd-mm-yyyy",
           
        }).change(function(event) {
            /* Act on the event */
            if ($(this).val() == "") {
                $(this).val(moment().format('DD-MM-YYYY'));
            } else {
                read_data($(this).val(),$('#filterKar').val());
            }
        });

        $('#filterKar').change(function(e){
            read_data($("#filterMonthYear").val(),$(this).val());
        })
        

        function read_data(tanggal,karyawan = 0) {
            $('#table-data').DataTable().destroy();
            $('#table-data').DataTable({
                processing: true,
                serverSide: true,

                "scrollX": true,
                language: {
                    searchPlaceholder: "All Karyawan"
                },
                ajax: {
                    url: '{{ url("absen/data") }}/'+tanggal+"/"+karyawan,
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
                        data: 'nama_karyawan',
                        name: 'nama_karyawan',
                    },
                    {
                        data: 'nik',
                        name: 'nik',
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal',
                    },
                    {
                        data: 'nama_shift',
                        name: 'nama_shift',
                    },
                    {
                        data: 'jam_masuk_shift',
                        name: 'jam_masuk_shift',
                    },
                    {
                        data: 'jam_keluar_shift',
                        name: 'jam_keluar_shift',
                    }
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
        });
    
        $("#formForm").on('submit',(function(e) {
            $(".loading").css('display','block');
            $('#file').css('display','none');
            $('.btn-import').attr('disabled','disabled');
            $(".msg").html("");
            e.preventDefault();
            $.ajax({
                url: "{{url('atur-shift-grup-karyawan/import')}}",
                type: "POST",
                data:  new FormData(this),
                contentType: false,
                cache: false,
                processData:false,
                success: function(data){
                    $('#file').css('display','block');
                    $('.btn-import').removeAttr('disabled');
                    $(".loading").css('display','none');
                    if(data.status){
                        if(data.error == 0){
                            console.log(data);
                            $('.table').DataTable().destroy();
                            read_data();
                            $(".msg").html('<div class="alert alert-success alert-sm mt-2">'+data.msg_import+'</div>');
                        }else{
                            $(".msg").html('<div class="alert alert-danger alert-sm mt-2">'+data.msg_import+'</div>');
                        }
                    }
                }
            });
        }));
    });
</script>
@endpush