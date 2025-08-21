@extends('include.main')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <form method="get" class="mb-3">
            <select name="sort" class="form-select w-auto d-inline" onchange="this.form.submit()">
                <option value="name_asc" {{ $sort == 'name_asc' ? 'selected' : '' }}>ชื่อไฟล์ น้อยไปมาก</option>
                <option value="name_desc" {{ $sort == 'name_desc' ? 'selected' : '' }}>ชื่อไฟล์ มากไปน้อ</option>
                <option value="date_desc" {{ $sort == 'date_desc' ? 'selected' : '' }}>วันที่ล่าสุด</option>
                <option value="date_asc" {{ $sort == 'date_asc' ? 'selected' : '' }}>วันที่เก่าสุด</option>
                <option value="book_id_desc" {{ $sort == 'book_id_desc' ? 'selected' : '' }}>หัวเรื่อง มากไปน้อย</option>
                <option value="book_id_asc" {{ $sort == 'book_id_asc' ? 'selected' : '' }}>หัวเรื่อง น้อยไปมาก</option>
            </select>

            <select name="limit" class="form-select w-auto d-inline ms-2" onchange="this.form.submit()">
                <option value="10" {{ $limit == 10 ? 'selected' : '' }}>แสดง 10 ไฟล์</option>
                <option value="50" {{ $limit == 50 ? 'selected' : '' }}>แสดง 50 ไฟล์</option>
                <option value="100" {{ $limit == 100 ? 'selected' : '' }}>แสดง 100 ไฟล์</option>
                <option value="200" {{ $limit == 200 ? 'selected' : '' }}>แสดง 200 ไฟล์</option>
                <option value="300" {{ $limit == 300 ? 'selected' : '' }}>แสดง 300 ไฟล์</option>
                <option value="1000" {{ $limit == 1000 ? 'selected' : '' }}>แสดง 1000 ไฟล์</option>
            </select>
        </form>

        <ul class="list-group" style="font-size: 20px;">
            @forelse($files as $file)
                <li class="list-group-item">
                    <a href="{{ $file['url'] }}" target="_blank" style="font-size: 40px; font-weight: 500;"  class="fa fa-file-pdf-o me-1" ></a>
                    <div class="small text-muted">
                        {{ \Carbon\Carbon::createFromTimestamp($file['time'])->format('d/m/Y H:i') }}
                        @if(!empty($file['book_id']))
                            <span class="ms-3">เลขหนังสือ: {{ $file['book_id'] }}</span>
                        @endif
                        @if(!empty($file['subject']))
                            <span class="ms-3">หัวเรื่อง: {{ $file['subject'] }}</span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="list-group-item">ไม่พบไฟล์ PDF</li>
            @endforelse
        </ul>

        {{-- Pagination --}}
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-3">

                {{-- ปุ่ม ก่อนหน้า --}}
                @if($page > 1)
                    <li class="page-item">
                        <a class="page-link" href="?sort={{ $sort }}&limit={{ $limit }}&page={{ $page - 1 }}">« ก่อนหน้า</a>
                    </li>
                @endif

                {{-- ปรับช่วงการแสดงผลหน้ากลาง --}}
                @php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                @endphp

                {{-- แสดงหน้าที่ 1 และ ... หากห่างเกิน --}}
                @if($startPage > 1)
                    <li class="page-item"><a class="page-link" href="?sort={{ $sort }}&limit={{ $limit }}&page=1">1</a></li>
                    @if($startPage > 2)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                @endif

                {{-- หน้าปัจจุบันรอบ ๆ --}}
                @for($i = $startPage; $i <= $endPage; $i++)
                    <li class="page-item {{ $i == $page ? 'active' : '' }}">
                        <a class="page-link" href="?sort={{ $sort }}&limit={{ $limit }}&page={{ $i }}">{{ $i }}</a>
                    </li>
                @endfor

                {{-- แสดง ... และหน้าสุดท้าย หากห่างเกิน --}}
                @if($endPage < $totalPages)
                    @if($endPage < $totalPages - 1)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    <li class="page-item"><a class="page-link" href="?sort={{ $sort }}&limit={{ $limit }}&page={{ $totalPages }}">{{ $totalPages }}</a></li>
                @endif

                {{-- ปุ่ม ถัดไป --}}
                @if($page < $totalPages)
                    <li class="page-item">
                        <a class="page-link" href="?sort={{ $sort }}&limit={{ $limit }}&page={{ $page + 1 }}">ถัดไป »</a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</div>

{{-- ป้องกัน pagination ล้นจอใน mobile --}}
<style>
    .pagination {
        flex-wrap: wrap;
    }
    .pagination .page-link {
        min-width: 40px;
        text-align: center;
    }
</style>
@endsection
