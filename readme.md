<h1> Preparation </h1>
1. composer create-project --prefer-dist laravel/laravel:^11.0 api-namaproject
2. cd api-namaproject
3. php artisan serve
4. php artisan storage:link

<H1> Database & Model </h1>
1. konfigurasi .env
3. setting database: mysql
    tambahkan: DB_COLLATION=utf8mb4_unicode_ci
    (name, user, password)
4. Membuat model: php artisan make:model Post -m
5. masuk pada database>migrations>model yang baru dibuat
6. tambahkan:
    $table->string('image'); //buat untuk gambar
    $table->string('judul'); //buat untuk judul gambar
    $table->string('keterangan'); //buat untuk keterangan
7. pada app>model>post
    tambahkan:
    use Illuminate\Database\Eloquent\Factories\HasFactory;

    dalam class tambahkan:
        use HasFactory;

        protected $fillable = [
            'foto',
            'judul',
            'keterangan',
        ];

8.  lakukan proses migrasi: php artisan migrate
9.  Berikan accessor untuk image dengan:
    tambahkan:
    use Illuminate\Database\Eloquent\Casts\Attribute;

    dalam class tambahkan:
    protected function image():Attribute

    {
    return Attribute::make(
    get: fn ($foto) => url('/storage/posts'. 'foto'),
    );
    }

<h1> Membuat Resources untuk API </h1>
1. php artisan make:resource PostRes
2. tambahkan  pada app>Http>Resources>PostRes.php

    //mendefinisikan properti
    public $status;
    public $message;
    public $resource;

    /**
     * __construct
     *
     * @param mixed $status
     * @param mixed $message
     * @param mixed $resource
     * @return void
     */

    public function __construct($status,$message,$resource)
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * toArray
     *
     * @param mixed $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return[
            'success' =>$this->status,
            'message' =>$this->message,
            'data' =>$this->resource
        ];
    }

<h1> Menampilkan data dari db <h1>
<h1> membuat controller: untuk method post </h1>
1. php artisan make:controller Api/PostController
    pada app>http>controllers>Api>PostController
2. Ubah kode sbb:
<?php

namespace App\Http\Controllers\Api;

//tambahkan model
use App\Models\Post;

use App\Http\Controllers\Controller;

//import resource
use App\Http\Resources\PostRes;

class PostController extends Controller
{

    public function index()
    {
        //ambil seluruh posts
        $posts = Post::latest()->paginate(5);

        //mengembalikan koleksi dari posts sebagai resource
        return new PostRes(true, 'List Data Posts', $posts);
    }

}

3. membuat route api: php artisan install:api
4. routes>api
5. ubah:
<?php

// auth
use Illuminate\Auth\Middleware\Authenticate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
return $request->user();
})->middleware('auth:sanctum');

//posts
Route::apiResource('/posts', App\Http\Controllers\Api\PostController::class );

6. cek list: php artisan route:list
7. pakai postman cek url: http://localhost:8000/api/posts
   -> sebelumnya pastikan php artisan serve berjalan

<h1>Create </h1>
1. import
    use Illuminate\Http\Request;
    use Illuminate\Http\Request;

2.  Edit Post Controller:
    // create data

    /\*\*

    - store
    -
    - @param mixed $request
    - @return void
      \*/
      public function store(Request $request)
    {
        //mendefinisikan aturan validasi
        $validator = Validator::make($request->all(),[
      'foto' => 'required|image|mimes:jpg,jpeg,png,giv,svg|max:2048',
      'judul' => 'required',
      'keterangan' => 'required',
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

} 3. pada postman methodnya post, http://localhost:8000/api/posts
