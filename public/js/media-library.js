(function () {
    const COLORS = {
        primary: '#16423c',
        primaryHover: '#1e5a50',
        border: '#e0e0e0',
        text: '#1a1a1a',
        dim: '#6b7280',
        bg: '#ffffff',
        overlay: 'rgba(15, 23, 42, 0.5)',
        danger: '#dc2626',
    };

    const BASE_URL = window.mediaLibraryEndpoint || '/adminweb/files/library';

    let modalEl = null;
    let activeOnSelect = null;

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function fileIcon(mime) {
        if (!mime) return '📄';
        if (mime.includes('pdf')) return '📕';
        if (mime.includes('word') || mime.includes('document')) return '📘';
        if (mime.includes('excel') || mime.includes('spreadsheet')) return '📗';
        if (mime.includes('presentation') || mime.includes('powerpoint')) return '📙';
        if (mime.includes('zip') || mime.includes('rar') || mime.includes('compressed')) return '🗜';
        return '📄';
    }

    function css(styles) {
        return Object.entries(styles)
            .map(([k, v]) => `${k}:${v}`)
            .join(';');
    }

    function ensureStyles() {
        if (document.getElementById('filament-media-library-styles')) return;

        const style = document.createElement('style');
        style.id = 'filament-media-library-styles';
        style.textContent = `
            .fml-overlay{position:fixed;inset:0;background:${COLORS.overlay};z-index:9998;display:flex;align-items:center;justify-content:center;padding:24px;}
            .fml-panel{background:${COLORS.bg};border-radius:16px;box-shadow:0 24px 60px rgba(15,23,42,.25);width:min(880px,100%);max-height:84vh;display:flex;flex-direction:column;overflow:hidden;font-family:'Poppins',sans-serif;}
            .fml-header{display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid ${COLORS.border};}
            .fml-title{font-size:16px;font-weight:700;color:${COLORS.text};flex:1;}
            .fml-search{flex:1;max-width:320px;padding:8px 14px;border:1.5px solid ${COLORS.border};border-radius:8px;font-size:13px;font-family:inherit;outline:none;color:${COLORS.text};background:#f8f9fa;}
            .fml-search:focus{border-color:${COLORS.primary};}
            .fml-close{width:32px;height:32px;border:none;border-radius:8px;background:transparent;color:${COLORS.dim};font-size:20px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;}
            .fml-close:hover{background:#f3f4f6;color:${COLORS.text};}
            .fml-body{flex:1;overflow-y:auto;padding:16px 20px;}
            .fml-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;}
            .fml-empty{padding:48px 20px;text-align:center;color:${COLORS.dim};font-size:13px;}
            .fml-item{border:1px solid ${COLORS.border};border-radius:10px;overflow:hidden;cursor:pointer;background:${COLORS.bg};transition:border-color .15s, box-shadow .15s, transform .15s;display:flex;flex-direction:column;}
            .fml-item:hover{border-color:${COLORS.primary};box-shadow:0 4px 14px rgba(22,66,60,.12);transform:translateY(-2px);}
            .fml-thumb{height:110px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;overflow:hidden;font-size:40px;}
            .fml-thumb img{width:100%;height:100%;object-fit:cover;}
            .fml-name{padding:8px 10px;font-size:11px;color:${COLORS.text};overflow:hidden;text-overflow:ellipsis;white-space:nowrap;border-top:1px solid #f3f4f6;}
            .fml-footer{padding:12px 20px;border-top:1px solid ${COLORS.border};display:flex;align-items:center;justify-content:space-between;gap:12px;}
            .fml-count{font-size:12px;color:${COLORS.dim};}
            .fml-cancel{padding:9px 20px;border:1px solid ${COLORS.border};border-radius:999px;background:${COLORS.bg};color:${COLORS.text};font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;}
            .fml-cancel:hover{background:#f9fafb;}
            .fml-loading{padding:48px 20px;text-align:center;color:${COLORS.dim};font-size:13px;}
            @keyframes fmlFadeIn{from{opacity:0}to{opacity:1}}
        `;
        document.head.appendChild(style);
    }

    async function fetchMedia(search) {
        const url = new URL(BASE_URL, window.location.origin);
        if (search) url.searchParams.set('search', search);
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('Gagal memuat media');
        const json = await res.json();
        return Array.isArray(json.data) ? json.data : [];
    }

    function buildModal(title, search) {
        modalEl = document.createElement('div');
        modalEl.className = 'fml-overlay';
        modalEl.style.cssText = css({ animation: 'fmlFadeIn .15s ease' });

        modalEl.innerHTML = `
            <div class="fml-panel">
                <div class="fml-header">
                    <span class="fml-title">${esc(title || 'Media Library')}</span>
                    <input type="text" class="fml-search" placeholder="Cari media..." value="${esc(search || '')}">
                    <button type="button" class="fml-close" aria-label="Tutup">&times;</button>
                </div>
                <div class="fml-body" data-fml-body><div class="fml-loading">Memuat media...</div></div>
                <div class="fml-footer">
                    <span class="fml-count"></span>
                    <button type="button" class="fml-cancel">Tutup</button>
                </div>
            </div>
        `;

        document.body.appendChild(modalEl);

        const close = () => {
            if (modalEl) {
                modalEl.remove();
                modalEl = null;
                activeOnSelect = null;
            }
        };

        modalEl.querySelector('.fml-close').addEventListener('click', close);
        modalEl.querySelector('.fml-cancel').addEventListener('click', close);
        modalEl.addEventListener('click', (e) => {
            if (e.target === modalEl) close();
        });
        document.addEventListener('keydown', function onKey(e) {
            if (e.key === 'Escape') {
                close();
                document.removeEventListener('keydown', onKey);
            }
        });

        return { panel: modalEl, body: modalEl.querySelector('[data-fml-body]'), close };
    }

    async function renderMedia(body, search, onSelect, countEl) {
        body.innerHTML = '<div class="fml-loading">Memuat media...</div>';
        try {
            const media = await fetchMedia(search);
            if (!modalEl) return;

            if (countEl) countEl.textContent = media.length + ' media';

            if (media.length === 0) {
                body.innerHTML = '<div class="fml-empty">Tidak ada media ditemukan.</div>';
                return;
            }

            const grid = document.createElement('div');
            grid.className = 'fml-grid';

            media.forEach((item, index) => {
                const cell = document.createElement('div');
                cell.className = 'fml-item';
                cell.tabIndex = 0;
                cell.setAttribute('role', 'button');
                cell.setAttribute('aria-label', item.original_name || item.name);

                const thumb = item.is_image
                    ? `<img src="${esc(item.url)}" alt="" loading="lazy" onerror="this.style.display='none';this.parentNode.textContent='🖼';this.parentNode.style.fontSize='40px';">`
                    : fileIcon(item.mime_type);

                cell.innerHTML = `<div class="fml-thumb">${thumb}</div><div class="fml-name">${esc(item.original_name || item.name)}</div>`;

                const activate = () => {
                    if (onSelect) onSelect(item);
                    if (modalEl) modalEl.remove();
                    modalEl = null;
                };
                cell.addEventListener('click', activate);
                cell.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        activate();
                    }
                });

                grid.appendChild(cell);
            });

            body.innerHTML = '';
            body.appendChild(grid);
        } catch (err) {
            body.innerHTML = `<div class="fml-empty">Gagal memuat media: ${esc(err.message)}</div>`;
        }
    }

    function openModal(options) {
        ensureStyles();

        const { title, onSelect } = options;
        activeOnSelect = onSelect;

        const { body, close, panel } = buildModal(title);
        const searchInput = panel.querySelector('.fml-search');
        const countEl = panel.querySelector('.fml-count');

        let debounce;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounce);
            debounce = setTimeout(() => renderMedia(body, searchInput.value.trim(), onSelect, countEl), 300);
        });

        renderMedia(body, '', onSelect, countEl);
        return close;
    }

    function pick(options) {
        return openModal(options);
    }

    function openForEditor(options) {
        const getEditor = options.getEditor || (() => null);

        return openModal({
            title: options.title || 'Sisipkan dari Media',
            onSelect(item) {
                const editor = getEditor();
                if (!editor) return;

                let content;
                if (item.is_image) {
                    content = { type: 'image', attrs: { src: item.url, alt: item.original_name || item.name } };
                } else {
                    content = {
                        type: 'fileEmbed',
                        attrs: {
                            fileId: String(item.id),
                            fileName: item.original_name || item.name,
                            fileUrl: item.url,
                            mimeType: item.mime_type || '',
                        },
                    };
                }

                editor.chain().focus().insertContent([content]).run();
            },
        });
    }

    window.FilamentMediaLibrary = {
        pick,
        openForEditor,
    };
})();
