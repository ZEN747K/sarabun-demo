<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Log_status_book;
use App\Models\Book;

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
            $this->signature = url('/storage/users/' . auth()->user()->signature);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $sort = $request->query('sort', 'name_asc');
        $limit = $request->query('limit', 10);
        $page = (int) $request->query('page', 1);
        $query = strtolower(trim($request->query('q', ''))); // เพิ่มสำหรับค้นหา
        $storagePath = public_path('storage');
        $files = [];

        if (File::exists($storagePath)) {
            $directories = File::directories($storagePath);
            foreach ($directories as $dir) {
                if (is_numeric(basename($dir))) {
                    $pdfs = File::allFiles($dir);
                    foreach ($pdfs as $pdf) {
                        if (strtolower($pdf->getExtension()) === 'pdf') {
                            $relativePath = str_replace($storagePath . DIRECTORY_SEPARATOR, '', $pdf->getPathname());

                            $book = Book::where('file', 'like', '%' . $pdf->getFilename() . '%')->first();
                            $bookId = $book ? $book->inputBookregistNumber : null;
                            $subject = $book ? $book->inputSubject : null;

                            if (!$bookId) {
                                $log = Log_status_book::where('file', 'like', '%' . $pdf->getFilename() . '%')->first();
                                if ($log) {
                                    $book = Book::find($log->book_id);
                                    $bookId = $book ? $book->inputBookregistNumber : null;
                                    $subject = $book ? $book->inputSubject : null;
                                }
                            }

                            $files[] = [
                                'name' => $pdf->getFilename(),
                                'url' => asset('storage/' . str_replace('\\', '/', $relativePath)),
                                'time' => $pdf->getMTime(),
                                'book_id' => $bookId,
                                'subject' => $subject,
                            ];
                        }
                    }
                }
            }
        }

        // 🔍 Filter by คำค้นหา (เลขหนังสือ หรือ หัวเรื่อง)
        if (!empty($query)) {
            $files = array_filter($files, function ($file) use ($query) {
                $book_id = strtolower($file['book_id'] ?? '');
                $subject = strtolower($file['subject'] ?? '');
                return str_contains($book_id, $query) || str_contains($subject, $query);
            });
        }

        // 🔃 Sorting
        switch ($sort) {
            case 'date_desc':
            case 'date':
                usort($files, fn ($a, $b) => $b['time'] <=> $a['time']);
                break;
            case 'date_asc':
                usort($files, fn ($a, $b) => $a['time'] <=> $b['time']);
                break;
            case 'book_id_desc':
                usort($files, fn ($a, $b) => ($b['book_id'] ?? '') <=> ($a['book_id'] ?? ''));
                break;
            case 'book_id_asc':
                usort($files, fn ($a, $b) => ($a['book_id'] ?? '') <=> ($b['book_id'] ?? ''));
                break;
            case 'name_desc':
                usort($files, fn ($a, $b) => strcasecmp($b['name'], $a['name']));
                break;
            case 'name_asc':
            case 'name':
            default:
                usort($files, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
                break;
        }

        // 📄 Pagination logic
        $totalFiles = count($files);
        $totalPages = 1;

        if ($limit === 'all') {
            $paginatedFiles = $files;
            $limit = 'all';
        } else {
            $limit = (int) $limit;
            $totalPages = ceil($totalFiles / $limit);
            $offset = ($page - 1) * $limit;
            $paginatedFiles = array_slice($files, $offset, $limit);
        }

        // ✅ ส่งไปยัง view
        return view('pdf.index', [
            'permission_data' => $this->permission_data,
            'function_key' => 'deepdetail',
            'files' => $paginatedFiles,
            'sort' => $sort,
            'limit' => $limit,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }
}
