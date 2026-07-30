<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} — Page Builder | RKM Al-Jannah</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
<style>
:root {
  --bg-app: var(--secondary-100);
  --bg-panel: var(--white);
  --bg-panel-2: var(--secondary-200);
  --bg-canvas: var(--secondary-300);
  --border: var(--secondary-400);
  --border-light: var(--secondary-500);
  --accent: var(--primary-500);
  --accent-dim: var(--primary-300);
  --text: var(--secondary-900);
  --text-dim: var(--secondary-700);
  --text-dimmer: var(--secondary-800);
  --mono: 'JetBrains Mono', 'Courier New', monospace;
  --sans: 'Segoe UI', 'Poppins', -apple-system, sans-serif;
}
*{box-sizing:border-box;}
html,body{margin:0;padding:0;height:100%;font-family:var(--sans);background:var(--bg-app);color:var(--text);overflow:hidden;}
button{font-family:inherit;cursor:pointer;}
input,select,textarea{font-family:inherit;}

/* ===== APP SHELL ===== */
#app{display:grid;grid-template-rows:52px 1fr;height:100vh;}
#topbar{
  display:flex;align-items:center;
  background:var(--bg-panel);
  border-bottom:1px solid var(--border);
  padding:0 14px;gap:10px;z-index:20;
}
.brand{display:flex;align-items:center;gap:8px;font-weight:700;font-size:14px;letter-spacing:0.3px;color:var(--text);margin-right:8px;}
.brand .dot{width:9px;height:9px;border-radius:2px;background:var(--accent);transform:rotate(45deg);}
.tb-group{display:flex;align-items:center;gap:4px;padding:0 8px;border-right:1px solid var(--border);height:32px;}
.tb-group:last-of-type{border-right:none;}
.tb-btn{
  display:flex;align-items:center;justify-content:center;
  width:30px;height:30px;border-radius:6px;
  background:transparent;border:1px solid transparent;color:var(--text-dim);
}
.tb-btn:hover{background:var(--bg-panel-2);color:var(--text);border-color:var(--border-light);}
.tb-btn:disabled{opacity:0.3;cursor:not-allowed;}
.tb-btn:disabled:hover{background:transparent;border-color:transparent;}
.tb-btn svg{width:16px;height:16px;}
.viewport-toggle{display:flex;background:var(--bg-panel-2);border-radius:8px;padding:3px;gap:2px;}
.viewport-toggle button{
  width:32px;height:26px;border:none;background:transparent;border-radius:5px;
  color:var(--text-dim);display:flex;align-items:center;justify-content:center;
}
.viewport-toggle button.active{background:var(--accent);color:#fff;}
.viewport-toggle svg{width:15px;height:15px;}
.spacer{flex:1;}
.back-btn{
  background:transparent;color:var(--text-dim);border:1px solid var(--border-light);
  padding:0 12px;height:32px;border-radius:7px;font-size:12.5px;font-weight:600;
  display:flex;align-items:center;gap:6px;text-decoration:none;
}
.back-btn:hover{color:var(--text);border-color:var(--text-dimmer);}
.primary-btn{
  background:var(--accent);color:#fff;border:none;
  padding:0 14px;height:32px;border-radius:7px;font-size:12.5px;font-weight:700;
  display:flex;align-items:center;gap:6px;
}
.primary-btn:hover{opacity:0.9;}
.ghost-btn{
  background:transparent;color:var(--text-dim);border:1px solid var(--border-light);
  padding:0 12px;height:32px;border-radius:7px;font-size:12.5px;font-weight:600;
  display:flex;align-items:center;gap:6px;
}
.ghost-btn:hover{color:var(--text);border-color:var(--text-dimmer);}

/* ===== MAIN GRID ===== */
#main{display:grid;grid-template-columns:240px 1fr 280px;min-height:0;}
#left-panel,#right-panel{background:var(--bg-panel);overflow-y:auto;min-height:0;}
#left-panel{border-right:1px solid var(--border);}
#right-panel{border-left:1px solid var(--border);}
.panel-tabs{display:flex;border-bottom:1px solid var(--border);}
.panel-tab{
  flex:1;padding:11px 0;text-align:center;font-size:11.5px;font-weight:700;
  letter-spacing:0.4px;text-transform:uppercase;color:var(--text-dimmer);
  background:transparent;border:none;border-bottom:2px solid transparent;
}
.panel-tab.active{color:var(--accent);border-bottom-color:var(--accent);}
.panel-section{padding:14px;}
.panel-section-title{
  font-size:10.5px;font-weight:700;letter-spacing:0.6px;text-transform:uppercase;
  color:var(--text-dimmer);margin:0 0 10px 2px;
}

/* Widget palette */
.widget-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px;}
.widget-item{
  display:flex;flex-direction:column;align-items:center;gap:6px;
  padding:12px 6px;border:1px solid var(--border-light);border-radius:8px;
  background:var(--bg-panel-2);color:var(--text-dim);font-size:11px;font-weight:600;
  cursor:grab;user-select:none;transition:border-color .12s, color .12s;
}
.widget-item:hover{border-color:var(--accent);color:var(--text);}
.widget-item svg{width:18px;height:18px;color:var(--accent);}
.widget-item.dragging{opacity:0.4;}

/* Layers tree */
.layer-row{
  display:flex;align-items:center;gap:6px;padding:6px 8px;border-radius:6px;
  font-size:12px;color:var(--text-dim);cursor:pointer;user-select:none;
}
.layer-row:hover{background:var(--bg-panel-2);}
.layer-row.selected{background:rgba(22,66,60,0.12);color:var(--accent);}
.layer-row svg{width:13px;height:13px;flex-shrink:0;}
.layer-row .lbl{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;}
.layer-row .del-mini{opacity:0;width:16px;height:16px;color:var(--text-dimmer);flex-shrink:0;}
.layer-row:hover .del-mini{opacity:1;}
.layer-row .del-mini:hover{color:var(--danger-500);}
.layer-children{margin-left:16px;border-left:1px dashed var(--border-light);}
.empty-hint{color:var(--text-dimmer);font-size:12px;padding:10px 4px;line-height:1.5;}

