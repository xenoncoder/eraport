<?php

namespace App\Http\Livewire\Perencanaan;

use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithPagination;
use Livewire\Component;
use App\Models\Rencana_penilaian;
use App\Models\Rombongan_belajar;
use App\Models\Pembelajaran;
use App\Models\Kompetensi_dasar;
use App\Models\Teknik_penilaian;
use App\Models\Kd_nilai;

class Pengetahuan extends Component
{
    use WithPagination, LivewireAlert;
    protected $paginationTheme = 'bootstrap';
    public $search = '';
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function loadPerPage(){
        $this->resetPage();
    }
    public $sortby = 'created_at';
    public $sortbydesc = 'DESC';
    public $per_page = 10;
    public $nama = 'Pengetahuan';
    public $data_rombongan_belajar;
    public $data_pembelajaran;
    public $semester_id;
    public $tingkat;
    public $rombongan_belajar_id;
    //public $mata_pelajaran_id;
    public $pembelajaran_id;
    public $kompetensi_id = 1;
    public $data_kd = [];
    public $placeholder = 'UH/UTS/UAS/Kinerja/Proyek/Portofolio';
    public $data_bentuk_penilaian;
    public $nama_penilaian = [];
    public $bentuk_penilaian = [];
    public $bobot_penilaian = [];
    public $kd_select = [];
    public $keterangan_penilaian = [];
    public $rencana;
    public $show = FALSE;
    /*protected $rules = [
        'bentuk_penilaian.*' => 'required',
    ];
    protected $messages = [
        'bentuk_penilaian.*.required' => 'Teknik Penilaian tidak boleh kosong!!',
    ];*/
    protected $listeners = ['cancel'];
    public function render()
    {
        $breadcrumbs = [
            ['link' => "/", 'name' => "Beranda"], 
            ['link' => '#', 'name' => 'Perencanaan'], 
            ['name' => "Penilaian Pengetahuan"]
        ];
        if(!status_penilaian()){
            return view('components.non-aktif', [
                'breadcrumbs' => $breadcrumbs,
            ]);
        }
        $this->semester_id = session('semester_id');
        $callback = function($query){
            $query->whereHas('rombongan_belajar', function($query){
                $query->whereHas('kurikulum', function($query){
                    $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                });
            });
			$query->with(['rombongan_belajar' => function($query){
                $query->select('rombongan_belajar_id', 'nama');
            }]);
			$query->where('sekolah_id', session('sekolah_id'));
			$query->where('guru_id', $this->loggedUser()->guru_id);
			$query->where('semester_id', session('semester_aktif'));
			$query->orWhere('guru_pengajar_id', $this->loggedUser()->guru_id);
			$query->where('sekolah_id', session('sekolah_id'));
			$query->where('semester_id', session('semester_aktif'));
            $query->whereNotNull('kelompok_id');
            $query->whereNotNull('no_urut');
		};
        return view('livewire.perencanaan.pengetahuan', [
            'collection' => Rencana_penilaian::with(['pembelajaran' => $callback, 'teknik_penilaian' => function($query){
                $query->select('teknik_penilaian_id', 'nama');
            }])->where(function($query) use ($callback){
                $query->where('kompetensi_id', $this->kompetensi_id);
                $query->whereHas('pembelajaran', $callback);
            })
            ->withCount('kd_nilai')
            ->orderBy($this->sortby, $this->sortbydesc)
                ->when($this->search, function($query) {
                    $query->where('nama_penilaian', 'ILIKE', '%' . $this->search . '%')
                    //->orWhere('pembelajaran.nama_mata_pelajaran', 'ILIKE', '%' . $this->search . '%');
                    ->orWhereIn('pembelajaran_id', function($query){
                        $query->select('pembelajaran_id')
                        ->from('pembelajaran')
                        ->whereNotNull('kelompok_id')
                        ->whereNotNull('no_urut')
                        ->where('sekolah_id', session('sekolah_id'))
                        ->where('nama_mata_pelajaran', 'ILIKE', '%' . $this->search . '%');
                    });
            })->paginate($this->per_page),
            'breadcrumbs' => $breadcrumbs,
            'tombol_add' => [
                'wire' => 'addModal',
                'color' => 'primary',
                'text' => 'Tambah Data',
            ],
            /*
            [
                'color' => 'success',
                'link' => 'textCase',
                'text' => 'New Text',
            ]
            */
        ]);
    }
    public function addModal(){
        $this->emit('showModal');
    }
    public function loggedUser(){
        return auth()->user();
    }
    public function updatedTingkat($value)
    {
        $this->reset(['rombongan_belajar_id', 'pembelajaran_id']);
        if($value){
            $this->data_rombongan_belajar = Rombongan_belajar::select('rombongan_belajar_id', 'nama')->where(function($query){
                $query->where('tingkat', $this->tingkat);
                $query->where('semester_id', session('semester_aktif'));
                $query->where('sekolah_id', session('sekolah_id'));
                $query->whereHas('pembelajaran', $this->kondisi());
            })->get();
            $this->dispatchBrowserEvent('data_rombongan_belajar', ['data_rombongan_belajar' => $this->data_rombongan_belajar]);
        }
    }
    public function updatedRombonganBelajarId($value){
        if($value){
            $this->data_pembelajaran = Pembelajaran::where($this->kondisi())->orderBy('mata_pelajaran_id', 'asc')->get();
            $this->dispatchBrowserEvent('data_pembelajaran', ['data_pembelajaran' => $this->data_pembelajaran]);
            $this->dispatchBrowserEvent('data_pembelajaran_copy', ['data_pembelajaran' => $this->data_pembelajaran]);
        }
    }
    public function updatedPembelajaranId($value){
        if($value){
            $pembelajaran = Pembelajaran::find($value);
            $this->data_kd = Kompetensi_dasar::where(function($query) use ($pembelajaran){
                $query->where('mata_pelajaran_id', $pembelajaran->mata_pelajaran_id);
                $query->where('kompetensi_id', $this->kompetensi_id);
                $query->where('kelas_'.$this->tingkat, 1);
                $query->where('aktif', 1);
            })->orderBy('id_kompetensi')->get();
            if($this->data_kd->count()){
                $this->show = TRUE;
            }
            $this->data_bentuk_penilaian = Teknik_penilaian::where('kompetensi_id', $this->kompetensi_id)->get();
        }
    }
    private function kondisi($copy = FALSE){
        return function($query) use ($copy){
            if($this->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', $this->rombongan_belajar_id);
            }
            if($copy){
                $query->where('mata_pelajaran_id', $this->rencana->pembelajaran->mata_pelajaran_id);
            }
            $query->where('guru_id', $this->loggedUser()->guru_id);
            $query->whereHas('rombongan_belajar', function($query){
                $query->whereHas('kurikulum', function($query){
                    $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                });
            });
            $query->whereNotNull('kelompok_id');
            $query->whereNotNull('no_urut');
            $query->orWhere('guru_pengajar_id', $this->loggedUser()->guru_id);
            if($copy){
                $query->where('mata_pelajaran_id', $this->rencana->pembelajaran->mata_pelajaran_id);
            }
            $query->whereHas('rombongan_belajar', function($query){
                $query->whereHas('kurikulum', function($query){
                    $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                });
            });
            $query->whereNotNull('kelompok_id');
            $query->whereNotNull('no_urut');
            if($this->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', $this->rombongan_belajar_id);
            }
        };
    }
    public function store(){
        foreach($this->nama_penilaian as $key => $nama_penilaian){
            if(isset($this->kd_select[$key])){
                $Rencana_penilaian = Rencana_penilaian::create([
                    'sekolah_id' => session('sekolah_id'),
                    'pembelajaran_id' => $this->pembelajaran_id,
                    'kompetensi_id' => $this->kompetensi_id,
                    'nama_penilaian' => $nama_penilaian,
                    'metode_id' => $this->bentuk_penilaian[$key],
                    'bobot' => $this->bobot_penilaian[$key],
                    'keterangan' => (isset($this->keterangan_penilaian[$key])) ? $this->keterangan_penilaian[$key] : NULL,
                    'last_sync' => now(),
                ]);
                foreach($this->kd_select[$key] as $kd_select){
                    Kd_nilai::create([
                        'sekolah_id' => session('sekolah_id'),
                        'rencana_penilaian_id' => $Rencana_penilaian->rencana_penilaian_id,
                        'kompetensi_dasar_id' => collect(explode('|', $kd_select))->first(),
                        'id_kompetensi' => collect(explode('|', $kd_select))->last()
                    ]);
                }
            }
        }
        $this->close();
    }
    private function resetInputFields(){
        $this->reset(['semester_id', 'tingkat', 'rombongan_belajar_id', 'pembelajaran_id', 'nama_penilaian', 'bentuk_penilaian', 'bobot_penilaian', 'data_kd', 'kd_select', 'keterangan_penilaian', 'rencana']);
    }
    public function cancel(){
        $this->resetInputFields();
    }
    public function close()
    {
        $this->resetInputFields();
        $this->emit('close-modal');
        $this->resetPage();
    }
    public function getID($rencana_penilaian_id, $query)
    {
        $this->emit($query);
        $this->rencana = Rencana_penilaian::with([
            'kd_nilai' => function($query){
                $query->select('kd_nilai_id', 'rencana_penilaian_id', 'kompetensi_dasar_id', 'id_kompetensi');
                $query->with(['kompetensi_dasar' => function($query){
                    $query->select('kompetensi_dasar_id', 'kompetensi_dasar', 'kompetensi_dasar_alias');
                }]);
            },
            'rombongan_belajar',
            'pembelajaran'
        ])->find($rencana_penilaian_id);
        $this->tingkat = $this->rencana->rombongan_belajar->tingkat;
        if($query == 'copyModal'){
            $this->data_rombongan_belajar = Rombongan_belajar::select('rombongan_belajar_id', 'nama')->where(function($query){
                $query->where('tingkat', $this->tingkat);
                $query->where('semester_id', session('semester_aktif'));
                $query->where('sekolah_id', session('sekolah_id'));
                $query->whereHas('pembelajaran', function($query){
                    $query->where($this->kondisi(TRUE));
                });
                $query->where('rombongan_belajar_id', '<>', $this->rencana->rombongan_belajar->rombongan_belajar_id);
            })->get();
            $this->dispatchBrowserEvent('data_rombongan_belajar_copy', ['data_rombongan_belajar' => $this->data_rombongan_belajar]);
        }
        if($query == 'editModal'){
            $this->data_kd = Kompetensi_dasar::where(function($query){
                $query->where('mata_pelajaran_id', $this->rencana->pembelajaran->mata_pelajaran_id);
                $query->where('kompetensi_id', $this->kompetensi_id);
                $query->where('kelas_'.$this->tingkat, 1);
                $query->where('aktif', 1);
            })->orderBy('id_kompetensi')->get();
            foreach ($this->rencana->kd_nilai as $item){
                $this->kd_select[$item->kompetensi_dasar_id] = $item->kompetensi_dasar_id;
            }
        }
    }
    public function delete(){
        if($this->rencana){
            $this->rencana->delete();
            $this->close();
            $this->alert('info', 'Rencana Penilaian Pengetahuan berhasil dihapus', [
                'position' => 'center',
                'allowOutsideClick' => false,
                'timer' => null,
                'toast' => false,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'onConfirmed' => 'ok',
            ]);
        } else {
            $this->rencana->delete();
            $this->close();
            $this->alert('info', 'Rencana Penilaian Pengetahuan gagal dihapus! Silahkan coba beberapa saat lagi', [
                'position' => 'center',
                'allowOutsideClick' => false,
                'timer' => null,
                'toast' => false,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'onConfirmed' => 'ok',
            ]);
        }
    }
    public function duplikasi(){
        $pembelajaran = Pembelajaran::where(function($query){
            $query->where('rombongan_belajar_id', $this->rombongan_belajar_id);
            $query->where('mata_pelajaran_id', $this->rencana->pembelajaran->mata_pelajaran_id);
        })->first();
        $Rencana_penilaian = Rencana_penilaian::create([
            'sekolah_id' => session('sekolah_id'),
            'pembelajaran_id' => $pembelajaran->pembelajaran_id,
            'kompetensi_id' => $this->kompetensi_id,
            'nama_penilaian' => $this->rencana->nama_penilaian,
            'metode_id' => $this->rencana->metode_id,
            'bobot' => $this->rencana->bobot,
            'keterangan' => $this->rencana->keterangan,
            'last_sync' => now(),
        ]);
        foreach($this->rencana->kd_nilai as $kd_nilai){
            Kd_nilai::create([
                'sekolah_id' => session('sekolah_id'),
                'rencana_penilaian_id' => $Rencana_penilaian->rencana_penilaian_id,
                'kompetensi_dasar_id' => $kd_nilai->kompetensi_dasar_id,
                'id_kompetensi' => $kd_nilai->id_kompetensi
            ]);
        }
        $this->close();
        if($Rencana_penilaian){
            $this->alert('info', 'Rencana Penilaian Pengetahuan berhasil di duplikasi', [
                'position' => 'center',
                'allowOutsideClick' => false,
                'timer' => null,
                'toast' => false,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'onConfirmed' => 'ok',
            ]);
        } else {
            $this->alert('info', 'Rencana Penilaian Pengetahuan gagal duplikasi! Silahkan coba beberapa saat lagi', [
                'position' => 'center',
                'allowOutsideClick' => false,
                'timer' => null,
                'toast' => false,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'onConfirmed' => 'ok',
            ]);
        }
    }
    public function perbaharui(){
        $kd_select = [];
        foreach($this->kd_select as $kd_id => $selected){
            if($selected){
                $kd_select[] = $kd_id;
                $kd = Kompetensi_dasar::find($kd_id);
                Kd_nilai::updateOrcreate([
                    'sekolah_id' => session('sekolah_id'),
                    'rencana_penilaian_id' => $this->rencana->rencana_penilaian_id,
                    'kompetensi_dasar_id' => $kd_id,
                    'id_kompetensi' => $kd->id_kompetensi
                ]);
            }
        }
        Kd_nilai::where('rencana_penilaian_id', $this->rencana->rencana_penilaian_id)->whereNotIn('kompetensi_dasar_id', $kd_select)->forceDelete();
        $this->alert('info', 'Berhasil', [
            'text' => 'Rencana Penilaian '.$this->nama.' berhasil di perbaharui',
            'position' => 'center',
            'allowOutsideClick' => false,
            'timer' => null,
            'toast' => false,
            'showConfirmButton' => true,
            'confirmButtonText' => 'OK',
            'onConfirmed' => 'confirmed',
        ]);
        $this->emit('close-modal');
    }
}
