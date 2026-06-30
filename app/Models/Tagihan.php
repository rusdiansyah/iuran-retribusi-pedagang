<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function details() { return $this->hasMany(TagihanDetail::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