/* ===== CANVAS AREA ===== */
#canvas-area{
  background:var(--bg-app);
  background-image:radial-gradient(var(--border-light) 1px, transparent 1px);
  background-size:18px 18px;
  overflow:auto;display:flex;justify-content:center;padding:36px 20px;
}
#canvas-wrap{
  background:var(--bg-canvas);
  min-height:600px;
  box-shadow:0 0 0 1px rgba(0,0,0,0.12), 0 20px 60px rgba(0,0,0,0.08);
  transition:width .2s ease;width:1180px;flex-shrink:0;
}
#canvas{min-height:600px;position:relative;}
#canvas.empty-canvas::before{
  content:'Seret komponen dari panel kiri ke sini untuk mulai membangun halaman';
  position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
  text-align:center;padding:40px;color:var(--text-dimmer);font-size:14px;font-weight:500;
  pointer-events:none;
}

/* Elements rendered inside canvas */
.el-block{position:relative;outline:1px dashed transparent;transition:outline-color .1s;}
.el-block:hover{outline-color:rgba(22,66,60,0.5);}
.el-block.selected{outline:2px solid var(--accent);}
.el-block.drag-over-top::before,.el-block.drag-over-bottom::after{
  content:'';position:absolute;left:0;right:0;height:3px;background:var(--accent);z-index:5;
}
.el-block.drag-over-top::before{top:-2px;}
.el-block.drag-over-bottom::after{bottom:-2px;}
.el-tag{
  position:absolute;top:-20px;left:-1px;background:var(--accent);color:#fff;
  font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px 4px 0 0;
  display:none;white-space:nowrap;z-index:6;letter-spacing:0.3px;
}
.el-block.selected .el-tag,.el-block:hover .el-tag{display:block;}
[contenteditable]:focus{outline:2px solid var(--accent);outline-offset:2px;}

/* ===== RIGHT PANEL: PROPERTIES ===== */
.prop-group{margin-bottom:16px;}
.prop-label{font-size:11px;color:var(--text-dim);font-weight:600;margin-bottom:6px;display:block;}
.prop-row{display:flex;gap:8px;margin-bottom:10px;}
.prop-row > div{flex:1;}
.prop-input{
  width:100%;background:var(--bg-panel-2);border:1px solid var(--border-light);
  color:var(--text);padding:7px 8px;border-radius:6px;font-size:12.5px;
}
.prop-input:focus{outline:none;border-color:var(--accent);}
textarea.prop-input{resize:vertical;min-height:60px;font-family:var(--mono);font-size:11.5px;}
select.prop-input{appearance:none;}
.prop-color-row{display:flex;align-items:center;gap:8px;}
.prop-color-swatch{width:32px;height:32px;border-radius:6px;border:1px solid var(--border-light);flex-shrink:0;padding:0;}
.align-btns{display:flex;gap:4px;}
.align-btns button{
  flex:1;height:30px;background:var(--bg-panel-2);border:1px solid var(--border-light);
  color:var(--text-dim);border-radius:6px;display:flex;align-items:center;justify-content:center;
}
.align-btns button.active{background:var(--accent);color:#fff;border-color:var(--accent);}
.align-btns svg{width:14px;height:14px;}
.no-selection{
  padding:40px 20px;text-align:center;color:var(--text-dimmer);font-size:12.5px;line-height:1.6;
}
.no-selection svg{width:32px;height:32px;margin-bottom:10px;color:var(--border-light);}
.prop-actions{display:flex;gap:8px;margin-top:4px;}
.prop-actions button{
  flex:1;padding:8px;border-radius:6px;font-size:11.5px;font-weight:600;
  background:var(--bg-panel-2);border:1px solid var(--border-light);color:var(--text-dim);
  display:flex;align-items:center;justify-content:center;gap:6px;
}
.prop-actions button:hover{color:var(--text);border-color:var(--text-dimmer);}
.prop-actions button.danger:hover{color:var(--danger-500);border-color:var(--danger-500);}
.prop-actions svg{width:13px;height:13px;}
.range-row{display:flex;align-items:center;gap:8px;}
.range-row input[type=range]{flex:1;}
.range-val{font-size:11px;color:var(--text-dimmer);width:36px;text-align:right;font-family:var(--mono);}

/* scrollbars */
#left-panel::-webkit-scrollbar,#right-panel::-webkit-scrollbar,#canvas-area::-webkit-scrollbar{width:9px;height:9px;}
#left-panel::-webkit-scrollbar-thumb,#right-panel::-webkit-scrollbar-thumb,#canvas-area::-webkit-scrollbar-thumb{
  background:var(--border-light);border-radius:5px;
}
#left-panel::-webkit-scrollbar-track,#right-panel::-webkit-scrollbar-track,#canvas-area::-webkit-scrollbar-track{background:transparent;}

/* toast */
#toast{
  position:fixed;bottom:20px;left:50%;transform:translateX(-50%) translateY(20px);
  background:var(--bg-panel);border:1px solid var(--border-light);color:var(--text);
  padding:10px 18px;border-radius:8px;font-size:12.5px;font-weight:600;
  box-shadow:0 10px 30px rgba(0,0,0,0.12);opacity:0;pointer-events:none;
  transition:all .25s ease;z-index:999;display:flex;align-items:center;gap:8px;
}
#toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
#toast svg{width:15px;height:15px;color:var(--accent);}

