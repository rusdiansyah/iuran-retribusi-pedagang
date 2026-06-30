<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function pedagang() { return $this->belongsTo(Pedagang::class); }
    public function metode() { return $this->belongsTo(Metode::class); }
    public function details() { return $this->hasMany(PembayaranDetail::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
