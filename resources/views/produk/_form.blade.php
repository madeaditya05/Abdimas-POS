@php
  /** @var \App\Models\Produk $produk */
  $selectedKategori = old('kategori', $produk->kategori ?? '');
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
                ? route('produk.store')
                : route('produk.update', $produk) }}"
      enctype="multipart/form-data"
      class="form">
  @csrf
  @if($mode === 'edit')
    @method('PUT')
  @endif

  {{-- DATA PRODUK --}}
  <div class="form-section">
    <div class="form-title">Data Produk</div>
    <div class="form-grid">

      {{-- Kode Produk --}}
      <div class="form-field">
        <label>Kode Produk</label>
        <div class="field-with-icon">
          <input
            class="form-input"
            type="text"
            value="{{ $produk->kode_barang ?? '' }}"
            disabled
          >
          <span class="field-icon">
            <x-heroicon-o-hashtag class="hi hi-5" />
          </span>
        </div>
        <div class="form-help">Kode diisi otomatis (format CF####).</div>
      </div>

      {{-- Nama Produk --}}
      <div class="form-field">
        <label>Nama Produk <span style="color:#ef4444">*</span></label>
        <div class="field-with-icon">
          <input
            class="form-input"
            name="nama_barang"
            value="{{ old('nama_barang', $produk->nama_barang ?? '') }}"
            required
          >
          <span class="field-icon">
            <x-heroicon-o-cube class="hi hi-5" />
          </span>
        </div>
        <div class="form-help"></div>
      </div>

      {{-- Stok --}}
      <div class="form-field">
        <label>Stok <span style="color:#ef4444">*</span></label>
        <div class="field-with-icon">
          <input
            class="form-input"
            type="number"
            step="1"
            min="0"
            name="stok"
            value="{{ old('stok', $produk->stok ?? 0) }}"
            required
          >
          <span class="field-icon">
            <x-heroicon-o-archive-box class="hi hi-5" />
          </span>
        </div>
        <div class="form-help"></div>
      </div>

      {{-- Harga --}}
      <div class="form-field">
        <label>Harga <span style="color:#ef4444">*</span></label>
        <div class="field-with-icon">
          <input
            class="form-input"
            type="number"
            step="1"
            min="0"
            name="harga"
            value="{{ old('harga', $produk->harga ?? 0) }}"
            required
          >
          <span class="field-icon">
            <x-heroicon-o-banknotes class="hi hi-5" />
          </span>
        </div>
        <div class="form-help">Isi harga jual dalam rupiah (tanpa titik).</div>
      </div>

      {{-- Kategori --}}
      <div class="form-field">
        <label>Kategori <span style="color:#ef4444">*</span></label>
        <div class="dd" data-select="kategori">
          <button type="button" class="dd-toggle" aria-haspopup="listbox" aria-expanded="false">
            <span class="dd-label">
              @switch($selectedKategori)
                @case('coffee') Coffee @break
                @case('non_coffee') Non Coffee @break
                @case('snack') Snack @break
                @default Pilih kategori
              @endswitch
            </span>
            <span class="dd-icon">
              <x-heroicon-o-tag class="hi hi-4" />
            </span>
          </button>

          <div class="dd-menu" role="listbox">
            <div class="dd-item {{ $selectedKategori==='' ? 'active':'' }}" data-value="">
              Pilih kategori
            </div>
            @foreach($kategoriOptions as $val => $label)
              <div class="dd-item {{ $selectedKategori===$val ? 'active':'' }}" data-value="{{ $val }}">
                {{ $label }}
              </div>
            @endforeach
          </div>

          <input type="hidden" name="kategori" value="{{ $selectedKategori }}" required>
        </div>
        <div class="form-help"></div>
      </div>

    </div>
  </div>

  {{-- GAMBAR & DESKRIPSI --}}
  <div class="form-section">
    <div class="form-title">Gambar & Deskripsi</div>
    <div class="form-grid">

      {{-- Gambar --}}
      <div class="form-field">
        <label>Gambar</label>

        @if(!empty($produk->gambar))
          <img
            src="{{ asset('storage/'.$produk->gambar) }}"
            alt="Gambar {{ $produk->nama_barang }}"
            class="preview-img"
            style="margin-bottom:10px;width:96px;height:96px;object-fit:cover;border-radius:12px;"
          >
        @endif

        <div class="file-field" id="ff-gambar">
          <input
            id="gambar"
            class="file-input"
            type="file"
            name="gambar"
            accept="image/*"
          >
          <label for="gambar" class="file-btn">Pilih File</label>
          <span class="file-name is-empty">
            {{ $produk->gambar ? 'Biarkan kosong jika tidak diubah' : 'Belum ada file dipilih' }}
          </span>
        </div>

        <div class="form-help">Opsional. Format gambar (JPG, PNG), maks 2MB.</div>
      </div>

      {{-- Deskripsi --}}
      <div class="form-field">
        <label>Deskripsi</label>
        <div class="field-with-icon">
          <textarea
            class="form-textarea"
            name="deskripsi"
            rows="4"
          >{{ old('deskripsi', $produk->deskripsi ?? '') }}</textarea>
          <span class="field-icon">
            <x-heroicon-o-pencil-square class="hi hi-5" />
          </span>
        </div>
        <div class="form-help"></div>
      </div>

    </div>

    <div class="form-actions">
      <a class="btn btn--danger" href="{{ route('produk.index') }}">
        Batal
      </a>

      <button type="submit" class="btn btn--success">
        Simpan
      </button>
    </div>
  </div>
</form>

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

  /* ========== File field (#ff-gambar) ========== */
  const wrap = document.getElementById('ff-gambar');
  if (wrap) {
    const input  = wrap.querySelector('.file-input');
    const nameEl = wrap.querySelector('.file-name');

    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (file) {
        nameEl.textContent = file.name;
        nameEl.classList.remove('is-empty');
      } else {
        nameEl.textContent = '{{ $produk->gambar ? 'Biarkan kosong jika tidak diubah' : 'Belum ada file dipilih' }}';
        nameEl.classList.add('is-empty');
      }
    });

    input.addEventListener('focus', () => wrap.classList.add('is-focus'));
    input.addEventListener('blur',  () => wrap.classList.remove('is-focus'));
  }
})();
</script>
@endpush
