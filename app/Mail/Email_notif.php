<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Email_notif extends Mailable
{
    use Queueable, SerializesModels;
    
    public $nama_karyawan,$judul,$isi,$url;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nama_karyawan,$judul,$isi,$url)
    {
        $this->nama_karyawan = $nama_karyawan;
        $this->judul = $judul;
        $this->isi = $isi;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {          
        return $this->subject($this->judul)
                    ->view('email.mailView');
    }
}
