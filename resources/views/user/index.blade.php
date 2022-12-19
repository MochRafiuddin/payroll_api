@extends('template')
@section('content')
<?php 
use App\Traits\Helper;
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4>Data Pengguna</h4><br>
                <div class="row mb-4">
                    <div class="col text-right">
                        @if(Helper::can_akses('setting_user_add'))
                            <a href="{{url('user/create')}}" class="btn btn-info">Tambah</a>
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
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
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
        <h5 class="modal-title" id="exampleModalLabel">Reset Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="postForm" name="postForm">
      <div class="modal-body">
        <div class="form-group col">
            <label for="exampleInputEmail1">Password</label>
            <input type="text" class="form-control" name="password" />
            <input type="hidden" class="form-control" name="id_role" id="id_role"/>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Keluar</button>
        <button type="button" class="btn btn-primary" id="savedata">Simpan</button>
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        read_data();

        function read_data() {
            $('table').DataTable({
                processing: true,
                serverSide: true,

                "scrollX": true,
                language: {
                    searchPlaceholder: "All Karyawan"
                },
                ajax: {
                    url: '{{ url("user/data") }}',
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
                        data: 'name',
                        name: 'name',                        
                    },
                    {
                        data: 'username',
                        name: 'username',                        
                    },
                    {
                        data: 'email',
                        name: 'email',                        
                    },
                    {
                        data: 'nama_role',
                        name: 'm_role.nama_role',                        
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
        })
    });

    $('body').on('click', '.editPass', function () {
      var id = $(this).data('id');    
          $('#ajaxModelexa').modal('show');
          $('#id_role').val(id);
    });

    $('#savedata').click(function (e) {
        $.ajax({
          data: $('#postForm').serialize(),
          url: "{{ url('user/reset-pass') }}",
          type: "POST",
          dataType: 'json',
          success: function (data) {
     
                $('#postForm').trigger("reset");
                $('#ajaxModelexa').modal('hide');
                $('table').DataTable().draw(true);
         
          },
          error: function (data) {
              console.log('Error:', data);
              $('#savedata').html('Simpan');
          }
      });
    });

</script>
@endpush