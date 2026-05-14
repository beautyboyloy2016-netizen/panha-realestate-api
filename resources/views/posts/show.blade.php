@extends('app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Show Post</h2>
            <a class="btn btn-secondary" href="{{ route('posts.index') }}">Back</a>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="card-title">{{ $post->title }}</h3>
                <hr>
                <div class="content">
                    {!! $post->body !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
