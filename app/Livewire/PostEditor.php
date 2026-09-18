<?php

namespace App\Livewire;

use App\Filament\RichEditor\FileEmbedPlugin;
use App\Models\Category;
use App\Models\FileOrganisasi;
use App\Models\Post;
use App\Services\FileRenamer;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class PostEditor extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithFileUploads;

    public array $data = [];
    public ?Post $post = null;
    public $thumbnail = null;

    /** Media library selection for thumbnail */
    public ?string $thumbnailMediaSource = null;
    public ?int $thumbnailSpatieMediaId = null;
    public ?string $thumbnailMediaUrl = null;

    /** Flag: prevents double-persist when updatedThumbnail already handled the upload */
    public bool $thumbnailPersisted = false;

    /** Tracks the media ID from immediate upload, for attaching to newly created posts */
    public ?int $pendingThumbnailMediaId = null;

    protected function rules()
    {
        return [
            'data.title' => 'required|string|max:255',
            'data.category_id' => 'nullable|exists:categories,id',
            'data.content' => 'required|string',
            'thumbnail' => 'nullable|image|max:10240',
            'thumbnailSpatieMediaId' => 'nullable|integer',
        ];
    }

    public function mount($post = null): void
    {
        $this->post = $post;

        if ($post) {
            $this->post->load(['media', 'legacyMedia']);
        }

        $this->form->fill([
            'content' => $post?->content ?? null,
        ]);

        $this->data['title'] = $post?->title ?? '';
        $this->data['category_id'] = $post?->category_id ?? null;
    }

    /**
     * When a thumbnail file is uploaded via wire:model, persist it immediately
     * so it appears in the media library right away.
     */
    public function updatedThumbnail(): void
    {
        if (!$this->thumbnail || !$this->thumbnail instanceof TemporaryUploadedFile) {
            return;
        }

        // Immediately persist to FileOrganisasi + Post's thumbnails collection
        $targetPost = $this->post ?? null;

        try {
            $mediaId = $this->persistThumbnailUploadToLibrary($this->thumbnail, $targetPost);
            $this->thumbnailPersisted = true;

            // Swap from temp file to permanent media URL so blade doesn't call temporaryUrl()
            $persistedMedia = $mediaId ? SpatieMedia::find($mediaId) : null;
            if ($persistedMedia) {
                $this->thumbnailMediaUrl = $persistedMedia->getUrl();
                $this->thumbnail = null; // Clear temp file — no longer needed
            }

            if ($mediaId && !$targetPost) {
                // Track for attaching to newly created posts in save()
                $this->pendingThumbnailMediaId = $mediaId;
            }
        } catch (\Exception $e) {
            Log::error('Immediate thumbnail persist failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function selectThumbnailFromLibrary(?int $foId, ?int $spatieMediaId, ?string $url): void
    {
        $this->thumbnail = null;
        $this->thumbnailMediaSource = 'library';
        $this->thumbnailSpatieMediaId = $spatieMediaId;
        $this->thumbnailMediaUrl = $url;
    }

    public function clearThumbnailSelection(): void
    {
        $this->thumbnail = null;
        $this->thumbnailMediaSource = null;
        $this->thumbnailSpatieMediaId = null;
        $this->thumbnailMediaUrl = null;

        if ($this->post && $this->post->getThumbnailUrl()) {
            $this->post->clearMediaCollection('thumbnails');
        }
    }

    public function removeThumbnail()
    {
        $this->thumbnail = null;
        $this->thumbnailMediaSource = null;
        $this->thumbnailSpatieMediaId = null;
        $this->thumbnailMediaUrl = null;

        if ($this->post && $this->post->getThumbnailUrl()) {
            $this->post->clearMediaCollection('thumbnails');
        }
    }

    /**
     * Persist an uploaded file into FileOrganisasi (Files & Images).
     * Used by the rich editor's saveUploadedFileAttachmentUsing callback.
     */
    private function persistEditorAttachment(TemporaryUploadedFile $file): int
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $kategori = in_array($extension, config('file-pemilah.diterima', []), true) ? 'diterima' : 'mencurigakan';

        $fileRecord = FileOrganisasi::create([
            'nama_file' => $originalName,
            'file_path' => '',
            'kategori' => $kategori,
            'status' => 'aktif',
            'uploaded_by' => auth()->id(),
        ]);

        $fileRecord
            ->addMedia($file->getPathname())
            ->withCustomProperties(['uploaded_by' => auth()->id()])
            ->usingFileName(FileRenamer::rename($originalName))
            ->toMediaCollection('uploads');

        return (int) $fileRecord->getFirstMedia('uploads')->id;
    }

    /**
     * Persist a manual thumbnail upload into FileOrganisasi (Files & Images),
     * and attach it to the Post's thumbnails collection if a Post exists.
     * Called immediately on upload (updatedThumbnail hook) AND during save.
     */
    private function persistThumbnailUploadToLibrary(TemporaryUploadedFile $file, ?Post $post): ?int
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $kategori = in_array($extension, config('file-pemilah.diterima', []), true) ? 'diterima' : 'mencurigakan';

        // 1) Create FileOrganisasi record so it appears in Files & Images / media library
        $fileRecord = FileOrganisasi::create([
            'nama_file' => $originalName,
            'file_path' => '',
            'kategori' => $kategori,
            'status' => 'aktif',
            'uploaded_by' => auth()->id(),
        ]);

        $fileRecord
            ->addMedia($file->getPathname())
            ->withCustomProperties(['uploaded_by' => auth()->id()])
            ->usingFileName(FileRenamer::rename($originalName))
            ->toMediaCollection('uploads');

        $spatieMedia = $fileRecord->getFirstMedia('uploads');
        if (!$spatieMedia) {
            return null;
        }

        // 2) Attach to the Post's thumbnails collection if Post is available
        if ($post) {
            $post->addMedia($spatieMedia->getPath())
                ->withCustomProperties(['uploaded_by' => auth()->id()])
                ->usingFileName($spatieMedia->file_name)
                ->toMediaCollection('thumbnails');
        }

        return $spatieMedia->id;
    }

    /**
     * Attach an existing media library item (Spatie Media) to the Post's thumbnails collection.
     */
    private function attachLibraryThumbnail(Post $post, ?int $spatieMediaId): void
    {
        if (!$spatieMediaId) {
            return;
        }

        $spatieMedia = SpatieMedia::find($spatieMediaId);
        if ($spatieMedia && file_exists($spatieMedia->getPath())) {
            $post->addMedia($spatieMedia->getPath())
                ->withCustomProperties(['uploaded_by' => auth()->id()])
                ->usingFileName($spatieMedia->file_name)
                ->toMediaCollection('thumbnails');
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                RichEditor::make('content')
                    ->hiddenLabel()
                    ->required()
                    ->live()
                    ->fileAttachments(true)
                    ->fileAttachmentsAcceptedFileTypes([
                        'image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff',
                        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain', 'text/csv',
                    ])
                    ->saveUploadedFileAttachmentUsing(
                        fn (TemporaryUploadedFile $file): string => (string) $this->persistEditorAttachment($file),
                    )
                    ->getFileAttachmentUrlUsing(
                        fn (string $file): ?string => filled($file) ? route('media.spatie', $file) : null,
                    )
                    ->plugins([new FileEmbedPlugin])
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link', 'h2', 'h3', 'h4', 'alignStart', 'alignCenter', 'alignEnd', 'blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                        ['attachFiles', 'table', 'tableAddColumnBefore', 'tableAddColumnAfter', 'tableDeleteColumn', 'tableAddRowBefore', 'tableAddRowAfter', 'tableDeleteRow', 'tableMergeCells', 'tableSplitCell', 'tableToggleHeaderRow', 'tableToggleHeaderCell', 'tableDelete'],
                        ['mediaLibrary'],
                        ['undo', 'redo'],
                    ])
                    ->floatingToolbars([]),
            ])
            ->statePath('data');
    }

    public function save()
    {
        $this->validate();

        $state = $this->form->getState();
        $rawContent = $state['content'] ?? '';
        if (is_array($rawContent)) {
            $renderer = app(\Filament\Forms\Components\RichEditor\RichContentRenderer::class, ['content' => $rawContent]);
            $content = $renderer->getEditor()->getHTML();
        } else {
            $content = (string) $rawContent;
        }

        if ($content) {
            $content = preg_replace(
                '/<div([^>]*data-type="file-embed"[^>]*)>\s*<a([^>]*)>.*?<\/a>\s*<\/div>/is',
                '<div$1><a$2></a></div>',
                $content,
            );

            $content = preg_replace_callback(
                '/<a\s+([^>]*href\s*=\s*")([^"]*)("[^>]*>)/i',
                function ($m) {
                    $href = $m[2];
                    if ($href !== '' && !preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/', $href) && $href[0] !== '/' && $href[0] !== '#') {
                        $href = 'https://' . $href;
                    }
                    return '<a ' . $m[1] . $href . $m[3];
                },
                $content,
            );

            $content = (new \Symfony\Component\HtmlSanitizer\HtmlSanitizer(
                (new \Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig)
                    ->allowSafeElements()
                    ->allowRelativeLinks()
                    ->allowRelativeMedias()
                    ->allowAttribute('class', allowedElements: '*')
                    ->allowAttribute('data-color', allowedElements: '*')
                    ->allowAttribute('data-cols', allowedElements: '*')
                    ->allowAttribute('data-col-span', allowedElements: '*')
                    ->allowAttribute('data-from-breakpoint', allowedElements: '*')
                    ->allowAttribute('data-id', allowedElements: '*')
                    ->allowAttribute('data-type', allowedElements: '*')
                    ->allowAttribute('data-file-id', allowedElements: '*')
                    ->allowAttribute('data-file-name', allowedElements: '*')
                    ->allowAttribute('data-file-url', allowedElements: '*')
                    ->allowAttribute('data-mime-type', allowedElements: '*')
                    ->allowAttribute('style', allowedElements: '*')
                    ->allowAttribute('width', allowedElements: 'img')
                    ->allowAttribute('height', allowedElements: 'img')
                    ->withMaxInputLength(500000),
            ))->sanitize($content);
        }

        $baseSlug = Str::slug($this->data['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (Post::where('slug', $slug)->when($this->post, fn ($q) => $q->where('id', '!=', $this->post->id))->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $data = [
            'title' => $this->data['title'],
            'slug' => $slug,
            'content' => $content,
            'category_id' => $this->data['category_id'] ?? null,
            'status' => 'draft',
        ];

        if ($this->post) {
            $this->post->update($data);

            // Handle thumbnail: media library pick
            if ($this->thumbnailMediaSource === 'library' && $this->thumbnailSpatieMediaId) {
                try {
                    $this->attachLibraryThumbnail($this->post, $this->thumbnailSpatieMediaId);
                } catch (\Exception $e) {
                    Log::error('Thumbnail library attach failed: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } elseif ($this->thumbnail && $this->thumbnail instanceof TemporaryUploadedFile && !$this->thumbnailPersisted) {
                // Fallback: if updatedThumbnail didn't fire (e.g. wire:model.defer), persist now
                try {
                    $this->persistThumbnailUploadToLibrary($this->thumbnail, $this->post);
                } catch (\Exception $e) {
                    Log::error('Thumbnail upload failed: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
            // Note: if thumbnailPersisted is true, updatedThumbnail() already attached to this post

            session()->flash('success', 'Postingan berhasil diperbarui.');
        } else {
            $post = Post::create($data);

            // Handle thumbnail: media library pick
            if ($this->thumbnailMediaSource === 'library' && $this->thumbnailSpatieMediaId) {
                try {
                    $this->attachLibraryThumbnail($post, $this->thumbnailSpatieMediaId);
                } catch (\Exception $e) {
                    Log::error('Thumbnail library attach failed: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } elseif ($this->thumbnailPersisted && $this->pendingThumbnailMediaId) {
                // updatedThumbnail() already persisted the file but Post didn't exist yet — attach now
                try {
                    $spatieMedia = SpatieMedia::find($this->pendingThumbnailMediaId);
                    if ($spatieMedia && file_exists($spatieMedia->getPath())) {
                        $post->addMedia($spatieMedia->getPath())
                            ->withCustomProperties(['uploaded_by' => auth()->id()])
                            ->usingFileName($spatieMedia->file_name)
                            ->toMediaCollection('thumbnails');
                    }
                } catch (\Exception $e) {
                    Log::error('Pending thumbnail attach to new post failed: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } elseif ($this->thumbnail && $this->thumbnail instanceof TemporaryUploadedFile && !$this->thumbnailPersisted) {
                // Fallback: if updatedThumbnail didn't fire, persist now
                try {
                    $this->persistThumbnailUploadToLibrary($this->thumbnail, $post);
                } catch (\Exception $e) {
                    Log::error('Thumbnail upload failed: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            session()->flash('success', 'Postingan berhasil dibuat.');
        }

        $this->redirect(route('adminweb.posts'), navigate: false);
    }

    public function render()
    {
        return view('livewire.post-editor');
    }
}
