@extends('include.main')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <form method="get" class="mb-3">
            <select name="sort" class="form-select w-auto d-inline" onchange="this.form.submit()">
                <option value="name" {{$sort == 'name' ? 'selected' : ''}}>เรียงตามชื่อไฟล์</option>
                <option value="date" {{$sort == 'date' ? 'selected' : ''}}>เรียงตามวันที่ล่าสุด</option>
            </select>
        </form>
        <ul class="list-group">
            @forelse($files as $file)
                <li class="list-group-item">
                    <a href="{{$file['url']}}" target="_blank">{{$file['name']}}</a>
                </li>
            @empty
                <li class="list-group-item">ไม่พบไฟล์ PDF</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection