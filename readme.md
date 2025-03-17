# Setup Laravel API Project

## Preparation

1. Buat proyek Laravel:
   ```sh
   composer create-project --prefer-dist laravel/laravel:^11.0 api-namaproject
   ```
2. Masuk ke folder proyek:
   ```sh
   cd api-namaproject
   ```
3. Jalankan server:
   ```sh
   php artisan serve
   ```
4. Buat symbolic link untuk storage:
   ```sh
   php artisan storage:link
   ```

---

## Database & Model

1. Konfigurasi `.env`, sesuaikan dengan database:
   ```
   DB_CONNECTION=mysql
   DB_DATABASE=namadb
   DB_USERNAME=userdb
   DB_PASSWORD=passdb
   DB_COLLATION=utf8mb4_unicode_ci
   ```
2. Buat model dengan migration:
   ```sh
   php artisan make:model Post -m
   ```
3. Buka file migration yang baru dibuat di `database/migrations/`
4. Tambahkan skema berikut:
   ```php
   $table->string('image'); // Untuk gambar
   $table->string('judul'); // Untuk judul gambar
   $table->string('keterangan'); // Untuk keterangan
   ```
5. Edit model `Post.php` di `app/Models/Post.php`:

   ```php
   use Illuminate\Database\Eloquent\Factories\HasFactory;
   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Database\Eloquent\Casts\Attribute;

   class Post extends Model
   {
       use HasFactory;

       protected $fillable = ['foto', 'judul', 'keterangan'];

       protected function image(): Attribute
       {
           return Attribute::make(
               get: fn ($foto) => url('/storage/posts/' . $foto),
           );
       }
   }
   ```

6. Jalankan migrasi:
   ```sh
   php artisan migrate
   ```

---

## Membuat Resource untuk API

1. Buat resource:
   ```sh
   php artisan make:resource PostRes
   ```
2. Edit file `app/Http/Resources/PostRes.php`:

   ```php
   namespace App\Http\Resources;

   use Illuminate\Http\Request;
   use Illuminate\Http\Resources\Json\JsonResource;

   class PostRes extends JsonResource
   {
       public $status;
       public $message;

       public function __construct($status, $message, $resource)
       {
           parent::__construct($resource);
           $this->status = $status;
           $this->message = $message;
       }

       public function toArray(Request $request): array
       {
           return [
               'success' => $this->status,
               'message' => $this->message,
               'data' => $this->resource
           ];
       }
   }
   ```

---

## Membuat Controller API & READ

1. Buat controller:
   ```sh
   php artisan make:controller Api/PostController
   ```
2. Edit `app/Http/Controllers/Api/PostController.php`:

   ```php
   namespace App\Http\Controllers\Api;

   use App\Models\Post;
   use App\Http\Controllers\Controller;
   use App\Http\Resources\PostRes;

   class PostController extends Controller
   {
       public function index()
       {
           $posts = Post::latest()->paginate(5);
           return new PostRes(true, 'List Data Posts', $posts);
       }
   }
   ```

---

## Membuat Routing API

1. Tambahkan route di `routes/api.php`:

   ```php
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Route;
   use App\Http\Controllers\Api\PostController;

   Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
       return $request->user();
   });

   Route::apiResource('/posts', PostController::class);
   ```

2. Periksa route API:
   ```sh
   php artisan route:list
   ```
3. Uji API dengan Postman:
   - **Method:** `GET`
   - **URL:** `http://localhost:8000/api/posts`
   - **Pastikan server berjalan dengan**:
     ```sh
     php artisan serve
     ```

---

## Read Data (By ID)

1. Tambahkan method `store` di `PostController`:

   ```php
      public function show($id)
        {
            // Cari post berdasarkan ID
            $post = Post::find($id);

            // Jika post tidak ditemukan, kembalikan respons error
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            // Jika ditemukan, kembalikan data post sebagai resource
            return new PostRes(true, 'Detail Data Post', $post);
        }
   ```

2. Uji API dengan Postman:
   - **Method:** `GET`
   - **URL:** `http://localhost:8000/api/posts/1`

---

## Create Data (POST)

1. Tambahkan import di `PostController.php`:
   ```php
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Validator;
   ```
2. Tambahkan method `store` di `PostController`:

   ```php
   public function store(Request $request)
   {
       // Validasi data
       $validator = Validator::make($request->all(), [
           'foto' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
           'judul' => 'required',
           'keterangan' => 'required',
       ]);

       if ($validator->fails()) {
           return response()->json($validator->errors(), 422);
       }

       // Upload image
       $foto = $request->file('foto');
       $foto->storeAs('public/posts', $foto->hashName());

       // Simpan data ke database
       $post = Post::create([
           'foto' => $foto->hashName(),
           'judul' => $request->judul,
           'keterangan' => $request->keterangan,
       ]);

       // Return response
       return new PostRes(true, 'Data Post ditambahkan', $post);
   }
   ```

3. Uji API dengan Postman:
   - **Method:** `POST`
   - **URL:** `http://localhost:8000/api/posts`
   - **Headers:**
     ```
     Content-Type: multipart/form-data
     ```
   - **Body (Form-Data):**
     ```
     foto        (file) - upload gambar
     judul       (text) - judul gambar
     keterangan  (text) - deskripsi gambar
     ```
