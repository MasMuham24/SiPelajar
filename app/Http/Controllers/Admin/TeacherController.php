<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TeacherController extends Controller
{
    /**
     * Download template for bulk import
     */
    public function downloadTemplate()
    {
        $headers = ['name','nip','gender','phone','address','classroom_id','is_homeroom'];
        $callback = function () use ($headers) {$file = fopen('php://output', 'w');fputcsv($file, $headers);fclose($file);};
        return response()->streamDownload($callback, 'teacher_template.csv',['Content-Type' => 'text/csv; charset=utf-8','Content-Disposition' => 'attachment; filename="teacher_template.csv"',]);
    }


    /**
     * Import teachers from CSV or Excel
     */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls']);
        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];
        if ($extension === 'xlsx' || $extension === 'xls') {
            try {
                $spreadsheet = IOFactory::load($path);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
            } catch (\Exception $e) {
                return redirect()->route('admin.teachers.index')->with('error','Gagal membaca file Excel: '.$e->getMessage());
            }
        } else {
            $handle = fopen($path,'r');
            if(!$handle){
                return redirect()->route('admin.teachers.index')->with('error','Gagal membuka file CSV.');
            }
            while(($row=fgetcsv($handle)) !== false){
                $rows[]=$row;
            }
            fclose($handle);
        }
        if(empty($rows)){
            return redirect()->route('admin.teachers.index')->with('error','File kosong.');
        }
        $header = array_map(function($h){
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/','',(string)$h)));
        },$rows[0]);
        
        $expectedHeaders = [
            'name',
            'nip',
            'gender',
            'phone',
            'address',
            'classroom_id',
            'is_homeroom'
        ];
        
        if(array_diff($expectedHeaders,$header)!==array()||array_diff($header,$expectedHeaders)!==array()){
            return redirect()->route('admin.teachers.index')->with('error',
                    'Format header tidak sesuai. Gunakan: '.implode(', ',$expectedHeaders)
                );
        }
        $importedCount=0;
        $errorRows=[];
        $rowNum=1;
        DB::beginTransaction();
        try{
            foreach(array_slice($rows,1) as $row){
                $rowNum++;
                if(empty(array_filter($row))){
                    continue;
                }
                
                if (count($header) > count($row)) {
                    $row = array_pad($row, count($header), null);
                } else if (count($header) < count($row)) {
                    $row = array_slice($row, 0, count($header));
                }
                
                $data=array_combine($header,$row);
                $data=array_map(function($val){
                    return trim((string)$val);
                },$data);
                if(empty($data['name'])|| empty($data['nip'])|| empty($data['gender'])){
                    $errorRows[]="Baris {$rowNum}: name, nip, gender wajib diisi.";
                    continue;
                }
                if(!in_array($data['gender'],['Laki-laki','Perempuan'])){
                    $errorRows[]="Baris {$rowNum}: Gender tidak valid.";
                    continue;
                }
                if(Teacher::where('nip',$data['nip'])->exists()){
                    $errorRows[]="Baris {$rowNum}: NIP sudah terdaftar.";
                    continue;
                }
                
                $isHomeroomStr = strtolower($data['is_homeroom'] ?? '');
                $isHomeroom = $isHomeroomStr === 'ya';
                $isNotHomeroom = $isHomeroomStr === 'tidak';
                
                if ($data['is_homeroom'] === '' || (!$isHomeroom && !$isNotHomeroom)) {
                    $errorRows[]="Baris {$rowNum}: is_homeroom tidak valid (Gunakan: Ya/Tidak).";
                    continue;
                }
                
                $classroomId = empty($data['classroom_id']) ? null : $data['classroom_id'];
                
                if ($isHomeroom) {
                    if ($classroomId === null) {
                        $errorRows[]="Baris {$rowNum}: Classroom ID wajib diisi untuk Wali Kelas.";
                        continue;
                    }
                    if(!\App\Models\Classroom::where('id',$classroomId)->exists()){
                        $errorRows[]="Baris {$rowNum}: Classroom ID {$classroomId} tidak ditemukan.";
                        continue;
                    }
                    if(Teacher::where('classroom_id',$classroomId)->exists()){
                        $errorRows[]="Baris {$rowNum}: Classroom ID {$classroomId} sudah memiliki wali kelas.";
                        continue;
                    }
                } elseif ($classroomId !== null && !\App\Models\Classroom::where('id',$classroomId)->exists()){
                    $errorRows[]="Baris {$rowNum}: Classroom ID {$classroomId} tidak ditemukan.";
                    continue;
                }

                $username=$this->generateUsername($data['name']);
                $user=User::create([
                    'name'=>$data['name'],
                    'username'=>$username,
                    'password'=>bcrypt('123456'),
                    'role'=>'guru',
                ]);
                Teacher::create([
                    'user_id'=>$user->id,
                    'nip'=>$data['nip'],
                    'gender'=>$data['gender'],
                    'phone'=>empty($data['phone']) ? null : $data['phone'],
                    'address'=>empty($data['address']) ? null : $data['address'],
                    'classroom_id'=> $isHomeroom ? $classroomId : null,
                ]);
                $importedCount++;
            }
            if(count($errorRows)>0){
                DB::rollBack();
                return redirect()->route('admin.teachers.index')->with('error',implode('<br>',$errorRows));
            }
            DB::commit();
            return redirect()->route('admin.teachers.index')->with('success',"Berhasil mengimpor {$importedCount} data guru.");
        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('admin.teachers.index')->with('error',$e->getMessage());
        }

    }

    public function index()
    {

        $teachers = Teacher::with([
            'user',
            'classroom'
        ])->when(request('search'),function($query){
            $search=request('search');
            $query->where(function($q)use($search){
                $q->where('nip','like',"%{$search}%")->orWhereHas('user',function($user)use($search){
                    $user->where('name','like',"%{$search}%");
                });
            });
        })->latest()->paginate(10)->withQueryString();
        return view('admin.teachers.index',compact('teachers'));
    }

    public function create()
    {
        $classrooms=Classroom::all();
        return view('admin.teachers.create',compact('classrooms'));
    }

    public function store(TeacherRequest $request)
    {
        $data=$request->validated();
        try{
            DB::transaction(function()use($data,$request){
                $username=$this->generateUsername($data['name']);
                $user=User::create([
                    'name'=>$data['name'],
                    'username'=>$username,
                    'password'=>bcrypt('123456'),
                    'role'=>'guru',
                ]);
                $data['user_id']=$user->id;
                if($request->hasFile('photo')){
                    $data['photo']=$request->file('photo')->store('teachers','public');
                }
                Teacher::create($data);
            });
            return redirect()->route('admin.teachers.index')->with('success','Data guru berhasil ditambahkan.');
        }catch(QueryException $e){
            throw $e;
        }
    }

    protected function generateUsername(string $name, ?int $excludeUserId=null):string
    {
        $words=preg_split('/\s+/',trim($name));
        $username=strtolower(implode('',array_slice($words,0,2)));
        $counter=1;
        $originalUsername=$username;
        while(User::where('username',$username)->when($excludeUserId,function($q)use($excludeUserId){
            $q->where('id','!=',$excludeUserId);
        })->exists()){
            $username=$originalUsername.$counter;
            $counter++;
        }
        return $username;
    }

    public function show(Teacher $teacher)
    {
        $teacher->load([
            'user',
            'classroom'
        ]);
        return view('admin.teachers.show',compact('teacher'));
    }
    
    public function edit(Teacher $teacher)
    {
        $classrooms=Classroom::all();
        return view('admin.teachers.edit',compact('teacher','classrooms'));
    }

    public function update(TeacherRequest $request,Teacher $teacher)
    {
        $data=$request->validated();
        DB::transaction(function()use($data,$request,$teacher){
            if($request->hasFile('photo')){
                if($teacher->photo){
                    Storage::disk('public')->delete($teacher->photo);
                }
                $data['photo']=$request->file('photo')->store('teachers','public');
            }
            $teacher->update($data);
            $teacher->user()->update([
                'name'=>$data['name'],
                'username'=>$this->generateUsername($data['name'],$teacher->user->id)
            ]);
        });
        return redirect()->route('admin.teachers.index')->with('success','Data guru berhasil diperbarui.');
    }




    public function destroy(Teacher $teacher)
    {
        if($teacher->photo){
            Storage::disk('public')->delete($teacher->photo);
        }
        $teacher->user()->delete();
        $teacher->delete();
        return redirect()->route('admin.teachers.index')->with('success','Data guru berhasil dihapus.');
    }

    public function bulkDestroy()
    {
        $rawIds = request()->input('ids', '');
        $ids = is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds);
        $ids = array_filter(array_map('trim', $ids));
        
        if (empty($ids)) {
            return redirect()->route('admin.teachers.index')->with('error', 'Pilih minimal 1 guru untuk dihapus.');
        }

        $teachers = Teacher::whereIn('id', $ids)->get();
        foreach ($teachers as $teacher) {
            if($teacher->photo){
                Storage::disk('public')->delete($teacher->photo);
            }
            $teacher->user()->delete();
            $teacher->delete();
        }

        return redirect()->route('admin.teachers.index')->with('success', 'Berhasil menghapus ' . count($ids) . ' guru.');
    }
}
