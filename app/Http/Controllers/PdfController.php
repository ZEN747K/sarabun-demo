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
            $this->signature = url('/storage/users/'.auth()->user()->signature);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $sort = $request->query('sort', 'name_asc');
        $limit = $request->query('limit', 10); // จำนวนไฟล์ต่อหน้า
        $page = $request->query('page', 1); // หน้าปัจจุบัน
        $storagePath = public_path('storage');
        $files = [];

        if (File::exists($storagePath)) {
            $directories = File::directories($storagePath);
            foreach ($directories as $dir) {
                if (is_numeric(basename($dir))) {
                    $pdfs = File::allFiles($dir);
                    foreach ($pdfs as $pdf) {
                        if (strtolower($pdf->getExtension()) === 'pdf') {
                            $relativePath = str_replace($storagePath.DIRECTORY_SEPARATOR, '', $pdf->getPathname());

                            // ค้นหา Book ID โดยใช้ชื่อไฟล์ PDF
                            $book = Book::where('file', 'like', '%'.$pdf->getFilename().'%')->first();
                            $bookId = $book ? $book->inputBookregistNumber : null;
                            $subject = $book ? $book->inputSubject : null;

                            // หากไม่พบในตาราง books ให้ค้นหาใน log_status_books
                            if (!$bookId) {
                                $log = Log_status_book::where('file', 'like', '%'.$pdf->getFilename().'%')->first();
                                if ($log) {
                                    $book = Book::find($log->book_id);
                                    $bookId = $book ? $book->inputBookregistNumber : null;
                                    $subject = $book ? $book->inputSubject : null;
                                }
                            }

                            $files[] = [
                                'name' => $pdf->getFilename(),
                                'url' => asset('storage/'.str_replace('\\', '/', $relativePath)),
                                'time' => $pdf->getMTime(),
                                'book_id' => $bookId,
                                'subject' => $subject,
                            ];
                        }
                    }
                }
            }
        }

        switch ($sort) {
            case 'date_desc':
            case 'date':
                usort($files, fn ($a, $b) => $b['time'] <=> $a['time']);
                break;
            case 'date_asc':
                usort($files, fn ($a, $b) => $a['time'] <=> $b['time']);
                break;
            case 'book_id_desc':
                usort($files, fn ($a, $b) => ($b['book_id'] ?? 0) <=> ($a['book_id'] ?? 0));
                break;
            case 'book_id_asc':
                usort($files, fn ($a, $b) => ($a['book_id'] ?? 0) <=> ($b['book_id'] ?? 0));
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

        // คำนวณการแบ่งหน้า
        $totalFiles = count($files);
        $offset = ($page - 1) * $limit;
        $files = array_slice($files, $offset, $limit);

        $data['permission_data'] = $this->permission_data;
        $data['function_key'] = 'deepdetail';
        $data['files'] = $files;
        $data['sort'] = $sort;
        $data['limit'] = $limit;
        $data['page'] = $page;
        $data['totalPages'] = ceil($totalFiles / $limit); // จำนวนหน้าทั้งหมด

        return view('pdf.index', $data);
    }
}