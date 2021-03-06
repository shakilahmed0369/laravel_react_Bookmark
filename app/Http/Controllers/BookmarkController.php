<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use OpenGraph;

class BookmarkController extends Controller
{

    
    public function index()
    {
        $bookmarks = Bookmark::query()
            ->where('user_id', Auth::user()->id)
            ->where('is_active', 1)
            ->orderByDesc('updated_at')
            ->get();
        return Inertia::render('Bookmark/List/index', compact('bookmarks'));
    }

    public function welcome()
    {
        if(Auth::check()){
            return redirect()->route('bookmark.index');
        }
        return Inertia::render('Bookmark/Welcome/index');
    }

    public function add()
    {
        return Inertia::render('Bookmark/Add/index');
    }

    public function getPreviewData(Request $request)
    {
        $postData = $this->validate($request, [
            'link' => ['required']
        ]);

        $data = OpenGraph::fetch($postData['link']);
        // laradump()->dump($data);
        $bookmark = Bookmark::create([
            'title'       => $data['title'],
            'description' => $data['description'],
            'type'        => $data['type'],
            'url'         => $postData['link'],
            'img_url'     => $data['image'],
            'user_id'     => $request->user()->id,

        ]);

        return redirect()->route('bookmark.view', ['bookmark' => $bookmark->id]);
    }

    public function view(Bookmark $bookmark)
    {
        if (Auth::user()->id !== $bookmark->user_id) {
            abort(401, 'You are not allowed to view this bookmark!');
        }
        // laradump()->dump($bookmark);
        return Inertia::render('Bookmark/View/index', compact('bookmark'));
    }

    public function makeActive(Request $request)
    {
        $postData = $this->validate($request, [
            'id' => ['required', 'exists:bookmarks,id'],
        ]);
        $bookmark = Bookmark::find($postData['id']);
        if (Auth::user()->id !== $bookmark->user_id) {
            abort(401, 'You are not allowed to view this bookmark!');
        }
        $bookmark->is_active = 1;
        $bookmark->save();

        return redirect()->route('bookmark.index');
    }
}
