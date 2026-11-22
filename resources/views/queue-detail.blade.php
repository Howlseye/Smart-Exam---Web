@extends('layouts.app')

@section('content')
<div class="d-flex flex-column gap-4" style="max-width: 1000px; margin: 0 auto;">
    
    <!-- Breadcrumb & Back Navigation -->
    <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('queue.index') }}" class="btn btn-sm btn-brand-outline">
            <i class="bi bi-arrow-left"></i> Kembali ke Antrean
        </a>
        <span class="font-mono text-muted small">
            ID Antrean: <strong class="text-dark">#{{ $queue->id }}</strong>
        </span>
    </div>

    <!-- AI Evaluation Score Banner -->
    <div class="card card-custom p-4 border-0" style="background: linear-gradient(135deg, #1E3A8A, #0F172A); color: white;">
        <div class="row align-items-center g-4">
            <div class="col-md-7 text-center text-md-start">
                <span class="small text-uppercase text-light opacity-75 fw-semibold" style="letter-spacing: 0.05em; font-size: 0.75rem;">
                    Status & Penilaian Evaluasi AI
                </span>
                <div class="d-flex align-items-baseline gap-2 justify-content-center justify-content-md-start mt-2">
                    <span class="fs-1 fw-bold font-mono text-white">
                        {{ $latestLog?->score !== null ? $latestLog->score : '--' }}
                    </span>
                    <span class="text-light opacity-75 fs-5">/ 100</span>
                </div>
                <p class="small text-light opacity-75 m-0 mt-1">
                    Model: <strong>Groq &bull; {{ config('services.groq.model', 'openai/gpt-oss-120b') }}</strong>
                </p>
            </div>
            
            <div class="col-md-5 d-flex flex-column gap-2 text-md-end text-center border-start-md border-light border-opacity-25 ps-md-4">
                <div>
                    <span class="small opacity-75">Tingkat Keyakinan:</span>
                    <strong class="font-mono text-warning ms-1 text-uppercase">
                        {{ $latestLog?->confidence ?: 'Belum Ada' }}
                    </strong>
                </div>
                <div>
                    <span class="small opacity-75">Waktu Komputasi:</span>
                    <strong class="font-mono text-white ms-1">
                        {{ $latestLog?->processing_time !== null ? $latestLog->processing_time . ' detik' : '--' }}
                    </strong>
                </div>
                <div>
                    <span class="small opacity-75">Status Antrean:</span>
                    <span class="badge bg-light text-dark font-mono ms-1 text-uppercase">
                        {{ $queue->status }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Breakdown -->
    <div class="d-flex flex-column gap-3">
        <!-- Question -->
        <div class="card card-custom p-4 bg-white">
            <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-semibold small text-uppercase" style="letter-spacing: 0.04em;">
                <i class="bi bi-question-circle-fill"></i> Pertanyaan Soal Esai
            </div>
            <h5 class="fw-semibold text-dark m-0" style="line-height: 1.5;">
                {{ $queue->question }}
            </h5>
            <span class="text-muted font-mono small mt-2 d-block">
                Didaftarkan: {{ $queue->created_at ? $queue->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') : '-' }} WIB
            </span>
        </div>

        <!-- Official Answer Reference -->
        <div class="card card-custom p-4" style="background-color: #F8FAFC; border-left: 4px solid #0284C7;">
            <div class="d-flex align-items-center gap-2 mb-2 text-info-emphasis fw-semibold small text-uppercase" style="letter-spacing: 0.04em;">
                <i class="bi bi-bookmark-check-fill text-info"></i> Kunci Jawaban Resmi (Referensi Pengajar)
            </div>
            <p class="text-dark m-0" style="line-height: 1.6;">
                {{ $correctAnswer ?: 'Tidak tersedia kunci jawaban referensi.' }}
            </p>
        </div>

        <!-- Student Answer -->
        <div class="card card-custom p-4" style="background-color: #FFFBEB; border-left: 4px solid #F59E0B;">
            <div class="d-flex align-items-center gap-2 mb-2 text-warning-emphasis fw-semibold small text-uppercase" style="letter-spacing: 0.04em;">
                <i class="bi bi-pencil-square text-warning"></i> Jawaban yang Diberikan Siswa
            </div>
            <p class="text-dark m-0" style="line-height: 1.6; white-space: pre-wrap;">
                {{ $queue->answer }}
            </p>
        </div>

        <!-- AI Raw Log Breakdown -->
        <div class="card card-custom p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary fw-semibold small text-uppercase" style="letter-spacing: 0.04em;">
                    <i class="bi bi-robot"></i> Respon & Rincian AI
                </span>
                <span class="badge bg-secondary-subtle text-secondary font-mono" style="font-size: 0.72rem;">
                    Percobaan #{{ $latestLog?->attempt ?? 1 }}
                </span>
            </div>
            <pre class="p-3 rounded-2 bg-dark text-light font-mono m-0 small" style="max-height: 300px; overflow-y: auto;">{{ $latestLog?->ai_response ?: 'Belum ada log respon dari model AI.' }}</pre>
        </div>
    </div>

    <!-- Bottom Actions -->
    <div class="d-flex justify-content-between align-items-center pt-2">
        <a href="{{ route('queue.index') }}" class="btn btn-brand-outline">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>

        @if($queue->status === 'failed')
            <a href="{{ route('queue.retry', $queue->id) }}" class="btn btn-brand-danger" onclick="return confirm('Retry antrean ini?')">
                <i class="bi bi-arrow-clockwise"></i> Coba Nilai Ulang
            </a>
        @endif
    </div>

</div>
@endsection

