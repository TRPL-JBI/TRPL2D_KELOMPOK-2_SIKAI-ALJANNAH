<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class Page extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'media_id',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function legacyMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function isBuilderContent(): bool
    {
        $content = $this->content;
        if (empty($content)) return false;
        $decoded = json_decode($content, true);
        return is_array($decoded) && isset($decoded[0]) && isset($decoded[0]['type']);
    }

    public function renderContent(): string
    {
        if (!$this->isBuilderContent()) {
            return $this->content ?? '';
        }

        $nodes = json_decode($this->content, true);
        return $this->nodesToHtml($nodes);
    }

    private function nodesToHtml(array $nodes, int $indent = 1): string
    {
        $html = '';
        foreach ($nodes as $node) {
            $html .= $this->nodeToHtml($node, $indent);
        }
        return $html;
    }

    private function nodeToHtml(array $node, int $indent): string
    {
        $pad = str_repeat('  ', $indent);
        $p = $node['props'] ?? [];
        $styleParts = [];
        if (isset($p['padding']) && $p['padding'] !== '') $styleParts[] = 'padding:' . $p['padding'] . 'px';
        if (!empty($p['bg']) && $p['bg'] !== 'transparent') $styleParts[] = 'background:' . $p['bg'];
        if (!empty($p['radius'])) $styleParts[] = 'border-radius:' . $p['radius'] . 'px';

        $type = $node['type'] ?? '';

        switch ($type) {
            case 'row':
                $cols = '';
                foreach ($node['children'] ?? [] as $col) {
                    $cols .= $pad . '  <div style="flex:1;">' . "\n" . $this->nodeToHtml($col, $indent + 2) . $pad . '  </div>' . "\n";
                }
                return $pad . '<div style="display:flex;gap:16px;' . implode(';', $styleParts) . '">' . "\n" . $cols . $pad . '</div>' . "\n";

            case 'container':
                $styleParts[] = 'min-height:' . ($p['minHeight'] ?? 40) . 'px';
                $kids = '';
                foreach ($node['children'] ?? [] as $child) {
                    $kids .= $this->nodeToHtml($child, $indent + 1);
                }
                return $pad . '<div style="' . implode(';', $styleParts) . '">' . "\n" . $kids . $pad . '</div>' . "\n";

            case 'heading':
                $styleParts[] = 'font-size:' . ($p['size'] ?? 32) . 'px';
                $styleParts[] = 'color:' . ($p['color'] ?? '#1a1a1a');
                $styleParts[] = 'font-weight:' . ($p['weight'] ?? 700);
                $styleParts[] = 'text-align:' . ($p['align'] ?? 'left');
                $styleParts[] = 'margin:0';
                return $pad . '<h2 style="' . implode(';', $styleParts) . '">' . e($p['text'] ?? '') . '</h2>' . "\n";

            case 'text':
                $styleParts[] = 'font-size:' . ($p['size'] ?? 15) . 'px';
                $styleParts[] = 'color:' . ($p['color'] ?? '#4a4a4a');
                $styleParts[] = 'font-weight:' . ($p['weight'] ?? 400);
                $styleParts[] = 'text-align:' . ($p['align'] ?? 'left');
                $styleParts[] = 'margin:0';
                $styleParts[] = 'line-height:1.6';
                return $pad . '<p style="' . implode(';', $styleParts) . '">' . e($p['text'] ?? '') . '</p>' . "\n";

            case 'button':
                return $pad . '<div style="padding:' . ($p['padding'] ?? 12) . 'px;text-align:' . ($p['align'] ?? 'left') . ';">' . "\n" .
                       $pad . '  <a href="' . e($p['href'] ?? '#') . '" style="display:inline-block;background:' . ($p['bg'] ?? '#16423C') . ';color:' . ($p['color'] ?? '#ffffff') . ';font-size:' . ($p['size'] ?? 14) . 'px;font-weight:' . ($p['weight'] ?? 600) . ';padding:10px 22px;border-radius:' . ($p['radius'] ?? 8) . 'px;text-decoration:none;">' . e($p['text'] ?? 'Tombol') . '</a>' . "\n" .
                       $pad . '</div>' . "\n";

            case 'image':
                return $pad . '<div style="padding:' . ($p['padding'] ?? 12) . 'px;text-align:' . ($p['align'] ?? 'left') . ';">' . "\n" .
                       $pad . '  <img src="' . e($p['src'] ?? '') . '" alt="' . e($p['alt'] ?? '') . '" style="max-width:100%;border-radius:' . ($p['radius'] ?? 0) . 'px;">' . "\n" .
                       $pad . '</div>' . "\n";

            case 'divider':
                return $pad . '<div style="padding:' . ($p['padding'] ?? 12) . 'px 0;"><div style="height:1px;background:' . ($p['color'] ?? '#d8d4c9') . ';"></div></div>' . "\n";

            case 'list':
                $items = '';
                foreach ($p['items'] ?? [] as $it) {
                    $items .= $pad . '  <li>' . e($it) . '</li>' . "\n";
                }
                return $pad . '<ul style="margin:0;padding:' . ($p['padding'] ?? 12) . 'px 12px ' . ($p['padding'] ?? 12) . 'px 28px;color:' . ($p['color'] ?? '#333333') . ';font-size:' . ($p['size'] ?? 14) . 'px;line-height:1.8;">' . "\n" . $items . $pad . '</ul>' . "\n";

            case 'spacer':
                return $pad . '<div style="height:' . ($p['height'] ?? 40) . 'px;"></div>' . "\n";

            default:
                return '';
        }
    }

    public function getFirstImageUrl(): ?string
    {
        $spatieMedia = $this->getFirstMedia('images');
        if ($spatieMedia) {
            return route('media.spatie', $spatieMedia->id);
        }
        if ($this->relationLoaded('legacyMedia') && $this->legacyMedia) {
            return route('media.download', $this->legacyMedia);
        }
        return null;
    }
}
