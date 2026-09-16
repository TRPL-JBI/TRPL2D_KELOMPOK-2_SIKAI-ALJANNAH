<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FileOrganisasi;
use App\Models\LogSuperadmin;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SuperAdminController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'aktif')->count();

        // Scan filesystem for real file count and size (same as file page)
        $allFiles = collect();
        $this->scanDirectory(storage_path('app'), $allFiles);
        $totalFiles = $allFiles->count();
        $totalSize = $allFiles->sum('size');

        // Storage usage by file type (from filesystem)
        $storageCategories = $allFiles->groupBy('kategori')->map(fn($items) => $items->count())->toArray();

        // Weekly activity data (last 7 days) - includes ALL modules
        $weeklyActivity = [];
        $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = LogSuperadmin::whereDate('created_at', $date)->count();
            $weeklyActivity[] = [
                'day' => $days[$date->dayOfWeek],
                'count' => $count,
            ];
        }

        // Role distribution
        $roleDistribution = User::select('role', \DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        // Recent logs for activity feed (from all modules)
        $recentLogs = LogSuperadmin::with('user')
            ->latest()
            ->take(5)
            ->get();

        $totalLogs = LogSuperadmin::count();

        return view('dashboard.superadmin.index', compact(
            'totalUsers', 'activeUsers', 'totalFiles', 'totalSize', 'totalLogs',
            'storageCategories', 'weeklyActivity', 'roleDistribution', 'recentLogs'
        ));
    }

    public function userIndex(Request $request)
    {
        $search = $request->get('search');
        $role = $request->get('role');
        $status = $request->get('status');

        $users = User::when($search, function ($query, $search) {
            return $query->where('nama', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })->when($role, function ($query, $role) {
            return $query->where('role', $role);
        })->when($status, function ($query, $status) {
            return $query->where('status', $status);
        })->paginate(20)->withQueryString();

        $totalUsers = User::count();
        $activeUsers = User::where('status', 'aktif')->count();

        return view('dashboard.superadmin.users', compact('users', 'totalUsers', 'activeUsers'));
    }

    public function fileIndex(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type');
        $sort = $request->get('sort', 'date-desc');

        $allItems = collect();
        $baseDir = storage_path('app');

        // Scan storage/app recursively, exclude whatsapp-sidecar
        $this->scanDirectory($baseDir, $allItems, $search, $type);

        // Sort
        $allItems = match ($sort) {
            'date-asc' => $allItems->sortBy('created_at')->values(),
            'size-desc' => $allItems->sortByDesc('size')->values(),
            'size-asc' => $allItems->sortBy('size')->values(),
            'name-asc' => $allItems->sortBy('nama_file')->values(),
            default => $allItems->sortByDesc('created_at')->values(),
        };

        // Manual pagination
        $perPage = 10;
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

        // Storage stats
        $totalSize = $allItems->sum('size');
        $totalFiles = $allItems->count();

        return view('dashboard.superadmin.file', compact('files', 'totalSize', 'totalFiles'));
    }

    private function scanDirectory($directory, &$items, $search = null, $type = null)
    {
        $excludedFolders = ['whatsapp-sidecar'];
        $baseAppDir = storage_path('app');

        if (!is_dir($directory)) {
            return;
        }

        $scanDir = function ($currentDir) use (&$scanDir, &$items, $search, $type, $excludedFolders, $baseAppDir) {
            $handle = opendir($currentDir);
            if (!$handle) return;

            while (($entry = readdir($handle)) !== false) {
                if ($entry === '.' || $entry === '..') continue;

                $fullPath = $currentDir . DIRECTORY_SEPARATOR . $entry;

                if (is_dir($fullPath)) {
                    // Skip excluded folders
                    if (in_array($entry, $excludedFolders)) continue;
                    // Skip hidden folders
                    if (str_starts_with($entry, '.')) continue;
                    $scanDir($fullPath);
                } elseif (is_file($fullPath)) {
                    $fileName = basename($fullPath);
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $size = filesize($fullPath);
                    $lastModified = filemtime($fullPath);

                    // Skip hidden files
                    if (str_starts_with($fileName, '.')) continue;

                    // Filter by type
                    if ($type) {
                        $fileType = $this->getFileType($extension);
                        if ($fileType !== $type) continue;
                    }

                    // Filter by search
                    if ($search && stripos($fileName, $search) === false) continue;

                    // Get relative path from storage/app
                    $relativePath = str_replace($baseAppDir . DIRECTORY_SEPARATOR, '', $fullPath);
                    $relativePath = str_replace('\\', '/', $relativePath);

                    $items->push((object) [
                        'id' => md5($fullPath),
                        'nama_file' => $fileName,
                        'file_path' => $relativePath,
                        'kategori' => $this->getFileType($extension),
                        'size' => $size,
                        'mime_type' => mime_content_type($fullPath) ?: 'application/octet-stream',
                        'created_at' => now()->setTimestamp($lastModified),
                        'extension' => $extension,
                    ]);
                }
            }
            closedir($handle);
        };

        $scanDir($directory);
    }

    private function getFileType($extension)
    {
        return match (true) {
            in_array($extension, ['pdf']) => 'pdf',
            in_array($extension, ['doc', 'docx', 'txt', 'rtf']) => 'doc',
            in_array($extension, ['xls', 'xlsx', 'csv']) => 'xls',
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp']) => 'img',
            in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'mkv']) => 'vid',
            in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz']) => 'zip',
            default => 'lainnya',
        };
    }

    public function logIndex(Request $request)
    {
        $search = $request->get('search');
        $action = $request->get('action');
        $time = $request->get('time');

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
            ->when($action, function ($query, $action) {
                return $query->where('aksi', $action);
            })
            ->when($time, function ($query, $time) {
                return match ($time) {
                    'today' => $query->whereDate('created_at', today()),
                    'yesterday' => $query->whereDate('created_at', today()->subDay()),
                    'week' => $query->where('created_at', '>=', now()->subDays(7)),
                    default => $query,
                };
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Log stats
        $totalLogs = LogSuperadmin::count();
        $loginLogs = LogSuperadmin::where('aksi', 'login')->count();
        $uploadDownloadLogs = LogSuperadmin::whereIn('aksi', ['upload', 'download'])->count();
        $adminActionLogs = LogSuperadmin::whereIn('aksi', ['create', 'update', 'delete'])->count();
        $securityLogs = LogSuperadmin::whereIn('aksi', ['login_failed', 'unauthorized'])->count();

        return view('dashboard.superadmin.log', compact(
            'logs', 'totalLogs', 'loginLogs', 'uploadDownloadLogs', 'adminActionLogs', 'securityLogs'
        ));
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
            'no_whatsapp' => 'nullable|string|max:20',
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
        if (($oldData->no_whatsapp ?? '') !== ($user->no_whatsapp ?? '')) {
            $changes[] = "no_whatsapp dari '{$oldData->no_whatsapp}' ke '{$user->no_whatsapp}'";
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
            'no_whatsapp' => 'nullable|string|max:20',
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
