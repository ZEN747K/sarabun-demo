<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Permission;
use App\Models\Position;

class PdfController extends Controller
{
    public $users;
    public $permission;
    public $permission_data;
    public $permission_id;
    public $position_id;
    public $position_name;
    public $signature;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->users = Auth::user();
            $sql = Permission::where('id', $this->users->permission_id)->first();
            $this->permission = explode(',', $sql->can_status);
            $this->permission_id = $this->users->permission_id;
            $this->permission_data = $sql;
            $sql = Position::where('id', $this->users->position_id)->first();
            $this->position_id = $this->users->position_id;
            $this->position_name = ($sql != null) ? $sql->position_name : '';
            $this->signature = url("/storage/users/" . auth()->user()->signature);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $sort = $request->query('sort', 'name');
        $storagePath = public_path('storage');
        $files = [];

        if (File::exists($storagePath)) {
            $directories = File::directories($storagePath);
            foreach ($directories as $dir) {
                if (is_numeric(basename($dir))) {
                    $pdfs = File::allFiles($dir);
                    foreach ($pdfs as $pdf) {
                        if (strtolower($pdf->getExtension()) === 'pdf') {
                            // สร้าง path แบบ relative จาก public/storage
                            $relativePath = str_replace($storagePath . DIRECTORY_SEPARATOR, '', $pdf->getPathname());
                            $files[] = [
                                'name' => $pdf->getFilename(),
                                'url' => asset('storage/' . str_replace('\\', '/', $relativePath)),
                                'time' => $pdf->getMTime(),
                            ];
                        }
                    }
                }
            }
        }

        if ($sort === 'date') {
            usort($files, fn($a, $b) => $b['time'] <=> $a['time']);
        } else {
            usort($files, fn($a, $b) => strcasecmp($a['name'], $b['name']));
        }

        $data['permission_data'] = $this->permission_data;
        $data['function_key'] = 'deepdetail';
        $data['files'] = $files;
        $data['sort'] = $sort;

        return view('pdf.index', $data);
    }
}