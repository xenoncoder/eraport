<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Absensi extends Model
{
    use HasFactory, Uuid;
    public $incrementing = false;
    public $keyType = 'string';
	protected $table = 'absensi';
	protected $primaryKey = 'absensi_id';
	protected $guarded = [];
	public function anggota_rombel(){
		return $this->hasOne(Anggota_rombel::class, 'anggota_rombel_id', 'anggota_rombel_id');
	}
}
