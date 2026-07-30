<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FileOrganisasi;
use App\Models\LogSuperadmin;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SuperAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('nama', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })->paginate(20)->withQueryString();

        $totalUsers = User::count();
        $activeUsers = User::where('status', 'aktif')->count();
        $files = FileOrganisasi::all();
        $logs = LogSuperadmin::all();

        return view('dashboard.superadmin.index', compact('users', 'totalUsers', 'activeUsers', 'files', 'logs'));
    }

    public function fileIndex(Request $request)
    {
        $search = $request->get('search');
        $kategori = $request->get('kategori');
        $kategoriLabels = config('file-pemilah.labels');

        $allItems = collect();

        // 1. FileOrganisasi records
        $fileOrganisasi = FileOrganisasi::with('uploadedBy')
            ->when($kategori, function ($q, $k) {
                return $q->where('kategori', $k);
            })
            ->get()
            ->map(function ($f) use ($kategoriLabels) {
                $media = $f->getFirstMedia('uploads');
                $kB = $kategoriLabels[$f->kategori] ?? ucfirst($f->kategori);
                $sClass = match ($f->status) {
                    'aktif' => 'badge-success', 'diperiksa' => 'badge-warning',
                    'arsip' => 'badge-info', 'dihapus' => 'badge-danger',
                    default => 'badge-info',
                };
                $sLabel = match ($f->status) {
                    'aktif' => 'Aktif', 'diperiksa' => 'Diperiksa',
                    'arsip' => 'Arsip', 'dihapus' => 'Dihapus',
                    default => $f->status,
                };
                return (object) [
                    'source' => 'file_organisasi',
                    'source_label' => 'File Organisasi',
                    'nama_file' => $f->nama_file,
                    'kategori' => $f->kategori,
                    'kategori_label' => $kB,
                    'kategori_badge' => config("file-pemilah.badge_classes.{$f->kategori}", 'badge-info'),
                    'status' => $f->status,
                    'status_badge' => $sClass,
                    'status_label' => $sLabel,
                    'uploader_nama' => $f->uploadedBy?->nama ?? 'Tidak diketahui',
                    'created_at' => $f->created_at,
                    'size' => $media?->size ?? 0,
                    'mime_type' => $media?->mime_type ?? '-',
                    'media_id' => $media?->id,
                    'download_url' => route('superadmin.file.download', $f->id),
                    'id' => $f->id,
                ];
            });

        $allItems = $allItems->merge($fileOrganisasi);

        // 2. Media from other models (exclude FileOrganisasi's own media)
        if (!$kategori || $kategori === 'media') {
            $mediaQuery = Media::where('model_type', '!=', FileOrganisasi::class);
            if ($kategori !== 'media') {
                // no kategori filter needed — media records are always shown unless filtered to a non-media category
            }
            $mediaRecords = $mediaQuery->get()->map(function ($m) {
                $uploaderName = 'Tidak diketahui';

                // 1. Check custom_properties from newer uploads
                $cpUploadedBy = $m->custom_properties['uploaded_by'] ?? null;
                if ($cpUploadedBy) {
                    $uploader = User::find($cpUploadedBy);
                    $uploaderName = $uploader?->nama ?? 'Tidak diketahui';
                } else {
                    // 2. Fallback: resolve from parent model (for legacy uploads)
                    try {
                        $parent = $m->model_type::find($m->model_id);
                        if ($parent && method_exists($parent, 'uploadedBy')) {
                            $uploaderName = $parent->uploadedBy?->nama ?? 'Tidak diketahui';
                        } elseif ($parent && method_exists($parent, 'createdBy')) {
                            $uploaderName = $parent->createdBy?->nama ?? 'Tidak diketahui';
                        } elseif ($parent && isset($parent->uploaded_by)) {
                            $uploaderName = User::find($parent->uploaded_by)?->nama ?? 'Tidak diketahui';
                        } elseif ($parent && isset($parent->created_by)) {
                            $uploaderName = User::find($parent->created_by)?->nama ?? 'Tidak diketahui';
                        }
                    } catch (\Throwable $e) {
                        $uploaderName = 'Tidak diketahui';
                    }
                }

                $modelShort = class_basename($m->model_type);

                return (object) [
                    'source' => 'media',
                    'source_label' => $modelShort . ' (' . $m->collection_name . ')',
                    'nama_file' => $m->file_name,
                    'kategori' => 'media',
                    'kategori_label' => $modelShort,
                    'kategori_badge' => 'badge-info',
                    'status' => '-',
                    'status_badge' => 'badge-info',
                    'status_label' => '-',
                    'uploader_nama' => $uploaderName,
                    'created_at' => $m->created_at,
                    'size' => $m->size ?? 0,
                    'mime_type' => $m->mime_type ?? '-',
                    'media_id' => $m->id,
                    'download_url' => route('media.download', $m->id),
                    'id' => $m->id,
                ];
            });
            $allItems = $allItems->merge($mediaRecords);
        }

        // Search filter
        if ($search) {
            $allItems = $allItems->filter(function ($item) use ($search) {
                return stripos($item->nama_file, $search) !== false
                    || stripos($item->uploader_nama, $search) !== false;
            })->values();
        }

        // Sort by created_at desc
        $allItems = $allItems->sortByDesc(function ($item) {
            return $item->created_at?->timestamp ?? 0;
        })->values();

        // Manual pagination
        $perPage = 20;
        $page = Paginator::resolveCurrentPage();
        $total = $allItems->count();
        $items = $allItems->forPage($page, $perPage);
        $files = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('dashboard.superadmin.file', compact('files', 'kategoriLabels'));
    }

    public function logIndex(Request $request)
    {
        $search = $request->get('search');

        $logs = LogSuperadmin::with('user')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('aksi', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('nama', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.superadmin.log', compact('logs'));
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        try {
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            $extension = strtolower($uploadedFile->getClientOriginalExtension());

            $kategori = $this->klasifikasikanEkstensi($extension);

            $fileRecord = FileOrganisasi::create([
                'nama_file' => $originalName,
                'file_path' => '',
                'kategori' => $kategori,
                'status' => 'aktif',
                'uploaded_by' => auth()->id(),
            ]);

            $fileRecord
                ->addMedia($uploadedFile->getRealPath())
                ->withCustomProperties(['uploaded_by' => auth()->id()])
                ->usingFileName(\App\Services\FileRenamer::rename($originalName))
                ->toMediaCollection('uploads');

            LogSuperadmin::create([
                'user_id' => auth()->id(),
                'aksi' => 'upload',
                'deskripsi' => 'Mengunggah file: ' . $originalName . ' (kategori: ' . config("file-pemilah.labels.{$kategori}") . ')',
                'modul' => 'File',
                'referensi_id' => $fileRecord->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $label = config("file-pemilah.labels.{$kategori}");
            return redirect('/superadmin/file')->with('success', "File \"{$originalName}\" berhasil diunggah sebagai kategori: {$label}.");
        } catch (FileIsTooBig $e) {
            Log::error('Upload gagal: file terlalu besar', ['error' => $e->getMessage()]);
            return redirect('/superadmin/file')->with('error', 'File terlalu besar. Maksimal 20MB.');
        } catch (\Exception $e) {
            Log::error('Upload gagal: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect('/superadmin/file')->with('error', 'Gagal mengunggah file: ' . $e->getMessage());
        }
    }

    public function downloadFile(Request $request, $id)
    {
        $file = FileOrganisasi::findOrFail($id);

        $spatieMedia = $file->getFirstMedia('uploads');
        if ($spatieMedia) {
            LogSuperadmin::create([
                'user_id' => auth()->id(),
                'aksi' => 'download',
                'deskripsi' => 'Mengunduh file: ' . $file->nama_file . ' (kategori: ' . config("file-pemilah.labels.{$file->kategori}") . ')',
                'modul' => 'File',
                'referensi_id' => $file->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $fullPath = $spatieMedia->getPath();
            return response()->download($fullPath, $spatieMedia->file_name, [
                'Content-Type' => 'application/octet-stream',
            ]);
        }

        $disk = match ($file->kategori) {
            'diterima' => 'file_diterima',
            'mencurigakan' => 'file_mencurigakan',
            'ditolak' => 'file_ditolak',
            default => 'file_ditolak',
        };

        if (!Storage::disk($disk)->exists($file->file_path)) {
            return redirect('/superadmin/file')->with('error', 'File tidak ditemukan di penyimpanan.');
        }

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'download',
            'deskripsi' => 'Mengunduh file: ' . $file->nama_file . ' (kategori: ' . config("file-pemilah.labels.{$file->kategori}") . ')',
            'modul' => 'File',
            'referensi_id' => $file->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Storage::disk($disk)->download($file->file_path, $file->nama_file, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function trashFiles()
    {
        $files = Media::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->map(function ($m) {
                $uploaderName = 'Tidak diketahui';
                $cpUploadedBy = $m->custom_properties['uploaded_by'] ?? null;
                if ($cpUploadedBy) {
                    $uploader = User::find($cpUploadedBy);
                    $uploaderName = $uploader?->nama ?? 'Tidak diketahui';
                }
                $modelShort = class_basename($m->model_type);
                return (object) [
                    'id' => $m->id,
                    'nama_file' => $m->file_name,
                    'source_label' => $modelShort . ' (' . $m->collection_name . ')',
                    'uploader_nama' => $uploaderName,
                    'size' => $m->size ?? 0,
                    'mime_type' => $m->mime_type ?? '-',
                    'deleted_at' => $m->deleted_at,
                    'download_url' => route('media.download', $m->id),
                ];
            });

        return view('dashboard.superadmin.file-trash', compact('files'));
    }

    public function restoreFile($id)
    {
        $media = Media::onlyTrashed()->findOrFail($id);
        $name = $media->file_name;
        $media->restore();

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'restore',
            'deskripsi' => 'Memulihkan file yang dihapus: ' . $name,
            'modul' => 'File',
            'referensi_id' => $media->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect('/superadmin/file/trash')->with('success', 'File "' . $name . '" berhasil dipulihkan.');
    }

    private function klasifikasikanEkstensi(string $extension): string
    {
        $ekstensiDiterima = config('file-pemilah.diterima', []);
        $ekstensiMencurigakan = config('file-pemilah.mencurigakan', []);
        $ekstensiDitolak = config('file-pemilah.ditolak', []);

        if (in_array($extension, $ekstensiDiterima)) {
            return 'diterima';
        }

        if (in_array($extension, $ekstensiMencurigakan)) {
            return 'mencurigakan';
        }

        return 'ditolak';
    }

    public function updateUser(Request $request, $id)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:bendahara,sekretaris,logistik,ketua,superadmin,adminweb',
            'status' => 'required|in:aktif,non_aktif',
        ]);

        $user = User::findOrFail($id);
        $oldData = $user->replicate();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        $changes = [];
        if ($oldData->nama !== $user->nama) {
            $changes[] = "nama dari '{$oldData->nama}' ke '{$user->nama}'";
        }
        if ($oldData->email !== $user->email) {
            $changes[] = "email dari '{$oldData->email}' ke '{$user->email}'";
        }
        if ($oldData->role !== $user->role) {
            $changes[] = "role dari '{$oldData->role}' ke '{$user->role}'";
        }
        if ($oldData->status !== $user->status) {
            $changes[] = "status dari '{$oldData->status}' ke '{$user->status}'";
        }
        if (!empty($data['password'])) {
            $changes[] = 'password diubah';
        }

        $deskripsi = 'Memperbarui user: ' . $user->nama;
        if (!empty($changes)) {
            $deskripsi .= ' (' . implode(', ', $changes) . ')';
        }

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'update',
            'deskripsi' => $deskripsi,
            'modul' => 'User',
            'referensi_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect('/superadmin')->with('success', 'User berhasil diperbarui.');
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:bendahara,sekretaris,logistik,ketua,superadmin,adminweb',
            'status' => 'required|in:aktif,non_aktif',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'deskripsi' => 'Menambahkan user baru: ' . $user->nama . ' (' . $user->email . ')',
            'modul' => 'User',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect('/superadmin')->with('success', 'User "' . $user->nama . '" berhasil ditambahkan.');
    }
}
