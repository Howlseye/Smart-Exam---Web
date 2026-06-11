@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Administrasi Platform | AI Queue</h3>
        <div class="d-flex gap-2">
            <form action="{{ route('queue.sync_missing') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning">Sinkronisasi Soal Terjawab</button>
            </form>
            <button type="button" id="processAiBtn" class="btn btn-success">Mulai Proses AI</button>
        </div>
    </div>

    {{-- FILTER --}}
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-2">
            <input type="text" name="keyword" class="form-control" placeholder="Cari ID/Pertanyaan"
                value="{{ request('keyword') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="finish" {{ request('status') == 'finish' ? 'selected' : '' }}>Finish</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"
                placeholder="From Date">
        </div>
        <div class="col-md-2">
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"
                placeholder="To Date">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('queue.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <p><strong>Total: {{ $total ?? 0 }} antrean(s)</strong></p>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Question</th>
                    <th>Answer</th>
                    <th>Score</th>
                    <th>AI Response</th>
                    <th>Processing Time</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($queues as $queue)
                    @php
                        $latestLog = $queue->logs->last();
                    @endphp
                    <tr data-queue-id="{{ $queue->id }}">
                        <td>{{ $queue->id }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($queue->question, 50) ?? '-' }}</td>
                        <td>{!! \Illuminate\Support\Str::limit($queue->answer, 50) ?? '-' !!}</td>
                        <td class="col-score">
                            @if($latestLog && $latestLog->score !== null)
                                <span class="badge bg-primary">{{ $latestLog->score }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="col-ai-response">{{ $latestLog ? \Illuminate\Support\Str::limit($latestLog->ai_response, 50) : '-' }}</td>
                        <td class="col-processing-time">{{ $latestLog && $latestLog->processing_time !== null ? $latestLog->processing_time . 's' : '-' }}</td>
                        <td>{{ $queue->created_at ? $queue->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') . ' WIB' : '-' }}</td>
                        <td>{{ $queue->updated_at ? $queue->updated_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') . ' WIB' : '-' }}</td>
                        <td class="col-status">
                            @if ($queue->status == 'finish' || $queue->status == 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($queue->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($queue->status == 'processing')
                                <span class="badge bg-info">On Progress</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('queue.show', $queue->id) }}" class="btn btn-sm btn-primary">Detail</a>
                            @if ($queue->status == 'failed')
                                <a href="{{ route('queue.retry', $queue->id) }}" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Retry antrean ini?')">Retry</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">Tidak ada data antrean AI Queue</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($queues) && $queues instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $queues->links() }}
    @endif
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const processBtn = document.getElementById('processAiBtn');
        let isProcessing = false;
        let isStopping = false;
        let timeoutId = null;

        if (processBtn) {
            processBtn.addEventListener('click', function() {
                // Jika sedang berjalan, maka klik ini berarti minta stop
                if (isProcessing) {
                    if (!isStopping) {
                        isStopping = true;
                        processBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyelesaikan antrean saat ini...';
                        processBtn.classList.replace('btn-danger', 'btn-secondary');
                        
                        // Jika saat ini sedang menunggu delay, langsung batalkan
                        if (timeoutId) {
                            clearTimeout(timeoutId);
                            timeoutId = null;
                            resetButton();
                        }
                    }
                    return;
                }
                
                // Jika belum jalan, maka klik ini berarti Start
                isProcessing = true;
                isStopping = false;
                processBtn.classList.remove('btn-success');
                processBtn.classList.add('btn-danger');
                processBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Hentikan Proses AI';
                
                processNextQueue();
            });
        }

        function resetButton() {
            isProcessing = false;
            isStopping = false;
            timeoutId = null;
            processBtn.innerHTML = 'Mulai Proses AI';
            processBtn.className = 'btn btn-success';
        }

        function processNextQueue() {
            if (isStopping) {
                resetButton();
                return;
            }

            // Step 1: Ambil antrean berikutnya dan ubah status jadi On Progress
            fetch('/queue/take-next', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'done') {
                    processBtn.innerHTML = 'Semua Selesai!';
                    processBtn.className = 'btn btn-success';
                    setTimeout(() => window.location.reload(), 1500);
                } else if (data.status === 'success') {
                    const rowId = data.data.id;
                    const row = document.querySelector(`tr[data-queue-id="${rowId}"]`);
                    
                    // Tandai UI On Progress
                    if (row) {
                        const statusCol = row.querySelector('.col-status');
                        if (statusCol) {
                            statusCol.innerHTML = '<span class="badge bg-info">On Progress <i class="spinner-border spinner-border-sm" style="width: 10px; height: 10px;"></i></span>';
                        }
                    }

                    if (isStopping) {
                        resetButton();
                        return;
                    }

                    // Step 2: Proses AI ke Gemini (Tunggu sampai selesai)
                    fetch(`/queue/process-id/${rowId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(resData => {
                        if (resData.status === 'success' || resData.status === 'failed') {
                            if (row) {
                                if (resData.data.score !== undefined) {
                                    row.querySelector('.col-score').innerHTML = `<span class="badge bg-primary">${resData.data.score}</span>`;
                                }
                                if (resData.data.ai_response !== undefined) {
                                    row.querySelector('.col-ai-response').innerText = resData.data.ai_response;
                                }
                                if (resData.data.processing_time !== undefined) {
                                    row.querySelector('.col-processing-time').innerText = resData.data.processing_time + 's';
                                }
                                const sCol = row.querySelector('.col-status');
                                if (resData.data.queue_status === 'completed') {
                                    sCol.innerHTML = '<span class="badge bg-success">Completed</span>';
                                } else if (resData.data.queue_status === 'failed') {
                                    sCol.innerHTML = '<span class="badge bg-danger">Failed</span>';
                                }
                            }
                            
                            if (isStopping) {
                                resetButton();
                            } else {
                                // Lanjut ke antrean berikutnya dengan delay
                                timeoutId = setTimeout(processNextQueue, 12000);
                            }
                        } else if (resData.status === 'rate_limit') {
                            console.warn(resData.message);
                            if (isStopping) {
                                resetButton();
                                return;
                            }
                            processBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Limit API! Menunggu 60d...';
                            timeoutId = setTimeout(() => {
                                processBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Hentikan Proses AI';
                                processNextQueue();
                            }, 60000);
                        } else {
                            if (isStopping) {
                                resetButton();
                            } else {
                                timeoutId = setTimeout(processNextQueue, 12000);
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Proses Error:', err);
                        if (isStopping) {
                            resetButton();
                        } else {
                            timeoutId = setTimeout(processNextQueue, 12000);
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Ambil Antrean Error:', error);
                alert('Terjadi kesalahan saat memuat antrean.');
                window.location.reload();
            });
        }
    });
</script>
@endsection
