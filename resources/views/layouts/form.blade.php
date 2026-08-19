<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Formulir') | RKM Al-Jannah</title>
    @include('components.favicon')
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-color: #f3f4f6;
            color: #111827;
        }

        .fp-navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 24px;
            background: #16423c;
            color: #fff;
            box-shadow: 0 4px 18px rgba(22, 66, 60, 0.35);
        }

        .fp-navbar-title {
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .fp-navbar-title .material-icons { font-size: 20px; }

        .fp-nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .fp-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .fp-btn .material-icons { font-size: 17px; }

        .fp-btn-back {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .fp-btn-back:hover { background: rgba(255, 255, 255, 0.22); }

        .fp-btn-print { background: #ffbb00; color: #16423c; }
        .fp-btn-print:hover { background: #ffcd33; }

        .fp-print-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 6px);
            background: #fff;
            color: #111827;
            border-radius: 10px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
            min-width: 210px;
            overflow: hidden;
            z-index: 60;
        }

        .fp-print-menu.hidden { display: none; }

        .fp-print-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 12px 16px;
            border: none;
            background: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 500;
            color: #111827;
            cursor: pointer;
            text-align: left;
        }

        .fp-print-menu-item:hover { background: #f3f4f6; }
        .fp-print-menu-item .material-icons { font-size: 18px; color: #16423c; }

        .fp-content {
            max-width: 820px;
            margin: 24px auto;
            background: #fff;
            padding: 38px 46px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .fp-content {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>