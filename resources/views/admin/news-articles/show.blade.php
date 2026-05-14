@extends('admin.layouts.master_layout')

@section('pageTitle', 'News Article Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">News Article Details</h4>
                <div class="card-tools">
                    <a href="{{ route('admin.news-articles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.news-articles.edit', $newsArticle->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Article
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Featured Image -->
                    @if($newsArticle->image_url)
                    <div class="col-md-12 mb-4">
                        <h5>Featured Image</h5>
                        <img src="{{ $newsArticle->image_url }}" alt="{{ $newsArticle->title }}" class="img-fluid rounded" style="max-height: 400px; object-fit: cover;">
                    </div>
                    @endif

                    <!-- Article Information -->
                    <div class="col-md-6">
                        <h5>Article Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">ID:</th>
                                <td>{{ $newsArticle->id }}</td>
                            </tr>
                            <tr>
                                <th>Title:</th>
                                <td><strong>{{ $newsArticle->title }}</strong></td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td><span class="badge bg-primary">{{ $newsArticle->category }}</span></td>
                            </tr>
                            <tr>
                                <th>Author:</th>
                                <td>{{ $newsArticle->author ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($newsArticle->published_at)
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-secondary">Draft</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Published At:</th>
                                <td>
                                    @if($newsArticle->published_at)
                                        {{ \Carbon\Carbon::parse($newsArticle->published_at)->format('Y-m-d H:i:s') }}<br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($newsArticle->published_at)->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Not Published</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Dates -->
                    <div class="col-md-6">
                        <h5>Timestamps</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">Created At:</th>
                                <td>{{ $newsArticle->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At:</th>
                                <td>{{ $newsArticle->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Excerpt -->
                    @if($newsArticle->excerpt)
                    <div class="col-md-12 mt-3">
                        <h5>Excerpt</h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0">{{ $newsArticle->excerpt }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Content -->
                    <div class="col-md-12 mt-3">
                        <h5>Content</h5>
                        <div class="card">
                            <div class="card-body">
                                {!! nl2br(e($newsArticle->content)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
