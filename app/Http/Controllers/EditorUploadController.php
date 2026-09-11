<?php

namespace App\Http\Controllers;

use App\Models\FileOrganisasi;
use App\Services\FileRenamer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\LogSuperadmin;

class EditorUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $mimeType = $uploadedFile->getMimeType();

        $kategori = $this->klasifikasikanEkstensi($extension);

        if ($kategori !== 'diterima') {
            $label = config("file-pemilah.labels.$kategori", $kategori);

            throw ValidationException::withMessages([
                'file' => __('Tipe file :ext tidak diizinkan (diklasifikasikan sebagai ":kategori").', [
                    'ext' => $extension,
                    'kategori' => $label,
                ]),
            ]);
        }

        $this->validasiMime($extension, $mimeType);

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
            ->usingFileName(FileRenamer::rename($originalName))
            ->toMediaCollection('uploads');

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'upload',
            'deskripsi' => 'Upload file via editor: ' . $originalName,
            'modul' => 'editor',
            'referensi_id' => $fileRecord->id,
            'ip_address' => request()->ip(),
        ]);

        $url = $fileRecord->getDownloadUrl();

        return response()->json([
            'success' => true,
            'file' => [
                'id' => $fileRecord->id,
                'nama_file' => $originalName,
                'url' => $url,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'kategori' => $kategori,
            ],
        ]);
    }

    private function validasiMime(string $extension, string $actualMime): void
    {
        $dangerousMimes = [
            'application/x-msdownload',
            'application/x-msi',
            'application/x-bat',
            'application/x-sh',
            'application/x-php',
            'application/x-httpd-php',
            'text/html',
            'application/javascript',
            'application/x-javascript',
            'application/java-archive',
            'application/vnd.microsoft.portable-executable',
        ];

        if (in_array($actualMime, $dangerousMimes, true)) {
            throw ValidationException::withMessages([
                'file' => __('Tipe MIME berbahaya tidak diizinkan: :mime.', ['mime' => $actualMime]),
            ]);
        }

        $mimeCategoryMap = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif', 'webp', 'psd', 'ai', 'indd', 'sketch', 'eps'],
            'audio' => ['mp3', 'wav', 'ogg', 'flac', 'aac'],
            'video' => ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'webm', 'flv'],
            'text' => ['txt', 'rtf', 'csv'],
        ];

        $mimeMajor = explode('/', $actualMime)[0] ?? '';

        foreach ($mimeCategoryMap as $category => $exts) {
            if (in_array($extension, $exts, true)) {
                if ($mimeMajor !== $category) {
                    throw ValidationException::withMessages([
                        'file' => __("Tipe MIME (:mime) tidak sesuai untuk ekstensi .:ext.", [
                            'mime' => $actualMime,
                            'ext' => $extension,
                        ]),
                    ]);
                }

                return;
            }
        }

        $documentMimeMap = [
            'pdf' => ['application/pdf', 'application/x-pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'odt' => ['application/vnd.oasis.opendocument.text'],
            'ods' => ['application/vnd.oasis.opendocument.spreadsheet'],
            'odp' => ['application/vnd.oasis.opendocument.presentation'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'rar' => ['application/vnd.rar', 'application/x-rar-compressed'],
            '7z' => ['application/x-7z-compressed'],
            'tar' => ['application/x-tar'],
            'gz' => ['application/gzip', 'application/x-gzip'],
        ];

        $expectedMimes = $documentMimeMap[$extension] ?? [];

        if (!empty($expectedMimes) && !in_array($actualMime, $expectedMimes, true)) {
            throw ValidationException::withMessages([
                'file' => __("Tipe MIME (:mime) tidak sesuai untuk ekstensi .:ext.", [
                    'mime' => $actualMime,
                    'ext' => $extension,
                ]),
            ]);
        }
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
}