/* modal */
.modal-backdrop{
  position:fixed;inset:0;background:rgba(0,0,0,0.35);display:none;
  align-items:center;justify-content:center;z-index:1000;
}
.modal-backdrop.show{display:flex;}
.modal{
  background:var(--bg-panel);border:1px solid var(--border-light);border-radius:12px;
  width:640px;max-width:90vw;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;
}
.modal-head{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.modal-head h3{margin:0;font-size:14px;}
.modal-body{padding:16px 18px;overflow:auto;}
.modal-body textarea{
  width:100%;height:340px;background:#f8faf9;color:var(--text);border:1px solid var(--border-light);
  border-radius:8px;padding:12px;font-family:var(--mono);font-size:11.5px;line-height:1.6;
}
.modal-foot{padding:12px 18px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;}
.close-x{background:none;border:none;color:var(--text-dim);width:26px;height:26px;border-radius:6px;}
.close-x:hover{background:var(--bg-panel-2);color:var(--text);}

/* saving overlay */
#savingOverlay{
  position:fixed;inset:0;background:rgba(0,0,0,0.25);display:none;
  align-items:center;justify-content:center;z-index:2000;
}
#savingOverlay.show{display:flex;}
#savingOverlay .spinner{
  width:36px;height:36px;border:3px solid var(--border-light);
  border-top-color:var(--accent);border-radius:50%;animation:spin .7s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<div id="app">

  <!-- ============ TOPBAR ============ -->
  <div id="topbar">
    <div class="brand"><span class="dot"></span>Page Builder</div>

    <div class="tb-group">
      <button class="tb-btn" id="btnUndo" title="Undo (Ctrl+Z)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>
      </button>
      <button class="tb-btn" id="btnRedo" title="Redo (Ctrl+Y)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 14 5-5-5-5"/><path d="M20 9H9.5a5.5 5.5 0 0 0 0 11H13"/></svg>
      </button>
    </div>

    <div class="tb-group">
      <div class="viewport-toggle">
        <button data-vp="1180" class="active" title="Desktop">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="13" rx="1"/><path d="M8 21h8M12 17v4"/></svg>
        </button>
        <button data-vp="760" title="Tablet">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M11 18h2"/></svg>
        </button>
        <button data-vp="380" title="Mobile">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg>
        </button>
      </div>
    </div>

    <div style="font-size:12px;color:var(--text-dim);font-weight:600;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $page->title }}</div>

    <div class="spacer"></div>

    <a href="{{ route('adminweb.pages') }}" class="back-btn">
      <span class="material-icons" style="font-size:16px;">arrow_back</span>
      Kembali
    </a>

    <button class="ghost-btn" id="btnClear">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
      Bersihkan
    </button>
    <button class="primary-btn" id="btnSave">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
      Simpan
    </button>
    <button class="ghost-btn" id="btnExport">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
      Export HTML
    </button>
  </div>

  <!-- ============ MAIN ============ -->
  <div id="main">

    <!-- LEFT PANEL -->
    <div id="left-panel">
      <div class="panel-tabs">
        <button class="panel-tab active" data-tab="widgets">Komponen</button>
        <button class="panel-tab" data-tab="layers">Struktur</button>
      </div>

      <div class="panel-section" id="tab-widgets">
        <div class="panel-section-title">Layout</div>
        <div class="widget-grid">
          <div class="widget-item" draggable="true" data-type="container">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/></svg>
            Kontainer
          </div>
          <div class="widget-item" draggable="true" data-type="spacer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M8 7l4-4 4 4M8 17l4 4 4-4"/></svg>
            Spacer
          </div>
        </div>
        <div class="panel-section-title">Elemen</div>
        <div class="widget-grid">
          <div class="widget-item" draggable="true" data-type="heading">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4v16M18 4v16M6 12h12"/></svg>
            Judul
          </div>
          <div class="widget-item" draggable="true" data-type="text">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
            Paragraf
          </div>
          <div class="widget-item" draggable="true" data-type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="8" rx="4"/></svg>
            Tombol
          </div>
          <div class="widget-item" draggable="true" data-type="image">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
            Gambar
          </div>
          <div class="widget-item" draggable="true" data-type="divider">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/></svg>
            Divider
          </div>
          <div class="widget-item" draggable="true" data-type="list">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/><path d="M9 6h11M9 12h11M9 18h11"/></svg>
            Daftar
          </div>
        </div>
        <div class="panel-section-title">Petunjuk</div>
        <div class="empty-hint">Seret komponen ke kanvas. Klik elemen untuk mengedit properti di panel kanan. Klik dua kali pada teks untuk mengetik langsung.</div>
      </div>

      <div class="panel-section" id="tab-layers" style="display:none;">
        <div class="panel-section-title">Struktur Halaman</div>
        <div id="layers-tree"></div>
      </div>
    </div>

    <!-- CANVAS -->
    <div id="canvas-area">
      <div id="canvas-wrap">
        <div id="canvas" class="empty-canvas"></div>
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div id="right-panel">
      <div class="panel-tabs"><button class="panel-tab active">Properti</button></div>
      <div id="prop-content">
        <div class="no-selection">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18M3 15v4a2 2 0 0 0 2 2h4"/></svg>
          Pilih sebuah elemen di kanvas untuk mengedit propertinya di sini.
        </div>
      </div>
    </div>
  </div>
</div>

<div id="toast"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 13 4 4L19 7"/></svg><span id="toast-msg"></span></div>
<div id="savingOverlay"><div class="spinner"></div></div>

<div class="modal-backdrop" id="exportModal">
  <div class="modal">
    <div class="modal-head"><h3>Export HTML</h3><button class="close-x" id="closeExport">✕</button></div>
    <div class="modal-body"><textarea id="exportCode" readonly></textarea></div>
    <div class="modal-foot">
      <button class="ghost-btn" id="copyExport">Salin Kode</button>
      <button class="primary-btn" id="downloadExport">Unduh .html</button>
    </div>
  </div>
</div>

