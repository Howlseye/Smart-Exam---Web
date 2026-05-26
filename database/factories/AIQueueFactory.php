<?php

namespace Database\Factories;

use App\Models\AIQueue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AIQueue>
 */
class AIQueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qaPairs = [
            [
                'question' => 'Jelaskan apa itu HTML dan perannya dalam pembuatan website.',
                'answer' => 'HTML atau HyperText Markup Language adalah bahasa standar untuk dokumen web. Perannya sangat penting karena HTML memberikan struktur dasar pada halaman, seperti paragraf, judul, gambar, dan tautan.'
            ],
            [
                'question' => 'Apa perbedaan mendasar antara pengembangan front-end dan back-end?',
                'answer' => 'Front-end fokus pada antarmuka visual website yang dilihat pengguna, dibuat dengan HTML, CSS, dan JavaScript. Back-end menangani logika server, database, dan pemrosesan data di balik layar agar website berfungsi.'
            ],
            [
                'question' => 'Sebutkan 3 jenis framework CSS yang sering digunakan dan mengapa itu penting.',
                'answer' => 'Tiga framework CSS populer adalah Bootstrap, Tailwind CSS, dan Bulma. Framework ini penting karena menyediakan class siap pakai yang mempercepat desain, memastikan konsistensi, dan mempermudah pembuatan layout yang responsif.'
            ],
            [
                'question' => 'Mengapa desain website yang responsif sangat dibutuhkan saat ini?',
                'answer' => 'Website responsif dibutuhkan karena pengguna mengakses internet dari berbagai perangkat dengan ukuran layar berbeda, terutama smartphone. Desain responsif memastikan tampilan dan pengalaman pengguna tetap optimal di layar manapun.'
            ],
            [
                'question' => 'Apa kegunaan utama JavaScript dalam sebuah halaman web?',
                'answer' => 'JavaScript berguna untuk membuat halaman web menjadi interaktif dan dinamis. Contohnya adalah membuat animasi, memperbarui konten tanpa reload halaman, validasi formulir, dan membuat aplikasi web yang kompleks.'
            ],
            [
                'question' => 'Jelaskan fungsi dari Database dalam pengembangan aplikasi web.',
                'answer' => 'Database berfungsi sebagai tempat penyimpanan data yang terstruktur. Dalam aplikasi web, database digunakan untuk menyimpan informasi pengguna, konten artikel, riwayat transaksi, dan data penting lainnya secara permanen dan aman.'
            ],
            [
                'question' => 'Apa yang dimaksud dengan SEO dan mengapa itu penting untuk sebuah website baru?',
                'answer' => 'SEO (Search Engine Optimization) adalah teknik optimalisasi agar website mudah ditemukan di mesin pencari seperti Google. Ini penting untuk mendatangkan pengunjung organik tanpa harus selalu membayar iklan.'
            ]
        ];

        $pair = fake()->randomElement($qaPairs);

        return [
            'question' => $pair['question'],
            'answer' => $pair['answer'],
            'status' => 'pending',
        ];
    }
}
