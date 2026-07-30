@props(['menus' => []])

@php
    if (count($menus) === 0) {
        $menus = \App\Models\Menu::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with(['children' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->get();
    }
@endphp

<nav class="navbar">
    <!-- Logo -->
    <a href="/" class="navbar-logo">RKM Al-Jannah</a>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Menu Tengah & Buttons Wrapper -->
    <div class="nav-menu-wrapper" id="navMenuWrapper">
        <!-- Menu Tengah -->
        @if(count($menus) > 0)
            <ul class="nav-menu" id="navMenu">
                @foreach($menus as $menu)
                    @php
                        $url = $menu->custom_url ?: '#';
                        $hasChildren = $menu->children && $menu->children->count() > 0;
                    @endphp
                    <li class="{{ $hasChildren ? 'nav-item-has-children' : '' }}">
                        <a href="{{ $url }}" class="nav-link scroll-link{{ $hasChildren ? ' nav-link-children' : '' }}">
                            {{ $menu->label }}
                            @if($hasChildren)
                                <span class="nav-arrow">▾</span>
                            @endif
                        </a>
                        @if($hasChildren)
                            <ul class="nav-submenu">
                                @foreach($menu->children as $child)
                                    @php $childUrl = $child->custom_url ?: '#'; @endphp
                                    <li>
                                        <a href="{{ $childUrl }}" class="nav-sub-link scroll-link">{{ $child->label }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <!-- Default Static Menus (Fallback) -->
            <ul class="nav-menu" id="navMenu">
                <li>
                    <a href="#tentang-kami" class="nav-link scroll-link">Tentang Kami</a>
                </li>
                <li>
                    <a href="#visi-misi" class="nav-link scroll-link">Visi & Misi</a>
                </li>
                <li>
                    <a href="#kanal-berita" class="nav-link scroll-link">Kanal Berita</a>
                </li>
                <li>
                    <a href="#layanan-keuntungan" class="nav-link scroll-link">Layanan & Keuntungan</a>
                </li>
                <li>
                    <a href="#hubungi-kami" class="nav-link scroll-link">Hubungi Kami</a>
                </li>
            </ul>
        @endif

        <!-- Tombol Kanan -->
        <div class="nav-buttons">
            <a href="/anggota" class="btn btn-login">Anggota</a>
            <a href="/login" class="btn btn-login">Login</a>
            <a href="/daftar" class="btn btn-daftar">Daftar Anggota</a>
        </div>
    </div>
</nav>

<script>
    // Initialize everything after DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Menu Toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const navMenuWrapper = document.getElementById('navMenuWrapper');

        if (mobileMenuToggle && navMenuWrapper) {
            mobileMenuToggle.addEventListener('click', function() {
                navMenuWrapper.classList.toggle('active');
                mobileMenuToggle.classList.toggle('active');
            });

            // Close menu when clicking on a link
            document.querySelectorAll('.nav-link, .nav-sub-link').forEach(link => {
                link.addEventListener('click', () => {
                    mobileMenuToggle.classList.remove('active');
                    navMenuWrapper.classList.remove('active');
                });
            });
        }

        // Smooth Scroll for Navbar Links
        const scrollLinks = document.querySelectorAll('.scroll-link');
        const navbar = document.querySelector('.navbar');

        scrollLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');

                // Only apply for anchor links
                if (href.startsWith('#') && href.length > 1) {
                    const targetId = href.substring(1);
                    const targetElement = document.getElementById(targetId);

                    // If section exists on current page, smooth scroll
                    if (targetElement) {
                        e.preventDefault();

                        // Close mobile menu if open
                        if (mobileMenuToggle && navMenuWrapper) {
                            mobileMenuToggle.classList.remove('active');
                            navMenuWrapper.classList.remove('active');
                        }

                        // Smooth scroll with offset for fixed navbar
                        const navbarHeight = navbar?.offsetHeight || 80;
                        const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight;

                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                    // If section doesn't exist (e.g., on post detail page), redirect to homepage
                    else if (window.location.pathname !== '/') {
                        e.preventDefault();

                        // Close mobile menu if open
                        if (mobileMenuToggle && navMenuWrapper) {
                            mobileMenuToggle.classList.remove('active');
                            navMenuWrapper.classList.remove('active');
                        }

                        // Redirect to homepage with hash
                        window.location.href = '/' + href;
                    }
                }
            });
        });

        // Mobile: toggle sub-menu on click for touch devices
        document.querySelectorAll('.nav-item-has-children > .nav-link-children').forEach(link => {
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    const parent = this.closest('.nav-item-has-children');
                    parent.classList.toggle('mobile-sub-open');
                }
            });
        });
    });
</script>
