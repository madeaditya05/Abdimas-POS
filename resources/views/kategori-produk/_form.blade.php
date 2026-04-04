@php
  /** @var \App\Models\KategoriProduk $row */
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
      action="{{ $mode === 'create'
                ? route('kategori-produk.store')
                : route('kategori-produk.update', $row) }}"
      class="form">
  @csrf
  @if($mode === 'edit')
    @method('PUT')
  @endif

  <div class="form-section">
    <div class="form-title">Data Kategori Produk</div>
    <div class="form-grid">
      <div class="form-field">
        <label>Nama Kategori <span style="color:#ef4444">*</span></label>
        <div class="field-with-icon">
          <input
            class="form-input"
            name="nama"
            value="{{ old('nama', $row->nama ?? '') }}"
            required
          >
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-5"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
          </span>
        </div>
        <div class="form-help">Nama ini akan dipakai di dropdown produk, filter, dan kasir.</div>
      </div>

      <div class="form-field">
        <label>Urutan</label>
        <div class="field-with-icon">
          <input
            class="form-input"
            type="number"
            min="0"
            step="1"
            name="urutan"
            value="{{ old('urutan', $row->urutan ?? 0) }}"
          >
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-5"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
          </span>
        </div>
        <div class="form-help">Semakin kecil angkanya, semakin atas urutannya.</div>
      </div>

      <div class="form-field">
        <label>Status</label>
        <label class="form-switch" style="margin-top:10px;">
          <input type="hidden" name="aktif" value="0">
          <input
            type="checkbox"
            name="aktif"
            value="1"
            {{ old('aktif', $row->aktif ?? true) ? 'checked' : '' }}
          >
          <span class="form-switch-track">
            <span class="form-switch-thumb"></span>
          </span>
          <span class="form-switch-label">Aktif</span>
        </label>
        <div class="form-help">Kategori nonaktif tidak muncul di form tambah/edit produk baru.</div>
      </div>

      <div class="form-field">
        <label>Slug</label>
        <div class="field-with-icon">
          <input
            class="form-input"
            value="{{ $row->slug ?? 'Dibuat otomatis saat disimpan' }}"
            disabled
          >
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-5"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
          </span>
        </div>
        <div class="form-help">Slug dibuat otomatis dari nama kategori dan dipakai untuk integrasi ke data produk.</div>
      </div>
    </div>
  </div>

  <div class="form-section">
    <div class="form-title">Deskripsi</div>
    <div class="form-grid">
      <div class="form-field">
        <label>Deskripsi</label>
        <div class="field-with-icon">
          <textarea
            class="form-textarea"
            name="deskripsi"
            rows="4"
          >{{ old('deskripsi', $row->deskripsi ?? '') }}</textarea>
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-5"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
          </span>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <a class="btn btn--danger" href="{{ route('kategori-produk.index') }}">
        Batal
      </a>

      <button type="submit" class="btn btn--success">
        Simpan
      </button>
    </div>
  </div>
</form>
