<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $post->title }} - RKM Al-Jannah</title>
    @include('components.favicon')
    
    <!-- Google Fonts: Space Grotesk & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Navbar Component Styles -->
    @vite('resources/css/components/navbar.css')

    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            font-style: normal;
            background-color: #f5f5f5;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Post Detail Section */
        .post-detail-section {
            padding: 4rem 2rem 6rem;
            background-color: #f5f5f5;
            min-height: 100vh;
        }

        .post-detail-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #16423C;
            font-family: "Poppins", sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            color: #6A9C89;
            transform: translateX(-5px);
        }

        .back-button svg {
            transition: transform 0.3s ease;
        }

        .back-button:hover svg {
            transform: translateX(-3px);
        }

        /* Post Header */
        .post-header {
            margin-bottom: 2rem;
        }

        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .post-tag {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            color: #16423C;
            background-color: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .post-title {
            font-family: "Poppins", sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #16423C;
            line-height: 1.3;
            margin-bottom: 1.5rem;
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 2rem;
            color: #6B7280;
            font-size: 0.95rem;
        }

        .post-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .post-meta-item svg {
            width: 20px;
            height: 20px;
            color: #6A9C89;
        }

        /* Post Featured Image */
        .post-featured-image {
            width: 100%;
            height: 500px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 3rem;
            box-shadow: 0 10px 40px rgba(22, 66, 60, 0.2);
        }

        .post-featured-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Post Content */
        .post-content {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .post-content h2 {
            font-family: "Poppins", sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #16423C;
            margin: 2rem 0 1rem;
            line-height: 1.4;
        }

        .post-content h3 {
            font-family: "Poppins", sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #16423C;
            margin: 1.5rem 0 0.75rem;
            line-height: 1.4;
        }

        .post-content p {
            font-family: "Poppins", sans-serif;
            font-size: 1.0625rem;
            line-height: 1.9;
            color: #374151;
            margin-bottom: 1.5rem;
        }

        .post-content ul,
        .post-content ol {
            font-family: "Poppins", sans-serif;
            font-size: 1.0625rem;
            line-height: 1.9;
            color: #374151;
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }

        .post-content ul li,
        .post-content ol li {
            margin-bottom: 0.75rem;
        }

        .post-content blockquote {
            border-left: 4px solid #6A9C89;
            padding-left: 1.5rem;
            margin: 2rem 0;
            font-style: italic;
            color: #6B7280;
        }

        .post-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .post-content p[style*="text-align: center"] {
            text-align: center;
        }

        .post-content p[style*="text-align: center"] img {
            display: inline-block;
        }

        .post-content .file-embed {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: #f9fafb;
            margin: 0.5rem 0;
        }

        .post-content .file-embed-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: #16423C;
            text-decoration: none;
        }

        .post-content .file-embed-link:empty::after {
            content: 'Download File';
        }

        .post-content .file-embed-link[data-file-name]:not([data-file-name=""]):empty::after {
            content: attr(data-file-name);
        }

        .post-content .file-embed-link:hover {
            text-decoration: underline;
        }

        .post-content pre {
            background: #1f2937;
            color: #f3f4f6;
            padding: 1.25rem;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 1.5rem 0;
        }

        .post-content code {
            font-family: monospace;
        }

        .post-content pre code {
            background: none;
            padding: 0;
        }

        .post-content p code {
            background: #f3f4f6;
            padding: 0.2em 0.4em;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .post-content hr {
            border: none;
            border-top: 2px solid #e5e7eb;
            margin: 2rem 0;
        }

        .post-content a {
            color: #16423C;
            text-decoration: underline;
        }

        .post-content a:hover {
            color: #6A9C89;
        }

        /* Share Section */
        .share-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid #e5e7eb;
        }

        .share-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.125rem;
            font-weight: 600;
            color: #16423C;
            margin-bottom: 1rem;
        }

        .share-buttons {
            display: flex;
            gap: 1rem;
        }

        .share-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: #f3f4f6;
            color: #16423C;
            transition: all 0.3s ease;
        }

        .share-button:hover {
            background-color: #6A9C89;
            color: #ffffff;
            transform: translateY(-3px);
        }

        /* Related Posts */
        .related-posts {
            margin-top: 4rem;
        }

        .related-posts-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #16423C;
            margin-bottom: 2rem;
        }

        /* Footer */
        .footer {
            background: linear-gradient(180deg, #16423C 0%, #0d2b26 100%);
            padding: 2rem;
            margin-top: 0;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .footer-brand-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }

        .footer-brand-text {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-divider {
            width: 1px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.3);
        }

        .footer-developer-text {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-developer-name {
            font-weight: 600;
            color: #98CBBE;
        }

        /* Post Detail Responsive */
        @media (max-width: 768px) {
            .post-detail-section {
                padding: 3rem 1.5rem 4rem;
            }

            .post-title {
                font-size: 1.75rem;
            }

            .post-featured-image {
                height: 300px;
            }

            .post-content {
                padding: 2rem 1.5rem;
            }

            .post-meta {
                flex-direction: column;
                gap: 1rem;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-divider {
                width: 100%;
                height: 1px;
            }
        }

        @media (max-width: 480px) {
            .post-title {
                font-size: 1.5rem;
            }

            .post-content {
                padding: 1.5rem 1rem;
            }

            .post-content h2 {
                font-size: 1.5rem;
            }

            .post-content h3 {
                font-size: 1.25rem;
            }
        }
    </style>
    <script>if('serviceWorker'in navigator){navigator.serviceWorker.register('/sw.js')}</script>
</head>
<body>
    @include('components.preloader')
    <!-- Navbar Component -->
    <x-navbar />

    @php
        $imageUrl = $post->getThumbnailUrl();
        $postDate = $post->published_at
            ? \Carbon\Carbon::parse($post->published_at)->locale('id')->isoFormat('D MMMM YYYY')
            : now()->locale('id')->isoFormat('D MMMM YYYY');
    @endphp

    <!-- Post Detail Section -->
    <section class="post-detail-section">
        <div class="post-detail-container">
            <!-- Back Button -->
            <a href="{{ route('home') }}#kanal-berita" class="back-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Kembali ke Kanal Berita
            </a>

            <!-- Post Header -->
            <div class="post-header">
                <div class="post-tags">
                    @if($post->category)
                        <span class="post-tag">{{ $post->category->name }}</span>
                    @else
                        <span class="post-tag">Umum</span>
                    @endif
                </div>
                <h1 class="post-title">{{ $post->title }}</h1>
                <div class="post-meta">
                    <div class="post-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>{{ $postDate }}</span>
                    </div>
                    <div class="post-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Admin RKM</span>
                    </div>
                </div>
            </div>

            <!-- Post Featured Image -->
            @if($imageUrl)
            <div class="post-featured-image">
                <img src="{{ $imageUrl }}" alt="{{ $post->title }}">
            </div>
            @endif

            <!-- Post Content -->
            <article class="post-content">
                {!! str($post->content)->sanitizeHtml() !!}

                <!-- Share Section -->
                <div class="share-section">
                    <h3 class="share-title">Bagikan Artikel Ini</h3>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" class="share-button" title="Share to Facebook" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" class="share-button" title="Share to Twitter" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                            </svg>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . request()->url()) }}" class="share-button" title="Share to WhatsApp" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                            </svg>
                        </a>
                        <a href="#" class="share-button" title="Copy Link" onclick="event.preventDefault(); navigator.clipboard.writeText(window.location.href).then(() => alert('Link disalin!'));">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>

            <!-- Related Posts -->
            @if($relatedPosts->isNotEmpty())
            <div class="related-posts">
                <h2 class="related-posts-title">Berita Terkait</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    @foreach($relatedPosts as $related)
                        @php
                            $relatedImageUrl = $related->getThumbnailUrl() ?? 'https://via.placeholder.com/400x250?text=No+Image';
                        @endphp
                        <a href="{{ route('post.show', $related->slug) }}" style="background: linear-gradient(135deg, #6A9C89 0%, #16423C 100%); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(22, 66, 60, 0.3); transition: transform 0.3s ease, box-shadow 0.3s ease; display: block;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(22, 66, 60, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(22, 66, 60, 0.3)'">
                            <img src="{{ $relatedImageUrl }}" alt="{{ $related->title }}" style="width: 100%; height: 180px; object-fit: cover;">
                            <div style="padding: 1.5rem;">
                                <h3 style="font-family: 'Poppins', sans-serif; font-size: 1rem; font-weight: 700; color: #ffffff; margin-bottom: 0.5rem; line-height: 1.4;">{{ $related->title }}</h3>
                                <span style="font-family: 'Poppins', sans-serif; font-size: 0.875rem; color: rgba(255,255,255,0.7);">{{ $related->published_at ? \Carbon\Carbon::parse($related->published_at)->locale('id')->isoFormat('D MMMM YYYY') : '' }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>

    <x-footer />

</body>
</html>
