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

---

## Read Data (By ID)

1. Tambahkan method `show` di `PostController`:

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
   - **URL:** `http://localhost:8000/api/posts/{:id}`

   {:id} bisa di isi dengan data id terdaftar (bisa di cek di read data)

---

## UPDATE Data (POST)

1. Tambahkan import di `PostController.php`:
   ```php
   use Illuminate\Support\Facades\Storage;
   ```
2. Tambahkan method `update` di `PostController`:

   ```php
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
   ```

3. Uji API dengan Postman:

   - **Method:** `POST`
   - **URL:** `http://localhost:8000/api/posts/{id}`
   - **Headers:**
     ```
     Content-Type: multipart/x-www-form-urlencoded
     ```
   - **Body (Form-Data):**
     ```
     foto        (file) - upload gambar
     judul       (text) - judul gambar
     keterangan  (text) - deskripsi gambar
     ```

   {:id} bisa di isi dengan data id terdaftar (bisa di cek di read data)

---

## Delete Data (POST)

1. Tambahkan method `destroy` di `PostController`:

   ```php
        public function destroy($id)
    {

        //find post by ID
        $post = Post::find($id);

        // Cek jika post tidak ditemukan
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        //delete image
        Storage::delete('public/posts/'.basename($post->foto));

        //delete post
        $post->delete();

        //return response
        return new PostRes(true, 'Data Post Berhasil Dihapus!', null);
    }
   ```

2. Uji API dengan Postman:

   - **Method:** `DELETE`
   - **URL:** `http://localhost:8000/api/posts/{id}`

   {:id} bisa di isi dengan data id terdaftar (bisa di cek di read data)

---

## Instalasi AUTH

1.  composer require tymon/jwt-auth
2.  Publish konfigurasi:
    php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
3.  bangun secret key: -> hasil akan di show di .env
    php artisan jwt:secret
4.  Pada config/auth.php
    ubah line

        ```php
            'guards' => [
                'web' => [
                    'driver' => 'session',
                    'provider' => 'users',
                ],
            ],

        ```

    menjadi:

        ```php
            'guards' => [
                'web' => [
                    'driver' => 'session',
                    'provider' => 'users',
                ],
                'api' => [
                    'driver' => 'jwt',
                    'provider'  => 'users',
                ],
            ],

        ```

5.  Pada app/Models/User.php
    import jwt:

    ```php
        use Tymon\JWTAuth\Contracts\JWTSubject;
    ```

    tambahkan implementasi jwt pada class:

    ```php
        class User extends Authenticatable implements JWTSubject
    ```

    tambahkan function untuk mendapatkan key dan mengembalikan key:

    ```php
        //mengambil identifier yang akan di klaim di jwt
        public function getJWTIdentifier()
            {
                return $this->getKey();
            }

        //mengembalikan nilai dalam bentuk array
        public function getJWTCustomClaims()
        {
            return [];
        }
    ```

---

## Membuat Registrasi USER (JWT)

1.  Membuat Controller Register
    php artisan make:controller Api/RegisterController -i

2.  Buka file RegisterController -> import validator dan model user dulu

    ```php
        use App\Models\User;
        use Illuminate\Support\Facades\Validator;
    ```

3.  pada action invoke tambahkan rule berikut:

    ```php
    // atur validasi
        $validator = Validator::make($request->all(), [
            'name'      =>'required',
            'email'     =>'required|email|unique:users',
            'password'  =>'required|min:8|confirmed',
        ]);

        //kalau validasi gagal
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        //create users
        $user=User::create([
            'name'      =>$request->name,
            'email'     =>$request->email,
            'password'  =>bcrypt($request->password)
        ]);

        //berikan respon json user dibuat
        if($user){
            return response()->json([
                'sucess'    => true,
                'user'      => $user,
            ],201);
        }

        //berikan respon json user gagal dibuat
        return response()->json([
            'sucess'    => false,
        ],409);

    ```

4.  tambahkan controller dalam routes (routes/api.php)

    ```php
        Route::post('/register', App\Http\Controllers\Api\RegisterController::class)->name('register');
    ```

