@extends('app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Posts Management</h2>
            <a class="btn btn-success" href="{{ route('posts.create') }}">Create New Post</a>
        </div>

        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th width="80">No</th>
                    <th>Title</th>
                    <th width="280">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($posts as $key => $post)
                <tr>
                    <td>{{ ++$key?? $loop->iteration }}</td>
                    <td>{{ $post->title }}</td>
                    <td>
                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                            <a class="btn btn-info btn-sm" href="{{ route('posts.show', $post->id) }}">Show</a>
                            <a class="btn btn-primary btn-sm" href="{{ route('posts.edit', $post->id) }}">Edit</a>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $posts->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