<script>
(function(){
  'use strict';

  const saveUrl = '{{ route('adminweb.pages.builder.save', $page->id) }}';

  /* ===================== STATE ===================== */
  let pageState = {!! json_encode($pageState) !!};
  if (!Array.isArray(pageState)) pageState = [];
  let selectedId = null;
  let idCounter = 1000;
  let undoStack = [];
  let redoStack = [];
  let draggingWidgetType = null;
  let draggingNodeId = null;
  let isSaving = false;

  const canvas = document.getElementById('canvas');
  const layersTree = document.getElementById('layers-tree');
  const propContent = document.getElementById('prop-content');

  function uid(){ return 'el_' + (idCounter++); }

  function defaultProps(type){
    const base = { padding:'12', margin:'0', bg:'transparent', radius:'0', align:'left' };
    switch(type){
      case 'heading': return {...base, text:'Judul Halaman Anda', size:'32', color:'#16423C', weight:'700'};
      case 'text': return {...base, text:'Ini adalah teks paragraf. Klik dua kali untuk mengedit isi teks ini sesuai kebutuhan Anda.', size:'15', color:'#4a4a4a', weight:'400'};
      case 'button': return {...base, text:'Klik Di Sini', href:'#', size:'14', color:'#ffffff', bg:'#16423C', radius:'8', weight:'600'};
      case 'image': return {...base, src:'https://placehold.co/600x320/e7e7e7/828282?text=Gambar', alt:'gambar', radius:'6'};
      case 'divider': return {...base, color:'#d8d4c9'};
      case 'spacer': return { height:'40' };
      case 'list': return {...base, items:['Item pertama','Item kedua','Item ketiga'], size:'14', color:'#333333'};
      case 'container': return {...base, bg:'#ffffff', minHeight:'80'};
      default: return base;
    }
  }

  function makeNode(type){
    const node = { id: uid(), type, props: defaultProps(type), children: [] };
    return node;
  }

  /* ===================== HISTORY ===================== */
  function snapshot(){ return JSON.stringify(pageState); }
  function pushHistory(){
    undoStack.push(snapshot());
    if(undoStack.length>60) undoStack.shift();
    redoStack = [];
    updateHistoryButtons();
  }
  function updateHistoryButtons(){
    document.getElementById('btnUndo').disabled = undoStack.length===0;
    document.getElementById('btnRedo').disabled = redoStack.length===0;
  }
  document.getElementById('btnUndo').addEventListener('click', ()=>{
    if(!undoStack.length) return;
    redoStack.push(snapshot());
    pageState = JSON.parse(undoStack.pop());
    selectedId = null;
    render();
    updateHistoryButtons();
  });
  document.getElementById('btnRedo').addEventListener('click', ()=>{
    if(!redoStack.length) return;
    undoStack.push(snapshot());
    pageState = JSON.parse(redoStack.pop());
    selectedId = null;
    render();
    updateHistoryButtons();
  });
  document.addEventListener('keydown', e=>{
    if((e.ctrlKey||e.metaKey) && e.key.toLowerCase()==='z' && !e.shiftKey){ e.preventDefault(); document.getElementById('btnUndo').click(); }
    if((e.ctrlKey||e.metaKey) && (e.key.toLowerCase()==='y' || (e.key.toLowerCase()==='z'&&e.shiftKey))){ e.preventDefault(); document.getElementById('btnRedo').click(); }
    if(e.key==='Delete' && selectedId && document.activeElement.tagName!=='TEXTAREA' && document.activeElement.tagName!=='INPUT' && !document.activeElement.isContentEditable){
      deleteNode(selectedId);
    }
  });

  /* ===================== TREE HELPERS ===================== */
  function findNode(list, id){
    for(const n of list){
      if(n.id===id) return n;
      if(n.children && n.children.length){
        const found = findNode(n.children, id);
        if(found) return found;
      }
    }
    return null;
  }
  function findParentArray(list, id){
    for(let i=0;i<list.length;i++){
      if(list[i].id===id) return {arr:list, index:i};
      if(list[i].children && list[i].children.length){
        const found = findParentArray(list[i].children, id);
        if(found) return found;
      }
    }
    return null;
  }
  function deleteNode(id){
    const loc = findParentArray(pageState, id);
    if(!loc) return;
    pushHistory();
    loc.arr.splice(loc.index,1);
    if(selectedId===id) selectedId=null;
    render();
  }
  function duplicateNode(id){
    const loc = findParentArray(pageState, id);
    if(!loc) return;
    pushHistory();
    const clone = deepCloneWithNewIds(loc.arr[loc.index]);
    loc.arr.splice(loc.index+1,0,clone);
    selectedId = clone.id;
    render();
  }
  function deepCloneWithNewIds(node){
    const clone = JSON.parse(JSON.stringify(node));
    (function reassign(n){ n.id = uid(); if(n.children) n.children.forEach(reassign); })(clone);
    return clone;
  }

  /* ===================== RENDER: CANVAS ===================== */
  function iconFor(type){
    const icons = {
      row:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="7" height="14" rx="1"/><rect x="14" y="5" width="7" height="14" rx="1"/></svg>',
      container:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/></svg>',
      heading:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4v16M18 4v16M6 12h12"/></svg>',
      text:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h10"/></svg>',
      button:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="8" rx="4"/></svg>',
      image:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>',
      divider:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/></svg>',
      list:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/><path d="M9 6h11M9 12h11M9 18h11"/></svg>',
      spacer:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M8 7l4-4 4 4M8 17l4 4 4-4"/></svg>'
    };
    return icons[type]||'';
  }
  function labelFor(type){
    const m = {row:'Baris',container:'Kontainer',heading:'Judul',text:'Paragraf',button:'Tombol',image:'Gambar',divider:'Divider',list:'Daftar',spacer:'Spacer'};
    return m[type]||type;
  }

  function renderNode(node, isRoot){
    const p = node.props||{};
    const wrap = document.createElement('div');
    wrap.className = 'el-block';
    wrap.dataset.id = node.id;
    wrap.draggable = true;

    const tag = document.createElement('div');
    tag.className = 'el-tag';
    tag.textContent = labelFor(node.type);
    wrap.appendChild(tag);

    let inner;
    switch(node.type){
      case 'container':
        inner = document.createElement('div');
        inner.dataset.dropzone = node.id;
        inner.style.background = p.bg||'transparent';
        inner.style.padding = (p.padding||'12')+'px';
        inner.style.borderRadius = (p.radius||'0')+'px';
        inner.style.minHeight = (p.minHeight||'40')+'px';
        if(node.children.length===0){
          inner.style.display='flex';inner.style.alignItems='center';inner.style.justifyContent='center';
          inner.style.color='#a8a498';inner.style.fontSize='11px';inner.style.border='1px dashed rgba(0,0,0,0.15)';
          inner.textContent = 'kontainer kosong — tarik komponen ke sini';
        } else {
          node.children.forEach(c=> inner.appendChild(renderNode(c,false)));
        }
        wrap.appendChild(inner);
        break;
      case 'heading': {
        inner = document.createElement('h2');
        inner.contentEditable = true;
        inner.spellcheck = false;
        inner.textContent = p.text;
        inner.style.margin='0';
        inner.style.fontSize=(p.size||'32')+'px';
        inner.style.color=p.color||'#16423C';
        inner.style.fontWeight=p.weight||'700';
        inner.style.textAlign=p.align||'left';
        inner.style.padding=(p.padding||'12')+'px';
        inner.style.background=p.bg==='transparent'?'transparent':(p.bg||'transparent');
        inner.style.borderRadius=(p.radius||'0')+'px';
        inner.addEventListener('input', ()=>{ p.text = inner.textContent; });
        inner.addEventListener('blur', ()=>{ pushHistory(); });
        wrap.appendChild(inner);
        break;
      }
      case 'text': {
        inner = document.createElement('p');
        inner.contentEditable = true;
        inner.spellcheck = false;
        inner.textContent = p.text;
        inner.style.margin='0';
        inner.style.fontSize=(p.size||'15')+'px';
        inner.style.color=p.color||'#4a4a4a';
        inner.style.fontWeight=p.weight||'400';
        inner.style.lineHeight='1.6';
        inner.style.textAlign=p.align||'left';
        inner.style.padding=(p.padding||'12')+'px';
        inner.addEventListener('input', ()=>{ p.text = inner.textContent; });
        inner.addEventListener('blur', ()=>{ pushHistory(); });
        wrap.appendChild(inner);
        break;
      }
      case 'button': {
        const holder = document.createElement('div');
        holder.style.padding=(p.padding||'12')+'px';
        holder.style.textAlign=p.align||'left';
        inner = document.createElement('span');
        inner.contentEditable = true;
        inner.spellcheck = false;
        inner.textContent = p.text;
        inner.style.display='inline-block';
        inner.style.background=p.bg||'#16423C';
        inner.style.color=p.color||'#fff';
        inner.style.fontSize=(p.size||'14')+'px';
        inner.style.fontWeight=p.weight||'600';
        inner.style.padding='10px 22px';
        inner.style.borderRadius=(p.radius||'8')+'px';
        inner.style.cursor='pointer';
        inner.addEventListener('input', ()=>{ p.text = inner.textContent; });
        inner.addEventListener('blur', ()=>{ pushHistory(); });
        holder.appendChild(inner);
        wrap.appendChild(holder);
        break;
      }
      case 'image': {
        const holder = document.createElement('div');
        holder.style.padding=(p.padding||'12')+'px';
        holder.style.textAlign=p.align||'left';
        inner = document.createElement('img');
        inner.src = p.src;
        inner.alt = p.alt||'';
        inner.style.maxWidth='100%';
        inner.style.borderRadius=(p.radius||'0')+'px';
        inner.style.pointerEvents='none';
        holder.appendChild(inner);
        wrap.appendChild(holder);
        break;
      }
      case 'divider': {
        inner = document.createElement('div');
        inner.style.padding=(p.padding||'12')+'px 0';
        const hr = document.createElement('div');
        hr.style.height='1px';
        hr.style.background=p.color||'#d8d4c9';
        inner.appendChild(hr);
        wrap.appendChild(inner);
        break;
      }
      case 'list': {
        inner = document.createElement('ul');
        inner.style.margin='0';
        inner.style.padding=(p.padding||'12')+'px 12px '+(p.padding||'12')+'px 28px';
        inner.style.color=p.color||'#333';
        inner.style.fontSize=(p.size||'14')+'px';
        inner.style.lineHeight='1.8';
        (p.items||[]).forEach(it=>{
          const li = document.createElement('li');
          li.textContent = it;
          inner.appendChild(li);
        });
        wrap.appendChild(inner);
        break;
      }
      case 'spacer': {
        inner = document.createElement('div');
        inner.style.height=(p.height||'40')+'px';
        inner.style.display='flex';inner.style.alignItems='center';inner.style.justifyContent='center';
        inner.style.color='#c9c5ba';inner.style.fontSize='10px';
        wrap.appendChild(inner);
        break;
      }
    }

    if(node.id===selectedId) wrap.classList.add('selected');

    wrap.addEventListener('click', (e)=>{
      e.stopPropagation();
      selectedId = node.id;
      render();
    });

    wrap.addEventListener('dragstart', (e)=>{
      e.stopPropagation();
      draggingNodeId = node.id;
      draggingWidgetType = null;
      e.dataTransfer.effectAllowed='move';
    });
    wrap.addEventListener('dragover', (e)=>{
      e.preventDefault(); e.stopPropagation();
      const rect = wrap.getBoundingClientRect();
      const mid = rect.top + rect.height/2;
      wrap.classList.remove('drag-over-top','drag-over-bottom');
      if(e.clientY < mid) wrap.classList.add('drag-over-top');
      else wrap.classList.add('drag-over-bottom');
    });
    wrap.addEventListener('dragleave', ()=> wrap.classList.remove('drag-over-top','drag-over-bottom'));
    wrap.addEventListener('drop', (e)=>{
      e.preventDefault(); e.stopPropagation();
      const before = wrap.classList.contains('drag-over-top');
      wrap.classList.remove('drag-over-top','drag-over-bottom');
      const loc = findParentArray(pageState, node.id);
      if(!loc) return;
      pushHistory();
      if(draggingWidgetType){
        const newNode = makeNode(draggingWidgetType);
        loc.arr.splice(before?loc.index:loc.index+1, 0, newNode);
      } else if(draggingNodeId && draggingNodeId!==node.id){
        const srcLoc = findParentArray(pageState, draggingNodeId);
        if(!srcLoc) return;
        const [moved] = srcLoc.arr.splice(srcLoc.index,1);
        const destLoc = findParentArray(pageState, node.id);
        destLoc.arr.splice(before?destLoc.index:destLoc.index+1,0,moved);
      }
      draggingWidgetType=null; draggingNodeId=null;
      render();
    });

    return wrap;
  }

  function render(){
    canvas.innerHTML = '';
    if(pageState.length===0){
      canvas.classList.add('empty-canvas');
    } else {
      canvas.classList.remove('empty-canvas');
      pageState.forEach(n=> canvas.appendChild(renderNode(n,true)));
    }
    renderLayers();
    renderProps();
  }

  /* ===================== CANVAS ROOT DROP ===================== */
  canvas.addEventListener('dragover', e=>{ e.preventDefault(); });
  canvas.addEventListener('drop', e=>{
    e.preventDefault();
    if(e.target !== canvas) return;
    if(draggingWidgetType){
      pushHistory();
      pageState.push(makeNode(draggingWidgetType));
      draggingWidgetType=null;
      render();
    }
  });
  canvas.addEventListener('click', ()=>{ selectedId=null; render(); });

  document.addEventListener('dragover', e=>{
    const zone = e.target.closest && e.target.closest('[data-dropzone]');
    if(zone) e.preventDefault();
  });
  document.addEventListener('drop', e=>{
    const zone = e.target.closest && e.target.closest('[data-dropzone]');
    if(!zone) return;
    if(e.target !== zone) return;
    e.preventDefault(); e.stopPropagation();
    const parentId = zone.dataset.dropzone;
    const parentNode = findNode(pageState, parentId);
    if(!parentNode) return;
    pushHistory();
    if(draggingWidgetType){
      parentNode.children.push(makeNode(draggingWidgetType));
    } else if(draggingNodeId){
      const srcLoc = findParentArray(pageState, draggingNodeId);
      if(srcLoc){
        const [moved] = srcLoc.arr.splice(srcLoc.index,1);
        parentNode.children.push(moved);
      }
    }
    draggingWidgetType=null; draggingNodeId=null;
    render();
  });

  /* ===================== WIDGET PALETTE DRAG ===================== */
  document.querySelectorAll('.widget-item').forEach(item=>{
    item.addEventListener('dragstart', e=>{
      draggingWidgetType = item.dataset.type;
      draggingNodeId = null;
      item.classList.add('dragging');
      e.dataTransfer.effectAllowed='copy';
    });
    item.addEventListener('dragend', ()=> item.classList.remove('dragging'));
  });

  /* ===================== LAYERS PANEL ===================== */
  function renderLayers(){
    layersTree.innerHTML='';
    if(pageState.length===0){
      layersTree.innerHTML = '<div class="empty-hint">Belum ada elemen. Tambahkan komponen dari tab "Komponen".</div>';
      return;
    }
    function build(list, container){
      list.forEach(node=>{
        const row = document.createElement('div');
        row.className='layer-row' + (node.id===selectedId?' selected':'');
        row.innerHTML = iconFor(node.type) + '<span class="lbl">'+labelFor(node.type)+'</span>' +
          '<svg class="del-mini" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>';
        row.addEventListener('click', (e)=>{ e.stopPropagation(); selectedId=node.id; render(); });
        row.querySelector('.del-mini').addEventListener('click',(e)=>{ e.stopPropagation(); deleteNode(node.id); });
        container.appendChild(row);
        if(node.children && node.children.length){
          const childWrap = document.createElement('div');
          childWrap.className='layer-children';
          container.appendChild(childWrap);
          build(node.children, childWrap);
        }
      });
    }
    build(pageState, layersTree);
  }

  /* ===================== PROPERTIES PANEL ===================== */
  function field(labelTxt, inputHtml){
    return '<div class="prop-group"><label class="prop-label">'+labelTxt+'</label>'+inputHtml+'</div>';
  }

  function escapeHtml(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function escapeAttr(s){ return (s||'').replace(/"/g,'&quot;'); }
  function toHex(c){
    if(!c) return '#ffffff';
    if(c.startsWith('#') && (c.length===7)) return c;
    const d = document.createElement('div'); d.style.color = c; document.body.appendChild(d);
    const rgb = getComputedStyle(d).color.match(/\d+/g); document.body.removeChild(d);
    if(!rgb) return '#ffffff';
    return '#'+rgb.slice(0,3).map(x=>(+x).toString(16).padStart(2,'0')).join('');
  }

  function renderProps(){
    if(!selectedId){
      propContent.innerHTML = '<div class="no-selection">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18M3 15v4a2 2 0 0 0 2 2h4"/></svg>' +
        'Pilih sebuah elemen di kanvas untuk mengedit propertinya di sini.</div>';
      return;
    }
    const node = findNode(pageState, selectedId);
    if(!node){ selectedId=null; renderProps(); return; }
    const p = node.props;
    let html = '<div class="panel-section"><div class="panel-section-title">'+labelFor(node.type)+'</div>';

    if(['heading','text','button'].includes(node.type)){
      html += field('Teks', '<textarea class="prop-input" id="p_text">'+escapeHtml(p.text)+'</textarea>');
    }
    if(node.type==='button'){
      html += field('Tautan (href)', '<input class="prop-input" id="p_href" value="'+escapeAttr(p.href)+'">');
    }
    if(node.type==='image'){
      html += field('URL Gambar', '<div style="display:flex;gap:6px;"><input class="prop-input" id="p_src" value="'+escapeAttr(p.src)+'" style="flex:1;"><button class="ghost-btn" id="p_upload" type="button" style="flex-shrink:0;height:32px;padding:0 10px;font-size:11px;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5M12 15V3"/></svg> Upload</button></div>');
      html += field('Teks Alternatif (alt)', '<input class="prop-input" id="p_alt" value="'+escapeAttr(p.alt)+'">');
    }
    if(node.type==='list'){
      html += field('Item (satu per baris)', '<textarea class="prop-input" id="p_items">'+escapeHtml((p.items||[]).join('\n'))+'</textarea>');
    }

    if(['heading','text','button','list'].includes(node.type)){
      html += '<div class="prop-row"><div>'+field('Ukuran Font (px)', '<input type="number" class="prop-input" id="p_size" value="'+p.size+'">')+'</div>' +
        '<div>'+field('Ketebalan', '<select class="prop-input" id="p_weight">' +
          '<option value="400"'+(p.weight==='400'?'selected':'')+'>Normal</option>' +
          '<option value="500"'+(p.weight==='500'?'selected':'')+'>Medium</option>' +
          '<option value="600"'+(p.weight==='600'?'selected':'')+'>Semi Bold</option>' +
          '<option value="700"'+(p.weight==='700'?'selected':'')+'>Bold</option>' +
          '<option value="800"'+(p.weight==='800'?'selected':'')+'>Extra Bold</option>' +
        '</select>')+'</div></div>';
    }

    if(['heading','text','button','list','divider'].includes(node.type)){
      html += field('Warna Teks/Garis', '<div class="prop-color-row">' +
        '<input type="color" class="prop-color-swatch" id="p_color" value="'+toHex(p.color)+'">' +
        '<input class="prop-input" id="p_color_txt" value="'+escapeAttr(p.color)+'"></div>');
    }

    if(['heading','text','button','image','container'].includes(node.type)){
      html += field('Warna Latar', '<div class="prop-color-row">' +
        '<input type="color" class="prop-color-swatch" id="p_bg" value="'+toHex(p.bg==='transparent'?'#ffffff':p.bg)+'">' +
        '<input class="prop-input" id="p_bg_txt" value="'+escapeAttr(p.bg)+'"></div>');
    }

    if(['heading','text','button','image','container'].includes(node.type)){
      html += field('Sudut Radius (px)', '<input type="range" min="0" max="40" class="prop-input" id="p_radius_range" value="'+(p.radius||0)+'" style="padding:0;">');
    }

    if(['heading','text','button','image','divider','container'].includes(node.type)){
      html += field('Padding (px)', '<div class="range-row"><input type="range" min="0" max="80" id="p_padding" value="'+(p.padding||0)+'">' +
        '<span class="range-val" id="p_padding_val">'+(p.padding||0)+'</span></div>');
    }

    if(['heading','text','button','image'].includes(node.type)){
      html += field('Perataan', '<div class="align-btns" id="p_align">' +
        '<button data-a="left" class="'+(p.align==='left'?'active':'')+'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h12M3 18h18"/></svg></button>' +
        '<button data-a="center" class="'+(p.align==='center'?'active':'')+'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M6 12h12M3 18h18"/></svg></button>' +
        '<button data-a="right" class="'+(p.align==='right'?'active':'')+'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M9 12h12M3 18h18"/></svg></button></div>');
    }

    if(node.type==='container'){
      html += field('Tinggi Minimum (px)', '<input type="number" class="prop-input" id="p_minheight" value="'+(p.minHeight||40)+'">');
    }
    if(node.type==='spacer'){
      html += field('Tinggi (px)', '<input type="number" class="prop-input" id="p_height" value="'+(p.height||40)+'">');
    }

    html += '</div><div class="panel-section" style="padding-top:0;">' +
      '<div class="prop-actions">' +
      '<button id="p_duplicate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>Duplikat</button>' +
      '<button id="p_delete" class="danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>Hapus</button>' +
      '</div></div>';

    propContent.innerHTML = html;
    bindPropEvents(node);
  }

  function bindPropEvents(node){
    const p = node.props;
    function byId(id){ return document.getElementById(id); }
    function commit(fn){ pushHistory(); fn(); render(); }
    function quickApply(node){
      const el = canvas.querySelector('[data-id="'+node.id+'"]');
      if(!el) return;
      renderLayers();
      const fresh = renderNode(node, false);
      el.replaceWith(fresh);
    }

    if(byId('p_text')) byId('p_text').addEventListener('change', e=> commit(()=> p.text = e.target.value));
    if(byId('p_href')) byId('p_href').addEventListener('change', e=> commit(()=> p.href = e.target.value));
    if(byId('p_src')) byId('p_src').addEventListener('change', e=> commit(()=> p.src = e.target.value));
    if(byId('p_alt')) byId('p_alt').addEventListener('change', e=> commit(()=> p.alt = e.target.value));
    if(byId('p_upload')) byId('p_upload').addEventListener('click', function(){
      var input = document.createElement('input');
      input.type = 'file';
      input.accept = 'image/*';
      input.addEventListener('change', function(){
        var file = input.files[0];
        if(!file) return;
        var fd = new FormData();
        fd.append('file', file);
        var btn = byId('p_upload');
        btn.disabled = true;
        btn.textContent = 'Mengunggah...';
        fetch('{{ route('editor.upload') }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
          body: fd
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
          if(res.success && res.file && res.file.url){
            pushHistory();
            p.src = res.file.url;
            render();
            showToast('Gambar berhasil diunggah');
          } else {
            showToast('Gagal mengunggah gambar');
          }
        })
        .catch(function(err){
          showToast('Gagal: ' + err.message);
        })
        .finally(function(){
          btn.disabled = false;
          btn.innerHTML = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5M12 15V3"/></svg> Upload';
        });
      });
      input.click();
    });
    if(byId('p_items')) byId('p_items').addEventListener('change', e=> commit(()=> p.items = e.target.value.split('\n').filter(Boolean)));
    if(byId('p_size')) byId('p_size').addEventListener('change', e=> commit(()=> p.size = e.target.value));
    if(byId('p_weight')) byId('p_weight').addEventListener('change', e=> commit(()=> p.weight = e.target.value));
    if(byId('p_color')) byId('p_color').addEventListener('input', e=> { p.color = e.target.value; byId('p_color_txt').value=e.target.value; quickApply(node); });
    if(byId('p_color')) byId('p_color').addEventListener('change', ()=> pushHistory());
    if(byId('p_color_txt')) byId('p_color_txt').addEventListener('change', e=> commit(()=> p.color = e.target.value));
    if(byId('p_bg')) byId('p_bg').addEventListener('input', e=> { p.bg = e.target.value; byId('p_bg_txt').value=e.target.value; quickApply(node); });
    if(byId('p_bg')) byId('p_bg').addEventListener('change', ()=> pushHistory());
    if(byId('p_bg_txt')) byId('p_bg_txt').addEventListener('change', e=> commit(()=> p.bg = e.target.value));
    if(byId('p_radius_range')) byId('p_radius_range').addEventListener('input', e=> { p.radius = e.target.value; quickApply(node); });
    if(byId('p_radius_range')) byId('p_radius_range').addEventListener('change', ()=> pushHistory());
    if(byId('p_padding')) byId('p_padding').addEventListener('input', e=> { p.padding = e.target.value; byId('p_padding_val').textContent=e.target.value; quickApply(node); });
    if(byId('p_padding')) byId('p_padding').addEventListener('change', ()=> pushHistory());
    if(byId('p_minheight')) byId('p_minheight').addEventListener('change', e=> commit(()=> p.minHeight = e.target.value));
    if(byId('p_height')) byId('p_height').addEventListener('change', e=> commit(()=> p.height = e.target.value));

    if(byId('p_align')) byId('p_align').querySelectorAll('button').forEach(btn=>{
      btn.addEventListener('click', ()=> commit(()=> p.align = btn.dataset.a));
    });

    byId('p_duplicate').addEventListener('click', ()=> duplicateNode(node.id));
    byId('p_delete').addEventListener('click', ()=> deleteNode(node.id));
  }

  /* ===================== PANEL TABS ===================== */
  document.querySelectorAll('.panel-tab').forEach(tab=>{
    tab.addEventListener('click', ()=>{
      if(!tab.dataset.tab) return;
      document.querySelectorAll('.panel-tab').forEach(t=> t.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById('tab-widgets').style.display = tab.dataset.tab==='widgets' ? 'block':'none';
      document.getElementById('tab-layers').style.display = tab.dataset.tab==='layers' ? 'block':'none';
    });
  });

  /* ===================== VIEWPORT TOGGLE ===================== */
  document.querySelectorAll('.viewport-toggle button').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      document.querySelectorAll('.viewport-toggle button').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('canvas-wrap').style.width = btn.dataset.vp+'px';
    });
  });

  /* ===================== TOAST ===================== */
  let toastTimer;
  function showToast(msg){
    const t = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(()=> t.classList.remove('show'), 2200);
  }

  /* ===================== CLEAR ===================== */
  document.getElementById('btnClear').addEventListener('click', ()=>{
    if(pageState.length===0) return;
    if(!confirm('Bersihkan seluruh kanvas? Tindakan ini bisa di-undo.')) return;
    pushHistory();
    pageState = [];
    selectedId = null;
    render();
    showToast('Kanvas dibersihkan');
  });

  /* ===================== SAVE TO SERVER ===================== */
  document.getElementById('btnSave').addEventListener('click', ()=>{
    if(isSaving) return;
    isSaving = true;
    document.getElementById('savingOverlay').classList.add('show');

    const payload = JSON.stringify({ pageState: JSON.parse(snapshot()), idCounter: idCounter });

    fetch(saveUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      },
      body: payload
    })
    .then(r=> r.json())
    .then(res=>{
      if(res.success){
        showToast('Halaman berhasil disimpan');
      } else {
        showToast('Gagal menyimpan: ' + (res.message||'unknown'));
      }
    })
    .catch(err=>{
      showToast('Gagal menyimpan: ' + err.message);
    })
    .finally(()=>{
      isSaving = false;
      document.getElementById('savingOverlay').classList.remove('show');
    });
  });

  /* ===================== EXPORT ===================== */
  function nodeToHtml(node, indent){
    const pad = '  '.repeat(indent);
    const p = node.props||{};
    const styleParts = [];
    if(p.padding!==undefined) styleParts.push('padding:'+p.padding+'px');
    if(p.bg && p.bg!=='transparent') styleParts.push('background:'+p.bg);
    if(p.radius) styleParts.push('border-radius:'+p.radius+'px');

    switch(node.type){
      case 'row': {
        const cols = node.children.map(c=> pad+'  <div style="flex:1;">\n'+nodeToHtml(c, indent+2)+pad+'  </div>').join('\n');
        return pad+'<div style="display:flex;gap:16px;'+styleParts.join(';')+'">\n'+cols+'\n'+pad+'</div>\n';
      }
      case 'container': {
        styleParts.push('min-height:'+(p.minHeight||40)+'px');
        const kids = node.children.map(c=> nodeToHtml(c, indent+1)).join('');
        return pad+'<div style="'+styleParts.join(';')+'">\n'+kids+pad+'</div>\n';
      }
      case 'heading':
        styleParts.push('font-size:'+p.size+'px','color:'+p.color,'font-weight:'+p.weight,'text-align:'+p.align,'margin:0');
        return pad+'<h2 style="'+styleParts.join(';')+'">'+escapeHtml(p.text)+'</h2>\n';
      case 'text':
        styleParts.push('font-size:'+p.size+'px','color:'+p.color,'font-weight:'+p.weight,'text-align:'+p.align,'margin:0','line-height:1.6');
        return pad+'<p style="'+styleParts.join(';')+'">'+escapeHtml(p.text)+'</p>\n';
      case 'button':
        return pad+'<div style="padding:'+p.padding+'px;text-align:'+p.align+';">\n'+pad+'  <a href="'+escapeAttr(p.href)+'" style="display:inline-block;background:'+p.bg+';color:'+p.color+';font-size:'+p.size+'px;font-weight:'+p.weight+';padding:10px 22px;border-radius:'+p.radius+'px;text-decoration:none;">'+escapeHtml(p.text)+'</a>\n'+pad+'</div>\n';
      case 'image':
        return pad+'<div style="padding:'+p.padding+'px;text-align:'+p.align+';">\n'+pad+'  <img src="'+escapeAttr(p.src)+'" alt="'+escapeAttr(p.alt)+'" style="max-width:100%;border-radius:'+p.radius+'px;">\n'+pad+'</div>\n';
      case 'divider':
        return pad+'<div style="padding:'+p.padding+'px 0;"><div style="height:1px;background:'+p.color+';"></div></div>\n';
      case 'list': {
        const items = (p.items||[]).map(it=> pad+'  <li>'+escapeHtml(it)+'</li>').join('\n');
        return pad+'<ul style="margin:0;padding:'+p.padding+'px 12px '+p.padding+'px 28px;color:'+p.color+';font-size:'+p.size+'px;line-height:1.8;">\n'+items+'\n'+pad+'</ul>\n';
      }
      case 'spacer':
        return pad+'<div style="height:'+p.height+'px;"></div>\n';
      default: return '';
    }
  }

  function buildExportHtml(){
    const body = pageState.map(n=> nodeToHtml(n,3)).join('');
    return '<!DOCTYPE html>\n<html lang="id">\n<head>\n<meta charset="UTF-8">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>'+escapeHtml('{{ $page->title }}')+'</title>\n<style>\n  *{box-sizing:border-box;}\n  body{margin:0;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif;background:#f4f2ec;}\n  .page-wrap{max-width:1180px;margin:0 auto;background:#ffffff;}\n  img{display:block;}\n  @media(max-width:760px){\n    .page-wrap [style*="display:flex"]{flex-direction:column;}\n  }\n</style>\n</head>\n<body>\n  <div class="page-wrap">\n'+body+'  </div>\n</body>\n</html>';
  }

  const exportModal = document.getElementById('exportModal');
  document.getElementById('btnExport').addEventListener('click', ()=>{
    document.getElementById('exportCode').value = buildExportHtml();
    exportModal.classList.add('show');
  });
  document.getElementById('closeExport').addEventListener('click', ()=> exportModal.classList.remove('show'));
  exportModal.addEventListener('click', e=>{ if(e.target===exportModal) exportModal.classList.remove('show'); });
  document.getElementById('copyExport').addEventListener('click', ()=>{
    const ta = document.getElementById('exportCode');
    ta.select();
    document.execCommand('copy');
    showToast('Kode HTML disalin ke clipboard');
  });
  document.getElementById('downloadExport').addEventListener('click', ()=>{
    const blob = new Blob([document.getElementById('exportCode').value], {type:'text/html'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = '{{ $page->slug }}.html';
    a.click();
    showToast('File HTML berhasil diunduh');
  });

  /* ===================== INIT ===================== */
  render();
  updateHistoryButtons();

})();
</script>
</body>
</html>