5.  Uji API dengan Postman:

    - **Method:** `POST`
    - **URL:** `http://localhost:8000/api/register`
    - **Headers:**
      ```
      Content-Type: multipart/x-www-form-urlencoded
      ```
    - **Body (Form-Data):**
      ```
      name                  (text) - Nama Asli
      email                 (text) - alamat email (akan digunakan sebagai username)
      password              (text) - password
      password_confirmation (text) - konfirmasi password
      ```

---

## Membuat Login

1. Membuat Controller untuk login:
   php artisan make:controller Api/LoginController -i
2. Buka Login Controller -> Import validator
   ```php
    use Illuminate\Support\Facades\Validator;
   ```
3. Dalam function invoke

   ```php
   //atur validator
           $validator = Validator::make($request->all(),[
               'email'     => 'required',
               'password'  => 'required',
           ]);

           // kalau gagal validasi
           if($validator->fails()){
               return response()->json($validator->errors(),422);
           }

           //mendapat kredensial dari request
           $credentials = $request->only('email','password');

           //kalau autentikasi gagal
           if(!$token = auth()->guard('api')->attempt($credentials)){
               return response()->json([
                   'success' => false,
                   'message' => 'Email atau password salah',
               ],401);
           }

           //if auth success
           return response()->json([
               'success' => true,
               'user'    => auth()->guard('api')->user(),
               'token'   => $token
           ], 200);
   ```

4. Tambahkan Route Login

   ```php
       Route::post('/login', App\Http\Controllers\Api\LoginController::class)->name('login');

       Route::middleware('auth:api')->get('/user', function (Request $request) {
           return $request->user();
       });
   ```

5. UJI API DENGAN POSTMAN

   - **Method:** `POST`

     - **URL:** `http://localhost:8000/api/login`
     - **Headers:**

     ```
     Content-Type: multipart/x-www-form-urlencoded
     ```

     - **Body (Form-Data):**

     ```
     email                 (text) - alamat email (akan digunakan sebagai username)
     password              (text) - password
     ```

   - **Method:** `GET`

   - **URL:** `http://localhost:8000/api/user`
   - **Headers:**

   ```
   Content-Type: multipart/x-www-form-urlencoded
   ```

   - **Headers**

   ```
   Accept           - application/json
   Content-Type     - application/json
   Authorization    - Bearer <spasi> TOKEN yang digenerate
   ```

   ***

---

## Membuat Logout

1. ubah .env (untuk aktifkan blacklist bila sudah logout)

```env
    JWT_SHOW_BLACKLIST_EXCEPTION=true
```

2. Buat controller Logout:
   php artisan make:controller Api/LogoutController -i

3. Buka LogoutController -> import:
   ```php
    use Tymon\JWTAuth\Facades\JWTAuth;
    use Tymon\JWTAuth\Exceptions\JWTException;
    use Tymon\JWTAuth\Exceptions\TokenExpiredException;
    use Tymon\JWTAuth\Exceptions\TokenInvalidException;
   ```
4. Dalam function invoke tambahkan:

   ```php
   $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

   if($removeToken) {
       //return response JSON
       return response()->json([
           'success' => true,
           'message' => 'Logout Berhasil!',
       ]);
   }
   ```

5. pada routes tambahkan:
   ```php
       Route::post('/logout', App\Http\Controllers\Api\LogoutController::class)->name('logout');
   ```
6. UJI API DENGAN POSTMAN

   - **Method:** `POST`

     - **URL:** `http://localhost:8000/api/logout`

   - **Headers:**

   ```
   Content-Type: multipart/x-www-form-urlencoded
   ```

   - **Headers**

   ```
   Accept           - application/json
   Content-Type     - application/json
   Authorization    - Bearer <spasi> TOKEN yang digenerate
   ```

   ***

---

### LAST

Sekarang bagaimana bila crud sebelumnya hanya bisa diakses apabila telah login?

1. pada route, sebelumnya silahkan comment / delete posts dan route->get user
2. Update code Anda:

   ```php
       // Update agar CRUD hanya bisa setelah login
       Route::middleware('auth:api')->group(function () {
           // Post CRUD (hanya untuk user yang sudah login)
           Route::apiResource('/posts', App\Http\Controllers\Api\PostController::class);

           // Get user info
           Route::get('/user', function (Request $request) {
               return response()->json($request->user());
           });
       });
   ```
