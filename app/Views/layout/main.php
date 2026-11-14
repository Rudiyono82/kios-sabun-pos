<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Kios Sabun & Parfum POS') ?></title>
    
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">

    <!-- Select2 Searchable Dropdown & Bootstrap 5 Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

    <!-- Core JavaScript Libraries Loaded in Head to Guarantee Availability for All Views -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Global Synchronizer: Otomatis sinkronisasi tampilan komponen Select2 saat nilai .val(...) diisi via JavaScript/Database retrieve
        (function($) {
            if (!$) return;
            const origVal = $.fn.val;
            $.fn.val = function(value) {
                if (arguments.length === 0) {
                    return origVal.call(this);
                }
                const res = origVal.apply(this, arguments);
                if (this && this.length) {
                    this.each(function() {
                        if (this.nodeType === 1 && this.tagName === 'SELECT' && $(this).hasClass('select2-hidden-accessible')) {
                            if (!$(this).data('_s2_syncing')) {
                                $(this).data('_s2_syncing', true);
                                $(this).trigger('change');
                                $(this).removeData('_s2_syncing');
                            }
                        }
                    });
                }
                return res;
            };
        })(jQuery);
    </script>

    <style>
        :root {
            --app-primary: #0284c7;
            --app-primary-dark: #0369a1;
            --app-secondary: #0d9488;
            --app-bg: #f8fafc;
            --app-sidebar: #0f172a;
            --app-card-border: rgba(226, 232, 240, 0.8);
        }

        /* Penyesuaian Komponen Select2 Searchable Dropdown */
        .select2-container--bootstrap-5 .select2-selection {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            border-color: #cbd5e1;
            border-radius: 8px;
            min-height: 38px;
            padding: 4px 8px;
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #0284c7;
            box-shadow: 0 0 0 0.2rem rgba(2, 132, 199, 0.2);
        }
        .select2-dropdown {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            border-color: #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            z-index: 99999 !important;
        }
        .select2-search--dropdown .select2-search__field {
            border-radius: 6px;
            border-color: #cbd5e1;
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #0284c7;
            color: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--app-bg);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
        }
        #sidebar {
            width: 265px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            transition: all 0.3s;
            z-index: 100;
            height: 100vh;
            position: sticky;
            top: 0;
        }
        #sidebar .brand {
            padding: 20px 18px 18px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 42, 0.35);
        }
        #sidebar .brand-badge {
            width: 44px;
            height: 44px;
            min-width: 44px;
            background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(13, 110, 253, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        #sidebar .brand:hover .brand-badge {
            transform: scale(1.05);
            box-shadow: 0 6px 18px rgba(13, 110, 253, 0.6);
        }
        #sidebar .brand-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.25;
            letter-spacing: 0.4px;
        }
        #sidebar .brand-subtitle {
            color: #94a3b8;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            display: block;
            margin-top: 2px;
        }
        #sidebar .sidebar-menu-wrapper {
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 24px;
        }
        #sidebar .sidebar-menu-wrapper::-webkit-scrollbar {
            width: 5px;
        }
        #sidebar .sidebar-menu-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }
        #sidebar .sidebar-menu-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .sidebar-section-title {
            color: #64748b;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 14px 18px 4px 18px;
            display: block;
        }
        #sidebar .nav-link {
            color: #94a3b8;
            padding: 10px 16px;
            font-weight: 500;
            font-size: 0.90rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.2s;
        }
        #sidebar .nav-link.parent-menu {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            color: #cbd5e1;
            cursor: pointer;
        }
        #sidebar .nav-link.parent-menu .chevron-icon {
            font-size: 0.75rem;
            transition: transform 0.25s ease;
            opacity: 0.7;
        }
        #sidebar .nav-link.parent-menu[aria-expanded="true"] .chevron-icon {
            transform: rotate(180deg);
            opacity: 1;
        }
        #sidebar .sub-menu {
            padding-left: 6px;
            margin-left: 18px;
            border-left: 1px solid rgba(255, 255, 255, 0.12);
            margin-bottom: 6px;
            margin-top: 2px;
        }
        #sidebar .sub-menu .nav-link {
            padding: 7.5px 12px;
            font-size: 0.84rem;
            margin: 1.5px 2px;
            gap: 8px;
            border-radius: 6px;
            color: #94a3b8;
        }
        #sidebar .sub-menu .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(3px);
        }
        #sidebar .sub-menu .nav-link.active {
            background: rgba(2, 132, 199, 0.25);
            color: #38bdf8;
            font-weight: 600;
            border-left: 3px solid #38bdf8;
            box-shadow: none;
        }
        #sidebar .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
            transform: translateX(3px);
        }
        #sidebar .nav-link.active {
            color: #ffffff;
            background: linear-gradient(90deg, #0284c7 0%, #0369a1 100%);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }
        #sidebar .nav-link i {
            font-size: 1.15rem;
        }
        #content-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow-x: clip;
        }
        /* ============================================================ */
        /* TOP NAVBAR MODERN DARK NAVY DESIGN & FREEZE STICKY           */
        /* Memberikan Kontras Elegan Membedakan Header dari Isi Screen  */
        /* ============================================================ */
        .top-navbar {
            background: linear-gradient(90deg, #0f172a 0%, #1e293b 60%, #0f172a 100%);
            border-bottom: 2px solid #0284c7;
            padding: 10px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.25);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #btn-toggle-sidebar {
            background: rgba(255, 255, 255, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, 0.22) !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            transition: all 0.2s ease;
        }
        #btn-toggle-sidebar:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #38bdf8 !important;
            border-color: #38bdf8 !important;
        }
        .top-navbar-title {
            font-size: 1.15rem;
            font-weight: 800 !important;
            color: #ffffff !important;
            letter-spacing: -0.2px;
        }
        .top-navbar-title .badge {
            background: rgba(2, 132, 199, 0.25) !important;
            color: #38bdf8 !important;
            border-color: rgba(56, 189, 248, 0.4) !important;
        }
        .branch-switcher-btn {
            background: rgba(16, 185, 129, 0.15) !important;
            border: 1px solid rgba(52, 211, 153, 0.45) !important;
            color: #34d399 !important;
            font-weight: 700 !important;
            border-radius: 30px !important;
            padding: 0.35rem 0.85rem !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }
        .branch-switcher-btn:hover,
        .branch-switcher-btn:focus {
            background: rgba(16, 185, 129, 0.28) !important;
            border-color: #6ee7b7 !important;
            color: #a7f3d0 !important;
            box-shadow: 0 3px 12px rgba(16, 185, 129, 0.3);
        }
        .navbar-clock-pill {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 30px;
            padding: 4px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        .navbar-clock-pill #live-clock {
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            font-weight: 800;
            font-size: 0.90rem;
            color: #38bdf8;
        }
        .navbar-clock-pill small {
            color: #94a3b8 !important;
        }
        .top-navbar .vr {
            background-color: rgba(255, 255, 255, 0.2) !important;
            opacity: 1;
        }
        .navbar-quick-tx-btn {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(56, 189, 248, 0.45) !important;
            font-weight: 700 !important;
            border-radius: 30px !important;
            padding: 0.38rem 0.95rem !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
            transition: all 0.2s ease;
        }
        .navbar-quick-tx-btn:hover {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%) !important;
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.5);
            transform: translateY(-1px);
        }
        .navbar-user-btn {
            background: rgba(255, 255, 255, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, 0.22) !important;
            border-radius: 30px !important;
            padding: 3px 12px 3px 5px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }
        .navbar-user-btn:hover {
            background: rgba(255, 255, 255, 0.22) !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3);
        }
        .navbar-user-btn .text-dark {
            color: #ffffff !important;
        }
        .navbar-user-btn .text-muted {
            color: #94a3b8 !important;
        }

        /* ============================================================ */
        /* TIPOGRAFI TEBAL: LABEL FIELD INPUT & HEADER KOLOM GRID       */
        /* ============================================================ */
        .form-label,
        label:not(.form-check-label):not(.btn) {
            font-weight: 750 !important;
            color: #0f172a !important;
            font-size: 0.84rem;
            letter-spacing: 0.15px;
            margin-bottom: 0.3rem;
        }
        .form-label.small,
        label.small {
            font-weight: 750 !important;
            font-size: 0.80rem;
        }
        .card-modern h6,
        .card-header h6,
        .modal-body h6,
        .modal-title {
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -0.15px;
        }

        .main-content {
            padding: 28px;
            flex-grow: 1;
        }
        .card-modern {
            background: #ffffff;
            border: 1px solid var(--app-card-border);
            border-radius: 16px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
        }
        .card-modern:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
        }
        .metric-card {
            padding: 22px;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
        }
        .metric-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }
        .badge-subtle-success { background: #dcfce7; color: #15803d; }
        .badge-subtle-primary { background: #e0f2fe; color: #0369a1; }
        .badge-subtle-warning { background: #fef9c3; color: #a16207; }
        .badge-subtle-danger { background: #fee2e2; color: #b91c1c; }

        /* ============================================================ */
        /* STYLING GRID LIST / TABEL: ZEBRA, HEADER KONTRAS & ELLIPSIS */
        /* ============================================================ */
        /* 1. Header Grid List Berwarna & Kontras Tinggi */
        .table thead,
        .table thead tr,
        .table thead th,
        .table thead.table-light th,
        .table thead.table-dark th,
        table.dataTable thead th {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 0.81rem !important;
            letter-spacing: 0.65px;
            text-transform: uppercase;
            border-bottom: 2px solid #0284c7 !important; /* aksen biru */
            border-top: none !important;
            vertical-align: middle;
            padding: 13px 14px !important;
        }

        .table thead tr th:first-child {
            border-top-left-radius: 8px;
        }
        .table thead tr th:last-child {
            border-top-right-radius: 8px;
        }

        /* DataTables Sorting Icon Kontras Putih */
        table.dataTable thead > tr > th.sorting:before,
        table.dataTable thead > tr > th.sorting:after,
        table.dataTable thead > tr > th.sorting_asc:before,
        table.dataTable thead > tr > th.sorting_asc:after,
        table.dataTable thead > tr > th.sorting_desc:before,
        table.dataTable thead > tr > th.sorting_desc:after {
            color: #ffffff !important;
            opacity: 0.75;
        }
        table.dataTable thead > tr > th.sorting_asc:after,
        table.dataTable thead > tr > th.sorting_desc:after {
            opacity: 1 !important;
            color: #38bdf8 !important;
        }

        /* 2. Warna Baris Selang-Seling (Zebra Striping) & Hover Modern */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .table > tbody > tr:nth-of-type(odd) > * {
            background-color: #ffffff !important;
            color: #1e293b;
        }

        .table > tbody > tr:nth-of-type(even) > * {
            background-color: #f8fafc !important; /* warna baris selang-seling */
            color: #1e293b;
        }

        .table > tbody > tr:hover > * {
            background-color: #f1f5f9 !important; /* hover halus */
            transition: background-color 0.15s ease-in-out;
        }

        .table td {
            padding: 11px 14px !important;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        /* Cegah pemecahan baris pada kolom nilai uang (Rp) */
        .text-nowrap,
        .table td.text-end,
        .table th.text-end,
        .table tfoot td.text-end {
            white-space: nowrap !important;
        }

        /* 3. Pemotongan Teks Panjang dengan Ellipsis (...) & Tooltip */
        .cell-truncate,
        .truncate-text {
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: middle;
            cursor: help;
        }

        /* Custom Bootstrap Tooltip */
        .tooltip .tooltip-inner {
            max-width: 340px;
            padding: 8px 12px;
            background-color: #0f172a;
            color: #f8fafc;
            border-radius: 8px;
            font-size: 0.8rem;
            line-height: 1.4;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            text-align: left;
            word-break: break-word;
        }
        .tooltip.bs-tooltip-top .tooltip-arrow::before {
            border-top-color: #0f172a;
        }
        .tooltip.bs-tooltip-bottom .tooltip-arrow::before {
            border-bottom-color: #0f172a;
        }

        /* ============================================================ */
        /* DRAGGABLE POPUP / MODAL & HEADER DISTINCT COLOR              */
        /* ============================================================ */
        .modal-content {
            border-radius: 1rem !important;
            overflow: hidden !important;
            border: none !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.15) !important;
        }

        /* Default Header: Kontras Elegan berbeda dari putihnya isi popup */
        .modal-header {
            cursor: grab;
            user-select: none;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1) !important;
            padding: 1.1rem 1.5rem !important;
        }
        .modal-header:active {
            cursor: grabbing;
        }
        .modal-header .modal-title,
        .modal-header h4,
        .modal-header h5,
        .modal-header h6 {
            color: #ffffff !important;
            font-weight: 700;
        }
        .modal-header .text-muted,
        .modal-header small {
            color: rgba(255, 255, 255, 0.75) !important;
        }
        .modal-header .btn-close {
            cursor: pointer !important;
            filter: invert(1) grayscale(100%) brightness(200%);
            opacity: 0.85;
        }
        .modal-header .btn-close:hover {
            opacity: 1;
        }

        /* Indikator visual pegangan (grip) bahwa header modal bisa digeser */
        .modal-header .modal-title::before {
            content: "⋮⋮ ";
            opacity: 0.6;
            margin-right: 5px;
            font-size: 1.15rem;
            letter-spacing: -2px;
            display: inline-block;
            vertical-align: middle;
            cursor: grab;
        }

        /* Variasi Warna Khusus Header Modal */
        .modal-header.bg-primary {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            color: #ffffff !important;
        }
        .modal-header.bg-success {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
            color: #ffffff !important;
        }
        .modal-header.bg-danger {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
            color: #ffffff !important;
        }
        .modal-header.bg-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #0f172a !important;
        }
        .modal-header.bg-warning .modal-title,
        .modal-header.bg-warning h4,
        .modal-header.bg-warning h5,
        .modal-header.bg-warning h6 {
            color: #0f172a !important;
        }
        .modal-header.bg-warning .text-muted,
        .modal-header.bg-warning small {
            color: rgba(15, 23, 42, 0.75) !important;
        }
        .modal-header.bg-warning .btn-close {
            filter: none !important;
        }
        .modal-header.bg-dark {
            background: linear-gradient(135deg, #0f172a 0%, #020617 100%) !important;
            color: #ffffff !important;
        }

        /* Modal Body tetap bersih & kontras terang */
        .modal-body {
            background-color: #ffffff !important;
            color: #1e293b !important;
        }
        /* Sticky Modal Footer: Selalu terlihat & mudah diklik di bagian bawah popup */
        .modal-footer {
            position: sticky;
            bottom: 0;
            z-index: 1050;
            background-color: #f8fafc !important;
            border-top: 1px solid #e2e8f0 !important;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* ============================================================ */
        /* SCROLL VERTIKAL JENDELA / MODAL SAAT POPUP MELEBIHI LAYAR    */
        /* ============================================================ */
        body.modal-open {
            overflow-y: scroll !important; /* Selalu ada scroll vertikal di bagian kanan window */
            padding-right: 0px !important;
        }

        .modal {
            overflow-x: hidden !important;
            overflow-y: auto !important; /* Scroll vertikal selalu aktif di sisi kanan saat tinggi popup melebihi window */
            -webkit-overflow-scrolling: touch;
        }

        .modal-dialog {
            margin-top: 1.5rem !important;
            margin-bottom: 3.5rem !important; /* Ruang lega di bagian bawah agar footer leluasa diklik */
        }

        /* Scrollbar visual di sisi kanan window saat modal terbuka */
        .modal::-webkit-scrollbar,
        body::-webkit-scrollbar {
            width: 12px;
        }
        .modal::-webkit-scrollbar-track,
        body::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .modal::-webkit-scrollbar-thumb,
        body::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 6px;
            border: 3px solid #f1f5f9;
        }
        .modal::-webkit-scrollbar-thumb:hover,
        body::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Backdrop lebih transparan & tidak pekat agar konten belakang tetap terlihat jelas saat modal digeser */
        .modal-backdrop.show {
            opacity: 0.3 !important;
        }
        .swal2-container.swal2-backdrop-show {
            background: rgba(15, 23, 42, 0.35) !important;
        }

        /* SweetAlert2 Popup Header Styling (Beda warna header dan isi popup) */
        .swal2-popup {
            display: flex !important;
            flex-direction: column !important;
            border-radius: 1.25rem !important;
            overflow: hidden !important;
            padding: 0 0 1.5rem 0 !important;
            border: none !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        }
        .swal2-title {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            color: #ffffff !important;
            padding: 1.15rem 1.5rem !important;
            margin: 0 !important;
            width: 100% !important;
            font-size: 1.25rem !important;
            border-bottom: 2px solid #0284c7 !important;
            cursor: grab !important;
            order: -1 !important; /* Posisikan judul sebagai Header di bagian paling atas */
            user-select: none;
        }
        .swal2-title:active {
            cursor: grabbing !important;
        }
        .swal2-icon {
            margin: 1.5rem auto 0.5rem auto !important;
        }
        .swal2-html-container {
            padding: 0.5rem 1.5rem 1rem 1.5rem !important;
            color: #334155 !important;
            font-size: 0.95rem !important;
        }
        .swal2-close {
            color: #ffffff !important;
            top: 12px !important;
            right: 12px !important;
            z-index: 10 !important;
        }

        /* ============================================================ */
        /* RESPONSIVE NAVIGATION & OFF-CANVAS / COLLAPSIBLE SIDEBAR     */
        /* Optimal untuk HP, Tablet 10-12 Inch, dan Desktop             */
        /* ============================================================ */
        #sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            z-index: 1040;
            transition: opacity 0.3s ease;
        }
        #sidebar-backdrop.show {
            display: block;
        }

        /* Desktop Collapse State (>= 1200px) */
        body.sidebar-collapsed #sidebar {
            margin-left: -265px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Tablet (hingga 12 Inch / 1199.98px) & Smartphone */
        @media (max-width: 1199.98px) {
            #sidebar {
                position: fixed !important;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.35);
            }
            #sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                padding: 16px 18px !important;
            }
            .top-navbar {
                padding: 10px 18px !important;
            }
            /* Optimalisasi Elemen Layar Sentuh Tablet */
            .btn-sm {
                padding: 0.35rem 0.65rem;
                font-size: 0.85rem;
            }
            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Khusus Tablet 10-12 Inch (768px s/d 1280px) */
        @media (min-width: 768px) and (max-width: 1280px) {
            .metric-card {
                padding: 18px;
            }
            .metric-card h3 {
                font-size: 1.45rem;
            }
            .table thead th {
                font-size: 0.78rem !important;
                padding: 10px 10px !important;
            }
            .table tbody td {
                padding: 10px 10px !important;
                font-size: 0.88rem;
            }
        }
    </style>
