@extends('template')
@section('content')
<?php
    use App\Traits\Helper;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>@lang('umum.data_izin')</h4><br>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="">@lang('umum.departemen_label')</label>
                            <select class="form-control js-example-basic-single" name="id_departemen" id="id_departemen"
                                style="width:100%" data-maximum-selection-length="10">
                                <option value="0">@lang('umum.semua')</option>
                                @foreach($departemen as $data)
                                    <option value="{{$data->id_departemen}}">{{$data->nama_departemen}}</option>
                                @endforeach
                            </select>                  
                        </div>
                        <div class="form-group col-md-3">
                            <label for="filterKar">@lang('umum.karyawan')</label>
                            <select class="form-control js-example-basic-single filterKar" name="filter_karyawan" id="filterKar">                                
                               <option value="0" selected>@lang('umum.semua')</option>
                               @foreach($karyawan as $key)
                                    <option value="{{$key->id_karyawan}}">{{$key->nama_karyawan}}</option>
                               @endforeach
                           </select>                  
                        </div>
                        <div class="form-group col-md-3">
                            <label for="">@lang('umum.bulan')</label>
                            <input class="form-control pickerdatemonths" type="text" name="bulan" id="bulan" value="{{date('m')}}">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="">@lang('umum.tahun')</label>
                            <input class="form-control pickerdateyear" type="text" name="tahun" id="tahun" value="{{date('Y')}}">
                        </div>
                        <!-- <div class="form-group col-md-2">
                            <label for=""></label>
                            <input type="button" class="btn btn-success form-control savedata" value="Cari" />                    
                        </div>   -->                  
                    </div>
                <div class="row mb-4">
                    <div class="col text-right">
                        @if(Helper::can_akses('absensi_izincuti_add'))
                        <a href="{{url('izin-cuti/create')}}" class="btn btn-info">@lang('umum.tambah')</a>
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
                                        <th>@lang('umum.nama_karyawan')</th>
                                        <th>@lang('umum.tipe_absen')</th>
                                        <th>@lang('umum.approval')</th>
                                        <th>@lang('umum.tanggal_mulai')</th>
                                        <th>@lang('umum.tanggal_akhir')</th>
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

    <!-- Modal -->
<div class="modal fade" id="ajaxModelexa" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">@lang('umum.approval_s')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        @lang('umum.apakah')
        <input type="hidden" name="id_izin" id="id_izin">
        <input type="hidden" name="id_role" id="id_role">
        <input type="hidden" name="id_karyawan" id="id_karyawan">
      </div>
      <div class="modal-footer">
          <div class="col-12">
              <button type="button" class="btn btn-success float-left" id="setuju">@lang('umum.setuju_btn')</button>
              <button type="button" class="btn btn-danger float-right" id="tolak">@lang('umum.tolak_btn')</button>
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var bulan = '{{ $bulan }}';
        var tahun = '{{ $tahun }}';
        var id_karyawan_filter = '{{ $id_karyawan_filter }}';
        var id_departement_filter = '{{ $id_departement_filter }}';

        read_data();
        if (id_karyawan_filter.length == 0 || id_departement_filter.length == 0) {
            $('.table').DataTable().state.clear();
            $('.table').DataTable().draw();
        }

        function read_data() {
            let start_date = $('#bulan').val();
            let end_date = $('#tahun').val();
            let departement = $('#id_departemen').val();
            let filterkar = $('#filterKar').val();
            $('.table').DataTable({
                processing: true,
                serverSide: true,
                "bStateSave": true,
                "scrollX": true,
                language: {
                    searchPlaceholder: "All Karyawan"
                },
                ajax: {
                    url: '{{ url("izin-cuti/data") }}',
                    type: 'GET',
                    data: {
                        start_date : start_date,
                        end_date : end_date,
                        departement : departement,
                        filterkar : filterkar,
                    }
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
                        name: 'm_karyawan.nama_karyawan',
                    },
                    {
                        data: 'nama_tipe_absensi',
                        name: 'nama_tipe_absensi',
                        searchable: false,
                    },
                    {
                        data: 'approval',
                        name: 'approval',
                        searchable: false,
                    },
                    {
                        data: 'tanggal_mulai',
                        name: 'tanggal_mulai',
                    },
                    {
                        data: 'tanggal_selesai',
                        name: 'tanggal_selesai',
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
                title: '@lang("umum.yakin")',
                text: "@lang('umum.hapus')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '@lang("umum.ya")',
                cancelButtonText: '@lang("umum.tidak")'
                }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = $(this).attr('href');
                }
                })
        })
         $('#filterKar').change(function(e){
            let table = $(".table").DataTable();
            table.destroy();
            read_data();
            $('.table').DataTable().state.clear();
            $('.table').DataTable().draw();
        })
        $('#bulan, #tahun').on('change',function(){
            let table = $(".table").DataTable();
            table.destroy();
            read_data();
            $('.table').DataTable().state.clear();
            $('.table').DataTable().draw();
        });
        $('#id_departemen').on('change',function(){
            let departement = $('#id_departemen').val();

            $.ajax({
                url: '{{ url("izin-cuti/data-karyawan-departemen") }}',
                type: "GET",
                data: { departement:departement } ,                
                success: function(res){
                    $('#filterKar').html(res.data);
                    let table = $(".table").DataTable();
                    table.destroy();
                    read_data();
                    $('.table').DataTable().state.clear();
                    $('.table').DataTable().draw();
                }
            });
        }); 
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


    $('body').on('click', '.editPost', function () {
      var id = $(this).data('id');    
      var id1 = $(this).data('role');    
      var id2 = $(this).data('karyawan');    
          $('#ajaxModelexa').modal('show');
          $('#id_izin').val(id);
          $('#id_role').val(id1);
          $('#id_karyawan').val(id2);
    });

    $('#setuju').click(function () {
        var id = $('#id_izin').val();
        var id_role = $('#id_role').val();
        var id_karyawan = $('#id_karyawan').val();
        var konfrim = 1;
        $.ajax({
            url: '{{ url("izin-cuti/persetujuan") }}',
            type: 'POST',
            data: { id:id, konfrim:konfrim, id_role:id_role, id_karyawan:id_karyawan},
            dataType: 'json',
          success: function (data) {
            //   console.log(data);
            $('#ajaxModelexa').modal('hide');
            // $('table').DataTable().draw(true);
            $(".table").DataTable().ajax.reload(null, false);
          },
      });
    }); 

    $('#tolak').click(function () {
        var id = $('#id_izin').val();
        var id_role = $('#id_role').val();
        var id_karyawan = $('#id_karyawan').val();
        var konfrim = 2;
        $.ajax({
            url: '{{ url("izin-cuti/persetujuan") }}',
            type: 'POST',
            data: { id:id, konfrim:konfrim, id_role:id_role, id_karyawan:id_karyawan },
            dataType: 'json',
          success: function (data) {
              
              $('#ajaxModelexa').modal('hide');
            //   $('table').DataTable().draw(true);
              $(".table").DataTable().ajax.reload(null, false);
            //   console.log(data);
         
          },
      });
    }); 

</script>
@endpush