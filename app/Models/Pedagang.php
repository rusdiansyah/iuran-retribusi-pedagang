<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedagang extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function lokasi() { return $this->belongsTo(Lokasi::class); }
    public function jenis() { return $this->belongsTo(Jenis::class); }
    public function zonasi() { return $this->belongsTo(Zonasi::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    
    public function tagihanDetails() { return $this->hasMany(TagihanDetail::class); }
    public function pembayarans() { return $this->hasMany(Pembayaran::class); }
}
