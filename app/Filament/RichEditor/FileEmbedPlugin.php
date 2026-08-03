<?php

namespace App\Filament\RichEditor;

use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Support\Icons\Heroicon;

class FileEmbedPlugin implements RichContentPlugin
{
    public function getTipTapPhpExtensions(): array
    {
        return [];
    }

    public function getTipTapJsExtensions(): array
    {
        return [
            asset('js/extension-file-embed.js'),
        ];
    }

    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('mediaLibrary')
                ->label('Media Library')
                ->icon(Heroicon::Photo)
                ->hiddenLabel()
                ->jsHandler('FilamentMediaLibrary.openForEditor({ getEditor: () => $getEditor() })'),
        ];
    }

    public function getEditorActions(): array
    {
        return [];
    }
}
