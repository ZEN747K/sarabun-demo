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
    </div>
</div>
@endsection