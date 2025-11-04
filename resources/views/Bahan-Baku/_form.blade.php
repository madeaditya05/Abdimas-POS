@extends('layouts.main')
@section('title', $mode === 'create' ? 'Create Bahan Baku' : 'Edit Bahan Baku')

@section('content')
@php
  $selectedKategori    = old('kategori',       $row->kategori        ?? '');
  $selectedSatuanPakai = old('satuan_pakai',   $row->satuan_pakai    ?? '');
  $selectedSatuanBeli  = old('satuan_beli',    $row->satuan_beli     ?? '');
  $selPeny             = old('penyimpanan',    $row->penyimpanan     ?? 'room');
  $selAlergen          = old('allergen_flag',  $row->allergen_flag   ?? '');
  $selHalal            = old('status_halal',   $row->status_halal    ?? '');
  $selLead             = old('lead_time_hari', $row->lead_time_hari  ?? '');
  $selMin              = old('min_order_qty',  $row->min_order_qty   ?? '');
  $selYield            = old('yield_persen',   $row->yield_persen    ?? 100);
@endphp

@if ($errors->any())
  <div class="form-section" style="border-color:#fecaca;background:#fff1f2;">
    <strong>Periksa kembali:</strong>
    <ul style="margin:6px 0 0 18px;">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<form method="POST"
      action="{{ $mode==='create' ? route('bahan-baku.store') : route('bahan-baku.update',$row) }}"
      enctype="multipart/form-data" class="form">
  @csrf
  @if($mode==='edit') @method('PUT') @endif

  {{-- IDENTITAS --}}
  <div class="form-section">
    <div class="form-title">Identitas</div>
    <div class="form-grid">
      <div class="form-field">
        <label>Kode Bahan</label>
        <input class="form-input" type="text" value="{{ $row->kode_bahan ?? '' }}" disabled>
        <div class="form-help">Kode diisi otomatis (format BHK####).</div>
      </div>

      <div class="form-field">
        <label>Nama Bahan <span style="color:#ef4444">*</span></label>
        <input class="form-input" name="nama_bahan" value="{{ old('nama_bahan', $row->nama_bahan ?? '') }}" required>
        <div class="form-help"></div>
      </div>

      <div class="form-field">
        <label>Kategori</label>
        <div class="dd" data-select="kategori">
          <button type="button" class="dd-toggle" aria-haspopup="listbox" aria-expanded="false">
            <span class="dd-label">
              {{ $selectedKategori && isset($opsiKategori[$selectedKategori]) ? $opsiKategori[$selectedKategori] : 'Pilih kategori' }}
            </span>
            <span class="dd-caret"></span>
          </button>
          <div class="dd-menu" role="listbox">
            <div class="dd-item {{ $selectedKategori=='' ? 'active':'' }}" data-value="">Pilih kategori</div>
            @foreach($opsiKategori as $val => $label)
              <div class="dd-item {{ $selectedKategori===$val ? 'active':'' }}" data-value="{{ $val }}">{{ $label }}</div>
            @endforeach
          </div>
          <input type="hidden" name="kategori" value="{{ $selectedKategori }}">
        </div>
        <div class="form-help"></div>
      </div>

      <div class="form-check">
        <input type="checkbox" name="aktif" value="1" {{ old('aktif', $row->aktif ?? true) ? 'checked' : '' }}>
        <label>Aktif</label>
        <div class="form-help"></div>
      </div>
    </div>
  </div>

  {{-- SATUAN & KONVERSI --}}
  <div class="form-section">
    <div class="form-title">Satuan & Konversi</div>
    <div class="form-grid">
      <div class="form-field">
        <label>Satuan Pakai <span style="color:#ef4444">*</span></label>
        <div class="dd" data-select="satuan_pakai">
          <button type="button" class="dd-toggle" aria-haspopup="listbox" aria-expanded="false">
            <span class="dd-label">
              {{ $selectedSatuanPakai && isset($opsiSatuan[$selectedSatuanPakai]) ? $opsiSatuan[$selectedSatuanPakai] : 'Pilih satuan' }}
            </span>
            <span class="dd-caret"></span>
          </button>
          <div class="dd-menu" role="listbox">
            <div class="dd-item {{ $selectedSatuanPakai=='' ? 'active':'' }}" data-value="">Pilih satuan</div>
            @foreach($opsiSatuan as $v => $t)
              <div class="dd-item {{ $selectedSatuanPakai===$v ? 'active':'' }}" data-value="{{ $v }}">{{ $t }}</div>
            @endforeach
          </div>
          <input type="hidden" name="satuan_pakai" value="{{ $selectedSatuanPakai }}" required>
        </div>
        <div class="form-help"></div>
      </div>

      <div class="form-field">
        <label>Satuan Beli</label>
        <div class="dd" data-select="satuan_beli">
          <button type="button" class="dd-toggle" aria-haspopup="listbox" aria-expanded="false">
            <span class="dd-label">
              {{ $selectedSatuanBeli && isset($opsiSatuan[$selectedSatuanBeli]) ? $opsiSatuan[$selectedSatuanBeli] : 'Sama dengan satuan pakai' }}
            </span>
            <span class="dd-caret"></span>
          </button>
          <div class="dd-menu" role="listbox">
            <div class="dd-item {{ $selectedSatuanBeli=='' ? 'active':'' }}" data-value="">Sama dengan satuan pakai</div>
            @foreach($opsiSatuan as $v => $t)
              <div class="dd-item {{ $selectedSatuanBeli===$v ? 'active':'' }}" data-value="{{ $v }}">{{ $t }}</div>
            @endforeach
          </div>
          <input type="hidden" name="satuan_beli" value="{{ $selectedSatuanBeli }}">
        </div>
        <div class="form-help"></div>
      </div>

      <div class="form-field">
        <label>Konversi Beli → Pakai</label>
        <input class="form-input" type="number" step="0.001" name="konversi_beli_ke_pakai"
               value="{{ old('konversi_beli_ke_pakai', $row->konversi_beli_ke_pakai ?? 1) }}">
        <div class="form-help"></div>
      </div>

      <div class="form-field">
        <label>Isi per Kemasan</label>
        <input class="form-input" type="number" step="0.001" name="isi_per_kemasan"
               value="{{ old('isi_per_kemasan', $row->isi_per_kemasan ?? '') }}">
        <div class="form-help"></div>
      </div>
    </div>
  </div>

  {{-- PENYIMPANAN & SHELF-LIFE --}}
  <div class="form-section">
    <div class="form-title">Penyimpanan & Shelf-life</div>
    <div class="form-grid">
      <div class="form-field">
        <label>Penyimpanan</label>
        <div class="dd" data-select="penyimpanan">
          <button type="button" class="dd-toggle">
            <span class="dd-label">{{ $opsiPenyimpanan[$selPeny] ?? 'Pilih' }}</span>
            <span class="dd-caret"></span>
          </button>
          <div class="dd-menu">
            @foreach($opsiPenyimpanan as $v => $t)
              <div class="dd-item {{ $selPeny===$v ? 'active':'' }}" data-value="{{ $v }}">{{ $t }}</div>
            @endforeach
          </div>
          <input type="hidden" name="penyimpanan" value="{{ $selPeny }}">
        </div>
        <div class="form-help"></div>
      </div>

      <div class="form-check">
        <input type="checkbox" name="is_perishable" value="1" {{ old('is_perishable',$row->is_perishable ?? false) ? 'checked' : '' }}>
        <label>Mudah Rusak?</label>
        <div class="form-help"></div>
      </div>

      <div class="form-field">
        <label>Masa Simpan (hari)</label>
        <input class="form-input" type="number" step="1" name="masa_simpan_hari"
               value="{{ old('masa_simpan_hari', $row->masa_simpan_hari ?? '') }}">
        <div class="form-help"></div>
      </div>

      <div class="form-check">
        <input type="checkbox" name="kelola_expired" value="1" {{ old('kelola_expired',$row->kelola_expired ?? false) ? 'checked' : '' }}>
        <label>Kelola Expired</label>
        <div class="form-help"></div>
      </div>
    </div>
  </div>

  {{-- KEAMANAN & KEHALALAN --}}
  <div class="form-section">
    <div class="form-title">Keamanan & Kehalalan</div>
    <div class="form-grid">
      <div class="form-field">
        <label>Alergen</label>
        <div class="dd" data-select="allergen_flag">
          <button type="button" class="dd-toggle">
            <span class="dd-label">
              {{ $selAlergen==='' ? 'Tidak ada' : ($opsiAlergen[$selAlergen] ?? 'Tidak ada') }}
            </span>
            <span class="dd-caret"></span>
          </button>
          <div class="dd-menu">
            <div class="dd-item {{ $selAlergen==='' ? 'active':'' }}" data-value="">Tidak ada</div>
            @foreach($opsiAlergen as $v => $t)
              <div class="dd-item {{ $selAlergen===$v ? 'active':'' }}" data-value="{{ $v }}">{{ $t }}</div>
            @endforeach
          </div>
          <input type="hidden" name="allergen_flag" value="{{ $selAlergen }}">
        </div>
        <div class="form-help"></div>
      </div>

      <div class="form-field">
        <label>Status Halal</label>
        <div class="dd" data-select="status_halal">
          <button type="button" class="dd-toggle">
            <span class="dd-label">
              {{ $selHalal==='' ? 'Pilih status' : ($opsiStatusHalal[$selHalal] ?? 'Pilih status') }}
            </span>
            <span class="dd-caret"></span>
          </button>
          <div class="dd-menu">
            <div class="dd-item {{ $selHalal==='' ? 'active':'' }}" data-value="">Pilih status</div>
            @foreach($opsiStatusHalal as $v => $t)
              <div class="dd-item {{ $selHalal===$v ? 'active':'' }}" data-value="{{ $v }}">{{ $t }}</div>
            @endforeach
          </div>
          <input type="hidden" name="status_halal" value="{{ $selHalal }}">
        </div>
        <div class="form-help"></div>
      </div>
    </div>
  </div>

  {{-- SUPPLIER --}}
  <div class="form-section">
    <div class="form-title">Supplier</div>
    <div class="form-grid">
      <div class="form-field">
        <label>Supplier Default</label>
        <input class="form-input" name="default_supplier_nama"
               value="{{ old('default_supplier_nama',$row->default_supplier_nama ?? '') }}">
        <div class="form-help"></div>
      </div>

      <div class="form-field">
        <label>Kontak Supplier</label>
        <input class="form-input" name="supplier_kontak"
               value="{{ old('supplier_kontak',$row->supplier_kontak ?? '') }}">
        <div class="form-help"></div>
      </div>

      <div class="form-field">
        <label>Lead Time (hari)</label>
        <div class="dd" data-select="lead_time_hari">
          <button type="button" class="dd-toggle">
            <span class="dd-label">{{ $selLead==='' ? 'Opsional' : $selLead }}</span>
            <span class="dd-caret"></span>
          </button>
          <div class="dd-menu">
            <div class="dd-item {{ $selLead==='' ? 'active':'' }}" data-value="">Opsional</div>
            @foreach($opsiLeadTime as $v => $t)
              <div class="dd-item {{ (string)$selLead===(string)$v ? 'active':'' }}" data-value="{{ $v }}">{{ $t }}</div>
            @endforeach
          </div>
          <input type="hidden" name="lead_time_hari" value="{{ $selLead }}">
        </div>
        <div class="form-help"></div>
      </div>

      <div class="form-field">
        <label>Min Order Qty</label>
        <div class="dd" data-select="min_order_qty">
          <button type="button" class="dd-toggle">
            <span class="dd-label">{{ $selMin==='' ? 'Opsional' : $selMin }}</span>
            <span class="dd-caret"></span>
          </button>
          <div class="dd-menu">
            <div class="dd-item {{ $selMin==='' ? 'active':'' }}" data-value="">Opsional</div>
            @foreach($opsiMinOrder as $v => $t)
              <div class="dd-item {{ (string)$selMin===(string)$v ? 'active':'' }}" data-value="{{ $v }}">{{ $t }}</div>
            @endforeach
          </div>
          <input type="hidden" name="min_order_qty" value="{{ $selMin }}">
        </div>
        <div class="form-help"></div>
      </div>
    </div>
  </div>

  {{-- PRODUKSI --}}
  <div class="form-section">
    <div class="form-title">Produksi</div>
    <div class="form-grid">
      <div class="form-field">
        <label>Yield (%) <span style="color:#ef4444">*</span></label>
        <div class="dd" data-select="yield_persen">
          <button type="button" class="dd-toggle">
            <span class="dd-label">{{ $opsiYield[$selYield] ?? $selYield }}</span>
            <span class="dd-caret"></span>
          </button>
          <div class="dd-menu">
            @foreach($opsiYield as $v => $t)
              <div class="dd-item {{ (string)$selYield===(string)$v ? 'active':'' }}" data-value="{{ $v }}">{{ $t }}</div>
            @endforeach
          </div>
          <input type="hidden" name="yield_persen" value="{{ $selYield }}" required>
        </div>
        <div class="form-help"></div>
      </div>

      <div class="form-check">
        <input type="checkbox" name="dipakai_di_resep" value="1" {{ old('dipakai_di_resep',$row->dipakai_di_resep ?? true) ? 'checked' : '' }}>
        <label>Dipakai di Resep</label>
        <div class="form-help"></div>
      </div>
    </div>
  </div>

  {{-- LAMPIRAN --}}
  <div class="form-section" style="padding-bottom:0;">
    <div class="form-title">Lampiran</div>
    <div class="form-grid">
      <div class="form-field span-2">
        <label>Foto</label>
        @if(!empty($row->foto_path))
          <img class="preview-img" src="{{ asset('storage/'.$row->foto_path) }}" alt="foto" style="margin-bottom:10px;">
        @endif
        <div class="file-field" id="ff-foto">
          <input id="foto_path" class="file-input" type="file" name="foto_path" accept="image/*">
          <label for="foto_path" class="file-btn">Choose File</label>
          <span class="file-name is-empty">No file chosen</span>
        </div>
        <div class="form-help"></div>
      </div>

      <div class="form-field span-2">
        <label>Catatan</label>
        <textarea class="form-textarea" name="catatan">{{ old('catatan', $row->catatan ?? '') }}</textarea>
        <div class="form-help"></div>
      </div>
    </div>

    <div class="form-actions">
      <a class="btn" href="{{ route('bahan-baku.index') }}">Batal</a>
      <button type="submit" class="btn btn--filled btn--success">Simpan</button>
    </div>
  </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
  /* ========== Dropdown (.dd) ========== */
  const closeAll = () => document.querySelectorAll('.dd.open')
    .forEach(dd => dd.classList.remove('open'));

  document.addEventListener('click', e => { if (!e.target.closest('.dd')) closeAll(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });

  document.querySelectorAll('.dd').forEach(dd => {
    const btn   = dd.querySelector('.dd-toggle');
    const menu  = dd.querySelector('.dd-menu');
    const label = dd.querySelector('.dd-label');
    const input = dd.querySelector('input[type="hidden"]');
    btn?.addEventListener('click', e => {
      e.stopPropagation();
      const willOpen = !dd.classList.contains('open');
      closeAll();
      if (willOpen) dd.classList.add('open');
    });
    menu?.querySelectorAll('.dd-item').forEach(item => {
      item.addEventListener('click', () => {
        menu.querySelectorAll('.dd-item.active').forEach(x => x.classList.remove('active'));
        item.classList.add('active');
        input.value = item.dataset.value ?? '';
        label.textContent = item.textContent.trim();
        dd.classList.remove('open');
      });
    });
  });

  /* ========== File field (#ff-foto) ========== */
  const wrap = document.getElementById('ff-foto');
  if (wrap) {
    const input  = wrap.querySelector('.file-input');
    const nameEl = wrap.querySelector('.file-name');
    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (file) { nameEl.textContent = file.name; nameEl.classList.remove('is-empty'); }
      else      { nameEl.textContent = 'No file chosen'; nameEl.classList.add('is-empty'); }
    });
    input.addEventListener('focus', () => wrap.classList.add('is-focus'));
    input.addEventListener('blur',  () => wrap.classList.remove('is-focus'));
  }
})();
</script>
@endpush
