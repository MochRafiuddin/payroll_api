<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_agama', function (Blueprint $table) {
            $table->id("id_agama");
            $table->string('nama_agama',100);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
        Schema::create('m_bank', function (Blueprint $table) {
            $table->id("id_bank");
            $table->string('nama_bank',100);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('m_departemen', function (Blueprint $table) {
            $table->id("id_departemen");
            $table->string('nama_departemen',100);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('m_shift', function (Blueprint $table) {
            $table->id("id_shift");
            $table->string('nama_shift',100);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('m_jabatan', function (Blueprint $table) {
            $table->id("id_jabatan");
            $table->string('nama_jabatan',100);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('m_status_karyawan', function (Blueprint $table) {
            $table->id("id_status_karyawan");
            $table->string('nama_status_karyawan',100);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('m_status_kawin', function (Blueprint $table) {
            $table->id("id_status_kawin");
            $table->string('kode_status_kawin',10);
            $table->string('nama_status_kawin',100);
            $table->double('nilai_ptkp',20,2);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('m_karyawan', function (Blueprint $table) {
            $table->id("id_karyawan");
            $table->string('nik',100);
            $table->integer('id_departemen');
            $table->integer('id_bank');
            $table->string('nama_karyawan',100);
            $table->integer('id_shift');
            $table->string('no_rekening',20);
            $table->string('jk',5);
            $table->integer('id_jabatan');
            $table->string('nama_rekening',100);    
            $table->date('tanggal_lahir');    
            $table->integer('id_status_karyawan');    
            $table->integer('tipe_gajian')->comment('type bulanan');    
            $table->integer('id_agama');    
            $table->date('tanggal_masuk');    
            $table->integer('id_status_kawin');    
            $table->date('tanggal_akhir_kontrak');    
            $table->integer('metode_pph21')->comment("1 GROSS, 2 NET");    
            $table->text('alamat');
            $table->text('status_npwp')->comment("0 tidak punya, 1 punya");
            $table->string('no_npwp',50);
            $table->integer('status_bjps_kes')->comment("0 tidak punya, 1 punya");
            $table->string('no_telp',20);
            $table->string('email',100);
            $table->string('kode_fingerprint',50);
            $table->integer('aktif')->comment("1 aktif, 0 non aktif");
            $table->integer('set_gaji')->comment("0 -> 0 belum, 1 sudah");

            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('m_gaji', function (Blueprint $table) {
            $table->id("id_gaji");
            $table->string('nama_gaji',10);
            $table->integer('id_jenis_gaji');
            $table->integer('periode_hitung')->comment("1 bulan, 2 hari");
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('ref_jenis_gaji', function (Blueprint $table) {
            $table->id("id_jenis_gaji");
            $table->string('nama_jenis_gaji',100);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('m_periode', function (Blueprint $table) {
            $table->id("id_periode");
            $table->integer('bulan');
            $table->integer('tahun');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('map_gaji_karyawan', function (Blueprint $table) {
            $table->id();
            $table->integer('id_karyawan');
            $table->integer('id_gaji');
            $table->double('nominal',20,2);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('map_gaji_karyawan_periode', function (Blueprint $table) {
            $table->id();
            $table->integer('id_periode');
            $table->integer('id_karyawan');
            $table->integer('id_gaji');
            $table->double('nominal',20,2);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('t_gaji_karyawan_periode', function (Blueprint $table) {
            $table->id();
            $table->integer('id_periode');
            $table->integer('id_karyawan');
            $table->integer('status_npwp')->comment("0 tidak punya, 1 punya");
            $table->integer('id_status_kawin');
            $table->integer('hari_hadir');
            $table->integer('hari_tidak_hadir');
            $table->integer('hari_sakit');
            $table->integer('hari_cuti');
            $table->integer('hari_terlambat');
            $table->integer('lama_kerja')->comment("tanggal masuk s/d current date");
            $table->double('jkm_perusahaan',20,2);
            $table->double('jkk_perusahaan',20,2);
            $table->double('jht_perusahaan',20,2);
            $table->double('jkn_perusahaan',20,2);
            $table->double('jpn_perusahaan',20,2);
            $table->double('jht_karyawan',20,2);
            $table->double('jkn_karyawan',20,2);
            $table->double('jpn_karyawan',20,2);
            $table->double('lembur',20,2);
            $table->double('bruto',20,2);
            $table->double('biaya_jabatan',20,2);
            $table->double('total_pengurang',20,2);
            $table->double('netto',20,2);
            $table->double('netto_setahun',20,2);
            $table->double('ptkp',20,2);
            $table->double('pkp',20,2);
            $table->double('pph21_setahun',20,2);
            $table->double('pph21_sebulan',20,2);
            $table->double('add_non_npwp',20,2);
            $table->integer('metode_pph21')->comment("1 GROSS, 2 NET");
            $table->double('pph21_nett',20,2);
            $table->double('gaji_bersih',20,2);
            $table->double('total_gaji_tunjangan',20,2)->comment("gaji + tunjangan");
            $table->double('total_potongan',20,2)->comment("semua potongan spti bpjs, pph21, potongan lain dll");
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('t_gaji_karyawan_periode_det', function (Blueprint $table) {
            $table->id();
            $table->integer('id_gaji_karyawan_periode');
            $table->integer('id_karyawan');
            $table->integer('id_gaji');
            $table->double('nominal',20,2);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        Schema::create('m_tarif_pph', function (Blueprint $table) {
            $table->id("id_tarif_pph");
            $table->double('batas_bawah',20,2);
            $table->double('batas_atas',20,2);
            $table->double('tarif')->comment("persen");
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->integer('deleted')->default(1);
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
