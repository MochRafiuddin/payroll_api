@extends('template')
@section('content')
<?php 
use App\Traits\Helper;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>@lang('umum.data_gaji')</h4><br>
                <div class="row mb-4">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="filterKar">@lang('umum.periode_gaji')</label>
                            <select class="form-control " name="filter_periode" id="filterPer">
                               @foreach($periode as $a)
                                    <option value="{{$a->id_periode}}" {{ ($a->aktif == 1) ? 'selected' : '' }}>{{Helper::convertBulan($a->bulan)}} - {{$a->tahun}}</option>
                               @endforeach
                           </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table w-100" id="table-data">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>@lang('umum.periode_gaji')</th>                                        
                                        <th>@lang('umum.periode_gaji')</th>
                                        <th>@lang('umum.gaji_pokok')</th>
                                        <th>@lang('umum.lembur')</th>
                                        <th>@lang('umum.tunjangan')</th>
                                        <th>@lang('umum.jht')</th>
                                        <th>@lang('umum.jpn')</th>
                                        <th>@lang('umum.jkn')</th>
                                        <th>@lang('umum.pph21')</th>
                                        <th>@lang('umum.deduction')</th>
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
        <p id="textP">@lang('umum.apakah')</p>    
        <input type="hidden" name="id" id="id">
      </div>
      <div class="modal-footer">
              <div class="col-12">
                  <button type="button" class="btn btn-success float-left" id="setuju">@lang('umum.setuju')</button>
                  <button type="button" class="btn btn-danger float-right" id="tolak">@lang('umum.tolak')</button>
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
<script>
    $(document).ready(function () {
        read_data($('#filterPer').val());

        $('#filterPer').change(function(e){
            read_data($(this).val());
        })

        function read_data(periode) {
            $('#table-data').DataTable().destroy();
            $('#table-data').DataTable({
                processing: true,
                serverSide: true,

                "scrollX": true,
                ajax: {
                    url: '{{ url("total_gaji_periode/data") }}/'+periode,
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
                        data: 'periode',
                        name: 'periode',
                    },
                    {
                        data: 'nominal',
                        name: 'nominal',
                    },
                    {
                        data: 'gaji_pokok',
                        name: 'gaji_pokok',
                    },
                    {
                        data: 'lembur',
                        name: 'lembur',
                    },
                    {
                        data: 'tunjangan',
                        name: 'tunjangan',
                    },
                    {
                        data: 'jht_karyawan',
                        name: 'jht_karyawan',
                    },
                    {
                        data: 'jpn_karyawan',
                        name: 'jpn_karyawan',
                    },
                    {
                        data: 'jkn_karyawan',
                        name: 'jkn_karyawan',
                    },
                    {
                        data: 'pph21',
                        name: 'pph21',
                    },
                    {
                        data: 'deduction',
                        name: 'deduction',
                    },
                    {
                        data: 'approval',
                        name: 'approval',
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
    $('body').on('click', '.editPost', function () {
      var id = $(this).data('id');        
          $('#ajaxModelexa').modal('show');
          $('#id').val(id);
    });

    $('#setuju').click(function () {
        var id = $('#id').val();
        var konfrim = 1;
        $.ajax({
            url: '{{ url("total_gaji_periode/persetujuan") }}',
            type: 'post',
            data: { id:id, konfrim:konfrim },
            dataType: 'json',
          success: function (data) {
            //   console.log(data);
            $('#ajaxModelexa').modal('hide');
            $('table').DataTable().draw(true);
         
          },
      });
    }); 

    $('#tolak').click(function () {
        var id = $('#id').val();
        var konfrim = 2;
        $.ajax({
            url: '{{ url("total_gaji_periode/persetujuan") }}',
            type: 'post',
            data: { id:id, konfrim:konfrim },
            dataType: 'json',
          success: function (data) {
              
              $('#ajaxModelexa').modal('hide');
            $('table').DataTable().draw(true);
            //   console.log(data);
         
          },
      });
    });   

</script>
@endpush