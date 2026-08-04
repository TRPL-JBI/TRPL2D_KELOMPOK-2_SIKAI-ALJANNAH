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

class PostEditor extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithFileUploads;

    public array $data = [];
    public ?Post $post = null;
    public $thumbnail = null;

    protected function rules()
    {
        return [
            'data.title' => 'required|string|max:255',
            'data.category_id' => 'nullable|exists:categories,id',
            'data.content' => 'required|string',
            'thumbnail' => 'nullable|image|max:10240',
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

    public function removeThumbnail()
    {
        $this->thumbnail = null;
        if ($this->post && $this->post->getThumbnailUrl()) {
            $this->post->clearMediaCollection('thumbnails');
        }
    }

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
            ->addMedia($file->getRealPath())
            ->withCustomProperties(['uploaded_by' => auth()->id()])
            ->usingFileName(FileRenamer::rename($originalName))
            ->toMediaCollection('uploads');

        return (int) $fileRecord->getFirstMedia('uploads')->id;
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

            if ($this->thumbnail) {
                try {
                    $this->post->addMedia($this->thumbnail->getRealPath())
                        ->withCustomProperties(['uploaded_by' => auth()->id()])
                        ->usingFileName(\App\Services\FileRenamer::rename($this->thumbnail->getClientOriginalName()))
                        ->toMediaCollection('thumbnails');
                } catch (\Exception $e) {
                    Log::warning('Thumbnail upload failed: ' . $e->getMessage());
                }
            }

            session()->flash('success', 'Postingan berhasil diperbarui.');
        } else {
            $post = Post::create($data);

            if ($this->thumbnail) {
                try {
                    $post->addMedia($this->thumbnail->getRealPath())
                        ->withCustomProperties(['uploaded_by' => auth()->id()])
                        ->usingFileName(\App\Services\FileRenamer::rename($this->thumbnail->getClientOriginalName()))
                        ->toMediaCollection('thumbnails');
                } catch (\Exception $e) {
                    Log::warning('Thumbnail upload failed: ' . $e->getMessage());
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
