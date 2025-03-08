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

//edit
use Illuminate\Support\Facades\Storage;

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

    //Update

    /**
     * Update a post.
     *
     * @param mixed $request
     * @param mixed $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'keterangan' => 'required'
        ]);

        // Cek jika validasi gagal
        if($validator->fails()){
            return response()->json($validator->errors(), 442);
        }

        // Cari post by ID
        $post = Post::find($id);

        // Cek jika post tidak ditemukan
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Cek jika ada file foto baru
        if($request->hasFile('foto')) {

            // Upload image
            $foto = $request->file('foto');
            $foto->storeAs('public/posts', $foto->hashName());

            // Delete old image if exists
            if ($post->foto) {
                Storage::delete('public/posts/' . basename($post->foto));
            }

            // Update post dengan gambar baru
            $post->update([
                'foto'          => $foto->hashName(),
                'judul'         => $request->judul,
                'keterangan'    => $request->keterangan,
            ]);
        } else {
            // Update post tanpa mengubah gambar
            $post->update([
                'judul'         => $request->judul,
                'keterangan'    => $request->keterangan
            ]);
        }

        // Mengembalikan respons setelah berhasil update
        return new PostRes(true, 'Data Post berhasil diperbarui', $post);
    }



}
