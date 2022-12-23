@extends('template')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Total Absensi</h4><br>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="">@lang('umum.departemen_label')</label>
                        <select class="form-control js-example-basic-single" name="id_departemen" id="id_departemen"
                             style="width:100%" data-maximum-selection-length="10">
                             @foreach($departemen as $data)
                                 <option value="{{$data->id_departemen}}">{{$data->nama_departemen}}</option>
                             @endforeach
                        </select>                  
                    </div>
                    <div class="form-group col-md-4">
                        <label for="">Tahun</label>
                        <input class="form-control pickerdateyear" type="text" name="tahun" id="tahun" value="{{date('Y')}}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="">Bulan</label>
                        <input class="form-control pickerdatemonths" type="text" name="bulan" id="bulan" value="{{ date('m')}}">
                    </div>              
                </div>              
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table w-100 tableData">
                                <thead>
                                    <tr id="thead">
                                        <th>No</th>
                                        <th>Nama Karyawan</th>
                                        <?php foreach ($ref_tipe_absensi as $key => $value): ?>
                                            <th>{{ $value->nama_tipe_absensi }}</th>
                                        <?php endforeach ?>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <p class="text-danger">* Klik untuk melihat detail</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->

    <!-- content-wrapper ends -->
    <!-- partial:partials/_footer.html -->
    @include("partial.footer")
    <!-- partial -->

    <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-labelledby="modalDetailTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportTitle">Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form_group row">
                        <label class="col-4">Karyawan</label>
                        <label class="col-8 label-nama" style="color:#9e9e9e;"></label>
                    </div>
                    <div class="form_group row">
                        <label class="col-4">Bulan</label>
                        <label class="col-8 label-bulan" style="color:#9e9e9e;"></label>
                    </div>
                    <div class="form_group row">
                        <label class="col-4">Tipe Absensi</label>
                        <label class="col-8 label-tipe" style="color:#9e9e9e;"></label>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 rowTabelDetail"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script src="{{asset('/')}}assets/js/select2.js"></script>
