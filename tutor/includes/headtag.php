<head>
    <!-- =====================================================
    =============== BASIC META CONFIG =======================
    ====================================================== -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SkillRise Tutor Dashboard</title>
    <link rel="shortcut icon" href="codez3.png">

    <!-- =====================================================
    =================== GOOGLE FONTS ========================
    ====================================================== -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"
        rel="stylesheet">

    <!-- =====================================================
    =================== BOOTSTRAP 5 =========================
    ====================================================== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- =====================================================
    =================== ICON LIBRARIES ======================
    ====================================================== -->
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- =====================================================
    =================== DATATABLES (BS5) ====================
    ====================================================== -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">



    <!-- =====================================================
    =================== APP / THEME CSS =====================
    ====================================================== -->
    <link id="theme-style" rel="stylesheet" href="assets/css/portal.css">

    <!-- =====================================================
    =================== CUSTOM STYLES =======================
    ====================================================== -->
    <style>
        .app-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-icon {
            width: 45px !important;
            height: 45px !important;
            object-fit: contain;
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
        }

        .logo-1 {
            color: #252930;
        }

        .logo-2 {
            color: #28a745;
        }

        .nav-icon i {
            font-size: 1.2em;
            color: #333;
            margin-right: 5px;
        }

        /* =====================================================
        ================ GLOBAL THEME OVERRIDES ===============
        ====================================================== */

        /* ── DataTables Button Styling ── */
        .dt-buttons .btn {
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            padding: 8px 18px !important;
            transition: all 0.3s ease !important;
            border: 1px solid #28a745 !important;
            background-color: #ffffff !important;
            color: #28a745 !important;
            margin-right: 8px !important;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.05) !important;
        }

        .dt-buttons .btn:hover {
            background-color: #28a745 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2) !important;
            transform: translateY(-2px) !important;
        }

        /* ── DataTables Pagination Styling ── */
        .page-item.active .page-link {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: #ffffff !important;
        }

        .page-link {
            color: #28a745 !important;
            border-radius: 6px !important;
            margin: 0 2px !important;
        }

        .page-link:focus {
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25) !important;
        }

        /* ── Global Table Row Hover ── */
        .table-hover tbody tr:hover {
            background-color: rgba(40, 167, 69, 0.08) !important;
            transition: background-color 0.2s ease !important;
        }

        /* ── Custom Switch Green ── */
        .form-check-input:checked {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
        }

        /* ── Premium Green Buttons ── */
        .btn-success,
        .app-btn-primary,
        .btn-primary {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: #ffffff !important;
            border-radius: 10px !important;
            padding: 8px 20px !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 6px rgba(40, 167, 69, 0.15) !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .btn-success:hover,
        .app-btn-primary:hover,
        .btn-primary:hover {
            background-color: #218838 !important;
            border-color: #1e7e34 !important;
            box-shadow: 0 6px 12px rgba(40, 167, 69, 0.25) !important;
            transform: translateY(-2px) !important;
        }

        .btn-outline-success {
            border-color: #28a745 !important;
            color: #28a745 !important;
            border-radius: 10px !important;
            background: transparent !important;
            transition: all 0.3s ease !important;
        }

        .btn-outline-success:hover {
            background-color: #28a745 !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
        }
    </style>
</head>