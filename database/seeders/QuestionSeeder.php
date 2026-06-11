<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        Question::query()->delete();

        $mcqs = [
            ['q' => 'Apa tag HTML dasar untuk membuat paragraf?', 'options' => ['A' => '<p>', 'B' => '<div>', 'C' => '<span>', 'D' => '<a>'], 'ans' => 'A'],
            ['q' => 'Properti CSS apa yang digunakan untuk mengubah warna teks?', 'options' => ['A' => 'background-color', 'B' => 'color', 'C' => 'font-color', 'D' => 'text-style'], 'ans' => 'B'],
            ['q' => 'Apa itu DOM dalam JavaScript?', 'options' => ['A' => 'Document Object Model', 'B' => 'Data Object Management', 'C' => 'Digital Output Method', 'D' => 'Dynamic Object Model'], 'ans' => 'A'],
            ['q' => 'Di Jetpack Compose, fungsi apa yang digunakan untuk membuat teks?', 'options' => ['A' => 'TextView', 'B' => 'Label', 'C' => 'Text', 'D' => 'String'], 'ans' => 'C'],
            ['q' => 'Apa peran modifier di Jetpack Compose?', 'options' => ['A' => 'Mengatur navigasi', 'B' => 'Mengatur style dan layout komponen', 'C' => 'Menghubungkan ke database', 'D' => 'Mengatur lifecycle'], 'ans' => 'B'],
            ['q' => 'Komponen Compose mana yang mengatur elemen secara vertikal?', 'options' => ['A' => 'Row', 'B' => 'Box', 'C' => 'Column', 'D' => 'Stack'], 'ans' => 'C'],
            ['q' => 'Apa command Artisan untuk membuat controller di Laravel?', 'options' => ['A' => 'php artisan create:controller', 'B' => 'php artisan make:controller', 'C' => 'php artisan build:controller', 'D' => 'php artisan new:controller'], 'ans' => 'B'],
            ['q' => 'Mana yang BUKAN merupakan HTTP method standar?', 'options' => ['A' => 'GET', 'B' => 'POST', 'C' => 'SEND', 'D' => 'PUT'], 'ans' => 'C'],
            ['q' => 'Bagaimana cara menangani state di Jetpack Compose?', 'options' => ['A' => 'Menggunakan mutableStateOf', 'B' => 'Menggunakan setState', 'C' => 'Menggunakan SharedPreferences', 'D' => 'Menggunakan Intent'], 'ans' => 'A'],
            ['q' => 'Apa itu Scaffold di Jetpack Compose?', 'options' => ['A' => 'Database lokal', 'B' => 'Struktur layout dasar Material Design', 'C' => 'Sistem routing', 'D' => 'Library animasi'], 'ans' => 'B'],
            ['q' => 'Fungsi @Composable digunakan untuk?', 'options' => ['A' => 'Mendeklarasikan UI function', 'B' => 'Mendeklarasikan variable', 'C' => 'Membuat background service', 'D' => 'Mendeklarasikan database table'], 'ans' => 'A'],
            ['q' => 'Apa fungsi dari useEffect di React / ekivalen LaunchedEffect di Compose?', 'options' => ['A' => 'Merender UI', 'B' => 'Menjalankan side effects', 'C' => 'Menghapus memori', 'D' => 'Mendeklarasikan state'], 'ans' => 'B'],
            ['q' => 'Dalam CSS, apa itu Flexbox?', 'options' => ['A' => 'Library animasi CSS', 'B' => 'Model layout untuk menyusun elemen', 'C' => 'Pre-processor CSS', 'D' => 'Font style'], 'ans' => 'B'],
            ['q' => 'Apa framework PHP yang sering digunakan selain Laravel?', 'options' => ['A' => 'CodeIgniter', 'B' => 'Django', 'C' => 'Spring Boot', 'D' => 'Express'], 'ans' => 'A'],
            ['q' => 'Mana yang digunakan untuk menyimpan data lokal di browser?', 'options' => ['A' => 'SessionStorage', 'B' => 'LocalStorage', 'C' => 'Cookies', 'D' => 'Semua benar'], 'ans' => 'D'],
            ['q' => 'Bagaimana membuat tombol (Button) di Jetpack Compose?', 'options' => ['A' => '<Button>', 'B' => 'new Button()', 'C' => 'Button(onClick = { }) { }', 'D' => 'createButton()'], 'ans' => 'C'],
            ['q' => 'Di mana dependensi ditambahkan pada proyek Android (Compose)?', 'options' => ['A' => 'build.gradle', 'B' => 'AndroidManifest.xml', 'C' => 'MainActivity.kt', 'D' => 'package.json'], 'ans' => 'A'],
            ['q' => 'Bahasa utama untuk Jetpack Compose adalah?', 'options' => ['A' => 'Java', 'B' => 'Kotlin', 'C' => 'C++', 'D' => 'Dart'], 'ans' => 'B'],
            ['q' => 'Bagaimana cara mengatur padding pada elemen di Compose?', 'options' => ['A' => 'Modifier.padding(16.dp)', 'B' => 'setPadding(16)', 'C' => 'padding="16dp"', 'D' => 'style={padding: 16}'], 'ans' => 'A'],
            ['q' => 'Fungsi mana yang bertindak sebagai entry point UI Compose di Activity?', 'options' => ['A' => 'setContentView', 'B' => 'setContent', 'C' => 'startCompose', 'D' => 'initUI'], 'ans' => 'B'],
        ];

        $essays = [
            ['q' => 'Jelaskan perbedaan antara Row dan Column di Jetpack Compose.', 'ans' => 'Row digunakan untuk menyusun elemen secara horizontal (kiri ke kanan), sedangkan Column digunakan untuk menyusun elemen secara vertikal (atas ke bawah).'],
            ['q' => 'Apa itu State Hoisting dalam Jetpack Compose dan mengapa penting?', 'ans' => 'State hoisting adalah pola pemindahan state ke pemanggil komponen (parent) untuk membuat komponen tersebut stateless. Ini penting agar komponen lebih mudah dites, di-reuse, dan mencegah bug terkait state.'],
            ['q' => 'Sebutkan langkah-langkah dasar membuat form login sederhana menggunakan HTML dan PHP.', 'ans' => 'Pertama, buat file HTML dengan form yang memiliki input username dan password serta tombol submit dengan method POST. Kedua, buat file PHP untuk menangkap data dari $_POST, lalu validasi data tersebut (misal cek ke database).'],
            ['q' => 'Jelaskan konsep dasar MVC (Model-View-Controller) pada pengembangan web.', 'ans' => 'MVC memisahkan aplikasi menjadi 3 bagian: Model mengatur logika data dan database, View mengatur tampilan antarmuka pengguna, dan Controller mengatur alur request pengguna serta menghubungkan Model dan View.'],
            ['q' => 'Bagaimana cara kerja modifier weight() di Jetpack Compose?', 'ans' => 'Modifier weight() membagi sisa ruang kosong (available space) di dalam Row atau Column secara proporsional sesuai dengan nilai beban (weight) yang diberikan pada tiap elemen.'],
            ['q' => 'Apa perbedaan antara let, const, dan var di JavaScript?', 'ans' => 'Var memiliki scope function dan bisa di-hoist. Let memiliki block scope dan nilainya bisa diubah. Const juga memiliki block scope tetapi nilainya tidak bisa diubah (immutable reference).'],
            ['q' => 'Jelaskan keuntungan menggunakan arsitektur MVVM saat membuat aplikasi Jetpack Compose.', 'ans' => 'MVVM memisahkan logika bisnis (ViewModel) dari UI (Compose). Ini membuat UI murni reaktif terhadap perubahan State dari ViewModel, sehingga kode lebih bersih, mudah dites, dan UI selalu sinkron dengan data.'],
            ['q' => 'Apa yang dimaksud dengan Recomposition di Jetpack Compose?', 'ans' => 'Recomposition adalah proses di mana Jetpack Compose memanggil ulang fungsi @Composable dengan data (state) baru ketika terjadi perubahan, lalu hanya memperbarui bagian UI yang datanya berubah.'],
            ['q' => 'Sebutkan 3 tag semantik HTML5 dan fungsinya.', 'ans' => '<header> untuk bagian atas/kepala halaman, <article> untuk konten yang berdiri sendiri, dan <footer> untuk bagian bawah halaman.'],
            ['q' => 'Mengapa kita perlu responsif desain dalam pembuatan website?', 'ans' => 'Agar website dapat menyesuaikan tampilannya dengan baik di berbagai ukuran layar dan perangkat (desktop, tablet, mobile), sehingga memberikan pengalaman pengguna yang optimal.'],
            ['q' => 'Jelaskan fungsi LazyColumn di Jetpack Compose.', 'ans' => 'LazyColumn mirip dengan RecyclerView; ia digunakan untuk menampilkan daftar elemen yang sangat panjang atau tak terbatas (infinite scroll) secara efisien karena hanya me-render elemen yang terlihat di layar.'],
            ['q' => 'Apa itu API dan apa perannya dalam aplikasi mobile-web?', 'ans' => 'API (Application Programming Interface) adalah perantara yang memungkinkan dua aplikasi berkomunikasi. Perannya adalah menghubungkan front-end (misal web/Android) dengan back-end/database untuk pertukaran data.'],
            ['q' => 'Jelaskan perbedaan antara margin dan padding pada CSS.', 'ans' => 'Padding adalah jarak antara konten dengan batas (border) elemen itu sendiri (di dalam), sedangkan margin adalah jarak antara elemen tersebut dengan elemen lain di sekitarnya (di luar border).'],
            ['q' => 'Bagaimana cara menambahkan gambar pada proyek Jetpack Compose?', 'ans' => 'Gambar ditempatkan di folder res/drawable, lalu dipanggil menggunakan komponen Image() dengan parameter painterResource(id = R.drawable.nama_gambar).'],
            ['q' => 'Apa itu Coroutines di Kotlin dan kenapa sering dipakai dengan Compose?', 'ans' => 'Coroutines adalah fitur concurrency di Kotlin untuk menjalankan tugas secara asynchronous. Sering dipakai di Compose untuk menangani tugas berat (seperti memanggil API) agar tidak memblokir UI thread.'],
            ['q' => 'Jelaskan siklus hidup (lifecycle) dari komponen React secara singkat.', 'ans' => 'Secara umum ada 3 fase: Mounting (saat komponen pertama kali dimuat ke DOM), Updating (saat props/state berubah dan merender ulang), dan Unmounting (saat komponen dihapus dari DOM).'],
            ['q' => 'Mengapa Jetpack Compose diklaim lebih baik daripada sistem View XML tradisional?', 'ans' => 'Compose menggunakan pendekatan deklaratif (UI ditulis dengan kode Kotlin murni), yang mengurangi boilerplate, mempercepat development, mempermudah manajemen state, dan lebih intuitif.'],
            ['q' => 'Apa yang dimaksud dengan SQL Injection dan bagaimana mencegahnya?', 'ans' => 'SQL Injection adalah teknik peretasan dengan menyisipkan perintah SQL berbahaya melalui input form. Cara mencegahnya adalah dengan menggunakan prepared statements atau parameter binding.'],
            ['q' => 'Jelaskan fungsi Remember dalam Jetpack Compose.', 'ans' => 'Fungsi remember digunakan untuk menyimpan sebuah objek di memori agar nilainya tidak hilang/direset ketika terjadi Recomposition (merender ulang UI).'],
            ['q' => 'Apa yang dimaksud dengan Cross-Origin Resource Sharing (CORS)?', 'ans' => 'CORS adalah mekanisme keamanan browser yang mengatur apakah sebuah halaman web diizinkan mengakses resource (seperti API) dari domain atau origin yang berbeda dari halaman web tersebut.'],
        ];

        // 1. UNANSWERED (10 MCQ, 10 Essay)
        for ($i = 0; $i < 10; $i++) {
            $this->createQuestion($mcqs[$i], 1, false); // 1 = multiple_choice
            $this->createQuestion($essays[$i], 2, false); // 2 = essay
        }

        // 2. ANSWERED (10 MCQ, 10 Essay)
        for ($i = 10; $i < 20; $i++) {
            $this->createQuestion($mcqs[$i], 1, true);
            $this->createQuestion($essays[$i], 2, true);
        }
    }

    private function createQuestion($data, $type, $isAnswered)
    {
        $studentAnswer = null;

        if ($isAnswered) {
            if ($type == 1) { // 1 = multiple_choice
                $isCorrect = rand(1, 100) <= 50; // 50% benar
                if ($isCorrect) {
                    $studentAnswer = $data['ans'];
                } else {
                    $wrongOptions = array_diff(['A', 'B', 'C', 'D'], [$data['ans']]);
                    $studentAnswer = $wrongOptions[array_rand($wrongOptions)];
                }
            } else {
                // Untuk Essay (2), kita variasikan jawaban menjadi 3 kategori:
                // 0 = Sangat Benar, 1 = Setengah Benar, 2 = Sangat Salah
                $answerQuality = rand(0, 2);
                
                if ($answerQuality === 0) {
                    // Sangat Benar (Jawaban sesuai kunci)
                    $studentAnswer = "Menurut saya, " . lcfirst($data['ans']);
                } elseif ($answerQuality === 1) {
                    // Setengah Benar (Mencampuradukkan konsep atau kurang lengkap)
                    $words = explode(' ', $data['ans']);
                    $partial = array_slice($words, 0, max(3, intval(count($words) / 2)));
                    $studentAnswer = implode(' ', $partial) . "... tapi saya lupa kelanjutannya, intinya berhubungan dengan pemrograman.";
                } else {
                    // Sangat Salah (Ngawur)
                    $wrongAnswers = [
                        "Itu adalah sebuah fungsi untuk meretas sistem keamanan server agar mendapatkan akses root.",
                        "Itu adalah tag untuk membuat animasi 3D di dalam database SQL.",
                        "Sebenarnya itu digunakan untuk mengunduh RAM agar laptop menjadi lebih cepat saat main game.",
                        "Itu adalah framework bahasa C yang dipakai untuk memasak nasi goreng otomatis.",
                        "Ini adalah metode mematikan komputer orang lain menggunakan Bluetooth dari jarak 100 kilometer."
                    ];
                    $studentAnswer = $wrongAnswers[array_rand($wrongAnswers)];
                }
            }
        }

        Question::create([
            'type' => $type,
            'question' => $data['q'],
            'options' => $type == 1 ? $data['options'] : null, // 1 = multiple_choice
            'correct_answer' => $data['ans'],
            'student_answer' => $studentAnswer,
            'is_answered' => $isAnswered,
        ]);
    }
}
