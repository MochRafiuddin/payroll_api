@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  





$name[0] = 'nik'; 
$name[] = 'nama_karyawan';
$name[] = 'id_departemen';
$name[] = 'id_bank';
$name[] = 'id_shift';
$name[] = 'no_rekening';
$name[] = 'jk';
$name[] = 'nama_rekening';
$name[] = 'tanggal_lahir';
$name[] = 'id_status_karyawan';
$name[] = 'tipe_gajian';
$name[] = 'id_agama';
$name[] = 'tanggal_masuk';
$name[] = 'id_status_kawin'; //
$name[] = 'tanggal_akhir_kontrak';
$name[] = 'metode_pph21'; // -> 1 GROSS, 2 NET
$name[] = 'alamat';
$name[] = 'status_npwp'; // -> 0 tidak punya, 1 punya
$name[] = 'no_npwp';
$name[] = 'status_bjps_kes'; // -> 0 tidak,1 ya
$name[] = 'no_telp';
$name[] = 'email';
$name[] = 'kode_fingerprint';
$name[] = 'aktif'; // -> 1 aktif, 0 non aktif
// $name[] = 'set_gaji'; // def 0 -> 0 belum, 1 sudah
$name[] = 'limit_asuransi'; // def 0 -> 0 belum, 1 sudah
$name[] = 'id_jabatan'; // def 0 -> 0 belum, 1 sudah
$name[] = 'id_grup_karyawan'; 
$name[] = 'employee_id'; 
$name[] = 'no_bpjs'; 
$name[] = 'max_izin';
$name[] = 'id_departemen_label';

