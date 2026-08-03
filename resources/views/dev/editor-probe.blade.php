@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'Files & Images', 'url' => '/adminweb/files', 'active' => 'adminweb/files*'],
    ]
])

@push('styles')
    @filamentStyles
    <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
@endpush

@push('scripts')
    @filamentScripts
@endpush

@section('title', 'Editor Probe')

@section('content')
@php $post ??= null; @endphp

@livewire('post-editor', ['post' => $post ?? null], key($post ? 'edit-' . $post->id : 'create'))

<pre id="__probe" style="position:fixed;bottom:0;left:0;z-index:99999;background:#111;color:#0f0;padding:12px;font:11px monospace;max-width:70%;white-space:pre-wrap;display:none;"></pre>

<script>
window.__probeData = {};
window.__errors = [];
(function () {
    var origOnError = window.onerror;
    window.onerror = function (msg, src, line) {
        window.__errors.push(msg + ' @' + (src||'') + ':' + line);
        if (origOnError) return origOnError.apply(this, arguments);
    };
    var origError = console.error.bind(console);
    console.error = function () {
        window.__errors.push('console.error: ' + Array.prototype.slice.call(arguments).join(' '));
        origError.apply(null, arguments);
    };
    var origWarn = console.warn.bind(console);
    console.warn = function () {
        window.__errors.push('console.warn: ' + Array.prototype.slice.call(arguments).join(' '));
        origWarn.apply(null, arguments);
    };

    function poke() {
        var r = window.__probe;
        var btn = null;
        var els = document.querySelectorAll('button[aria-label="Media Library"],[data-toolbar-tool][aria-label="Media Library"]');
        if (els.length) btn = els[0];
        r.global = !(typeof window.FilamentMediaLibrary === 'undefined');
        r.freLoaded = !(typeof window.FilamentRichEditor === 'undefined');
        r.pm = !!document.querySelector('.tiptap, .ProseMirror');
        r.buttons = document.querySelectorAll('button[aria-label="Media Library"]').length;
        r.allToolButtons = document.querySelectorAll('.fi-fo-rich-editor-tool').length;
        r.alpineGlobal = !(typeof window.Alpine === 'undefined');

        var rets = {};
        if (btn) btn.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, view: window }));
        rets.afterDispatchClick = !!document.querySelector('.fml-overlay');

        // 2) native .click()
        if (btn) btn.click();
        rets.afterNativeClick = !!document.querySelector('.fml-overlay');

        // 3) call the global directly
        try {
            window.FilamentMediaLibrary.openForEditor({ getEditor: () => null, title: 'DIRECT' });
            rets.afterDirectCall = !!document.querySelector('.fml-overlay');
        } catch (e) { rets.directErr = e.message; }

        r.directTests = rets;

        setTimeout(function () {
            r.overlayFinal = !!document.querySelector('.fml-overlay');
            if (r.overlayFinal) {
                var ov = document.querySelector('.fml-overlay');
                var c = getComputedStyle(ov);
                var rc = ov.getBoundingClientRect();
                r.overlayInfo = { pos: c.position, z: c.zIndex, disp: c.display, vis: c.visibility, rect: [rc.x, rc.y, rc.width, rc.height], items: document.querySelectorAll('.fml-item').length, mediaText: (document.querySelector('.fml-empty')||{}).textContent||'' };
            }
            r.consoleErrors = window.__errors;
            document.getElementById('__probe').textContent = JSON.stringify(r, null, 2);
            document.getElementById('__probe').style.display = 'block';
        }, 2000);
    }
    window.addEventListener('load', function(){ setTimeout(poke, 4000); });
})();
</script>
@endsection