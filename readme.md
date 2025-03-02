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
