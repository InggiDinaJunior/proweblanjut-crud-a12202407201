<?php
$page_title = $page_title ?? 'Inventaris';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> Inggi Dina</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --red:        #C0392B;
            --red-dark:   #922B21;
            --maroon:     #7B241C;
            --maroon-dark:#4A1210;
            --red-light:  #FADBD8;
            --sidebar-w:  220px;
            --topbar-h:   54px;
        }

        body {
            font-family: sans-serif;
            font-size: 0.9rem;
            background: #F4F4F4;
        }

        /* TOPBAR */
        .topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--topbar-h);
            background: var(--maroon-dark);
            color: #fff;
            display: flex;
            align-items: center;
            padding: 0 1rem 0 calc(var(--sidebar-w) + 1rem);
            z-index: 100;
            border-bottom: 2px solid var(--red);
        }

        .topbar-brand {
            position: absolute;
            left: 0;
            width: var(--sidebar-w);
            height: 100%;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0 1rem;
            background: var(--maroon-dark);
            border-right: 1px solid rgba(255,255,255,0.1);
            text-decoration: none;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
        }

        .topbar-brand i {
            color: var(--red-light);
            font-size: 1.2rem;
        }

        .topbar-title {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.75);
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: var(--topbar-h);
            left: 0;
            width: var(--sidebar-w);
            height: calc(100vh - var(--topbar-h));
            background: var(--maroon-dark);
            overflow-y: auto;
            z-index: 90;
        }

        /* MAIN CONTENT */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            margin-top: var(--topbar-h);
            padding: 1.5rem;
            min-height: calc(100vh - var(--topbar-h));
        }

        /* BUTTON */
        .btn-red {
            background: var(--red);
            color: #fff;
            border: none;
        }
        .btn-red:hover {
            background: var(--red-dark);
            color: #fff;
        }
        .btn-outline-red {
            border: 1px solid var(--red);
            color: var(--red);
            background: transparent;
        }
        .btn-outline-red:hover {
            background: var(--red);
            color: #fff;
        }

        /* CARD HEADER */
        .card-header-red {
            background: var(--red-dark);
            color: #fff;
            padding: 0.65rem 1rem;
            font-weight: 600;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* TABLE */
        .table-red thead th {
            background: var(--maroon);
            color: #fff;
            font-size: 0.8rem;
            border: none;
        }
        .table-red tbody tr:hover {
            background: #FEF5F4;
        }

        /* BADGE */
        .badge-aktif    { background: #D5F5E3; color: #1E8449; }
        .badge-nonaktif { background: #EAECEE; color: #555; }
        .badge-habis    { background: var(--red-light); color: var(--red); }
        .badge-aman     { background: #D5F5E3; color: #1E8449; }
        .badge-rendah   { background: #FEF9E7; color: #B7950B; }

        .badge-pill {
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        /* FORM */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 0.15rem rgba(192,57,43,0.2);
        }

        /* STAT CARD */
        .stat-card {
            background: #fff;
            border: 1px solid #E0E0E0;
            border-radius: 6px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .stat-icon.red    { background: var(--red-light); color: var(--red); }
        .stat-icon.yellow { background: #FEF9E7; color: #B7950B; }
        .stat-icon.green  { background: #D5F5E3; color: #1E8449; }
        .stat-icon.blue   { background: #D6EAF8; color: #1A5276; }

        .stat-val   { font-size: 1.4rem; font-weight: 700; line-height: 1; }
        .stat-label { font-size: 0.75rem; color: #888; margin-top: 0.2rem; }

        /* PAGE HEADER */
        .page-header {
            margin-bottom: 1.25rem;
        }
        .page-header h5 {
            font-weight: 700;
            margin: 0;
        }
        .page-header p {
            font-size: 0.8rem;
            color: #888;
            margin: 0;
        }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <a href="../index.php" class="topbar-brand">
        <i class="bi bi-boxes"></i> Inggi Dina
    </a>
    <span class="topbar-title"><?= htmlspecialchars($page_title) ?></span>
</div>