</head>
<body>

    <!-- Backdrop Overlay for Mobile Sidebar -->
    <div id="sidebar-backdrop"></div>

    <!-- Sidebar Menu -->
    <aside id="sidebar">
        <div class="brand">
            <div class="brand-badge">
                <i class="bi bi-droplet-half fs-4"></i>
            </div>
            <div class="overflow-hidden">
                <div class="brand-title">KIOS SABUN &amp; PARFUM</div>
                <span class="brand-subtitle">METLAND CILEUNGSI</span>
            </div>
        </div>

        <div class="p-3 pb-2">
            <a href="<?= base_url('pos') ?>" class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm rounded-3">
                <i class="bi bi-cart-check-fill fs-5"></i> Buka Kasir (POS)
            </a>
        </div>

        <?php
            $uri = uri_string();
            $isMasterActive = (!str_contains($uri, 'laporan') && (str_contains($uri, 'barang') || str_contains($uri, 'kategori') || str_contains($uri, 'satuan') || str_contains($uri, 'supplier') || str_contains($uri, 'member') || str_contains($uri, 'barcode')));
            $isTransaksiActive = (!str_contains($uri, 'laporan') && (str_contains($uri, 'pos') || str_contains($uri, 'penjualan') || str_contains($uri, 'pembelian') || str_contains($uri, 'retur') || str_contains($uri, 'produksi') || str_contains($uri, 'keuangan') || str_contains($uri, 'stok-opname') || str_contains($uri, 'transfer-stok')));
            $isLaporanActive = (str_contains($uri, 'laporan'));
            $isConfigActive = (str_contains($uri, 'karyawan') || str_contains($uri, 'cabang') || str_contains($uri, 'profile') || str_contains($uri, 'audit-trail') || str_contains($uri, 'live-kasir') || str_contains($uri, 'monitoring-kasir'));
        ?>

        <div class="sidebar-menu-wrapper">
            <ul class="nav flex-column">
                <!-- 1. DASHBOARD UTAMA -->
                <li class="nav-item">
                    <a href="<?= base_url('dashboard') ?>" class="nav-link <?= uri_string() == 'dashboard' || uri_string() == '' ? 'active' : '' ?>">
                        <i class="bi bi-grid-1x2-fill text-primary"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- ======================================================== -->
                <!-- PARENT 1: MASTER DATA                                    -->
                <!-- ======================================================== -->
                <div class="sidebar-section-title">Master Data</div>
                <li class="nav-item">
                    <a class="nav-link parent-menu <?= $isMasterActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#collapseMasterData" role="button" aria-expanded="<?= $isMasterActive ? 'true' : 'false' ?>">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-database-fill text-info"></i>
                            <span>Master Data</span>
                        </div>
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </a>
                    <div class="collapse <?= $isMasterActive ? 'show' : '' ?>" id="collapseMasterData">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a href="<?= base_url('barang') ?>" class="nav-link <?= ($uri === 'barang' || (str_contains($uri, 'barang') && !str_contains($uri, 'laporan'))) ? 'active' : '' ?>">
                                    <i class="bi bi-boxes"></i>
                                    <span>Master Barang & Stok</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('kategori') ?>" class="nav-link <?= str_contains($uri, 'kategori') ? 'active' : '' ?>">
                                    <i class="bi bi-tag-fill text-info"></i>
                                    <span>Master Kategori Produk</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('satuan') ?>" class="nav-link <?= str_contains($uri, 'satuan') ? 'active' : '' ?>">
                                    <i class="bi bi-rulers text-warning"></i>
                                    <span>Master Satuan Barang</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('supplier') ?>" class="nav-link <?= str_contains($uri, 'supplier') ? 'active' : '' ?>">
                                    <i class="bi bi-truck text-primary"></i>
                                    <span>Master Data Supplier</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('member') ?>" class="nav-link <?= str_contains($uri, 'member') ? 'active' : '' ?>">
                                    <i class="bi bi-people-fill text-success"></i>
                                    <span>Pelanggan & Member</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('barcode') ?>" class="nav-link <?= str_contains($uri, 'barcode') ? 'active' : '' ?>">
                                    <i class="bi bi-upc-scan text-primary"></i>
                                    <span>Cetak Barcode & Label</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- ======================================================== -->
                <!-- PARENT 2: TRANSAKSI                                      -->
                <!-- ======================================================== -->
                <div class="sidebar-section-title">Transaksi</div>
                <li class="nav-item">
                    <a class="nav-link parent-menu <?= $isTransaksiActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#collapseTransaksi" role="button" aria-expanded="<?= $isTransaksiActive ? 'true' : 'false' ?>">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-cart-check-fill text-success"></i>
                            <span>Transaksi</span>
                        </div>
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </a>
                    <div class="collapse <?= $isTransaksiActive ? 'show' : '' ?>" id="collapseTransaksi">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a href="<?= base_url('pos') ?>" class="nav-link <?= $uri == 'pos' ? 'active' : '' ?>">
                                    <i class="bi bi-cart-plus-fill text-success"></i>
                                    <span>Kasir POS Kasir</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('penjualan') ?>" class="nav-link <?= ($uri === 'penjualan' || (str_contains($uri, 'penjualan') && !str_contains($uri, 'laporan'))) ? 'active' : '' ?>">
                                    <i class="bi bi-receipt-cutoff text-info"></i>
                                    <span>Faktur Penjualan</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('transfer-stok') ?>" class="nav-link <?= str_contains($uri, 'transfer-stok') ? 'active' : '' ?>">
                                    <i class="bi bi-arrow-left-right text-info"></i>
                                    <span>Mutasi / Transfer Stok</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('pembelian') ?>" class="nav-link <?= ($uri === 'pembelian' || (str_contains($uri, 'pembelian') && !str_contains($uri, 'laporan'))) ? 'active' : '' ?>">
                                    <i class="bi bi-truck text-primary"></i>
                                    <span>Pembelian / Restock</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('retur') ?>" class="nav-link <?= str_contains($uri, 'retur') ? 'active' : '' ?>">
                                    <i class="bi bi-arrow-left-right text-warning"></i>
                                    <span>Retur Supplier (Rijek)</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('produksi') ?>" class="nav-link <?= str_contains($uri, 'produksi') ? 'active' : '' ?>">
                                    <i class="bi bi-gear-wide-connected text-info"></i>
                                    <span>Produksi Sabun</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('keuangan') ?>" class="nav-link <?= str_contains($uri, 'keuangan') ? 'active' : '' ?>">
                                    <i class="bi bi-wallet2 text-success"></i>
                                    <span>Buku Kas & Operasional</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('stok-opname') ?>" class="nav-link <?= (str_contains($uri, 'stok-opname') && !str_contains($uri, 'laporan')) ? 'active' : '' ?>">
                                    <i class="bi bi-clipboard2-check text-warning"></i>
                                    <span>Stok Opname (STO)</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- ======================================================== -->
                <!-- PARENT 3: LAPORAN                                        -->
                <!-- ======================================================== -->
                <div class="sidebar-section-title">Laporan</div>
                <li class="nav-item">
                    <a class="nav-link parent-menu <?= $isLaporanActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#collapseLaporan" role="button" aria-expanded="<?= $isLaporanActive ? 'true' : 'false' ?>">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-bar-graph-fill text-warning"></i>
                            <span>Laporan</span>
                        </div>
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </a>
                    <div class="collapse <?= $isLaporanActive ? 'show' : '' ?>" id="collapseLaporan">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a href="<?= base_url('laporan/barang') ?>" class="nav-link <?= str_contains($uri, 'laporan/barang') ? 'active' : '' ?>">
                                    <i class="bi bi-file-earmark-spreadsheet-fill text-success"></i>
                                    <span>Katalog &amp; Daftar Harga</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('laporan/penjualan') ?>" class="nav-link <?= str_contains($uri, 'laporan/penjualan') ? 'active' : '' ?>">
                                    <i class="bi bi-cart-check-fill text-info"></i>
                                    <span>Laporan Penjualan (Periode)</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('laporan/pembelian') ?>" class="nav-link <?= str_contains($uri, 'laporan/pembelian') ? 'active' : '' ?>">
                                    <i class="bi bi-bag-plus-fill text-primary"></i>
                                    <span>Laporan Pembelian (Periode)</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('laporan/arus-kas') ?>" class="nav-link <?= str_contains($uri, 'laporan/arus-kas') ? 'active' : '' ?>">
                                    <i class="bi bi-cash-coin text-success"></i>
                                    <span>Arus Kas &amp; Valuasi Aset</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('laporan/harian') ?>" class="nav-link <?= str_contains($uri, 'laporan/harian') ? 'active' : '' ?>">
                                    <i class="bi bi-graph-up-arrow text-warning"></i>
                                    <span>Laporan Toko Harian</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('laporan/cetak-struk-harian') ?>" target="_blank" onclick="window.open(this.href, 'StrukHarian', 'width=440,height=700'); return false;" class="nav-link">
                                    <i class="bi bi-receipt text-warning"></i>
                                    <span>Struk Rekap Kasir (Thermal)</span>
                                </a>
                            </li>
                            <?php if (session()->get('role') === 'admin'): ?>
                            <li class="nav-item">
                                <a href="<?= base_url('laporan/stok-opname') ?>" class="nav-link <?= str_contains($uri, 'laporan/stok-opname') ? 'active' : '' ?>">
                                    <i class="bi bi-box-seam-fill text-success"></i>
                                    <span>Laporan Aset &amp; STO</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('laporan/kartu-stok') ?>" class="nav-link <?= str_contains($uri, 'kartu-stok') ? 'active' : '' ?>">
                                    <i class="bi bi-journal-text text-info"></i>
                                    <span>Kartu Stok & Audit</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>

                <!-- ======================================================== -->
                <!-- PARENT 4: SISTEM KONFIGURASI                             -->
                <!-- ======================================================== -->
                <div class="sidebar-section-title">Sistem Konfigurasi</div>
                <li class="nav-item">
                    <a class="nav-link parent-menu <?= $isConfigActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#collapseKonfigurasi" role="button" aria-expanded="<?= $isConfigActive ? 'true' : 'false' ?>">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-gear-fill text-secondary"></i>
                            <span>Sistem Konfigurasi</span>
                        </div>
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </a>
                    <div class="collapse <?= $isConfigActive ? 'show' : '' ?>" id="collapseKonfigurasi">
                        <ul class="nav flex-column sub-menu">
                            <?php if (session()->get('role') === 'admin'): ?>
                            <li class="nav-item">
                                <a href="<?= base_url('karyawan') ?>" class="nav-link <?= str_contains($uri, 'karyawan') ? 'active' : '' ?>">
                                    <i class="bi bi-people-gear"></i>
                                    <span>Monitoring Karyawan & User</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('cabang') ?>" class="nav-link <?= str_contains($uri, 'cabang') ? 'active' : '' ?>">
                                    <i class="bi bi-shop-window text-warning"></i>
                                    <span>Master Cabang & Toko</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('dashboard/live-kasir') ?>" class="nav-link <?= (str_contains($uri, 'live-kasir') || str_contains($uri, 'monitoring-kasir')) ? 'active' : '' ?>">
                                    <i class="bi bi-broadcast text-danger"></i>
                                    <span>Pantau Kinerja Kasir Live</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('audit-trail') ?>" class="nav-link <?= str_contains($uri, 'audit-trail') ? 'active' : '' ?>">
                                    <i class="bi bi-shield-check text-success"></i>
                                    <span>Audit Trail & Jejak Aktivitas</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a href="<?= base_url('profile') ?>" class="nav-link <?= str_contains($uri, 'profile') ? 'active' : '' ?>">
                                    <i class="bi bi-person-badge"></i>
                                    <span>Profil Akun Pengguna</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>

        <div class="p-3 border-top border-secondary border-opacity-25 mt-auto">
            <div class="d-flex align-items-center justify-content-between">
                <a href="<?= base_url('profile') ?>" class="d-flex align-items-center gap-3 text-decoration-none text-white overflow-hidden" title="Lihat Profil Pengguna">
                    <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                        <i class="bi bi-person-fill fs-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-white fw-semibold small text-truncate"><?= esc(session()->get('nama_user') ?? 'Kasir / Admin') ?></div>
                        <div class="text-success small" style="font-size: 0.75rem;"><i class="bi bi-circle-fill" style="font-size: 7px;"></i> <?= esc(strtoupper(session()->get('role') ?? 'User')) ?></div>
                    </div>
                </a>
                <a href="<?= base_url('logout') ?>" class="btn btn-outline-danger btn-sm border-0" title="Keluar / Logout" onclick="return confirm('Yakin ingin keluar dari sistem?')">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div id="content-wrapper">
        <?php
            $cabangModelLayout = new \App\Models\CabangModel();
            $activeCabangList = $cabangModelLayout->getActiveBranches();
            $currentCabangId = session()->get('id_cabang') ?? 1;
            $currentCabangNama = session()->get('nama_cabang') ?? 'KSP Pusat';
            $canSwitchCabang = (session()->get('role') === 'admin' || session()->get('can_switch_cabang') == 1);
        ?>
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <!-- Hamburger Toggle Button (Aktif di HP, Tablet 12 Inch & Desktop) -->
                <button type="button" class="btn btn-sm btn-light border p-1 px-2 text-dark shadow-sm" id="btn-toggle-sidebar" aria-label="Toggle navigation" title="Sembunyikan / Tampilkan Menu Sidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>

                <h5 class="mb-0 top-navbar-title d-none d-sm-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary p-1 px-2 rounded-3 border border-primary-subtle" style="font-size: 0.85rem;">
                        <i class="bi bi-window-sidebar"></i>
                    </span>
                    <span><?= esc($title ?? 'Dashboard') ?></span>
                </h5>
                
                <!-- Branch Badge / Interactive Dropdown Switcher -->
                <?php if ($canSwitchCabang): ?>
                <div class="dropdown">
                    <button class="btn btn-sm branch-switcher-btn dropdown-toggle d-flex align-items-center gap-2 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Klik untuk beralih cabang operasional">
                        <i class="bi bi-shop-window text-success"></i>
                        <span class="text-truncate" style="max-width: 170px;"><?= esc($currentCabangNama) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-start shadow-lg border-0 py-2" style="min-width: 260px; border-radius: 12px; z-index: 9999;">
                        <li class="dropdown-header text-uppercase fw-bold text-primary" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <i class="bi bi-arrow-repeat me-1"></i> Beralih Cabang Operasional
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <?php foreach ($activeCabangList as $cb): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between py-2 <?= ($cb['id'] == $currentCabangId) ? 'active fw-bold' : '' ?>" href="<?= base_url('cabang/switch/' . $cb['id']) ?>">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt<?= ($cb['id'] == $currentCabangId) ? '-fill text-warning' : '' ?>"></i>
                                        <span><?= esc($cb['nama_cabang']) ?></span>
                                    </div>
                                    <?php if ($cb['is_pusat'] == 1): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">PUSAT</span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item small text-muted text-center py-1" href="<?= base_url('cabang') ?>">
                                <i class="bi bi-gear me-1"></i> Kelola Jaringan Cabang
                            </a>
                        </li>
                    </ul>
                </div>
                <?php else: ?>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold d-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-geo-alt-fill text-danger"></i> <?= esc($currentCabangNama) ?>
                </span>
                <?php endif; ?>
            </div>
            
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <!-- Jam Live Modern Pill Widget -->
                <div class="navbar-clock-pill d-none d-md-flex align-items-center">
                    <i class="bi bi-clock-history text-info fs-6"></i>
                    <div class="text-end">
                        <div class="lh-1" id="live-clock">--:--:--</div>
                        <small class="fw-semibold" style="font-size: 0.70rem;"><?= date('D, d M Y') ?></small>
                    </div>
                </div>
                
                <div class="vr mx-1 d-none d-md-block"></div>

                <!-- Tombol Transaksi Cepat -->
                <a href="<?= base_url('pos') ?>" class="btn btn-sm navbar-quick-tx-btn d-none d-sm-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-plus-circle-fill"></i> Transaksi Baru
                </a>

                <!-- User Profile & Status Area (Header Kanan) -->
                <div class="dropdown">
                    <button class="btn navbar-user-btn d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                            <i class="bi bi-person-fill fs-6"></i>
                        </div>
                        <div class="text-start d-none d-sm-block me-1">
                            <div class="fw-bold small text-white lh-1" style="font-size: 0.82rem;"><?= esc(session()->get('nama_user') ?? 'User Kasir') ?></div>
                            <small class="fw-bold" style="font-size: 0.70rem; color: #4ade80;"><i class="bi bi-circle-fill" style="font-size: 6px;"></i> <?= esc(ucfirst(session()->get('role') ?? 'Kasir')) ?></small>
                        </div>
                        <i class="bi bi-chevron-down text-white-50 small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-3 border-0 py-2" style="min-width: 210px;">
                        <li class="px-3 py-2 border-bottom mb-1">
                            <div class="fw-bold text-dark"><?= esc(session()->get('nama_user') ?? 'Pengguna') ?></div>
                            <small class="text-muted">Kode: <?= esc(session()->get('kode_user') ?? 'KASIR') ?></small>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= base_url('profile') ?>">
                                <i class="bi bi-person-gear text-primary fs-5"></i>
                                <span>Ubah Profil & Sandi</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="<?= base_url('logout') ?>" onclick="return confirm('Yakin ingin logout?')">
                                <i class="bi bi-box-arrow-right fs-5"></i>
                                <span>Keluar / Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Dynamic Content -->
        <main class="main-content">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Modal Input Kas Awal Harian (Translasi SimpanUangKasHarian VB6) -->
    <?php if (session()->getFlashdata('show_modal_kas_awal')): ?>
    <div class="modal fade" id="modalKasAwal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form action="<?= base_url('auth/simpan-kas-awal') ?>" method="POST">
                    <div class="modal-header bg-primary text-white py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-wallet2 fs-4 text-white"></i>
                            <h5 class="modal-title fw-bold text-white mb-0">Input Uang Kas Awal Hari Ini</h5>
                        </div>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted small">
                            Sebelum memulai transaksi penjualan hari ini, masukkan nominal uang kas / modal receh yang tersedia di laci kasir untuk uang kembalian.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Nominal Kas Awal (Rp)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white fw-bold">Rp</span>
                                <input type="text" inputmode="numeric" name="nominal_kas_awal" class="form-control text-end fw-bold fs-4 text-primary input-currency" placeholder="0" autofocus required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-semibold">
                            <i class="bi bi-check-circle me-1"></i> Simpan Kas Awal & Lanjutkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script>
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const clockEl = document.getElementById('live-clock');
            if (clockEl) clockEl.innerText = timeStr + ' WIB';
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Auto-show Modal Kas Awal saat login pertama hari ini
        $(document).ready(function() {
            if ($('#modalKasAwal').length) {
                const modalKasAwalEl = new bootstrap.Modal(document.getElementById('modalKasAwal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                modalKasAwalEl.show();
                setTimeout(() => {
                    $('#modalKasAwal input[name="nominal_kas_awal"]').focus().select();
                }, 400);
            }
        });

        // Inisialisasi Otomatis Select2 Searchable Dropdown untuk Semua Komponen Select
        function initSearchableSelects(context) {
            try {
                if (!$.fn.select2) return;
                const $ctx = context ? $(context) : $(document);
                $ctx.find('select:not(.no-select2):not([name$="_length"])').each(function() {
                    const $sel = $(this);
                    // Jika sudah ada class select2-hidden-accessible, lewati agar tidak dobel
                    if ($sel.hasClass('select2-hidden-accessible')) return;

                    const $modal = $sel.closest('.modal');
                    // Sesuai permintaan: Hapus textbox pencarian di semua dropdown jika isi list sedikit (<= 8) atau tanpa scrollbar
                    const minResults = $sel.data('min-results') !== undefined ? parseInt($sel.data('min-results')) : 8;

                    $sel.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        minimumResultsForSearch: minResults,
                        dropdownParent: $modal.length ? $modal : $(document.body),
                        language: {
                            noResults: function() {
                                return "Data tidak ditemukan";
                            },
                            searching: function() {
                                return "Mencari...";
                            }
                        }
                    });
                });
            } catch(e) {
                console.warn('Select2 init notice:', e);
            }
        }

        // Listener global select2:open untuk memastikan textbox pencarian otomatis disembunyikan jika list sedikit atau tidak ada scrollbar
        $(document).on('select2:open', function(e) {
            setTimeout(() => {
                const $dropdown = $('.select2-dropdown:visible');
                if (!$dropdown.length) return;
                const $results = $dropdown.find('.select2-results__options');
                const $search = $dropdown.find('.select2-search--dropdown');
                if ($results.length && $search.length) {
                    const optionCount = $results.find('.select2-results__option').length;
                    const hasScrollbar = $results[0].scrollHeight > ($results[0].clientHeight + 4);
                    if (optionCount <= 8 || !hasScrollbar) {
                        $search.hide();
                    } else {
                        $search.show();
                    }
                }
            }, 10);
        });

        // Auto-Truncate Kolom Grid Panjang dengan Ellipsis (...) & Tooltip Lengkap
        function applyGridTruncationAndTooltips(context) {
            try {
                const $ctx = context ? $(context) : $(document);
                // Lewati tabel form input atau tabel yang memiliki class no-truncate / id table-input-sto
                $ctx.find('table:not(.no-truncate):not(#table-input-sto) tbody tr td').each(function() {
                    const $td = $(this);
                    if ($td.closest('.no-truncate, #table-input-sto, form').length > 0) return;

                    // Lewati jika kolom aksi atau memiliki tombol / input / select / badge tunggal
                    if ($td.find('button, input, select, .btn, .btn-group').length > 0) {
                        return;
                    }

                    // Periksa teks dalam cell
                    const rawText = $td.text().trim().replace(/\s+/g, ' ');

                    // Jika panjang teks lebih dari 24 karakter dan belum di-truncate
                    if (rawText.length > 24 && !$td.hasClass('no-truncate')) {
                        if ($td.find('.cell-truncate').length === 0) {
                            if ($td.children().length > 0) {
                                $td.children().each(function() {
                                    const $child = $(this);
                                    const childText = $child.text().trim().replace(/\s+/g, ' ');
                                    if (childText.length > 24 && !$child.hasClass('badge')) {
                                        $child.addClass('cell-truncate');
                                        $child.attr('title', childText);
                                        $child.attr('data-bs-toggle', 'tooltip');
                                        $child.attr('data-bs-placement', 'top');
                                    }
                                });
                            } else {
                                $td.wrapInner('<span class="cell-truncate"></span>');
                                const $span = $td.find('.cell-truncate');
                                $span.attr('title', rawText);
                                $span.attr('data-bs-toggle', 'tooltip');
                                $span.attr('data-bs-placement', 'top');
                            }
                        }
                    }
                });

                // Inisialisasi Tooltip Bootstrap 5 (Batasi maksimal 50 instansiasi aktif untuk mencegah UI freeze)
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                const maxTooltips = Math.min(tooltipTriggerList.length, 50);
                for (let i = 0; i < maxTooltips; i++) {
                    const tooltipTriggerEl = tooltipTriggerList[i];
                    if (!bootstrap.Tooltip.getInstance(tooltipTriggerEl)) {
                        new bootstrap.Tooltip(tooltipTriggerEl, {
                            container: 'body',
                            boundary: 'window'
                        });
                    }
                }
            } catch(err) {
                console.warn('Grid tooltip init notice:', err);
            }
        }

        // Fitur Drag & Move Seluruh Modal / Popup Agar Bisa Digeser
        function initDraggablePopups() {
            // 1. Draggable Bootstrap 5 Modals
            $(document).on('mousedown', '.modal-header', function(e) {
                // Abaikan jika user mengklik tombol close, dropdown, atau input di header
                if ($(e.target).closest('button, a, input, select, .btn-close').length > 0) return;

                const $header = $(this);
                const $dialog = $header.closest('.modal-dialog');
                if (!$dialog.length) return;

                const rect = $dialog[0].getBoundingClientRect();

                // Jika tinggi popup modal melebihi tinggi layar, jangan gunakan position: fixed
                // agar scroll vertikal alami ke footer tetap berfungsi 100% lancar
                if (rect.height > (window.innerHeight - 80)) {
                    return;
                }

                const startMouseX = e.clientX;
                const startMouseY = e.clientY;
                const initialLeft = rect.left;
                const initialTop = rect.top;

                $dialog.css({
                    position: 'fixed',
                    margin: '0',
                    left: initialLeft + 'px',
                    top: initialTop + 'px',
                    width: rect.width + 'px',
                    zIndex: 1060
                });

                $header.css('cursor', 'grabbing');

                $(document).on('mousemove.modalDrag', function(ev) {
                    const dx = ev.clientX - startMouseX;
                    const dy = ev.clientY - startMouseY;
                    let newLeft = initialLeft + dx;
                    let newTop = initialTop + dy;

                    // Batasan agar dialog tidak terlempar keluar layar
                    newTop = Math.max(10, Math.min(window.innerHeight - 80, newTop));
                    newLeft = Math.max(10 - rect.width + 120, Math.min(window.innerWidth - 120, newLeft));

                    $dialog.css({
                        left: newLeft + 'px',
                        top: newTop + 'px'
                    });
                });

                $(document).on('mouseup.modalDrag', function() {
                    $header.css('cursor', 'grab');
                    $(document).off('mousemove.modalDrag mouseup.modalDrag');
                });

                e.preventDefault();
            });

            // Reset posisi modal saat ditutup agar kembali rapi di posisi default
            $(document).on('hidden.bs.modal', '.modal', function() {
                const $dialog = $(this).find('.modal-dialog');
                $dialog.css({
                    position: '',
                    margin: '',
                    left: '',
                    top: '',
                    width: '',
                    zIndex: ''
                });
            });

            // 2. Draggable SweetAlert2 Popups
            $(document).on('mousedown', '.swal2-title, .swal2-header', function(e) {
                if ($(e.target).closest('button, a, input, select, .swal2-close').length > 0) return;

                const $popup = $(this).closest('.swal2-popup');
                if (!$popup.length) return;

                const rect = $popup[0].getBoundingClientRect();
                const startMouseX = e.clientX;
                const startMouseY = e.clientY;
                const initialLeft = rect.left;
                const initialTop = rect.top;

                $popup.css({
                    position: 'fixed',
                    margin: '0',
                    left: initialLeft + 'px',
                    top: initialTop + 'px',
                    transform: 'none'
                });

                $(document).on('mousemove.swalDrag', function(ev) {
                    const dx = ev.clientX - startMouseX;
                    const dy = ev.clientY - startMouseY;
                    $popup.css({
                        left: (initialLeft + dx) + 'px',
                        top: (initialTop + dy) + 'px'
                    });
                });

                $(document).on('mouseup.swalDrag', function() {
                    $(document).off('mousemove.swalDrag mouseup.swalDrag');
                });

                e.preventDefault();
            });
        }

        // ==========================================
        // UNIVERSAL CURRENCY (RUPIAH) FORMATTER
        // ==========================================
        function formatRupiah(val, prefix = '') {
            if (val === null || val === undefined || val === '') return prefix ? prefix + '0' : '0';
            
            // Konversi ke integer bulat jika berupa angka / desimal
            let num = 0;
            if (typeof val === 'number') {
                num = Math.round(val);
            } else {
                let str = val.toString().trim();
                // Jika memiliki format desimal seperti "25000.00"
                if (str.includes('.') && str.indexOf('.') === str.lastIndexOf('.') && str.split('.')[1].length <= 2) {
                    num = Math.round(parseFloat(str) || 0);
                } else {
                    let clean = str.replace(/[^0-9]/g, '');
                    num = parseInt(clean, 10) || 0;
                }
            }

            let strVal = Math.abs(num).toString();
            let sisa = strVal.length % 3,
                rupiah = strVal.substr(0, sisa),
                ribuan = strVal.substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            let res = rupiah ? rupiah : '0';
            if (num < 0) res = '-' + res;
            return prefix ? prefix + res : res;
        }

        function unformatRupiah(val) {
            if (val === null || val === undefined) return 0;
            let clean = val.toString().replace(/[^0-9]/g, '');
            return clean ? parseFloat(clean) : 0;
        }

        // Realtime Auto-formatting pada saat user mengetik angka mata uang
        $(document).on('input', '.input-currency, input[data-type="currency"]', function(e) {
            let cursorPosition = this.selectionStart;
            let originalLength = this.value.length;
            let clean = this.value.replace(/[^0-9]/g, '');
            
            if (clean === '') {
                this.value = '';
            } else {
                let formatted = formatRupiah(parseInt(clean, 10) || 0);
                this.value = formatted;
                
                // Pertahankan posisi kursor agar nyaman saat mengetik
                let newLength = formatted.length;
                cursorPosition = cursorPosition + (newLength - originalLength);
                if (cursorPosition > 0 && this.setSelectionRange) {
                    this.setSelectionRange(cursorPosition, cursorPosition);
                }
            }
        });

        // Format otomatis saat kehilangan fokus (blur)
        $(document).on('blur', '.input-currency, input[data-type="currency"]', function() {
            if ($(this).val().trim() !== '') {
                $(this).val(formatRupiah($(this).val()));
            }
        });

        // Format otomatis elemen label / badge dengan class .label-currency atau [data-format="currency"]
        function applyCurrencyLabels(context) {
            const $ctx = context ? $(context) : $(document);
            $ctx.find('.label-currency, [data-format="currency"]').each(function() {
                let raw = $(this).attr('data-val') || $(this).text().trim();
                if (raw && !raw.startsWith('Rp')) {
                    $(this).text('Rp ' + formatRupiah(raw));
                }
            });
        }

        // Otomatis bersihkan titik ribuan sebelum form POST dikirim ke backend
        $(document).on('submit', 'form', function() {
            $(this).find('.input-currency, input[data-type="currency"]').each(function() {
                let clean = unformatRupiah($(this).val());
                $(this).val(clean);
            });
        });

        $(document).ready(function() {
            // Kontrol Sidebar Toggle: Off-canvas di Tablet/HP (< 1200px) & Collapse di Desktop (>= 1200px)
            $('#btn-toggle-sidebar, #sidebar-backdrop').on('click', function(e) {
                e.preventDefault();
                if ($(window).width() < 1200) {
                    $('#sidebar').toggleClass('show');
                    $('#sidebar-backdrop').toggleClass('show');
                } else {
                    $('body').toggleClass('sidebar-collapsed');
                }
            });

            // Tutup drawer otomatis saat link menu diklik di perangkat tablet/mobile (< 1200px)
            $('#sidebar .nav-link:not(.parent-menu)').on('click', function() {
                if ($(window).width() < 1200) {
                    $('#sidebar').removeClass('show');
                    $('#sidebar-backdrop').removeClass('show');
                }
            });

            initSearchableSelects();
            applyGridTruncationAndTooltips();
            initDraggablePopups();
            applyCurrencyLabels();

            // Format seluruh input-currency yang sudah memiliki initial value saat halaman dimuat
            $('.input-currency, input[data-type="currency"]').each(function() {
                if ($(this).val()) {
                    $(this).val(formatRupiah($(this).val()));
                }
            });

            // Aktifkan otomatis saat DataTables melakukan draw / filter / pagination
            $(document).on('draw.dt', function() {
                applyGridTruncationAndTooltips();
                applyCurrencyLabels();
            });

            // Aktifkan otomatis saat modal Bootstrap dibuka
            $(document).on('shown.bs.modal', '.modal', function() {
                initSearchableSelects(this);
                applyGridTruncationAndTooltips(this);
                applyCurrencyLabels(this);
                $(this).find('.input-currency, input[data-type="currency"]').each(function() {
                    if ($(this).val()) {
                        $(this).val(formatRupiah($(this).val()));
                    }
                });
            });

            // Aktifkan setelah ajax selesai (misal load data modal)
            $(document).ajaxComplete(function() {
                setTimeout(() => {
                    applyGridTruncationAndTooltips();
                    applyCurrencyLabels();
                }, 200);
            });
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>

