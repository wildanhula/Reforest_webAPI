<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pohonku extends Model
{
    use HasFactory;

    protected $table = 'pohonku';

    // Tentukan nama tabel jika tidak mengikuti konvensi Laravel (plural dari nama model)
    // protected $table = 'nama_tabel_pohonku_anda';

    // Tentukan kolom-kolom yang bisa diisi secara massal (mass assignable)
    protected $fillable = [
        'namaPohon',
        'jenis_pohon',
        'tanggal_tanam',
        'lat',
        'long',
        'user_id', // Pastikan user_id ada di fillable jika diisi dari form
        'gambarUrl', // Jika Anda menyimpan URL gambar
    ];

    /**
     * Definisikan relasi 'user'.
     * Sebuah Pohonku belongs to (dimiliki oleh) satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
        // Secara default, Laravel akan mencari kolom 'user_id' di tabel 'pohonku'
        // Jika nama kolom foreign key Anda berbeda (misal 'owner_id'), Anda bisa menentukannya:
        // return $this->belongsTo(User::class, 'nama_kolom_foreign_key_anda');
    }

    // Anda bisa menambahkan relasi lain di sini jika diperlukan,
    // misalnya relasi ke lokasi, dll.
}