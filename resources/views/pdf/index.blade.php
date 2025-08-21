@extends('include.main')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <form method="get" class="mb-3">
            <select name="sort" class="form-select w-auto d-inline" onchange="this.form.submit()">
                <option value="name" {{$sort == 'name' ? 'selected' : ''}}>เรียงตามชื่อไฟล์</option>
                <option value="date" {{$sort == 'date' ? 'selected' : ''}}>เรียงตามวันที่ล่าสุด</option>
            </select>
            <select name="limit" class="form-select w-auto d-inline ms-2" onchange="this.form.submit()">
                <option value="10" {{$limit == 10 ? 'selected' : ''}}>แสดง 10 ไฟล์</option>
                <option value="50" {{$limit == 50 ? 'selected' : ''}}>แสดง 50 ไฟล์</option>
                <option value="100" {{$limit == 100 ? 'selected' : ''}}>แสดง 100 ไฟล์</option>
                <option value="100" {{$limit == 200 ? 'selected' : ''}}>แสดง 200 ไฟล์</option>
                <option value="100" {{$limit == 300 ? 'selected' : ''}}>แสดง 300 ไฟล์</option>
                <option value="100" {{$limit == 1000 ? 'selected' : ''}}>แสดง 1000 ไฟล์</option>
            </select>
        </form>
        <ul class="list-group">
            @forelse($files as $file)
                <li class="list-group-item">
                    <a href="{{$file['url']}}" target="_blank">{{$file['name']}}</a>
                    <div class="small text-muted">
                        {{ \Carbon\Carbon::createFromTimestamp($file['time'])->format('d/m/Y H:i') }}
                        @if(!empty($file['book_id']))
                            <span class="ms-3">Book ID: {{ $file['book_id'] }}</span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="list-group-item">ไม่พบไฟล์ PDF</li>
            @endforelse
        </ul>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-3">
                @if($page > 1)
                    <li class="page-item">
                        <a class="page-link" href="?sort={{ $sort }}&limit={{ $limit }}&page={{ $page - 1 }}">ก่อนหน้า</a>
                    </li>
                @endif
                @for($i = 1; $i <= $totalPages; $i++)
                    <li class="page-item {{ $i == $page ? 'active' : '' }}">
                        <a class="page-link" href="?sort={{ $sort }}&limit={{ $limit }}&page={{ $i }}">{{ $i }}</a>
                    </li>
                @endfor
                @if($page < $totalPages)
                    <li class="page-item">
                        <a class="page-link" href="?sort={{ $sort }}&limit={{ $limit }}&page={{ $page + 1 }}">ถัดไป</a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</div>
@endsection