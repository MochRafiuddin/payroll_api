@extends('template')
@section('content')
@push('css-app')
    <style type="text/css">
        .pointer{
            cursor: pointer;
        }
    </style>
@endpush
<?php
    use App\Traits\Helper;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Shift Grup Karyawan</h4><br>
                <div class="row mb-4">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="filterMonthYear">Bulan - Tahun</label>
                            <input class="form-control" type="text" name="filterMonthYear" id="filterMonthYear" value="{{old('valFilterMonthYear') ?? date('m-Y')}}">
                        </div>
                    </div>
                    <div class="col text-right">
                        @if(Helper::can_akses('absensi_atur_shift_grup_karyawan_import'))
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalImport">Import</button>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="loading" style="display: none;">
                            <div class="jumping-dots-loader">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table w-100">
                                <thead>
                                    <tr>

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

    <div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-labelledby="modalImportTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalImportTitle">Import Excel</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
            <form class="form-horizontal" role="form" id="formForm" method="post"
                action="{{url('atur-shift-grup-karyawan/import')}}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form_group">
                      <label>Format Excel</label>
                      <p><a href="{{ url('atur-shift-grup-karyawan/format-excel') }}">Download Format Excel</a></p>
                  </div>
                  <br>
                  <div class="form_group" id="file">
                      <label>File</label>
                      <input type="file" class="form-control" name="file_excel"  accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" >
                  </div>
                  <div class="msg"></div>
                  <div class="loading" style="display: none;">
                     <div class="jumping-dots-loader">
                         <span></span>
                         <span></span>
                         <span></span>
                     </div>
                  </div>
                 
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-import">Import</button>
                </div>
            </form>
        </div>
      </div>


    </div>
      <!-- Modal starts -->
        <div class="modal fade" id="modalInput" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel-3" aria-hidden="false">
          <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel-3">Atur shift karyawan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form method="post" action="{{route('set_shift')}}">
                @csrf
                  <div class="modal-body">
                    <label>Nama Grup :</label>
                    <input type="text" class="form-control" name="nama_grup" readonly><br>
                    <input type="text" class="form-control" name="id_grup_karyawan" hidden><br>
                    <label>Tanggal :</label>
                    <input type="input" class="form-control" name="tanggal_show" readonly><br>
                    <input type="input" class="form-control" name="tanggal" hidden><br>
                    <input type="input" class="form-control" name="valFilterMonthYear" hidden><br>
                    <label>Ubah Shift ke :</label>
                    <select class="form-control" name="id_shift" required>
                        <option value="">-- Pilih Shift --</option>
                        @foreach($m_shift as $data)
                        <option value="{{$data->id_shift}}">{{$data->nama_shift}}</option>
                        @endforeach
                    </select>
                  </div>
                  <div class="modal-footer">
                    <input type="submit" class="btn btn-success" value="Simpan"/>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                  </div>
              </form>
            </div>
          </div>
        </div>
        <!-- Modal Ends -->
