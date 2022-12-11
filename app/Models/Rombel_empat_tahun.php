<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rombel_empat_tahun extends Model
{
    use HasFactory;
    public $incrementing = false;
	public $keyType = 'string';
	protected $table = 'rombel_4_tahun';
	protected $primaryKey = 'rombongan_belajar_id';
	protected $guarded = [];
}
