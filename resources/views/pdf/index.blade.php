@extends('include.main')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        {{-- 🔍 ฟอร์มค้นหา --}}
        <form method="get" class="mb-3 d-flex flex-wrap align-items-center gap-2">
            {{-- ช่องค้นหา --}}
            <input type="text" name="q" class="form-control w-auto" placeholder="ค้นหาเลขหนังสือหรือหัวเรื่อง" value="{{ request('q') }}">

            {{-- การเรียงลำดับ --}}
            <select name="sort" class="form-select w-auto" onchange="this.form.submit()">
                <option value="name_asc" {{ $sort == 'name_asc' ? 'selected' : '' }}>ชื่อไฟล์ น้อยไปมาก</option>
                <option value="name_desc" {{ $sort == 'name_desc' ? 'selected' : '' }}>ชื่อไฟล์ มากไปน้อย</option>
                <option value="date_desc" {{ $sort == 'date_desc' ? 'selected' : '' }}>วันที่ล่าสุด</option>
                <option value="date_asc" {{ $sort == 'date_asc' ? 'selected' : '' }}>วันที่เก่าสุด</option>
                <option value="book_id_desc" {{ $sort == 'book_id_desc' ? 'selected' : '' }}>หัวเรื่อง มากไปน้อย</option>
                <option value="book_id_asc" {{ $sort == 'book_id_asc' ? 'selected' : '' }}>หัวเรื่อง น้อยไปมาก</option>
            </select>

            {{-- จำนวนไฟล์ต่อหน้า --}}
            <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                <option value="10" {{ $limit == 10 ? 'selected' : '' }}>แสดง 10 ไฟล์</option>
                <option value="50" {{ $limit == 50 ? 'selected' : '' }}>แสดง 50 ไฟล์</option>
                <option value="100" {{ $limit == 100 ? 'selected' : '' }}>แสดง 100 ไฟล์</option>
                <option value="200" {{ $limit == 200 ? 'selected' : '' }}>แสดง 200 ไฟล์</option>
                <option value="300" {{ $limit == 300 ? 'selected' : '' }}>แสดง 300 ไฟล์</option>
                <option value="1000" {{ $limit == 1000 ? 'selected' : '' }}>แสดง 1000 ไฟล์</option>
                <option value="all" {{ $limit == 'all' ? 'selected' : '' }}>แสดงไฟล์ทั้งหมด</option>
            </select>

            <button type="submit" class="btn btn-primary">ค้นหา</button>
        </form>

        {{-- 🔗 รายการไฟล์ --}}
        <ul class="list-group" style="font-size: 20px;">
            @forelse($files as $file)
                <li class="list-group-item">
                    <a href="{{ $file['url'] }}" target="_blank" class="btn btn-sm btn-danger">
                        <i class="fa fa-file-pdf-o me-1"></i> PDF
                    </a>
                    <div class="text-muted mt-1" style="font-size: 14px;">
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
        @if($limit != 'all')
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-3">
                @if($page > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}">« ก่อนหน้า</a>
                    </li>
                @endif

                @php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                @endphp

                @if($startPage > 1)
                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a></li>
                    @if($startPage > 2)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                @endif

                @for($i = $startPage; $i <= $endPage; $i++)
                    <li class="page-item {{ $i == $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                    </li>
                @endfor

                @if($endPage < $totalPages)
                    @if($endPage < $totalPages - 1)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $totalPages]) }}">{{ $totalPages }}</a></li>
                @endif

                @if($page < $totalPages)
                    <li class="page-item">
                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}">ถัดไป »</a>
                    </li>
                @endif
            </ul>
        </nav>
        @endif
    </div>
</div>

{{-- ป้องกัน pagination ล้นจอ --}}
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
