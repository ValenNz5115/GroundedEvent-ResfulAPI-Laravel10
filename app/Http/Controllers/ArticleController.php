<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    public function createArticle(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'author' => 'required',
            'title' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048'
        ], [
            'author' => 'auhtor is required',
            'title' => 'auhtor is required',
            'description' => 'description required',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpg, png, jpeg, gif, svg.',
            'image.max' => 'The image may not be greater than 2048 kilobytes.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->toJson()]);
        }

        try {
            $imagePath = null;

            if ($req->hasFile('image')) {
                $file = $req->file('image');
                $name = time() . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('public/image/articles', $name);
            }

            $article = Article::create([
                'author' =>  $req->input('author'),
                'title' =>  $req->input('title'),
                'description' =>  $req->input('description'),
                'image' => $imagePath,
            ]);

            if ($article) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully added a new article',
                    'data' => $article
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add a new article'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function allArticle(Request $req)
    {
        try {
            $query = Article::query();

            if ($req->has('title')) {
                $query->where('title', 'like', '%' . $req->input('title') . '%');
            }

            $sortBy = $req->input('sort_by', 'article_id');
            $sortOrder = $req->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $req->input('per_page', 10);
            $article = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'article retrieved successfully',
                'data' => $article,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getArticle(Request $req, $article_id)
    {
        try {
            $article = Article::findOrFail($article_id);

            return response()->json([
                'status' => 'success',
                'message' => 'article retrieved successfully',
                'data' => $article,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'article not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function updateArticle(Request $req, $article_id)
    {
        $data = Article::find($article_id);

        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Article data not found']);
        }

        $validator = Validator::make($req->all(), [
            'author' => 'required',
            'title' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048'
        ], [
            'author' => 'auhtor is required',
            'title' => 'auhtor is required',
            'description' => 'description required',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpg, png, jpeg, gif, svg.',
            'image.max' => 'The image may not be greater than 2048 kilobytes.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->toJson()]);
        }

        try {

            $oldImage = $data->image;

            if ($req->hasFile('image')) {
                Storage::delete('public/image/articles/' . $oldImage);

                $file = $req->file('image');
                $nama = time() . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('public/image/articles/', $nama);
                $imageName = basename($imagePath);
            } else {
                $imageName = $oldImage;
            }

            $article = [
                'author_id' => $req->input('author_id'),
                'title' => $req->input('title'),
                'description' => $req->input('description'),
                'image' => $imageName,
            ];

            $result = $data->update($article);

            if ($result) {
                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'Successfully updated a new article',
                        'data' => $article
                    ]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Failed to add a new article']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletearticle($article_id)
    {
        try {
            $article = Article::findOrFail($article_id);



            Storage::delete('public/image/articles/' . $article->image);

            $deletearticle = $article->delete();

            if ($deletearticle) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'article deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete article',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500); 
        }
    }

}
