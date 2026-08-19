@extends('layouts.form')

@section('title', 'Formulir Pendaftaran - ' . $pendaftar['nama'])

@section('content')
    <nav class="fp-navbar no-print">
        <div class="fp-navbar-title">
            <span class="material-icons">description</span>
            Formulir Pendaftaran
        </div>
        <div class="fp-nav-actions">
            <a href="{{ $backUrl }}" class="fp-btn fp-btn-back">
                <span class="material-icons">arrow_back</span>
                Kembali
            </a>
            <button type="button" class="fp-btn fp-btn-print" onclick="togglePrintMenu()">
                <span class="material-icons">print</span>
                Cetak
                <span class="material-icons" style="font-size:15px;">arrow_drop_down</span>
            </button>
            <div id="fpPrintMenu" class="fp-print-menu hidden">
                <button type="button" class="fp-print-menu-item" onclick="window.print()">
                    <span class="material-icons">print</span>
                    Cetak Langsung
                </button>
                <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="fp-print-menu-item" style="text-decoration:none;">
                    <span class="material-icons">picture_as_pdf</span>
                    Cetak jadi PDF
                </a>
            </div>
        </div>
    </nav>

    <div class="fp-content">
        <x-formulir-pendaftaran
            :pendaftar="$pendaftar"
            :keluarga="$keluarga"
            :signed="$signed"
            :ketua-data="$ketuaData"
            :logo-data-uri="$logoDataUri"
        />
    </div>

    <script>
        function togglePrintMenu() {
            document.getElementById('fpPrintMenu').classList.toggle('hidden');
        }

        document.addEventListener('click', function (e) {
            var menu = document.getElementById('fpPrintMenu');
            if (menu && !menu.classList.contains('hidden') && !e.target.closest('.fp-btn-print') && !e.target.closest('.fp-print-menu')) {
                menu.classList.add('hidden');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                var menu = document.getElementById('fpPrintMenu');
                if (menu) menu.classList.add('hidden');
            }
        });
    </script>
@endsection