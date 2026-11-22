<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SmartExam') }} - AI Grading Studio</title>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons 1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-mono: 'JetBrains Mono', SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            
            --bg-canvas: #F8FAFC;
            --bg-surface: #FFFFFF;
            --bg-surface-elevated: #FFFFFF;
            
            --text-heading: #0F172A;
            --text-body: #334155;
            --text-muted: #64748B;
            
            --border-subtle: #E2E8F0;
            --border-strong: #CBD5E1;
            
            --brand-navy: #0B132B;
            --brand-navy-light: #1C2D4A;
            --brand-primary: #1E3A8A;
            --brand-accent: #2563EB;
            
            --status-success: #059669;
            --status-success-bg: #ECFDF5;
            --status-warning: #D97706;
            --status-warning-bg: #FFFBEB;
            --status-danger: #DC2626;
            --status-danger-bg: #FEF2F2;
            --status-info: #0284C7;
            --status-info-bg: #F0F9FF;
            
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --radius-pill: 9999px;

            --shadow-card: 0 1px 3px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-hover: 0 10px 15px -3px rgba(15, 23, 42, 0.06), 0 4px 6px -2px rgba(15, 23, 42, 0.03);
            --shadow-modal: 0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 10px 10px -5px rgba(15, 23, 42, 0.04);
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-canvas);
            color: var(--text-body);
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .font-mono {
            font-family: var(--font-mono);
            letter-spacing: -0.03em;
        }

        /* Top Navbar */
        .site-navbar {
            background-color: var(--brand-navy);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.75rem 1.5rem;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.15rem;
            color: #FFFFFF !important;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            letter-spacing: -0.02em;
        }

        .brand-icon-wrapper {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #2563EB, #1D4ED8);
            border-radius: var(--radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 1rem;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
        }

        .brand-badge {
            font-size: 0.68rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            background: rgba(255, 255, 255, 0.12);
            color: #93C5FD;
            padding: 0.2rem 0.5rem;
            border-radius: var(--radius-pill);
            margin-left: 0.35rem;
        }

        .nav-engine-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.75rem;
            color: #94A3B8;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.3rem 0.75rem;
            border-radius: var(--radius-pill);
            font-family: var(--font-mono);
        }

        .nav-engine-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #10B981;
            box-shadow: 0 0 8px #10B981;
        }

        .nav-user-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #E2E8F0;
            padding: 0.25rem 0.6rem;
            border-radius: var(--radius-sm);
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #334155;
            color: #F1F5F9;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Main Page Layout */
        .main-container {
            max-width: 1400px;
            width: 100%;
            padding: 2rem 1.5rem;
            margin: 0 auto;
            flex: 1;
        }

        /* Card Styles */
        .card-custom {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            transition: all 0.2s ease-in-out;
        }

        /* Buttons */
        .btn-brand-primary {
            background-color: var(--brand-primary);
            color: #FFFFFF;
            border: 1px solid var(--brand-primary);
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: var(--radius-md);
            padding: 0.55rem 1.15rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.15s ease;
        }
        .btn-brand-primary:hover, .btn-brand-primary:focus {
            background-color: #1E40AF;
            border-color: #1E40AF;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .btn-brand-success {
            background-color: var(--status-success);
            color: #FFFFFF;
            border: 1px solid var(--status-success);
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: var(--radius-md);
            padding: 0.55rem 1.15rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.15s ease;
        }
        .btn-brand-success:hover, .btn-brand-success:focus {
            background-color: #047857;
            border-color: #047857;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .btn-brand-danger {
            background-color: var(--status-danger);
            color: #FFFFFF;
            border: 1px solid var(--status-danger);
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: var(--radius-md);
            padding: 0.55rem 1.15rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.15s ease;
        }
        .btn-brand-danger:hover, .btn-brand-danger:focus {
            background-color: #B91C1C;
            border-color: #B91C1C;
            color: #FFFFFF;
        }

        .btn-brand-outline {
            background-color: transparent;
            color: var(--text-body);
            border: 1px solid var(--border-strong);
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: var(--radius-md);
            padding: 0.55rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.15s ease;
        }
        .btn-brand-outline:hover, .btn-brand-outline:focus {
            background-color: #F1F5F9;
            color: var(--text-heading);
            border-color: #94A3B8;
        }

        /* Pulsing indicator */
        .pulse-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10B981;
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .pulse-indicator.warning {
            background-color: #F59E0B;
            animation: pulse-ring-warning 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-ring {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.2); }
        }

        @keyframes pulse-ring-warning {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.2); }
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg site-navbar">
        <div class="container-fluid px-3">
            <a class="navbar-brand" href="{{ route('queue.index') }}">
                <span class="brand-icon-wrapper">
                    <i class="bi bi-mortarboard-fill"></i>
                </span>
                <span>SmartExam</span>
                <span class="brand-badge">AI Grading Studio</span>
            </a>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <div class="nav-engine-pill d-none d-md-inline-flex" title="Engine AI Aktif">
                    <span class="nav-engine-dot"></span>
                    <span>Groq &bull; {{ config('services.groq.model', 'openai/gpt-oss-120b') }}</span>
                </div>

                <div class="nav-user-chip">
                    <span class="user-avatar">AD</span>
                    <span class="d-none d-sm-inline font-medium">Administrator</span>
                </div>

                <!-- 3-Dots Hidden Action Menu -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-link text-white-50 p-1.5 rounded-2 text-decoration-none d-flex align-items-center justify-content-center" 
                            type="button" 
                            id="adminOptionsDropdown" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false" 
                            title="Opsi Lanjutan"
                            style="font-size: 1.15rem; width: 32px; height: 32px; border: 1px solid rgba(255,255,255,0.15);">
                        <i class="bi bi-three-dots-vertical text-light"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2 mt-2" aria-labelledby="adminOptionsDropdown" style="min-width: 250px; border-radius: var(--radius-md);">
                        <li>
                            <h6 class="dropdown-header text-uppercase font-mono text-muted" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                Manajemen & Simulasi
                            </h6>
                        </li>
                        <li>
                            <form action="{{ route('queue.reset_simulation') }}" method="POST" class="m-0" 
                                  onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin me-reset seluruh simulasi?\n\n- Bank soal akan di-reset (20 soal siap diambil ulang aplikasi Android).\n- Semua antrean & log penilaian AI akan dikosongkan.\n\nLanjutkan?');">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger py-2 px-3">
                                    <i class="bi bi-arrow-counterclockwise fs-5"></i>
                                    <div>
                                        <div class="fw-semibold small">Reset Simulasi Ulang</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Kosongkan antrean & segarkan 20 soal</div>
                                    </div>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="main-container">
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="background-color: #ECFDF5; color: #065F46; border-left: 4px solid #059669 !important; border-radius: 8px;">
                <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="background-color: #FEF2F2; color: #991B1B; border-left: 4px solid #DC2626 !important; border-radius: 8px;">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-danger"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    @yield('scripts')
</body>
</html>
