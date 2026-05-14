<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        if(request()->ajax()) {
            return datatables()->of(Post::select('*'))
                ->addColumn('action', function($row){
                    $btn = '<a href="javascript:void(0)" data-id="'.$row->id.'" class="edit btn btn-primary btn-sm">Edit</a>';
                    $btn = $btn.' <a href="javascript:void(0)" data-id="'.$row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('posts.posts');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        Post::updateOrCreate(
            ['id' => $request->post_id],
            ['title' => $request->title, 'body' => $request->body]
        );

        return response()->json(['success' => 'Post saved successfully.']);
    }

    public function edit($id)
    {
        $post = Post::find($id);
        return response()->json($post);
    }

    public function destroy($id)
    {
        Post::find($id)->delete();
        return response()->json(['success' => 'Post deleted successfully.']);
    }
}
