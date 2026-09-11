<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use App\Models\LogSuperadmin;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('dashboard.adminweb.menus.index', compact('menus'));
    }

    public function create()
    {
        $parents = Menu::whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('dashboard.adminweb.menus.form', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'custom_url' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:menus,id',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? Menu::max('sort_order') + 1;

        Menu::create($data);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'deskripsi' => 'Menambahkan menu: ' . $data['label'],
            'modul' => 'menu',
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('adminweb.menus')
            ->with('success', 'Menu "' . e($data['label']) . '" berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        $parents = Menu::whereNull('parent_id')
            ->where('id', '!=', $id)
            ->orderBy('sort_order')
            ->get();

        return view('dashboard.adminweb.menus.form', compact('menu', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $data = $request->validate([
            'label' => 'required|string|max:255',
            'custom_url' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:menus,id|not_in:' . $id,
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? $menu->sort_order;

        // Prevent setting self as parent
        if ($data['parent_id'] == $id) {
            $data['parent_id'] = null;
        }

        // Prevent setting a child as parent (avoid deep nesting)
        if ($data['parent_id']) {
            $childIds = $this->getAllChildIds($id);
            if (in_array($data['parent_id'], $childIds)) {
                return back()->withErrors(['parent_id' => 'Tidak bisa memilih menu turunan sebagai induk.'])->withInput();
            }
        }

        $menu->update($data);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'update',
            'deskripsi' => 'Memperbarui menu: ' . $menu->label,
            'modul' => 'menu',
            'referensi_id' => $menu->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('adminweb.menus')
            ->with('success', 'Menu "' . e($menu->label) . '" berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $label = $menu->label;

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'delete',
            'deskripsi' => 'Menghapus menu: ' . $label,
            'modul' => 'menu',
            'referensi_id' => $menu->id,
            'ip_address' => request()->ip(),
        ]);

        $menu->delete();

        return redirect()->route('adminweb.menus')
            ->with('success', 'Menu "' . e($label) . '" berhasil dihapus.');
    }

    public function toggle($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->update([
            'is_active' => !$menu->is_active,
        ]);

        LogSuperadmin::create([
            'user_id' => auth()->id(),
            'aksi' => 'update',
            'deskripsi' => ($menu->is_active ? 'Mengaktifkan' : 'Menonaktifkan') . ' menu: ' . $menu->label,
            'modul' => 'menu',
            'referensi_id' => $menu->id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('adminweb.menus')
            ->with('success', 'Menu "' . e($menu->label) . '" ' . ($menu->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.');
    }

    private function getAllChildIds($id): array
    {
        $ids = [];
        $children = Menu::where('parent_id', $id)->get();
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllChildIds($child->id));
        }
        return $ids;
    }
}
