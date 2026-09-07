<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Page;
use App\Models\Category;
use App\Services\FileRenamer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\LogSuperadmin;

class AdminWebController extends Controller
{
    public function index()
    {
        $jumlahPosts = Post::count();
        $jumlahPages = Page::count();
        $jumlahKategori = Category::count();
        $jumlahPengunjung = 0;

        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $pengunjungPerBulan = array_fill(0, 12, 0);

        return view('dashboard.adminweb.index', compact(
            'jumlahPosts',
            'jumlahPages',
            'jumlahKategori',
            'jumlahPengunjung',
            'bulan',
            'pengunjungPerBulan'
        ));
    }

    public function posts(Request $request)
    {
        $search = $request->get('search');

        $posts = Post::with(['category', 'legacyMedia'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhereHas('category', function ($cq) use ($search) {
                          $cq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::all();

        return view('dashboard.adminweb.posts', compact('posts', 'categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $data['slug'] = Str::slug($data['name']);

        Category::create($data);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'deskripsi' => 'Menambahkan kategori: ' . $data['name'],
            'modul' => 'admin_web',
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('adminweb.posts')->with('success', 'Kategori "' . e($data['name']) . '" berhasil ditambahkan.');
    }

    public function createPost()
    {
        return view('dashboard.adminweb.editor');
    }

    public function editPost($id)
    {
        $post = Post::with('category')->findOrFail($id);
        return view('dashboard.adminweb.editor', compact('post'));
    }

    public function publishPost($id)
    {
        $post = Post::findOrFail($id);
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'update',
            'deskripsi' => 'Menerbitkan postingan: ' . $post->title,
            'modul' => 'admin_web',
            'referensi_id' => $post->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('adminweb.posts')->with('success', 'Postingan "' . e($post->title) . '" berhasil diterbitkan.');
    }

    public function destroyPost($id)
    {
        $post = Post::findOrFail($id);
        $title = $post->title;

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'delete',
            'deskripsi' => 'Menghapus postingan: ' . $title,
            'modul' => 'admin_web',
            'referensi_id' => $post->id,
            'ip_address' => request()->ip(),
        ]);

        $post->delete();

        return redirect()->route('adminweb.posts')->with('success', 'Postingan "' . e($title) . '" berhasil dihapus.');
    }

    public function editPage($id)
    {
        $page = Page::findOrFail($id);

        if ($page->slug === 'beranda') {
            $data = json_decode($page->content, true) ?? [];
            return view('dashboard.adminweb.page-edit-beranda', compact('page', 'data'));
        }

        return redirect()->route('adminweb.pages.builder', $id);
    }

    public function updatePage(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        if ($page->slug === 'beranda') {
            $request->validate([
                'services.items.*.image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'member_benefits.items.*.image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            ]);

            $data = $this->sanitizeData($request->except('_token', '_method'));

            if (isset($data['services']['items'])) {
                foreach ($data['services']['items'] as $i => &$svc) {
                    if ($request->hasFile("services.items.{$i}.image_file")) {
                        $svc['image'] = $this->storePageImage($request->file("services.items.{$i}.image_file"), $page);
                    } else {
                        $svc['image'] = $svc['existing_image'] ?? '';
                    }
                    unset($svc['existing_image']);
                }
            }

            if (isset($data['member_benefits']['items'])) {
                foreach ($data['member_benefits']['items'] as $i => &$item) {
                    if ($request->hasFile("member_benefits.items.{$i}.image_file")) {
                        $item['image'] = $this->storePageImage($request->file("member_benefits.items.{$i}.image_file"), $page);
                    } else {
                        $item['image'] = $item['existing_image'] ?? '';
                    }
                    unset($item['existing_image']);
                }
            }

            $page->update([
                'content' => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ]);

            LogSuperadmin::create([
                'user_id' => auth()->id(),
                'aksi' => 'update',
                'deskripsi' => 'Memperbarui halaman beranda',
                'modul' => 'admin_web',
                'referensi_id' => $page->id,
                'ip_address' => request()->ip(),
            ]);

            return redirect()->route('adminweb.pages.edit', $id)
                ->with('success', 'Halaman Beranda berhasil diperbarui.');
        }

        $page->update($request->only(['title', 'content']));

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'update',
            'deskripsi' => 'Memperbarui halaman: ' . $page->title,
            'modul' => 'admin_web',
            'referensi_id' => $page->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('adminweb.pages.edit', $id)
            ->with('success', 'Halaman "' . e($page->title) . '" berhasil diperbarui.');
    }

    private function storePageImage($file, Page $page): string
    {
        $media = $page
            ->addMedia($file->getRealPath())
            ->withCustomProperties(['uploaded_by' => auth()->id()])
            ->usingFileName(FileRenamer::rename($file->getClientOriginalName()))
            ->toMediaCollection('images');

        return (string) $media->id;
    }

    private function sanitizeData(array $data): array
    {
        $sanitizer = new \Symfony\Component\HtmlSanitizer\HtmlSanitizer(
            (new \Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowRelativeLinks()
                ->allowAttribute('class', allowedElements: '*')
                ->allowAttribute('style', allowedElements: '*')
                ->allowAttribute('href', allowedElements: 'a')
                ->allowAttribute('target', allowedElements: 'a')
                ->withMaxInputLength(500000),
        );

        array_walk_recursive($data, function (&$value) use ($sanitizer) {
            if (is_string($value)) {
                $value = $sanitizer->sanitize($value);
            }
        });

        return $data;
    }

    public function storePage(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug',
        ]);

        $page = Page::create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => '',
            'status' => 'draft',
        ]);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'deskripsi' => 'Menambahkan halaman baru: ' . $data['title'],
            'modul' => 'admin_web',
            'referensi_id' => $page->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('adminweb.pages.builder', $page->id)
            ->with('success', 'Halaman "' . e($page->title) . '" berhasil dibuat.');
    }

    public function builderPage($id)
    {
        $page = Page::findOrFail($id);

        if ($page->slug === 'beranda') {
            return redirect()->route('adminweb.pages.edit', $id);
        }

        $pageState = [];
        $content = $page->content;
        if ($content && $content !== '') {
            $decoded = json_decode($content, true);
            if (is_array($decoded) && isset($decoded[0]) && isset($decoded[0]['type'])) {
                $pageState = $decoded;
            }
        }

        return view('dashboard.adminweb.page-builder', compact('page', 'pageState'));
    }

    public function saveBuilder(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $pageState = $request->input('pageState', []);
        if (!is_array($pageState)) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid.'], 422);
        }

        $page->update([
            'content' => json_encode($pageState, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json(['success' => true, 'message' => 'Halaman berhasil disimpan.']);
    }

    public function pages(Request $request)
    {
        $search = $request->get('search');

        $pages = Page::with('legacyMedia')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->when(!$search, function ($query) {
                return $query->orderByRaw("CASE WHEN slug = 'beranda' THEN 0 ELSE 1 END");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.adminweb.pages', compact('pages'));
    }

    public function publishPage($id)
    {
        $page = Page::findOrFail($id);
        $page->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()->route('adminweb.pages')->with('success', 'Halaman "' . e($page->title) . '" berhasil diterbitkan.');
    }

    public function destroyPage($id)
    {
        $page = Page::findOrFail($id);
        $title = $page->title;

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'delete',
            'deskripsi' => 'Menghapus halaman: ' . $title,
            'modul' => 'admin_web',
            'referensi_id' => $page->id,
            'ip_address' => request()->ip(),
        ]);

        $page->delete();

        return redirect()->route('adminweb.pages')->with('success', 'Halaman "' . e($title) . '" berhasil dihapus.');
    }

    public function files()
    {
        $files = Media::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.adminweb.files.index', compact('files'));
    }

    public function mediaLibrary(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $mediaQuery = Media::whereNull('deleted_at');

        if ($search !== '') {
            $mediaQuery->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('custom_properties', 'like', "%{$search}%");
            });
        }

        $media = $mediaQuery
            ->orderBy('created_at', 'desc')
            ->limit(300)
            ->get();

        $items = $media->map(function (Media $file) {
            $custom = $file->custom_properties ?? [];
            $mime = $file->mime_type ?? '';

            return [
                'id' => $file->getKey(),
                'name' => $file->file_name,
                'original_name' => $file->name ?: $file->file_name,
                'url' => $file->getUrl(),
                'mime_type' => $mime,
                'extension' => $file->extension ?: strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)),
                'is_image' => str_starts_with($mime, 'image/'),
                'uploaded_by' => $custom['uploaded_by'] ?? ($custom['uploaded_by'] ?? null),
                'created_at' => optional($file->created_at)->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $items,
            'count' => $items->count(),
        ]);
    }

    public function destroyFile($id)
    {
        $media = Media::findOrFail($id);
        $name = $media->file_name;

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'delete',
            'deskripsi' => 'Menghapus file media: ' . $name,
            'modul' => 'admin_web',
            'referensi_id' => $media->id,
            'ip_address' => request()->ip(),
        ]);

        $media->delete();

        return response()->json(['success' => true, 'message' => '"' . $name . '" berhasil dihapus.']);
    }

    public function restoreFile($id)
    {
        $media = Media::withTrashed()->findOrFail($id);
        $name = $media->file_name;
        $media->restore();

        return response()->json(['success' => true, 'message' => '"' . $name . '" berhasil dipulihkan.']);
    }
}
