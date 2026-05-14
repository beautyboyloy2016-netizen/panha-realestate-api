@extends('app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Create New Post</h2>
            <a class="btn btn-secondary" href="{{ route('posts.index') }}">Back</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Error!</strong> Please check your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('posts.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="title" class="form-label"><strong>Title:</strong></label>
                <input type="text" name="title" class="form-control" placeholder="Enter title" value="{{ old('title') }}">
            </div>
            <div class="mb-3">
                <label for="body" class="form-label"><strong>Body:</strong></label>
                <textarea class="form-control tinymce-editor" name="body" rows="10">{{ old('body') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @include('partials.tinymce')
@endpush
