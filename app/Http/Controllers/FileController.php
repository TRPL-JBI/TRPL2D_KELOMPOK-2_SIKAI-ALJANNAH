<?php

namespace App\Http\Controllers;

use App\Models\FileOrganisasi;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LogSuperadmin;

class FileController extends Controller
{
    public function downloadFile(int $id): Response
    {
        $file = FileOrganisasi::find($id);

        if (! $file) {
            abort(404);
        }

        $spatieMedia = $file->getFirstMedia('uploads');
        if ($spatieMedia) {
            $fullPath = $spatieMedia->getPath();

            if (! file_exists($fullPath)) {
                abort(404);
            }

            LogSuperadmin::create([
                'user_id' => auth()->id(),
                'aksi' => 'download',
                'deskripsi' => 'Download file: ' . $spatieMedia->file_name,
                'modul' => 'file',
                'referensi_id' => $file->id,
                'ip_address' => request()->ip(),
            ]);

            return $this->serve($fullPath, $spatieMedia->file_name);
        }

        $disk = match ($file->kategori) {
            'diterima' => 'file_diterima',
            'mencurigakan' => 'file_mencurigakan',
            'ditolak' => 'file_ditolak',
            default => 'file_diterima',
        };

        if (blank($file->file_path)) {
            abort(404, 'File path is missing for this record.');
        }

        $diskDriver = Storage::disk($disk);

        if (! $diskDriver->exists($file->file_path)) {
            abort(404);
        }

        $fullPath = $diskDriver->path($file->file_path);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'download',
            'deskripsi' => 'Download file: ' . $file->nama_file,
            'modul' => 'file',
            'referensi_id' => $file->id,
            'ip_address' => request()->ip(),
        ]);

        return $this->serve($fullPath, $file->nama_file);
    }

    public function downloadMedia(int $id): Response
    {
        $spatieMedia = SpatieMedia::find($id);
        if ($spatieMedia) {
            $fullPath = $spatieMedia->getPath();

            if (! file_exists($fullPath)) {
                abort(404);
            }

            LogSuperadmin::create([
                'user_id' => auth()->id(),
                'aksi' => 'download',
                'deskripsi' => 'Download media: ' . $spatieMedia->file_name,
                'modul' => 'file',
                'referensi_id' => $spatieMedia->id,
                'ip_address' => request()->ip(),
            ]);

            return $this->serve($fullPath, $spatieMedia->file_name);
        }

        $media = Media::find($id);

        if (! $media) {
            abort(404);
        }

        $diskDriver = Storage::disk('local');

        if (! $diskDriver->exists($media->file_path)) {
            abort(404);
        }

        $fullPath = $diskDriver->path($media->file_path);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'download',
            'deskripsi' => 'Download media: ' . $media->file_name,
            'modul' => 'file',
            'referensi_id' => $media->id,
            'ip_address' => request()->ip(),
        ]);

        return $this->serve($fullPath, $media->file_name);
    }

    public function serveStorage(string $path): Response
    {
        $pattern = '#^file_organisasi/(diterima|mencurigakan|ditolak)/(.+)$#';
        if (preg_match($pattern, $path, $m)) {
            $kategori = $m[1];
            $filePath = $m[2];
            $disk = match ($kategori) {
                'diterima' => 'file_diterima',
                'mencurigakan' => 'file_mencurigakan',
                'ditolak' => 'file_ditolak',
            };
            $diskDriver = Storage::disk($disk);

            if (! $diskDriver->exists($filePath)) {
                abort(404);
            }

            return $this->serve($diskDriver->path($filePath), basename($filePath));
        }

        if (str_starts_with($path, 'thumbnails/')) {
            $diskDriver = Storage::disk('local');

            if (! $diskDriver->exists($path)) {
                abort(404);
            }

            return $this->serve($diskDriver->path($path), basename($path));
        }

        // Serve Spatie media files: /storage/{mediaId}/{fileName}
        if (preg_match('#^(\d+)/(.+)$#', $path, $m)) {
            $media = SpatieMedia::find($m[1]);
            if ($media) {
                $fullPath = $media->getPath();
                if (file_exists($fullPath)) {
                    return $this->serve($fullPath, $media->file_name);
                }
            }
            abort(404);
        }

        abort(404);
    }

    public function serveSpatieMedia(int $id): Response
    {
        $media = SpatieMedia::find($id);

        if (! $media) {
            abort(404);
        }

        $fullPath = $media->getPath();

        if (! file_exists($fullPath)) {
            abort(404);
        }

        return $this->serve($fullPath, $media->file_name);
    }

    private function serve(string $fullPath, string $filename): Response
    {
        $secFetchDest = strtolower(request()->header('Sec-Fetch-Dest', ''));
        $accept = strtolower(request()->header('Accept', ''));

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
        ];

        if ($secFetchDest === 'image' || ($secFetchDest === '' && str_contains($accept, 'image/'))) {
            $headers['Content-Disposition'] = 'inline; filename="' . addcslashes($filename, '"') . '"';

            return response()->file($fullPath, $headers);
        }

        $headers['Content-Type'] = 'application/octet-stream';
        $headers['Content-Disposition'] = 'attachment; filename="' . addcslashes($filename, '"') . '"';
        $headers['Pragma'] = 'no-cache';

        return response()->download($fullPath, $filename, $headers);
    }
}
