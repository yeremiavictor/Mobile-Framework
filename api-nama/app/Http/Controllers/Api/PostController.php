<?php

namespace App\Http\Controllers\Api;

//tambahkan model
use App\Models\Post;

use App\Http\Controllers\Controller;

//import resource
use App\Http\Resources\PostRes;

//import http request
use Illuminate\Http\Request;

//import facade validator
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    // read data

    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //ambil seluruh posts
        $posts = Post::latest()->paginate(5);

        //mengembalikan koleksi dari posts sebagai resource
        return new PostRes(true, 'List Data Posts', $posts);
    }

    // create data

    /**
     * store
     *
     * @param mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        //mendefinisikan aturan validasi
        $validator = Validator::make($request->all(),[
            'foto'          => 'required|image|mimes:jpg,jpeg,png,giv,svg|max:2048',
            'judul'         => 'required',
            'keterangan'    => 'required',
        ]);

        //cek jika validasi gagal
        if($validator->fails()) {
            return response()->json($validator->errors(),442);
        }

        //upload image
        $foto = $request->file('foto');
        $foto->storeAs('public/posts', $foto->hashName());

        //membuat post
        $post = Post::create([
            'foto'          => $foto->hashName(),
            'judul'         => $request->judul,
            'keterangan'    => $request->keterangan,
        ]);

        //mengembalikan respon
        return new PostRes(true, 'Data Post ditambahkan', $post);
    }
}