//dd(old($name[3]));
//dd(old($name[19]) == 0);
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} Karyawan</h6>
                <form action="{{$url}}" method="post" id="form">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">NIK</label>
                                <input type="text" class="form-control @error($name[0]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[0])}}" name="{{$name[0]}}" />
                            <input type="hidden" name="filter" value="{{$filter}}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Employee ID</label>
                                <input type="text" class="form-control @error($name[27]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[27])}}" name="{{$name[27]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">No BPJS</label>
                                <input type="text" class="form-control @error($name[28]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[28])}}" name="{{$name[28]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Nama</label>
                                <input type="text" class="form-control @error($name[1]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[1])}}" name="{{$name[1]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group">
                               <label for="exampleInputEmail1">@lang('umum.departemen_proses')</label>
                               <select class="form-control @error($name[2]) is-invalid @enderror"
                                   name="{{$name[2]}}">
                                   <option value="" selected disabled> Pilih Departement </option>
                                   @foreach($departement as $key)
                                   <option value="<?= $key->{$name[2]} ?>"
                                       {{(old($name[2]) == $key->{$name[2]}) ? 'selected' : ''}}
                                       {{Helper::showDataSelected($data,$name[2],$key->{$name[2]})}}>
                                       {{$key->nama_departemen}}
                                   </option>
                                   @endforeach
                               </select>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group">
                               <label for="exampleInputEmail1">@lang('umum.departemen_label')</label>
                               <select class="form-control @error($name[30]) is-invalid @enderror"
                                   name="{{$name[30]}}">
                                   <option value="" selected disabled> Pilih Departement</option>
                                   @foreach($departement as $key)
                                   <option value="{{$key->id_departemen}}" 
                                       {{(old($name[30]) == $key->id_departemen) ? 'selected' : ''}}
                                       {{Helper::showDataSelected($data,$name[30],$key->id_departemen)}}>
                                       {{$key->nama_departemen}}
                                   </option>
                                   @endforeach
                               </select>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group">
                               <label for="exampleInputEmail1">Bank</label>
                               <select class="form-control @error($name[3]) is-invalid @enderror"
                                   name="{{$name[3]}}">
                                   <option value="" selected disabled> Pilih Bank </option>
                                   @foreach($bank as $key)
                                   <option value="<?= $key->{$name[3]} ?>"
                                       {{(old($name[3]) == $key->{$name[3]}) ? 'selected' : ''}}
                                       {{Helper::showDataSelected($data,$name[3],$key->{$name[3]})}}>
                                       {{$key->nama_bank}}
                                   </option>
                                   @endforeach
                               </select>
                           </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Nomor Rekening</label>
                                <input type="text" class="form-control @error($name[5]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[5])}}" name="{{$name[5]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Jenis Kelamin</label>
                                <select class="form-control @error($name[6]) is-invalid @enderror"
                                    name="{{$name[6]}}">
                                    <option value="" selected disabled> Pilih Jenis Kelamin </option>

                                    <option value="L" {{(old($name[6]) == "L") ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[6],"L")}}>
                                        Laki-Laki
                                    </option>
                                    <option value="P" {{(old($name[6]) == "P") ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[6],"P")}}>
                                        Perempuan
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Jabatan</label>
                               <select class="form-control @error($name[25]) is-invalid @enderror" name="{{$name[25]}}">
                                   <option value="" selected disabled> Pilih Jabatan </option>
                                   @foreach($jabatan as $key)
                                   <option value="<?= $key->{$name[25]} ?>"
                                       {{(old($name[25]) == $key->{$name[25]}) ? 'selected' : ''}}
                                       {{Helper::showDataSelected($data,$name[25],$key->{$name[25]})}}>
                                       {{$key->nama_jabatan}}
                                   </option>
                                   @endforeach
                               </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Nama Rekening</label>
                                <input type="text" class="form-control @error($name[7]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[7])}}" name="{{$name[7]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Tanggal Lahir</label>
                                @php
                                    if($data != null){
                                        $hsl1=date('d-m-Y',strtotime(Helper::showDataDate($data,$name[8])));
                                    }else{
                                        $hsl1="";
                                    }
                                @endphp
                                <input type="text" class="form-control pickerdate @error($name[8]) is-invalid @enderror"
                                    value="{{ $hsl1 }}" name="{{$name[8]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Status Karyawan</label>
                                <select class="form-control @error($name[9]) is-invalid @enderror" name="{{$name[9]}}">
                                    <option value="" selected disabled> Pilih Status Karyawan </option>
                                    @foreach($status_karyawan as $key)
                                    <option value="<?= $key->{$name[9]} ?>"
                                        {{(old($name[9]) == $key->{$name[9]}) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[9],$key->{$name[9]})}}>
                                        {{$key->nama_status_karyawan}}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Tipe Gaji</label>
                                <select class="form-control @error($name[10]) is-invalid @enderror" name="{{$name[10]}}">
                                    <option value="" selected disabled> Pilih Tipe Gaji </option>
                                    
                                    <option value="1"
                                        {{(old($name[10]) == 1) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[10],1)}}>
                                        Bulanan
                                    </option>
                                    
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Agama</label>
                                <select class="form-control @error($name[11]) is-invalid @enderror" name="{{$name[11]}}">
                                    <option value="" selected disabled> Pilih Agama </option>
                                    @foreach($agama as $key)
                                    <option value="<?= $key->{$name[11]} ?>"
                                        {{(old($name[11]) == $key->{$name[11]}) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[11],$key->{$name[11]})}}>
                                        {{$key->nama_agama}}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Tanggal Masuk</label>
                                @php
                                    if($data != null){
                                        $hsl2=date('d-m-Y',strtotime(Helper::showDataDate($data,$name[12])));
                                    }else{
                                        $hsl2="";
                                    }
                                @endphp
                                <input type="text" class="form-control pickerdate @error($name[12]) is-invalid @enderror"
                                    value="{{$hsl2}}" name="{{$name[12]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Status Kawin</label>
                                <select class="form-control @error($name[13]) is-invalid @enderror" name="{{$name[13]}}">
                                    <option value="" selected disabled> Pilih Status Kawin</option>
                                    @foreach($status_kawin as $key)
                                    <option value="<?= $key->{$name[13]} ?>"
                                        {{(old($name[13]) == $key->{$name[13]}) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[13],$key->{$name[13]})}}>
                                        {{$key->nama_status_kawin}}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Tanggal Akhir Kontrak</label>
                                @php
                                    if($data != null){
                                        $hsl=date('d-m-Y',strtotime(Helper::showDataDate($data,$name[14])));
                                    }else{
                                        $hsl="";
                                    }
                                @endphp
                                <input type="text" class="form-control pickerdate @error($name[14]) is-invalid @enderror"
                                    value="{{$hsl}}" name="{{$name[14]}}" />
                            </div>
                        </div>
                        <input type="hidden" value="{{Helper::showDataDate($data,$name[15])}}" name="{{$name[15]}}">
                        <!-- <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Metode PPH21</label>
                                <select class="form-control @error($name[15]) is-invalid @enderror" name="{{$name[15]}}">
                                    <option value="" selected disabled> Pilih Metode PPH21 </option>

                                    <option value="1" {{(old($name[15]) == 1) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[15],1)}}>
                                        GROSS
                                    </option>
                                    <option value="2" {{(old($name[15]) == 2) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[15],2)}}>
                                        NET
                                    </option>
                                </select>
                            </div>
                        </div> -->
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Status NPWP</label>
                                <select class="form-control @error($name[17]) is-invalid @enderror"
                                    name="{{$name[17]}}">
                                    <option value="" selected disabled> Pilih Status NPWP </option>

                                    <option value="0" {{(old($name[17]) == 0) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[17],0)}}>
                                        Tidak Punya
                                    </option>
                                    <option value="1" {{(old($name[17]) == 1) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[17],1)}}>
                                        Punya
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Nomor NPWP</label>
                                @php
                                $disabled = 'disabled';
                                if($data != null){
                                    $disabled = ($data->{$name[17]} == 0) ? "disabled" : "";
                                }

                                @endphp
                                <input type="text" maxlength="20"
                                    class="form-control @error($name[18]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[18])}}" name="{{$name[18]}}"
                                     {{$disabled}}/>
                            </div>
                        </div>
                        <input type="hidden" name="{{$name[19]}}" value="{{Helper::showData($data,$name[19])}}">
                        <!-- <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Status BPJS</label>
                                <select class="form-control @error($name[19]) is-invalid @enderror"
                                    name="{{$name[19]}}">
                                    <option value="" selected disabled> Pilih Status BPJS </option>

                                    <option value="0" {{(old($name[19]) == 0) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[19],0)}}>
                                        Non Aktif
                                    </option>
                                    <option value="1" {{(old($name[19]) == 1) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[19],1)}}>
                                        Aktif
                                    </option>
                                </select>
                            </div>
                        </div> -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Nomor Telepon</label>
                                <input type="text" maxlength="13"
                                    class="form-control @error($name[20]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[20])}}" name="{{$name[20]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Email</label>
                                <input type="email"
                                    class="form-control @error($name[21]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[21])}}" name="{{$name[21]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Kode Finger Print</label>
                                <input type="text" maxlength="13"
                                    class="form-control @error($name[22]) is-invalid @enderror"
                                    value="{{Helper::showData($data,$name[22])}}" name="{{$name[22]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Aktif</label>
                                <select class="form-control @error($name[23]) is-invalid @enderror"
                                    name="{{$name[23]}}">
                                    <option value="" selected disabled> Pilih Aktif </option>

                                    <option value="0" {{(old($name[23]) == 0) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[23],0)}}>
                                        Non Aktif
                                    </option>
                                    <option value="1" {{(old($name[23]) == 1) ? 'selected' : ''}}
                                        {{Helper::showDataSelected($data,$name[23],1)}}>
                                        Aktif
                                    </option>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Set Gaji</label>
                                <select class="form-control @error($name[24]) is-invalid @enderror"
                                    name="{{$name[24]}}">
                                    <option value="" selected disabled> Pilih Set Gaji </option>

                                    <option value="0" {{(old($name[24]) == 0) ? 'selected' : ''}}
                                        {{Helper::showDataSelected2($data,$name[24],0)}}>
                                        Belum
                                    </option>
                                    <option value="1" {{(old($name[24]) == 1) ? 'selected' : ''}}
                                        {{Helper::showDataSelected2($data,$name[24],1)}}>
                                        Sudah
                                    </option>
                                </select>
                            </div>
                        </div> -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Limit Asuransi</label>
                                <input type="text" class="form-control numeric @error($name[24]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[24])}}" name="{{$name[24]}}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group">
                               <label for="{{ $name[26] }}">Grup Karyawan</label>
                               <select class="form-control @error($name[26]) is-invalid @enderror"
                                   name="{{$name[26]}}">
                                   <option value="" selected disabled> Pilih Grup </option>
                                   @foreach($grup_karyawan as $key)
                                   <option value="<?= $key->{$name[26]} ?>"
                                       {{(old($name[26]) == $key->{$name[26]}) ? 'selected' : ''}}
                                       {{Helper::showDataSelected($data,$name[26],$key->{$name[26]})}}>
                                       {{$key->nama_grup}}
                                   </option>
                                   @endforeach
                               </select>
                               <span class="text-default font-italic text-small">*Grup karyawan akan berpengaruh saat mengatur shift karyawan</span>
                           </div>
                        </div>
                        <div class="col-md-4">
                   
                            <div class="form-group">
                                <label for="exampleInputEmail1">Alamat</label>
                                <textarea type="text" class="form-control @error($name[16]) is-invalid @enderror"
                                    cols="5" rows="6" value=""
                                    name="{{$name[16]}}">{{Helper::showData($data,$name[16])}}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Max Cuti</label>
                                <input type="text" class="form-control @error($name[29]) is-invalid @enderror"
                                value="{{Helper::showData($data,$name[29])}}" name="{{$name[29]}}" />
                            </div>
                        </div>
                    </div>
                     
                    <input type="button" class="btn btn-success" value="Simpan" id="submit"/>

                    <!-- Modal starts -->
                      <div class="modal fade" id="modalTanggal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel-3" aria-hidden="true">
                        <div class="modal-dialog modal-md" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="exampleModalLabel-3">Atur shift karyawan</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <p id="notif_modal"></p>
                              <input type="text" class="form-control" name="daterange">
                            </div>
                            <div class="modal-footer">
                              <input type="button" class="btn btn-success" value="Simpan" onclick="submitData()"/>                            
                              <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <!-- Modal Ends -->

                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('js')
