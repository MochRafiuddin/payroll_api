<?php 
    use App\Traits\Helper;  
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            @if (Helper::can_akses('dashboard'))
            <a class="nav-link" href="{{url('dashboard')}}">
                <i class="mdi mdi-view-dashboard-outline menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
            @endif
        </li>
        <li class="nav-item">
            @if (Helper::can_akses('dashboard_karyawan'))
            <a class="nav-link" href="{{url('dashboard/karyawan')}}">
                <i class="mdi mdi-view-dashboard-outline menu-icon"></i>
                <span class="menu-title">Dashboard Karyawan</span>
            </a>
            @endif
        </li>
        <li><hr></li>
        @if(Helper::can_akses('referensi_agama_list')!=null||Helper::can_akses('referensi_bank_list')!=null||Helper::can_akses('referensi_departement_list')!=null||Helper::can_akses('referensi_jabatan_list')!=null||Helper::can_akses('referensi_status_karyawan_list'))
        <li class="nav-item {{ in_array(Request::segment(1), ['agama','bank','departement','jabaran','status_karyawan']) ? 'active' : '' }}">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                <i class="mdi mdi-puzzle-outline menu-icon"></i>
                <span class="menu-title">Referensi</span>
                <i class="menu-arrow"></i>
            </a> 
            <div class="collapse {{ in_array(Request::segment(1), ['agama','bank','departement','jabaran','status_karyawan']) ? 'show' : '' }}" id="ui-basic">
                <ul class="nav flex-column sub-menu">
                
                    @if (Helper::can_akses('referensi_agama_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('agama-index')}}">Agama</a></li>
                    @endif
                    @if (Helper::can_akses('referensi_bank_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('bank-index')}}">Bank</a></li>
                    @endif
                    @if (Helper::can_akses('referensi_departement_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('departement-index')}}">Departement</a></li>
                    @endif
                    @if (Helper::can_akses('referensi_jabatan_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('jabatan-index')}}">Jabatan</a></li>
                    @endif
                    @if (Helper::can_akses('referensi_status_karyawan_list'))                    
                    <li class="nav-item"> <a class="nav-link" href="{{route('status-karyawan-index')}}">Status Karyawan</a></li>                 
                    @endif
                </ul>
            </div>
        </li>
        @endif
        @if(Helper::can_akses('master_shift_list')!=null||Helper::can_akses('master_status_kawin_list')!=null||Helper::can_akses('master_grup_karyawan_list')!=null||Helper::can_akses('master_karyawan_list')!=null)
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-advanced" aria-expanded="false" aria-controls="ui-advanced">
                <i class="mdi mdi-puzzle-outline menu-icon"></i>
                <span class="menu-title">Master</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-advanced">
                <ul class="nav flex-column sub-menu">
                    @if (Helper::can_akses('master_shift_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('shift-index')}}">Shift</a></li>
                    @endif
                    @if (Helper::can_akses('master_status_kawin_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('status-kawin-index')}}">Status Kawin</a></li>
                    @endif                    
                    @if (Helper::can_akses('master_grup_karyawan_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('grup-karyawan-index')}}">Grup Karyawan</a></li>
                    @endif
                    @if (Helper::can_akses('master_karyawan_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('karyawan-index')}}">Karyawan</a></li>
                    @endif
                </ul>
            </div>
        </li>
        @endif
        @if(Helper::can_akses('konfigurasi_tarif_PPH_list')!=null||Helper::can_akses('konfigurasi_tarif_lembur_list')!=null)
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#konfigurasi" aria-expanded="false" aria-controls="konfigurasi">
                <i class="mdi mdi-puzzle-outline menu-icon"></i>
                <span class="menu-title">Konfigurasi</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="konfigurasi">
                <ul class="nav flex-column sub-menu">
                    @if (Helper::can_akses('konfigurasi_tarif_PPH_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('tarif-pph-index')}}">Tarif PPH</a></li>
                    @endif
                    @if (Helper::can_akses('konfigurasi_tarif_lembur_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('tarif-lembur-index')}}">Tarif Lembur</a></li>
                    @endif
                </ul>
            </div>
        </li>
        <li><hr></li>
        @endif
        @if(Helper::can_akses('absensi_atur_shift_grup_karyawan_list')!=null||Helper::can_akses('absensi_atur_shift_karyawan_list')!=null||Helper::can_akses('absensi_data_fingerprint_list')!=null||Helper::can_akses('absensi_tipe_absensi_list')!=null||Helper::can_akses('absensi_izincuti_list')!=null||Helper::can_akses('absensi_lembur_karyawan_list')!=null)
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#absensi" aria-expanded="false" aria-controls="absensi">
                <i class="mdi mdi-puzzle-outline menu-icon"></i>
                <span class="menu-title">Absensi</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="absensi">
                <ul class="nav flex-column sub-menu">
                    @if(Helper::can_akses('absensi_atur_shift_grup_karyawan_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('atur-shift-grup-karyawan-index')}}">Atur Shift Grup Karyawan</a></li>
                    @endif
                    @if(Helper::can_akses('absensi_atur_shift_karyawan_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('atur-shift-karyawan-index')}}">Atur Shift Karyawan</a></li>
                    @endif
                    @if(Helper::can_akses('absensi_data_fingerprint_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('absen-index')}}">Data Fingerprint</a></li>
                    @endif
                    @if(Helper::can_akses('absensi_tipe_absensi_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('ref-tipe-absensi-index')}}">Tipe Absensi</a></li>
                    @endif
                    @if(Helper::can_akses('absensi_izincuti_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('izin-cuti-index')}}">Izin/Cuti</a></li>
                    @endif
                    @if(Helper::can_akses('absensi_lembur_karyawan_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('lembur-index')}}">Lembur Karyawan</a></li>
                    @endif
                </ul>
            </div>
        </li>
        @endif
        @if(Helper::can_akses('penggajian_periode_list')!=null||Helper::can_akses('penggajian_gaji_list')!=null||Helper::can_akses('penggajian_gaji_karyawan_list')!=null||Helper::can_akses('penggajian_gaji_karyawan_periode_list')!=null||Helper::can_akses('penggajian_asuransi_ekses_list')!=null||Helper::can_akses('penggajian_approval_total_gaji_list')!=null)
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#penggajian" aria-expanded="false" aria-controls="penggajian">
                <i class="mdi mdi-puzzle-outline menu-icon"></i>
                <span class="menu-title">Penggajian</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="penggajian">
                <ul class="nav flex-column sub-menu">
                    @if(Helper::can_akses('penggajian_periode_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('periode-index')}}">Periode</a></li>
                    @endif
                    @if(Helper::can_akses('penggajian_gaji_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('gaji-index')}}">Gaji</a></li>
                    @endif                
                    @if(Helper::can_akses('penggajian_gaji_karyawan_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('gaji-pegawai-index')}}">Gaji Karyawan</a>
                    @endif
                    @if(Helper::can_akses('penggajian_gaji_karyawan_periode_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('gaji-period-index')}}">Gaji Karyawan Periode</a></li>
                    @endif
                    @if(Helper::can_akses('penggajian_asuransi_ekses_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('asuransi-ekses-index')}}">Asuransi Ekses</a></li>
                    @endif
                    @if(Helper::can_akses('penggajian_approval_total_gaji_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('approval-gaji-index')}}">Approval Total Gaji</a></li>
                    @endif
                </ul>
            </div>
        </li>
        <li><hr></li>
        @endif
        @if(Helper::can_akses('riwayat_penggajian_list')!=null||Helper::can_akses('riwayat_absensi_karyawan_list')!=null||Helper::can_akses('riwayat_absensi_lembur_list')!=null||Helper::can_akses('riwayat_total_absensi_list')!=null)
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#riwayat" aria-expanded="false" aria-controls="riwayat">
                <i class="mdi mdi-puzzle-outline menu-icon"></i>
                <span class="menu-title">Riwayat</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="riwayat">
                <ul class="nav flex-column sub-menu">
                    @if(Helper::can_akses('riwayat_penggajian_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('riwayat-gaji-index')}}">Penggajian</a></li>
                    @endif
                    @if(Helper::can_akses('riwayat_absensi_karyawan_list'))                
                    <li class="nav-item"> <a class="nav-link" href="{{route('absensi-karyawan-view')}}">Absensi Karyawan</a></li>
                    @endif
                    @if(Helper::can_akses('riwayat_absensi_lembur_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('absensi-lembur-view')}}">Absensi Lembur</a></li>
                    @endif
                    @if(Helper::can_akses('riwayat_total_absensi_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('total-absensi-index')}}">Total Absensi</a></li>
                    @endif
                </ul>
            </div>
        </li>
        @endif
        @if(Helper::can_akses('Update_Bulk')!=null)
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#perbaikan_data" aria-expanded="false" aria-controls="setting">
                <i class="mdi mdi-puzzle-outline menu-icon"></i>
                <span class="menu-title">Perbaikan Data</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="perbaikan_data">
                <ul class="nav flex-column sub-menu">
                    @if(Helper::can_akses('Update_Bulk'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('marked')}}">Update Bulk</a></li>
                    @endif                    
                </ul>
            </div>
        </li>
        @endif
        @if(Helper::can_akses('setting_user_list')!=null||Helper::can_akses('setting_role_list')!=null)
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#setting" aria-expanded="false" aria-controls="setting">
                <i class="mdi mdi-puzzle-outline menu-icon"></i>
                <span class="menu-title">Setting</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="setting">
                <ul class="nav flex-column sub-menu">
                    @if(Helper::can_akses('setting_user_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('user-index')}}">User</a></li>
                    @endif
                    @if(Helper::can_akses('setting_role_list'))
                    <li class="nav-item"> <a class="nav-link" href="{{route('role-index')}}">Role</a></li>
                    @endif
                </ul>
            </div>
        </li>
        @endif
        
    </ul>
</nav>