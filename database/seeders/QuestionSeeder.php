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
            ['q' => 'Ibu kota negara Australia adalah?', 'options' => ['A' => 'Sydney', 'B' => 'Melbourne', 'C' => 'Canberra', 'D' => 'Perth'], 'ans' => 'C'],
            ['q' => 'Gunung tertinggi di dunia adalah?', 'options' => ['A' => 'Kilimanjaro', 'B' => 'Everest', 'C' => 'Fuji', 'D' => 'Jayawijaya'], 'ans' => 'B'],
            ['q' => 'Benua terkecil di dunia adalah?', 'options' => ['A' => 'Eropa', 'B' => 'Antartika', 'C' => 'Australia', 'D' => 'Amerika Selatan'], 'ans' => 'C'],
            ['q' => 'Siapakah penemu bola lampu pijar?', 'options' => ['A' => 'Alexander Graham Bell', 'B' => 'Thomas Alva Edison', 'C' => 'Nikola Tesla', 'D' => 'Albert Einstein'], 'ans' => 'B'],
            ['q' => 'Mata uang negara Jepang adalah?', 'options' => ['A' => 'Won', 'B' => 'Yuan', 'C' => 'Yen', 'D' => 'Baht'], 'ans' => 'C'],
            ['q' => 'Mamalia laut terbesar di dunia adalah?', 'options' => ['A' => 'Hiu Paus', 'B' => 'Paus Biru', 'C' => 'Lumba-lumba', 'D' => 'Paus Orca'], 'ans' => 'B'],
            ['q' => 'Sungai terpanjang di dunia adalah?', 'options' => ['A' => 'Nil', 'B' => 'Amazon', 'C' => 'Mississippi', 'D' => 'Yangtze'], 'ans' => 'A'],
            ['q' => 'Siapakah presiden pertama Amerika Serikat?', 'options' => ['A' => 'Abraham Lincoln', 'B' => 'Thomas Jefferson', 'C' => 'George Washington', 'D' => 'John F. Kennedy'], 'ans' => 'C'],
            ['q' => 'Negara manakah yang dijuluki Negeri Tirai Bambu?', 'options' => ['A' => 'Jepang', 'B' => 'Tiongkok', 'C' => 'Korea Selatan', 'D' => 'Vietnam'], 'ans' => 'B'],
            ['q' => 'Berapakah jumlah tulang pada tubuh manusia dewasa?', 'options' => ['A' => '206', 'B' => '208', 'C' => '210', 'D' => '212'], 'ans' => 'A'],
            ['q' => 'Organisasi kesehatan dunia di bawah PBB dinamakan?', 'options' => ['A' => 'UNICEF', 'B' => 'UNESCO', 'C' => 'WHO', 'D' => 'FAO'], 'ans' => 'C'],
            ['q' => 'Samudra manakah yang terluas di dunia?', 'options' => ['A' => 'Hindia', 'B' => 'Atlantik', 'C' => 'Pasifik', 'D' => 'Arktik'], 'ans' => 'C'],
            ['q' => 'Tahun berapakah manusia pertama kali mendarat di bulan?', 'options' => ['A' => '1965', 'B' => '1969', 'C' => '1971', 'D' => '1975'], 'ans' => 'B'],
            ['q' => 'Negara terkecil di dunia berdasarkan wilayah adalah?', 'options' => ['A' => 'Monako', 'B' => 'Nauru', 'C' => 'Tuvalu', 'D' => 'Vatikan'], 'ans' => 'D'],
            ['q' => 'Planet keberapa bumi dari matahari?', 'options' => ['A' => 'Kedua', 'B' => 'Ketiga', 'C' => 'Keempat', 'D' => 'Kelima'], 'ans' => 'B'],
            ['q' => 'Bahasa resmi yang paling banyak digunakan di dunia berdasarkan penutur asli adalah?', 'options' => ['A' => 'Inggris', 'B' => 'Mandarin', 'C' => 'Spanyol', 'D' => 'Arab'], 'ans' => 'B'],
            ['q' => 'Apa nama hewan nasional negara Australia?', 'options' => ['A' => 'Koala', 'B' => 'Kiwi', 'C' => 'Kanguru', 'D' => 'Emu'], 'ans' => 'C'],
            ['q' => 'Berapa jumlah warna pada pelangi?', 'options' => ['A' => '5', 'B' => '6', 'C' => '7', 'D' => '8'], 'ans' => 'C'],
            ['q' => 'Penemu benua Amerika adalah?', 'options' => ['A' => 'Vasco da Gama', 'B' => 'Christopher Columbus', 'C' => 'Ferdinand Magellan', 'D' => 'James Cook'], 'ans' => 'B'],
            ['q' => 'Apa unsur gas terbanyak di atmosfer bumi?', 'options' => ['A' => 'Oksigen', 'B' => 'Karbon dioksida', 'C' => 'Hidrogen', 'D' => 'Nitrogen'], 'ans' => 'D'],
        ];

        $essays = [
            ['q' => 'Mengapa langit berwarna biru pada siang hari yang cerah?', 'ans' => 'Karena atmosfer bumi menyebarkan cahaya biru dari matahari ke segala arah lebih banyak daripada warna lain, fenomena ini disebut hamburan Rayleigh.'],
            ['q' => 'Sebutkan dan jelaskan secara singkat 3 wujud benda!', 'ans' => 'Padat (memiliki bentuk dan volume tetap), Cair (mengikuti bentuk wadah namun volume tetap), dan Gas (mengikuti bentuk dan volume wadahnya).'],
            ['q' => 'Apa perbedaan antara astronomi dan astrologi?', 'ans' => 'Astronomi adalah ilmu pengetahuan yang mempelajari benda-benda langit dan alam semesta berdasarkan metode ilmiah, sedangkan astrologi adalah kepercayaan semu yang menghubungkan posisi benda langit dengan nasib manusia.'],
            ['q' => 'Jelaskan mengapa air laut terasa asin!', 'ans' => 'Karena air laut mengandung garam-garam mineral terlarut (terutama natrium klorida) yang terbawa oleh aliran sungai dari daratan selama jutaan tahun dan tidak ikut menguap.'],
            ['q' => 'Sebutkan fungsi utama dari ginjal pada tubuh manusia!', 'ans' => 'Fungsi utama ginjal adalah menyaring darah dari limbah dan zat beracun, serta mengatur keseimbangan cairan dan elektrolit dalam tubuh untuk dibuang melalui urine.'],
            ['q' => 'Apa yang dimaksud dengan pemanasan global (global warming)?', 'ans' => 'Pemanasan global adalah peristiwa meningkatnya suhu rata-rata atmosfer, laut, dan daratan bumi akibat menumpuknya gas rumah kaca yang memerangkap panas matahari.'],
            ['q' => 'Jelaskan perbedaan satelit alami dan satelit buatan beserta contohnya!', 'ans' => 'Satelite alami adalah benda langit yang mengorbit planet secara alami (contoh: Bulan), sedangkan satelit buatan adalah benda buatan manusia yang diluncurkan ke luar angkasa (contoh: satelit Palapa).'],
            ['q' => 'Apa yang menyebabkan terjadinya siang dan malam di bumi?', 'ans' => 'Terjadinya siang dan malam disebabkan oleh rotasi bumi, yaitu perputaran bumi pada porosnya. Bagian bumi yang menghadap matahari mengalami siang, sedangkan yang membelakangi mengalami malam.'],
            ['q' => 'Sebutkan 3 faktor yang menyebabkan kepunahan hewan dan tumbuhan!', 'ans' => 'Perburuan liar, hilangnya habitat alaminya akibat deforestasi, dan perubahan iklim atau bencana alam.'],
            ['q' => 'Mengapa kelelawar bisa terbang dalam gelap tanpa menabrak benda di sekitarnya?', 'ans' => 'Karena kelelawar menggunakan ekolokasi, yaitu mengeluarkan gelombang suara frekuensi tinggi yang memantul kembali ke telinganya saat mengenai suatu benda.'],
            ['q' => 'Apa fungsi dari klorofil pada daun?', 'ans' => 'Klorofil berfungsi menyerap energi cahaya matahari untuk digunakan dalam proses fotosintesis, yaitu mengubah air dan karbon dioksida menjadi glukosa dan oksigen.'],
            ['q' => 'Jelaskan perbedaan antara revolusi dan rotasi bumi!', 'ans' => 'Rotasi bumi adalah perputaran bumi pada porosnya (membutuhkan 24 jam), sedangkan revolusi bumi adalah pergerakan bumi mengelilingi matahari (membutuhkan 365 hari).'],
            ['q' => 'Mengapa es bisa mengapung di atas air?', 'ans' => 'Karena es memiliki massa jenis (kepadatan) yang lebih rendah daripada air dalam wujud cair, sehingga daya apungnya menahan es tetap berada di atas permukaan.'],
            ['q' => 'Apa yang dimaksud dengan gaya gravitasi?', 'ans' => 'Gaya gravitasi adalah gaya tarik-menarik yang terjadi antara semua partikel yang memiliki massa di alam semesta, seperti gaya tarik bumi yang membuat benda jatuh ke bawah.'],
            ['q' => 'Jelaskan proses terjadinya hujan!', 'ans' => 'Air di permukaan bumi menguap karena panas matahari (evaporasi), uap air naik dan mengembun membentuk awan (kondensasi), setelah awan jenuh air akan jatuh ke bumi sebagai hujan (presipitasi).'],
            ['q' => 'Sebutkan tiga jenis batuan berdasarkan proses pembentukannya!', 'ans' => 'Batuan beku (terbentuk dari pendinginan magma), batuan sedimen (terbentuk dari pengendapan material), dan batuan metamorf (terbentuk dari perubahan suhu dan tekanan tinggi).'],
            ['q' => 'Apa yang dimaksud dengan rantai makanan?', 'ans' => 'Rantai makanan adalah proses perpindahan energi makanan dari organisme satu ke organisme lain melalui urutan peristiwa makan dan dimakan.'],
            ['q' => 'Mengapa minyak dan air tidak bisa bercampur?', 'ans' => 'Karena air adalah molekul polar sedangkan minyak adalah molekul non-polar, perbedaan sifat kimia ini menyebabkan keduanya menolak satu sama lain dan tidak bisa menyatu.'],
            ['q' => 'Apa fungsi dari DNA di dalam tubuh makhluk hidup?', 'ans' => 'DNA berfungsi untuk menyimpan informasi genetik yang membawa instruksi pewarisan sifat dari orang tua ke keturunannya serta mengarahkan pembentukan protein dalam sel.'],
            ['q' => 'Jelaskan apa yang dimaksud dengan ekosistem!', 'ans' => 'Ekosistem adalah suatu sistem ekologi yang terbentuk oleh hubungan timbal balik tak terpisahkan antara makhluk hidup (biotik) dengan lingkungan fisiknya (abiotik).'],
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
                    $studentAnswer = implode(' ', $partial) . "... tapi saya kurang yakin dengan kelanjutannya, intinya berkaitan dengan materi pelajaran ini.";
                } else {
                    // Sangat Salah (Ngawur)
                    $wrongAnswers = [
                        "Itu adalah nama makanan khas dari daerah Jawa Timur yang terbuat dari singkong.",
                        "Sebenarnya itu adalah karakter pahlawan fiksi dari komik buatan Jepang.",
                        "Ini adalah metode mengendarai sepeda motor dengan gigi mundur.",
                        "Itu adalah istilah yang digunakan ketika kita memancing ikan di malam hari.",
                        "Menurut saya, itu adalah nama rasi bintang yang hanya terlihat saat musim kemarau."
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
