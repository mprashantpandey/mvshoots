@if(session('status'))
    <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-4">
        <div class="fw-semibold mb-1">Please review the form</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