<script src="{{asset('/')}}assets/vendors/daterangepicker/daterangepicker.min.js"></script>
<script src="{{asset('/')}}assets/vendors/daterangepicker/moment.min.js"></script>
<script>
    $(document).ready(function () {
        // $("select").prop('selectedIndex',0);
        $('input[name="daterange"]').daterangepicker({
            locale: {
                        format: 'DD/MM/YYYY'
                    }
        });
        $('select[name="status_npwp"]').change(function(e){
            var val = $(this).val();
            // alert(val);
            if(val == 0){
                $('input[name=no_npwp]').attr('disabled','disabled');
            }else if(val == 1){
                $('input[name=no_npwp]').removeAttr('disabled','disabled');
            }
        });
    });
    $(".pickerdate").datepicker( {
            format: "dd-mm-yyyy",
            orientation: "bottom"
        });

    $('#submit').click(function(e){
        let id_karyawan = '{{$id_karyawan}}';
        let id_grup_karyawan_lama = '{{$id_grup_karyawan["id_grup_karyawan"]}}';
        let id_grup_karyawan_baru = $('select[name="id_grup_karyawan"]').val();
        let text = $('select[name="id_grup_karyawan"] option:selected').text();
        nama_grup_karyawan_lama = '{{$id_grup_karyawan["nama_grup"]}}';
        nama_grup_karyawan_baru = text.replace(/\s/g, '');
        // console.log(id_grup_karyawan_lama,id_grup_karyawan_baru)
        if (id_grup_karyawan_lama != id_grup_karyawan_baru) {

                $.ajax({
                    url: "{{url('karyawan/cek-shift-karyawan')}}",
                    type: "POST",
                    data:  {
                        id_karyawan : id_karyawan,
                    },
                    dataType: "JSON",
                    success: function(res){
                        
                        if (res){
                            let notif_modal = 'Anda akan mengubah grup karyawan ini dari grup '+nama_grup_karyawan_lama+' ke grup '+nama_grup_karyawan_baru+', silakan pilih tanggal shift yang ingin diubah.';
                            $('#notif_modal').text(notif_modal);
                            $('#modalTanggal').modal('show');
                        }else{
                            submitData();
                        }
                    
                    }
                });

        }else{
            submitData();
        }
        
    });

    function submitData(){
        $.ajax({
            url: "{{$url}}",
            type: "POST",
            data:  new FormData($('#form')[0]),
            contentType: false,
            cache: false,
            processData:false,
            success: function(res){
                if (res.error=='error') {
                    location.reload();
                }else{
                    window.location = "{{route('karyawan-index')}}";
                }
            }
        });
    }

</script>

@endpush