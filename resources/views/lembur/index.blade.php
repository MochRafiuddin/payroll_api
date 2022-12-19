@extends('template')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
              <h4>@lang('umum.data_lembur')</h4><br>
                <div class="row">
                        <div class="form-group col-md-4">
                            <label for="">@lang('umum.karyawan')</label>
                             <select class="form-control js-example-basic-single" name="id_karyawan" id="id_karyawan"
                                     style="width:100%" data-maximum-selection-length="10">
                                 <option value="">@lang('umum.cari_karyawan')</option>
                                 @foreach($karyawan as $data)
                                     <option value="{{$data->id_karyawan}}" {{($id_karyawan_filter == $data->id_karyawan) ? 'selected' : ''}}>{{$data->nama_karyawan}}</option>
                                 @endforeach
                             </select>                
                        </div>                    
                        <div class="form-group col-md-4">
                            <label for="">@lang('umum.bulan')</label>
                            <input class="form-control pickerdatemonths" type="text" name="bulan" id="bulan" value="{{$bulan ?? date('m')}}">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="">@lang('umum.tahun')</label>
                            <input class="form-control pickerdateyear" type="text" name="tahun" id="tahun" value="{{$tahun ?? date('Y')}}">
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
                                        <th>@lang('umum.tanggal')</th>
                                        <th>@lang('umum.jumlah_jam')</th>
                                        <th>@lang('umum.alasan_lembur')</th>
                                        <th>@lang('umum.approval')</th>
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
      <form id="postForm" name="postForm">
      <div class="modal-body text-center">
        <p id="textP"></p>    
        <input type="hidden" name="karyawan_id" id="karyawan_id">
        <input type="hidden" name="filter" id="filter">
        <input type="hidden" name="tanggal" id="tanggal">
        <input type="hidden" name="approval" id="approval">
      </div>
      <div class="modal-footer">
          <div class="col-12">
              <button type="button" class="btn btn-success float-left" id="setuju">@lang('umum.setuju_btn')</button>
              <button type="button" class="btn btn-danger float-right" data-dismiss="modal">@lang('umum.keluar')</button>
          </div>
      </div>
      </form>
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
      $('.js-example-basic-single').select2({
          placeholder: "@lang('umum.cari_karyawan')",
          // maximumSelectionLength: 10
      });
       var bulan = '{{ $bulan }}';
       var tahun = '{{ $tahun }}';
       var id_karyawan_filter = '{{ $id_karyawan_filter }}';       

        read_data();
        if (id_karyawan_filter.length == 0) {
            $('.table').DataTable().state.clear();
            $('.table').DataTable().draw();
        }

        function read_data() {
            $('.table').DataTable({
                processing: true,
                serverSide: true,

                "scrollX": true,
                language: {
                    searchPlaceholder: "All Karyawan"
                },
                ajax: {
                    url: '{{ url("lembur/data") }}',
                    type: 'GET',
                    data: function (d) {
                        d.start_date = $('#bulan').val();
                        d.end_date = $('#tahun').val();
                        d.id_karyawan = $('#id_karyawan').val();
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
                        data: 'nama_karyawan',
                        name: 'm_karyawan.nama_karyawan',
                        
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal',
                        
                        searchable: false
                    },
                    {
                        data: 'total_jam',
                        name: 'total_jam',
                        
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'alasan',
                        name: 'alasan',
                        
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'approval',
                        name: 'approval',
                        
                        orderable: false,
                        searchable: false
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

        $('#bulan, #tahun, #id_karyawan').on('change',function(){
            let table = $(".table").DataTable();
            table.destroy();
            read_data();
            $('.table').DataTable().state.clear();
            $('.table').DataTable().draw();
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
      var tanggal = $(this).data('tanggal');    
      var karyawan = $(this).data('karyawan');    
      var filter = $(this).data('karyawanFilter');    
      var approval = $(this).data('approval'); 
          $('#ajaxModelexa').modal('show');
          $('#tanggal').val(tanggal);
          $('#karyawan_id').val(karyawan);
          $('#approval').val(approval);
          if (approval == 0) {
              $('#textP').html('@lang("umum.p_lembur")');
          }else{
              $('#textP').html('@lang("umum.p_batal_lembur")');
          }

    });

    $('#setuju').click(function (e) {
        $.ajax({
          data: $('#postForm').serialize(),
          url: "{{ url('lembur/persetujuan') }}",
          type: "POST",
          dataType: 'json',
          success: function (data) {
     
                $('#postForm').trigger("reset");
                $('#ajaxModelexa').modal('hide');
                // $('.table').DataTable().draw();
                // $(".table").DataTable().ajax.reload();
                $(".table").DataTable().ajax.reload(null, false);
         
          },
          error: function (data) {
              console.log('Error:', data);
              $('').html('Simpan');
          }
      });
    });

</script>
@endpush