<script src="{{ asset('/') }}assets/vendors/daterangepicker/daterangepicker.min.js"></script>
<script>
    var ref_tipe_absensi = <?php echo json_encode($ref_tipe_absensi) ?>

    $("#id_departemen,#tahun,#bulan").change(function(event) {
        /* Act on the event */
        read_data();
    });

    $(document).ready(function () {


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        read_data();

        
    });

    $(".pickerdatemonths").datepicker( {
        format: "mm",
        startView: "months", 
        minViewMode: "months"
    });

    $(".pickerdateyear").datepicker( {
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years",
        autoclose:true //to close picker once year is selected
    });

    function read_data() {
        $.ajax({
            url: '{{ route("total-absensi-data") }}',
            type: 'GET',
            dataType: 'json',
            data: {
                departemen : $('#id_departemen').val(),
                tahun : $('#tahun').val(),
                bulan : $('#bulan').val(),
            },
        })
        .done(function(res) {
            console.log("success");
            var data_karyawan = res.data.data_karyawan;
            var data_absensi = res.data.data_absensi;

            html_body_tabel = '';
            for (var i = 0; i < data_karyawan.length; i++) {
                no = i+1;
                html_body_tabel2 = '';

                for (var j = 0; j < ref_tipe_absensi.length; j++) {

                    var search_absensi = $.grep(data_absensi, function(absensi){
                        return absensi.id_tipe_absensi == ref_tipe_absensi[j].id_tipe_absensi && absensi.id_karyawan == data_karyawan[i].id_karyawan;
                    });

                    if (search_absensi.length > 0) {
                        html_body_tabel2 += '<td><a href="javascript:undefined" class="detailAbsensi" data-id="'+search_absensi[0].id_report_absensi+'" data-karyawan="'+data_karyawan[i].nama_karyawan+'" data-tipe="'+ref_tipe_absensi[j].nama_tipe_absensi+'">'+search_absensi[0].jumlah_hari+'</a></td>';
                    } else {
                        html_body_tabel2 += '<td><a href="javascript:undefined" class="detailAbsensi" data-id="0" data-karyawan="'+data_karyawan[i].nama_karyawan+'" data-tipe="'+ref_tipe_absensi[j].nama_tipe_absensi+'">0</a></td>';
                    }
                }



                html_body_tabel += '<tr>\
                    <td>'+no+'</td>\
                    <td>'+data_karyawan[i].nama_karyawan+'</td>\
                    '+html_body_tabel2+'\
                </tr>';


            }

            $(".tableData tbody").html(html_body_tabel);

            $('.tableData').DataTable();

            $(".dataTables_filter input").attr("placeholder", "All Karyawan");

        })
        .fail(function() {
            console.log("error");
        })
        .always(function() {
            console.log("complete");
        });
    
    }

    $(document).on('click', '.detailAbsensi', function(event) {
        event.preventDefault();
        /* Act on the event */
        id = $(this).data("id");
        karyawan = $(this).data("karyawan");
        tipe = $(this).data("tipe");

        $.ajax({
            url: '{{ route("total-absensi-detail") }}',
            type: 'GET',
            dataType: 'json',
            data: {
                id : id,
            },
        })
        .done(function(res) {
            var data_absensi = res.data.data_absensi;
            var data_absensi_det = res.data.data_absensi_det;
            var month_text = {
                1 : "Januari",
                2 : "Februari",
                3 : "Maret",
                4 : "April",
                5 : "Mei",
                6 : "Juni",
                7 : "Juli",
                8 : "Agustus",
                9 : "September",
                10 : "Oktober",
                11 : "November",
                12 : "Desember",
            }

            table_body = '';

            if (data_absensi != null ) {

                $(".label-nama").text(data_absensi.nama_karyawan);
                $(".label-bulan").text(month_text[data_absensi.bulan]+" "+data_absensi.tahun);
                $(".label-tipe").text(data_absensi.nama_tipe_absensi);

                if (data_absensi.kode_tipe_absensi == 'lembur' || data_absensi.kode_tipe_absensi == 'terlambat') {

                    table_body_det = '';

                    for (var i = 0; i < data_absensi_det.length; i++) {
                        no = i+1;

                        var dmY = data_absensi_det[i].tanggal.split("-");
                        var dmY = dmY[2]+"/"+dmY[1]+"/"+dmY[0];

                        var menit = data_absensi_det[i].total_menit;
                        var jam = (menit/60);
                        var m = menit%60;

                        var text_menit = "";

                        if (menit < 60) {
                            text_menit = Math.floor(m)+" Menit";
                        } else {
                            text_menit = Math.floor(jam)+" Jam ";

                            if (m != 0) {
                                text_menit += Math.floor(m)+" Menit";
                            }
                        }

                        table_body_det += '<tr>\
                                            <td>'+no+'</td>\
                                            <td>'+dmY+'</td>\
                                            <td>'+text_menit+'</td>\
                                        </tr>';
                    }

                    table_body = '<thead>\
                                    <tr>\
                                        <th>No</th>\
                                        <th>Tanggal</th>\
                                        <th>Total Jam</th>\
                                    </tr>\
                                </thead>\
                                <tbody>\
                                    '+table_body_det+'\
                                </tbody>';
                } else {

                    table_body_det = '';

                    for (var i = 0; i < data_absensi_det.length; i++) {
                        no = i+1;

                        var dmY = data_absensi_det[i].tanggal.split("-");
                        var dmY = dmY[2]+"/"+dmY[1]+"/"+dmY[0];

                        table_body_det += '<tr>\
                                            <td>'+no+'</td>\
                                            <td>'+dmY+'</td>\
                                        </tr>';
                    }

                    table_body = '<thead>\
                                    <tr>\
                                        <th>No</th>\
                                        <th>Tanggal</th>\
                                    </tr>\
                                </thead>\
                                <tbody>\
                                    '+table_body_det+'\
                                </tbody>';
                }
            } else {

                $(".label-nama").text(karyawan);
                $(".label-bulan").text(month_text[parseInt($('#bulan').val())]+" "+$('#tahun').val());
                $(".label-tipe").text(tipe);

                table_body = '<thead>\
                                    <tr>\
                                        <th>No</th>\
                                        <th>Tanggal</th>\
                                    </tr>\
                                </thead>\
                                <tbody>\
                                </tbody>';
            }


            var tabel = '<table class="table w-100">'+table_body+'</table>';

            $(".rowTabelDetail").html(tabel);

            $("#modalDetail").modal('show');
        })
        .fail(function() {
            console.log("error");
        })
        .always(function() {
            console.log("complete");
        });
    });
</script>
@endpush