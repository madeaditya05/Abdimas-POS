@php
    /** @var \App\Models\Resep|null $row */
    $idProduk  = old('produk_id', $row->produk_id ?? '');
    $isActive  = old('is_active', $row->is_active ?? true);
    $catatan   = old('catatan',  $row->catatan  ?? '');

    // details: array of [bahan_baku_id, qty_per_porsi, keterangan]
    $detailOld = old('details');
    if (is_array($detailOld)) {
        $detailRows = collect($detailOld);
    } else {
        $detailRows = $detailRows ?? collect();
    }
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
                ? route('resep.store')
                : route('resep.update', $row) }}">
  @csrf
  @if ($mode === 'edit')
    @method('PUT')
  @endif

  {{-- SECTION: Header / info utama --}}
  <div class="form-section">
    <div class="form-title">Informasi Resep</div>
    <div class="form-grid">

      {{-- Produk --}}
      <div class="form-field span-2">
        <label>Produk <span style="color:#ef4444">*</span></label>
        <select name="produk_id" class="form-input" required>
          <option value="">Pilih produk…</option>
          @foreach ($opsiProduk as $id => $label)
            <option value="{{ $id }}" {{ (string)$idProduk === (string)$id ? 'selected' : '' }}>
              {{ $label }}
            </option>
          @endforeach
        </select>
        <div class="form-help"></div>
      </div>

      {{-- Aktif? --}}
      <div class="form-check">
        <label class="form-switch">
          <input type="checkbox" name="is_active" value="1"
                 {{ $isActive ? 'checked' : '' }}>
          <span class="form-switch-track">
            <span class="form-switch-thumb"></span>
          </span>
          <span class="form-switch-label">Aktif?</span>
        </label>
        <div class="form-help">Jika aktif, resep lain untuk produk ini akan otomatis dinonaktifkan.</div>
      </div>

    </div>
  </div>

  {{-- SECTION: Catatan header --}}
  <div class="form-section">
    <div class="form-title">Catatan Resep</div>
    <div class="form-grid">
      <div class="form-field span-2">
        <label>Catatan</label>
        <textarea name="catatan" class="form-textarea" rows="2">{{ $catatan }}</textarea>
        <div class="form-help">Opsional. Misalnya: “Gunakan es batu kecil”, dsb.</div>
      </div>
    </div>
  </div>

  {{-- SECTION: Detail Bahan (Bill of Material) --}}
  <div class="form-section">
    <div class="form-title">Detail Bahan (Bill of Material)</div>

    <div id="detail-rows" class="form-grid span-2" style="grid-template-columns: 1fr;">
      @php
        $rows = $detailRows->count()
          ? $detailRows
          : collect([['bahan_baku_id' => '', 'qty_per_porsi' => '', 'keterangan' => '']]);
      @endphp

      @foreach ($rows as $i => $detail)
        @php
          $bahanId   = $detail['bahan_baku_id'] ?? '';
          $qty       = $detail['qty_per_porsi'] ?? '';
          $ket       = $detail['keterangan'] ?? '';
        @endphp

        <div class="detail-row card" data-row-index="{{ $i }}" style="margin-bottom:12px;">
          <div class="form-grid">

            {{-- Bahan Baku --}}
            <div class="form-field">
              <label>Bahan Baku <span style="color:#ef4444">*</span></label>
              <select name="details[{{ $i }}][bahan_baku_id]" class="form-input" required>
                <option value="">Pilih bahan…</option>
                @foreach ($opsiBahan as $id => $label)
                  <option value="{{ $id }}" {{ (string)$bahanId === (string)$id ? 'selected' : '' }}>
                    {{ $label }}
                  </option>
                @endforeach
              </select>
              <div class="form-help"></div>
            </div>

            {{-- Qty per porsi --}}
            <div class="form-field">
              <label>Qty per porsi <span style="color:#ef4444">*</span></label>
              <input type="number" step="0.001"
                     name="details[{{ $i }}][qty_per_porsi]"
                     class="form-input"
                     value="{{ $qty }}"
                     required>
              <div class="form-help">Isi sesuai satuan pakai (gram/ml/pcs).</div>
            </div>

            {{-- Keterangan --}}
            <div class="form-field span-2">
              <label>Keterangan</label>
              <textarea name="details[{{ $i }}][keterangan]"
                        class="form-textarea"
                        rows="1">{{ $ket }}</textarea>
              <div class="form-help"></div>
            </div>

          </div>

          <button type="button"
                  class="btn btn--outline-danger btn--sm"
                  onclick="window.removeDetailRow(this)">
            Hapus baris
          </button>
        </div>
      @endforeach
    </div>

    <div class="form-actions" style="justify-content:flex-start;margin-top:8px;">
      <button type="button"
              class="btn btn--outline-coffee"
              onclick="window.addDetailRow()">
        Tambah Bahan
      </button>
    </div>
  </div>

  {{-- BUTTONS --}}
  <div class="form-section" style="border-top:none;padding-top:0;">
    <div class="form-actions">
      <a href="{{ route('resep.index') }}" class="btn btn--danger">Batal</a>
      <button type="submit" class="btn btn--success">
        {{ $mode === 'create' ? 'Simpan Resep' : 'Update Resep' }}
      </button>
    </div>
  </div>
</form>

@push('scripts')
<script>
  (function () {
    let currentIndex = {{ $rows->count() }};

    window.addDetailRow = function () {
      const container = document.getElementById('detail-rows');
      if (!container) return;

      const idx = currentIndex++;
      const wrapper = document.createElement('div');
      wrapper.className = 'detail-row card';
      wrapper.dataset.rowIndex = idx;
      wrapper.style.marginBottom = '12px';

      wrapper.innerHTML = `
        <div class="form-grid">
          <div class="form-field">
            <label>Bahan Baku <span style="color:#ef4444">*</span></label>
            <select name="details[${idx}][bahan_baku_id]" class="form-input" required>
              <option value="">Pilih bahan…</option>
              @foreach ($opsiBahan as $id => $label)
                <option value="{{ $id }}">{{ $label }}</option>
              @endforeach
            </select>
            <div class="form-help"></div>
          </div>

          <div class="form-field">
            <label>Qty per porsi <span style="color:#ef4444">*</span></label>
            <input type="number" step="0.001"
                   name="details[${idx}][qty_per_porsi]"
                   class="form-input"
                   value=""
                   required>
            <div class="form-help">Isi sesuai satuan pakai (gram/ml/pcs).</div>
          </div>

          <div class="form-field span-2">
            <label>Keterangan</label>
            <textarea name="details[${idx}][keterangan]"
                      class="form-textarea"
                      rows="1"></textarea>
            <div class="form-help"></div>
          </div>
        </div>

        <button type="button"
                class="btn btn--outline-danger btn--sm"
                onclick="window.removeDetailRow(this)">
          Hapus baris
        </button>
      `;

      container.appendChild(wrapper);
    };

    window.removeDetailRow = function (btn) {
      const row = btn.closest('.detail-row');
      if (row) {
        row.remove();
      }
    };
  })();
</script>
@endpush