</div>
@endsection
@push('js')
<script>
    $(document).ready(function () {

        function getDaysByMonth() {
            var filterMonthYear = $("#filterMonthYear").val();
            var filterMonthYearSplit = filterMonthYear.split("-");
            var new_filterMonthYear = filterMonthYearSplit[1]+"-"+filterMonthYearSplit[0];

          const daysInMonth = moment(new_filterMonthYear).daysInMonth();
          return Array.from({length: daysInMonth}, (v, k) => k + 1)
        };

        $("#filterMonthYear").datepicker( {
            format: "mm-yyyy",
            startView: "months", 
            minViewMode: "months"
        }).change(function(event) {
            /* Act on the event */
            if ($(this).val() == "") {
                $(this).val(moment().format('MM-YYYY'));
            } else {
            $(".loading").css('display','block');
            $(".table-responsive").css('display','none');
                // setTableShiftHeader();
                read_data();
            }
        });

        setTableShiftHeader();

        function setTableShiftHeader()
        {
            html_tabel_header = '';

            html_tabel_header += '<th>No</th>';
            html_tabel_header += '<th>Nama Grup</th>';

            var filterMonthYear = $("#filterMonthYear").val();
            
            var arrDay = getDaysByMonth();

            for (var i = 0; i < arrDay.length; i++) {

                if (i < 9) {
                    arrday = '0'+arrDay[i];
                } else {
                    arrday = arrDay[i];
                }

                html_tabel_header += '<th>'+arrday+'-'+filterMonthYear+'</th>';
            }

            $(".table thead tr").html(html_tabel_header);

        }

        read_data();

        function read_data() {
            $.ajax({
                url: '{{ url("atur-shift-grup-karyawan/data") }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    month_year: $("#filterMonthYear").val(),
                },
            })
            .done(function(res) {
                console.log("success");
                var data_grup = res.data.data_grup;
                var data_shift_grup = res.data.data_shift_grup;
                var arrDay = getDaysByMonth();
                var filterMonthYear = $("#filterMonthYear").val();
                var filterMonthYearSplit = filterMonthYear.split("-");
                var new_filterMonthYear = filterMonthYearSplit[1]+"-"+filterMonthYearSplit[0];

                html_body_tabel = '';
                for (var i = 0; i < data_grup.length; i++) {
                    no = i+1;
                    html_body_tabel2 = '';

                    for (var j = 0; j < arrDay.length; j++) {

                        if (j < 9) {
                            arrday = '0'+arrDay[j];
                        } else {
                            arrday = arrDay[j];
                        }

                        var search_shift = $.grep(data_shift_grup, function(shift){
                            return shift.tanggal == new_filterMonthYear+'-'+arrday && shift.id_grup_karyawan == data_grup[i].id_grup_karyawan;
                        });

                        if (search_shift.length > 0) {
                            if (search_shift[0].nama_shift.toLowerCase() == 'libur'){
                                search_shift[0].nama_shift = '<div class="badge badge-outline-success badge-pill pointer" tanggal="'+search_shift[0].tanggal+'" id_grup_karyawan="'+data_grup[i].id_grup_karyawan+'" nama_grup="'+data_grup[i].nama_grup+'">'+search_shift[0].nama_shift+'</div>';
                            }else{
                                search_shift[0].nama_shift = '<div class="badge badge-outline-primary badge-pill pointer" tanggal="'+search_shift[0].tanggal+'" id_grup_karyawan="'+data_grup[i].id_grup_karyawan+'" nama_grup="'+data_grup[i].nama_grup+'">'+search_shift[0].nama_shift+'</div>';
                            }
                            html_body_tabel2 += '<td>'+search_shift[0].nama_shift+'</td>';
                        } else {
                            html_body_tabel2 += '<td><div class="badge badge-outline-danger badge-pill pointer" tanggal="'+new_filterMonthYear+'-'+arrday+'" id_grup_karyawan="'+data_grup[i].id_grup_karyawan+'" nama_grup="'+data_grup[i].nama_grup+'">-</div></td>';
                        }
                    }



                    html_body_tabel += '<tr>\
                        <td>'+no+'</td>\
                        <td>'+data_grup[i].nama_grup+'</td>\
                        '+html_body_tabel2+'\
                    </tr>';


                }

                setTableShiftHeader();

                $(".table tbody").html(html_body_tabel);

                $('.table').DataTable();

            })
            .fail(function() {
                console.log("error");
            })
            .always(function() {
            $(".loading").css('display','none');
            $(".table-responsive").css('display','block');
                console.log("complete");
            });
        
        }

        $(document).on('click','.pointer', function (e){
            let nama_grup = $(this).attr('nama_grup');
            let id_grup_karyawan = $(this).attr('id_grup_karyawan');
            let tanggal = $(this).attr('tanggal');
            let tanggal_show = formatDate(tanggal);
            let filterMonthYear = $('input[name=filterMonthYear]').val();
            $('input[name=nama_grup]').val(nama_grup);
            $('input[name=id_grup_karyawan]').val(id_grup_karyawan);
            $('input[name=tanggal_show]').val(tanggal_show);
            $('input[name=tanggal]').val(tanggal);
            $('input[name=valFilterMonthYear]').val(filterMonthYear);
            $('#modalInput').modal('show');
        })

        function formatDate(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) 
                month = '0' + month;
            if (day.length < 2) 
                day = '0' + day;

            return [day, month, year].join('-');
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