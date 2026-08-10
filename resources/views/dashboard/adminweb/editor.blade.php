@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'WhatsApp', 'url' => '/adminweb/whatsapp', 'active' => 'adminweb/whatsapp*'],
        ['label' => 'Files & Images', 'url' => '/adminweb/files', 'active' => 'adminweb/files*'],
    ]
])

@push('styles')
    @filamentStyles
    <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
    <style>
        :root {
            --primary-50: #f0f5f4;
            --primary-100: #E8ECEC;
            --primary-200: #B9C6C5;
            --primary-300: #8BA19E;
            --primary-400: #5C7B77;
            --primary-500: #16423C;
            --primary-600: #11322D;
            --primary-700: #0B211E;
            --primary-800: #071412;
            --primary-900: #020706;
            --primary-950: #010403;
        }
    </style>
@endpush

@push('scripts')
    @filamentScripts
@endpush

@section('title', 'Editor')

@php $post ??= null; @endphp

@section('content')
<style>
    .editor-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }

    .editor-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        color: #16423c;
        text-decoration: none;
        transition: background 0.2s;
    }

    .editor-back:hover {
        background-color: rgba(22, 66, 60, 0.08);
    }

    .editor-title {
        font-size: 24px;
        font-weight: 700;
        color: #16423c;
    }
</style>

<div class="editor-header">
    <a href="{{ route('adminweb.posts') }}" class="editor-back">
        <span class="material-icons">arrow_back</span>
    </a>
    <h1 class="editor-title">Editor</h1>
</div>

@livewire('post-editor', ['post' => $post ?? null], key($post ? 'edit-' . $post->id : 'create'))
@endsection
