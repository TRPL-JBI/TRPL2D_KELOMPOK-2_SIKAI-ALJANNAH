<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }} - RKM Al-Jannah</title>
    @include('components.favicon')

    <!-- Google Fonts: Space Grotesk & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    @vite('resources/css/components/navbar.css')

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Poppins", sans-serif; background-color: #f5f5f5; line-height: 1.6; color: #2b2b2b; }
        a { text-decoration: none; color: inherit; }
        .page-section { padding: 4rem 2rem 6rem; background-color: #f5f5f5; min-height: 100vh; }
        .page-container { max-width: 900px; margin: 0 auto; }
        .page-title { font-size: 2rem; font-weight: 700; color: #16423c; margin-bottom: 0.5rem; font-family: 'Space Grotesk', sans-serif; }
        .page-divider { border: none; border-top: 2px solid #6A9C89; margin: 1rem 0 2rem 0; }
        .page-content { font-size: 1.0625rem; line-height: 1.9; }
        .page-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1.5rem 0; }
        .page-content p { margin-bottom: 1.2rem; }
        footer { background: #16423c; color: #c5d5c0; text-align: center; padding: 1.5rem; font-size: 0.875rem; }
        {{-- Builder content styles --}}
        .builder-content { max-width: 1180px; margin: 0 auto; background: #ffffff; padding: 20px; }
        .builder-content p { margin-bottom: 1rem; }
        .builder-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0; }
        .builder-content a { color: #16423c; font-weight: 600; }
        .builder-content ul { margin-bottom: 1rem; }
        .builder-content li { margin-bottom: 0.25rem; }
    </style>
    <script>if('serviceWorker'in navigator){navigator.serviceWorker.register('/sw.js')}</script>
</head>
<body>
    @include('components.preloader')
    <x-navbar />
    <section class="page-section">
        @if($page->isBuilderContent())
        <div class="builder-content">
            {!! $page->renderContent() !!}
        </div>
        @else
        <div class="page-container">
            <h1 class="page-title">{{ $page->title }}</h1>
            <hr class="page-divider">
            <div class="page-content">
                {!! str($page->content)->sanitizeHtml() !!}
            </div>
        </div>
        @endif
    </section>
    <footer>
        &copy; {{ date('Y') }} RKM Al-Jannah. All rights reserved.
    </footer>
</body>
</html>
