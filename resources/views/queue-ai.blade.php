@extends('layouts.app')

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Page Header & Action Controls -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 fw-bold text-dark m-0" style="letter-spacing: -0.02em;">Antrean Penilaian Esai AI</h1>
                <span id="liveStatusPill" class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1 font-mono" style="font-size: 0.72rem;">
                    <span class="pulse-indicator warning" id="statusPulseDot"></span>
                    <span id="liveStatusText">Memeriksa...</span>
                </span>
            </div>
            <p class="text-muted m-0 small">
                Otomasi evaluasi dan scoring jawaban esai siswa secara asinkron menggunakan model penalaran AI.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Retry All Failed Button (Hanya tampil jika ada antrean berstatus failed) -->
            @if(isset($stats['failed']) && $stats['failed'] > 0)
                <form action="{{ route('queue.retry_all_failed') }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin me-reset semua {{ $stats['failed'] }} antrean yang gagal kembali ke status pending?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger d-inline-flex align-items-center gap-1.5" title="Reset semua antrean yang gagal ke status pending">
                        <i class="bi bi-arrow-clockwise"></i>
                        <span>Ulangi Semua Gagal</span>
                        <span class="badge bg-danger text-white rounded-pill ms-1">{{ $stats['failed'] }}</span>
                    </button>
                </form>
            @endif

            <!-- Sync Missing Button -->
            <form action="{{ route('queue.sync_missing') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-brand-outline" title="Tarik soal esai yang sudah dijawab siswa tapi belum masuk antrean">
                    <i class="bi bi-arrow-repeat text-primary"></i>
                    <span>Sinkronisasi Soal</span>
                    @if(isset($stats['unsynced_count']) && $stats['unsynced_count'] > 0)
                        <span class="badge bg-primary text-white ms-1 rounded-pill">{{ $stats['unsynced_count'] }} baru</span>
                    @endif
                </button>
            </form>

            <!-- Start / Stop Process Button -->
            <button type="button" id="processAiBtn" class="btn btn-brand-success">
                <i class="bi bi-play-circle-fill"></i>
                <span id="processAiBtnLabel">Mulai Penilaian</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Summary Cards -->
    <div class="row g-3">
        <!-- Total In Queue -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Total Antrean</span>
                    <div class="p-2 rounded-2 bg-light text-secondary">
                        <i class="bi bi-stack fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-dark font-mono">{{ $stats['total'] ?? 0 }}</span>
                    <span class="text-muted small">soal esai</span>
                </div>
                <div class="mt-2 pt-2 border-top small text-muted d-flex justify-content-between">
                    <span>Hasil filter saat ini:</span>
                    <span class="fw-semibold text-dark">{{ $total ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Completed Evaluations -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Selesai Dinilai</span>
                    <div class="p-2 rounded-2" style="background-color: var(--status-success-bg); color: var(--status-success);">
                        <i class="bi bi-check2-circle fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-success font-mono">{{ $stats['completed'] ?? 0 }}</span>
                    @php
                        $completionRate = ($stats['total'] ?? 0) > 0 ? round((($stats['completed'] ?? 0) / $stats['total']) * 100) : 0;
                    @endphp
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-mono" style="font-size: 0.75rem;">
                        {{ $completionRate }}%
                    </span>
                </div>
                <div class="mt-2 pt-2 border-top small text-muted d-flex justify-content-between">
                    <span>Gagal dinilai:</span>
                    <span class="fw-semibold {{ ($stats['failed'] ?? 0) > 0 ? 'text-danger' : 'text-muted' }} font-mono">{{ $stats['failed'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Pending & Processing -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Menunggu / Proses</span>
                    <div class="p-2 rounded-2" style="background-color: var(--status-warning-bg); color: var(--status-warning);">
                        <i class="bi bi-hourglass-split fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-warning font-mono">{{ ($stats['pending'] ?? 0) + ($stats['processing'] ?? 0) }}</span>
                    <span class="text-muted small">
                        ({{ $stats['processing'] ?? 0 }} running)
                    </span>
                </div>
                <div class="mt-2 pt-2 border-top small text-muted d-flex justify-content-between">
                    <span>Status antrean:</span>
                    <span class="fw-semibold font-mono {{ ($stats['pending'] ?? 0) > 0 ? 'text-warning' : 'text-muted' }}">
                        {{ ($stats['pending'] ?? 0) }} pending
                    </span>
                </div>
            </div>
        </div>

        <!-- Average Score & Latency -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Rata-rata Nilai & Waktu</span>
                    <div class="p-2 rounded-2" style="background-color: var(--status-info-bg); color: var(--status-info);">
                        <i class="bi bi-speedometer2 fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-primary font-mono">
                        {{ $stats['avg_score'] !== null ? $stats['avg_score'] : '-' }}
                    </span>
                    <span class="text-muted small">/ 100</span>
                </div>
                <div class="mt-2 pt-2 border-top small text-muted d-flex justify-content-between">
                    <span>Rata-rata latensi AI:</span>
                    <span class="fw-semibold text-dark font-mono">{{ $stats['avg_time'] !== null ? $stats['avg_time'] . 's' : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card card-custom p-3 bg-white">
        <form method="GET" action="{{ route('queue.index') }}" class="row g-2 align-items-end">
            <!-- Keyword Search -->
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small fw-semibold text-muted mb-1">Pencarian</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="keyword" class="form-control form-control-sm border-start-0" placeholder="Cari ID atau isi soal..." value="{{ request('keyword') }}">
                </div>
            </div>

            <!-- Status Dropdown -->
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ in_array(request('status'), ['completed', 'finish']) ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="col-6 col-md-2 col-lg-2">
                <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>

            <!-- Date To -->
            <div class="col-6 col-md-2 col-lg-2">
                <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>

            <!-- Filter Buttons -->
            <div class="col-6 col-md-3 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-brand-primary flex-fill">
                    <i class="bi bi-funnel-fill"></i> Terapkan
                </button>
                <a href="{{ route('queue.index') }}" class="btn btn-sm btn-brand-outline" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Main Data Table Card -->
    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                <thead style="background-color: #F1F5F9; border-bottom: 2px solid var(--border-subtle);">
                    <tr class="text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        <th class="ps-3 py-3" style="width: 38px;">
                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input" title="Pilih Semua di Halaman Ini">
                        </th>
                        <th class="py-3" style="width: 70px;">ID</th>
                        <th class="py-3" style="min-width: 250px;">Pertanyaan Soal</th>
                        <th class="py-3" style="min-width: 230px;">Jawaban Siswa</th>
                        <th class="py-3 text-center" style="width: 100px;">Nilai Skor</th>
                        <th class="py-3" style="width: 130px;">Keyakinan</th>
                        <th class="py-3 text-center" style="width: 90px;">Durasi</th>
                        <th class="py-3" style="width: 120px;">Status</th>
                        <th class="py-3 text-end pe-3" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($queues as $queue)
                        @php
                            $latestLog = $queue->logs->last();
                            $score = $latestLog?->score;
                        @endphp
                        <tr data-queue-id="{{ $queue->id }}" style="transition: background 0.15s ease;">
                            <!-- Checkbox -->
                            <td class="ps-3">
                                <input type="checkbox" value="{{ $queue->id }}" class="form-check-input row-select-checkbox" title="Pilih antrean #{{ $queue->id }}">
                            </td>

                            <!-- ID -->
                            <td class="fw-bold font-mono text-secondary">
                                #{{ $queue->id }}
                            </td>

                            <!-- Question -->
                            <td>
                                <div class="fw-semibold text-dark" style="max-width: 320px; line-height: 1.4;">
                                    {{ \Illuminate\Support\Str::limit($queue->question, 65) }}
                                </div>
                                <span class="text-muted font-mono" style="font-size: 0.72rem;">
                                    {{ $queue->created_at ? $queue->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }} WIB
                                </span>
                            </td>

                            <!-- Student Answer -->
                            <td>
                                <div class="text-muted fst-italic" style="max-width: 280px; font-size: 0.8125rem; line-height: 1.4;">
                                    "{{ \Illuminate\Support\Str::limit(strip_tags($queue->answer), 60) }}"
                                </div>
                            </td>

                            <!-- Score Pill -->
                            <td class="text-center col-score">
                                @if($score !== null)
                                    @php
                                        $badgeBg = $score >= 75 ? 'bg-success-subtle text-success border-success-subtle' : ($score >= 50 ? 'bg-warning-subtle text-warning-emphasis border-warning-subtle' : 'bg-danger-subtle text-danger border-danger-subtle');
                                    @endphp
                                    <span class="badge border font-mono fs-6 px-2 py-1 {{ $badgeBg }}" style="min-width: 48px;">
                                        {{ $score }}
                                    </span>
                                @else
                                    <span class="text-muted font-mono">-</span>
                                @endif
                            </td>

                            <!-- Confidence -->
                            <td class="col-confidence">
                                @if($latestLog && $latestLog->confidence)
                                    @php
                                        $conf = strtolower($latestLog->confidence);
                                        $confDot = str_contains($conf, 'tinggi') ? 'bg-success' : (str_contains($conf, 'sedang') ? 'bg-warning' : 'bg-secondary');
                                    @endphp
                                    <span class="d-inline-flex align-items-center gap-1.5 small text-capitalize fw-medium text-dark">
                                        <span class="rounded-circle {{ $confDot }}" style="width: 6px; height: 6px;"></span>
                                        {{ $latestLog->confidence }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            <!-- Processing Latency -->
                            <td class="text-center col-processing-time font-mono small text-muted">
                                @if($latestLog && $latestLog->processing_time !== null)
                                    {{ $latestLog->processing_time }}s
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Queue Status -->
                            <td class="col-status">
                                @if ($queue->status == 'finish' || $queue->status == 'completed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                    </span>
                                @elseif($queue->status == 'pending')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-clock-history me-1"></i> Pending
                                    </span>
                                @elseif($queue->status == 'processing')
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1 rounded-pill">
                                        <span class="spinner-border spinner-border-sm me-1" style="width: 0.7rem; height: 0.7rem;"></span> Proses
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i> Gagal
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-sm btn-brand-outline py-1 px-2 btn-inspect" data-queue-id="{{ $queue->id }}" title="Inspeksi detail pertanyaan, kunci jawaban, dan kritik AI">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>

                                    @if ($queue->status == 'failed')
                                        <a href="{{ route('queue.retry', $queue->id) }}" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="return confirm('Ulangi penilaian antrean ini?')" title="Jadwalkan ulang penilaian">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                    <i class="bi bi-inbox fs-1 mb-2 text-secondary opacity-50"></i>
                                    <h6 class="fw-semibold text-dark mb-1">Belum Ada Antrean Ditemukan</h6>
                                    <p class="small text-muted mb-3">Tidak ada data antrean AI yang sesuai dengan filter pencarian.</p>
                                    @if(isset($stats['unsynced_count']) && $stats['unsynced_count'] > 0)
                                        <form action="{{ route('queue.sync_missing') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-brand-primary">
                                                <i class="bi bi-arrow-repeat me-1"></i> Sinkronisasi {{ $stats['unsynced_count'] }} Soal Terjawab Sekarang
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($queues) && $queues instanceof \Illuminate\Pagination\LengthAwarePaginator && $queues->hasPages())
            <div class="p-3 border-top bg-light d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    Menampilkan <span class="fw-semibold">{{ $queues->firstItem() }}</span> - <span class="fw-semibold">{{ $queues->lastItem() }}</span> dari <span class="fw-semibold">{{ $queues->total() }}</span> antrean
                </span>
                <div>
                    {{ $queues->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Floating Bulk Action Bar (muncul otomatis saat ada baris dipilih) -->
    <div id="bulkActionBar" class="card shadow-lg border border-primary-subtle d-none position-fixed bottom-0 start-50 translate-middle-x mb-4 z-3" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px); color: white; border-radius: var(--radius-md); min-width: 340px;">
        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary font-mono fs-6" id="selectedCountBadge">0</span>
                <span class="small text-light">antrean dipilih</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form id="bulkRetryForm" action="{{ route('queue.retry_bulk') }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin me-reset antrean yang dipilih kembali ke status pending?');">
                    @csrf
                    <div id="bulkHiddenInputsContainer"></div>
                    <button type="submit" class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1.5 py-1 px-2.5 fw-semibold">
                        <i class="bi bi-arrow-clockwise"></i>
                        <span>Ulangi Terpilih</span>
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-link text-white-50 text-decoration-none py-1 px-1.5" id="clearSelectionBtn">
                    Batal
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Inspection / Detail Modal -->
<div class="modal fade" id="queueDetailModal" tabindex="-1" aria-labelledby="queueDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom py-3 px-4" style="background-color: var(--brand-navy); color: #FFFFFF; border-top-left-radius: var(--radius-lg); border-top-right-radius: var(--radius-lg);">
                <div class="d-flex align-items-center gap-2">
                    <span class="brand-icon-wrapper" style="width: 28px; height: 28px; font-size: 0.85rem;">
                        <i class="bi bi-file-earmark-check-fill"></i>
                    </span>
                    <h5 class="modal-title fw-bold m-0" id="queueDetailModalLabel" style="font-size: 1.05rem;">
                        Inspeksi Evaluasi Antrean <span id="modalQueueId" class="font-mono text-info">#...</span>
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-light" id="modalBodyContent">
                <!-- Skeleton Loader -->
                <div id="modalLoadingSpinner" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat data...</span>
                    </div>
                    <p class="text-muted small mt-2">Mengambil rincian evaluasi AI...</p>
                </div>

                <div id="modalLoadedContent" class="d-none d-flex flex-column gap-3">
                    <!-- AI Score Overview Banner -->
                    <div class="card card-custom p-3 border-0" style="background: linear-gradient(135deg, #1E3A8A, #0F172A); color: white;">
                        <div class="row align-items-center g-3">
                            <div class="col-sm-6 text-center text-sm-start">
                                <span class="small text-uppercase text-light opacity-75 fw-semibold" style="letter-spacing: 0.05em; font-size: 0.72rem;">Hasil Penilaian AI</span>
                                <div class="d-flex align-items-baseline gap-2 justify-content-center justify-content-sm-start mt-1">
                                    <span id="modalScoreDisplay" class="fs-1 fw-bold font-mono">--</span>
                                    <span class="text-light opacity-75">/ 100</span>
                                </div>
                            </div>
                            <div class="col-sm-6 d-flex flex-column gap-1 text-sm-end text-center">
                                <div class="small">
                                    <span class="opacity-75">Keyakinan:</span>
                                    <span id="modalConfidenceDisplay" class="fw-semibold font-mono text-warning">--</span>
                                </div>
                                <div class="small">
                                    <span class="opacity-75">Waktu Komputasi:</span>
                                    <span id="modalTimeDisplay" class="font-mono">-- detik</span>
                                </div>
                                <div class="small">
                                    <span class="opacity-75">Status:</span>
                                    <span id="modalStatusBadge" class="badge bg-light text-dark font-mono">--</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Question Box -->
                    <div class="card card-custom p-3 bg-white">
                        <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-semibold small text-uppercase" style="letter-spacing: 0.04em;">
                            <i class="bi bi-question-circle-fill"></i> Pertanyaan Soal
                        </div>
                        <p id="modalQuestionText" class="text-dark m-0 fw-medium" style="line-height: 1.5; font-size: 0.925rem;"></p>
                    </div>

                    <!-- Teacher Reference Answer -->
                    <div class="card card-custom p-3" style="background-color: #F8FAFC; border-left: 4px solid #0284C7;">
                        <div class="d-flex align-items-center gap-2 mb-2 text-info-emphasis fw-semibold small text-uppercase" style="letter-spacing: 0.04em;">
                            <i class="bi bi-bookmark-check-fill text-info"></i> Kunci Jawaban Resmi (Referensi Guru)
                        </div>
                        <p id="modalReferenceText" class="text-dark m-0 small" style="line-height: 1.5;"></p>
                    </div>

                    <!-- Student Answer -->
                    <div class="card card-custom p-3" style="background-color: #FFFBEB; border-left: 4px solid #F59E0B;">
                        <div class="d-flex align-items-center gap-2 mb-2 text-warning-emphasis fw-semibold small text-uppercase" style="letter-spacing: 0.04em;">
                            <i class="bi bi-pencil-square text-warning"></i> Jawaban Siswa
                        </div>
                        <p id="modalStudentAnswerText" class="text-dark m-0 small" style="line-height: 1.5; white-space: pre-wrap;"></p>
                    </div>

                    <!-- AI Raw Critique / Response -->
                    <div class="card card-custom p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary fw-semibold small text-uppercase" style="letter-spacing: 0.04em;">
                                <i class="bi bi-robot"></i> Respon & Analisis Model AI
                            </span>
                            <span class="badge bg-secondary-subtle text-secondary font-mono" style="font-size: 0.7rem;">Groq &bull; {{ config('services.groq.model', 'openai/gpt-oss-120b') }}</span>
                        </div>
                        <pre id="modalAiResponseCode" class="p-3 rounded-2 bg-dark text-light font-mono m-0 small" style="max-height: 200px; overflow-y: auto; font-size: 0.78rem;"></pre>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top bg-white py-2.5 px-4 d-flex justify-content-between">
                <span class="text-muted font-mono small" id="modalTimestamp">--</span>
                <button type="button" class="btn btn-sm btn-brand-outline" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const processBtn = document.getElementById('processAiBtn');
        const processBtnLabel = document.getElementById('processAiBtnLabel');
        const liveStatusPill = document.getElementById('liveStatusPill');
        const liveStatusText = document.getElementById('liveStatusText');
        const statusPulseDot = document.getElementById('statusPulseDot');

        let isProcessing = false;
        let pollingInterval = null;

        // Cek status saat pertama kali load
        checkStatus();

        if (processBtn) {
            processBtn.addEventListener('click', function() {
                if (isProcessing) {
                    stopProcess();
                } else {
                    startProcess();
                }
            });
        }

        function checkStatus() {
            fetch('/queue/status')
                .then(res => res.json())
                .then(data => {
                    isProcessing = data.is_active;
                    
                    if (isProcessing) {
                        setButtonProcessing();
                        liveStatusPill.className = 'badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1.5 font-mono';
                        statusPulseDot.className = 'pulse-indicator';
                        liveStatusText.textContent = 'Proses Berjalan';

                        if (!pollingInterval) {
                            // Polling refresh page setiap 6 detik jika aktif untuk update realtime
                            pollingInterval = setInterval(() => {
                                window.location.reload();
                            }, 6000);
                        }
                    } else {
                        setButtonIdle();
                        liveStatusPill.className = 'badge bg-secondary-subtle text-secondary border px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1.5 font-mono';
                        statusPulseDot.className = 'pulse-indicator warning';
                        liveStatusText.textContent = data.has_pending ? `${data.has_pending ? 'Antrean Tersedia' : 'Idle'}` : 'Siap / Idle';

                        if (pollingInterval) {
                            clearInterval(pollingInterval);
                            pollingInterval = null;
                        }
                        
                        // Jika ada data progress, tapi server berhenti, reload sekali
                        if (data.has_processing && !data.is_active) {
                            window.location.reload();
                        }
                    }
                })
                .catch(() => {
                    liveStatusText.textContent = 'Offline';
                });
        }

        function startProcess() {
            setButtonProcessing();
            liveStatusText.textContent = 'Memulai...';

            fetch('/queue/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => checkStatus());
        }

        function stopProcess() {
            processBtn.disabled = true;
            processBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menghentikan...';
            processBtn.className = 'btn btn-sm btn-secondary';
            liveStatusText.textContent = 'Menghentikan...';
            
            fetch('/queue/stop', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => {
                isProcessing = false;
                processBtn.disabled = false;
                setButtonIdle();
                if (pollingInterval) clearInterval(pollingInterval);
                checkStatus();
            });
        }

        function setButtonProcessing() {
            processBtn.className = 'btn btn-brand-danger';
            processBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Hentikan Penilaian';
        }

        function setButtonIdle() {
            processBtn.className = 'btn btn-brand-success';
            processBtn.innerHTML = '<i class="bi bi-play-circle-fill me-1"></i> Mulai Penilaian';
        }

        // Inspeksi / Detail Modal Handler
        const inspectButtons = document.querySelectorAll('.btn-inspect');
        const detailModalEl = document.getElementById('queueDetailModal');
        const detailModal = new bootstrap.Modal(detailModalEl);

        const modalLoadingSpinner = document.getElementById('modalLoadingSpinner');
        const modalLoadedContent = document.getElementById('modalLoadedContent');
        const modalQueueId = document.getElementById('modalQueueId');
        const modalScoreDisplay = document.getElementById('modalScoreDisplay');
        const modalConfidenceDisplay = document.getElementById('modalConfidenceDisplay');
        const modalTimeDisplay = document.getElementById('modalTimeDisplay');
        const modalStatusBadge = document.getElementById('modalStatusBadge');
        const modalQuestionText = document.getElementById('modalQuestionText');
        const modalReferenceText = document.getElementById('modalReferenceText');
        const modalStudentAnswerText = document.getElementById('modalStudentAnswerText');
        const modalAiResponseCode = document.getElementById('modalAiResponseCode');
        const modalTimestamp = document.getElementById('modalTimestamp');

        inspectButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const queueId = this.getAttribute('data-queue-id');
                modalQueueId.textContent = `#${queueId}`;
                
                modalLoadingSpinner.classList.remove('d-none');
                modalLoadedContent.classList.add('d-none');
                detailModal.show();

                fetch(`/queue/${queueId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success' && res.data) {
                        const d = res.data;
                        modalQuestionText.textContent = d.question || '-';
                        modalReferenceText.textContent = d.correct_answer || 'Tidak ada referensi kunci jawaban.';
                        modalStudentAnswerText.textContent = d.answer || '-';

                        if (d.log) {
                            modalScoreDisplay.textContent = d.log.score !== null ? d.log.score : '--';
                            modalConfidenceDisplay.textContent = d.log.confidence ? d.log.confidence.toUpperCase() : '--';
                            modalTimeDisplay.textContent = (d.log.processing_time !== null ? d.log.processing_time : '--') + ' detik';
                            modalStatusBadge.textContent = d.log.status.toUpperCase();
                            modalAiResponseCode.textContent = d.log.ai_response || '(Tidak ada data respon)';
                            modalTimestamp.textContent = `Dievaluasi: ${d.log.created_at}`;
                        } else {
                            modalScoreDisplay.textContent = '--';
                            modalConfidenceDisplay.textContent = 'Belum Ada';
                            modalTimeDisplay.textContent = '--';
                            modalStatusBadge.textContent = d.status.toUpperCase();
                            modalAiResponseCode.textContent = '(Antrean ini belum diproses oleh AI atau masih dalam antrean pending)';
                            modalTimestamp.textContent = `Dibuat: ${d.created_at}`;
                        }

                        modalLoadingSpinner.classList.add('d-none');
                        modalLoadedContent.classList.remove('d-none');
                    }
                })
                .catch(err => {
                    modalLoadingSpinner.innerHTML = `
                        <div class="text-danger">
                            <i class="bi bi-exclamation-octagon-fill fs-2"></i>
                            <p class="mt-2 mb-0">Gagal mengambil detail antrean.</p>
                        </div>
                    `;
                });
            });
        });

        // ==========================================
        // Bulk Selection Checkbox & Retry Handler
        // ==========================================
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const rowCheckboxes = document.querySelectorAll('.row-select-checkbox');
        const bulkActionBar = document.getElementById('bulkActionBar');
        const selectedCountBadge = document.getElementById('selectedCountBadge');
        const bulkHiddenInputsContainer = document.getElementById('bulkHiddenInputsContainer');
        const clearSelectionBtn = document.getElementById('clearSelectionBtn');

        function updateBulkSelection() {
            const checkedBoxes = document.querySelectorAll('.row-select-checkbox:checked');
            const count = checkedBoxes.length;

            if (count > 0) {
                if (bulkActionBar) bulkActionBar.classList.remove('d-none');
                if (selectedCountBadge) selectedCountBadge.textContent = count;

                // Sinkronisasi data ID ke hidden inputs di dalam form
                if (bulkHiddenInputsContainer) {
                    bulkHiddenInputsContainer.innerHTML = '';
                    checkedBoxes.forEach(cb => {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'queue_ids[]';
                        hiddenInput.value = cb.value;
                        bulkHiddenInputsContainer.appendChild(hiddenInput);
                    });
                }
            } else {
                if (bulkActionBar) bulkActionBar.classList.add('d-none');
                if (bulkHiddenInputsContainer) bulkHiddenInputsContainer.innerHTML = '';
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = (count > 0 && count === rowCheckboxes.length);
                selectAllCheckbox.indeterminate = (count > 0 && count < rowCheckboxes.length);
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => {
                    cb.checked = selectAllCheckbox.checked;
                });
                updateBulkSelection();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkSelection);
        });

        if (clearSelectionBtn) {
            clearSelectionBtn.addEventListener('click', function() {
                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                rowCheckboxes.forEach(cb => {
                    cb.checked = false;
                });
                updateBulkSelection();
            });
        }
    });
</script>
@endsection
