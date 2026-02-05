@php
  /** @var \App\Models\PembelianBahan $row */

  $valTanggal = old('tanggal', optional($row->tanggal)->format('Y-m-d\TH:i'));

  // siapkan data detail: kalau ada old() pakai itu, kalau tidak pakai dari relasi, kalau kosong minimal 1 row
  $details = old('details');

  if (! is_array($details)) {
      if (isset($row) && $row->relationLoaded('details') && $row->details->count()) {
          $details = $row->details->map(function ($d) {
              return [
                  'bahan_baku_id' => $d->bahan_baku_id,
                  'nama_bahan'    => $d->nama_bahan,
                  'qty_beli'      => $d->qty_beli,
                  'harga_satuan'  => $d->harga_satuan,
                  'expired_date'  => optional($d->expired_date)->format('Y-m-d'),
                  'subtotal'      => $d->subtotal,
              ];
          })->toArray();
      } else {
          $details = [
              [
                  'bahan_baku_id' => null,
                  'nama_bahan'    => '',
                  'qty_beli'      => 1,
                  'harga_satuan'  => 0,
                  'expired_date'  => null,
                  'subtotal'      => 0,
              ],
          ];
      }
  }

  $existingFile = $row->bukti_file ?? null;
@endphp

@if ($errors->any())
  <div class="form-section" style="border-color:#fecaca;background:#fff1f2;">
    <strong>Periksa kembali:</strong>
    <ul style="margin:6px 0 0 18px;">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST"
      action="{{ $mode === 'create'
          ? route('pembelian-bahan.store')
          : route('pembelian-bahan.update', $row) }}"
      class="form"
      enctype="multipart/form-data">
  @csrf
  @if ($mode === 'edit')
    @method('PUT')
  @endif

  {{-- INFO PEMBELIAN --}}
  <div class="form-section">
    <div class="form-title">Info Pembelian</div>

    <div class="form-grid">
      {{-- Kode Pembelian (readonly, auto dari model) --}}
      <div class="form-field">
        <label>Kode Pembelian</label>
        <div class="field-with-icon">
          <input class="form-input"
                 type="text"
                 value="{{ $row->kode_pembelian ?? 'Auto' }}"
                 disabled>
          <span class="field-icon">
            <x-heroicon-o-hashtag class="hi hi-5" />
          </span>
        </div>
        <div class="form-help">Kode akan diisi otomatis (format PBL-YYMMDD-####).</div>
      </div>

      {{-- Tanggal --}}
      <div class="form-field">
        <label>Tanggal</label>
        <div class="field-with-icon">
          <input class="form-input"
                 type="datetime-local"
                 name="tanggal"
                 value="{{ $valTanggal }}">
          <span class="field-icon">
            <x-heroicon-o-calendar-days class="hi hi-5" />
          </span>
        </div>
        <div class="form-help"></div>
      </div>

      {{-- Supplier --}}
      <div class="form-field">
        <label>Supplier</label>
        <div class="field-with-icon">
          <input class="form-input"
                 name="supplier_nama"
                 value="{{ old('supplier_nama', $row->supplier_nama ?? '') }}">
          <span class="field-icon">
            <x-heroicon-o-truck class="hi hi-5" />
          </span>
        </div>
        <div class="form-help"></div>
      </div>

      {{-- Kontak --}}
      <div class="form-field">
        <label>Kontak Supplier</label>
        <div class="field-with-icon">
          <input class="form-input"
                 name="supplier_kontak"
                 value="{{ old('supplier_kontak', $row->supplier_kontak ?? '') }}">
          <span class="field-icon">
            <x-heroicon-o-phone class="hi hi-5" />
          </span>
        </div>
        <div class="form-help"></div>
      </div>

      {{-- Total (readonly) --}}
      <div class="form-field">
        <label>Total</label>
        <div class="field-with-icon">
          @php $total = (float) (old('total', $row->total ?? 0)); @endphp
          <input class="form-input"
                 type="text"
                 value="{{ $total > 0 ? 'Rp '.number_format($total, 0, ',', '.') : '-' }}"
                 disabled>
          <span class="field-icon">
            <x-heroicon-o-banknotes class="hi hi-5" />
          </span>
        </div>
        <div class="form-help">Total akan dihitung dari detail pembelian.</div>
      </div>

      {{-- Catatan (1 line) - sekarang sejajar dengan Total --}}
      <div class="form-field">
        <label>Catatan</label>
        <div class="field-with-icon">
          <input class="form-input"
                 type="text"
                 name="catatan"
                 value="{{ old('catatan', $row->catatan ?? '') }}"
                 placeholder="Contoh: pembelian gula untuk stok minggu ini">
          <span class="field-icon">
            <x-heroicon-o-pencil-square class="hi hi-5" />
          </span>
        </div>
        <div class="form-help"></div>
      </div>
    </div>
  </div>

  {{-- LAMPIRAN (UPLOAD NOTA) --}}
  <div class="form-section" style="padding-bottom:0;">
    <div class="form-title">Lampiran</div>
    <div class="form-grid">

      <div class="form-field span-2">
        <label>Bukti Pembelian (Nota)</label>

        @if(!empty($existingFile))
          <div style="margin:6px 0 10px 0;">
            <a class="btn btn--outline-coffee btn--sm"
               href="{{ asset('storage/'.$existingFile) }}"
               target="_blank" rel="noopener">
              Lihat nota yang tersimpan
            </a>
            <span class="muted" style="margin-left:8px;">{{ $existingFile }}</span>
          </div>
        @endif

        <div class="file-field" id="ff-bukti">
          <input
              id="bukti_file"
              class="file-input"
              type="file"
              name="bukti_file"
              accept=".jpg,.jpeg,.png,.pdf"
          >
          <label for="bukti_file" class="file-btn">Choose File</label>

          <span class="file-name {{ empty($existingFile) ? 'is-empty' : '' }}">
            {{ empty($existingFile) ? 'Belum ada file dipilih' : basename($existingFile) }}
          </span>

          <span class="file-icon">
            <x-heroicon-o-paper-clip class="hi hi-5" />
          </span>
        </div>

        <div class="form-help">
          Upload JPG/PNG/PDF. Kalau sudah ada file, akan tetap dipakai sampai kamu upload yang baru.
        </div>
      </div>

    </div>
  </div>

  {{-- DETAIL PEMBELIAN --}}
  <div class="form-section">
    <div class="form-title">Detail Pembelian</div>

    <div class="form-grid">
      <div class="form-field span-2">
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th style="width: 26%;">Bahan</th>
                <th style="width: 16%;">Qty</th>
                <th style="width: 18%;">Harga Satuan</th>
                <th style="width: 14%;">Expired</th>
                <th class="num" style="width: 18%;">Subtotal</th>
                <th style="width: 8%;"></th>
              </tr>
            </thead>
            <tbody id="details-body">
              @foreach ($details as $i => $detail)
                @php
                  $selBahan   = $detail['bahan_baku_id'] ?? null;
                  $qtyBeli    = $detail['qty_beli']      ?? 1;
                  $hargaSatuan= $detail['harga_satuan']  ?? 0;
                  $expired    = $detail['expired_date']  ?? null;
                  $subtotal   = $detail['subtotal']      ?? 0;
                @endphp
                <tr class="detail-row">
                  <td>
                    <select name="details[{{ $i }}][bahan_baku_id]" class="form-input">
                      <option value="">Pilih bahan…</option>
                      @foreach ($bahanOptions as $bahan)
                        <option value="{{ $bahan->id }}"
                          {{ (string)$selBahan === (string)$bahan->id ? 'selected' : '' }}>
                          {{ $bahan->kode_bahan }} — {{ $bahan->nama_bahan }}
                        </option>
                      @endforeach
                    </select>
                  </td>

                  <td>
                    <input class="form-input"
                           type="number"
                           step="0.001"
                           name="details[{{ $i }}][qty_beli]"
                           value="{{ $qtyBeli }}">
                  </td>

                  <td>
                    <input class="form-input"
                           type="number"
                           step="0.01"
                           name="details[{{ $i }}][harga_satuan]"
                           value="{{ $hargaSatuan }}">
                  </td>

                  <td>
                    <input class="form-input"
                           type="date"
                           name="details[{{ $i }}][expired_date]"
                           value="{{ $expired }}">
                  </td>

                  <td class="num">
                    <input class="form-input"
                           type="number"
                           step="0.01"
                           name="details[{{ $i }}][subtotal]"
                           value="{{ $subtotal }}"
                           readonly>
                  </td>

                  <td>
                    <button type="button"
                            class="btn btn--outline-danger btn--sm btn-detail-remove"
                            title="Hapus baris">
                      <x-heroicon-o-trash class="icon-aksi" />
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <button type="button"
                class="btn btn--outline-coffee"
                id="btn-add-detail"
                style="margin-top:8px;">
          + Tambah baris
        </button>
      </div>
    </div>
  </div>

  {{-- AKSI FORM --}}
  <div class="form-section" style="padding-bottom:0;">
    <div class="form-actions">
      <a href="{{ route('pembelian-bahan.index') }}" class="btn btn--danger">
        Batal
      </a>
      <button type="submit" class="btn btn--success">
        Simpan
      </button>
    </div>
  </div>
</form>

{{-- TEMPLATE BARIS DETAIL UNTUK JS --}}
<template id="detail-row-template">
  <tr class="detail-row">
    <td>
      <select name="details[__INDEX__][bahan_baku_id]" class="form-input">
        <option value="">Pilih bahan…</option>
        @foreach ($bahanOptions as $bahan)
          <option value="{{ $bahan->id }}">
            {{ $bahan->kode_bahan }} — {{ $bahan->nama_bahan }}
          </option>
        @endforeach
      </select>
    </td>

    <td>
      <input class="form-input"
             type="number"
             step="0.001"
             name="details[__INDEX__][qty_beli]"
             value="1">
    </td>

    <td>
      <input class="form-input"
             type="number"
             step="0.01"
             name="details[__INDEX__][harga_satuan]"
             value="0">
    </td>

    <td>
      <input class="form-input"
             type="date"
             name="details[__INDEX__][expired_date]"
             value="">
    </td>

    <td class="num">
      <input class="form-input"
             type="number"
             step="0.01"
             name="details[__INDEX__][subtotal]"
             value="0"
             readonly>
    </td>

    <td>
      <button type="button"
              class="btn btn--outline-danger btn--sm btn-detail-remove"
              title="Hapus baris">
        <x-heroicon-o-trash class="icon-aksi" />
      </button>
    </td>
  </tr>
</template>

@push('scripts')
<script>
(() => {
  // simple repeater: tambah / hapus baris detail
  const body      = document.getElementById('details-body');
  const tmpl      = document.getElementById('detail-row-template');
  const addBtn    = document.getElementById('btn-add-detail');
  let detailIndex = body ? body.querySelectorAll('.detail-row').length : 0;

  if (addBtn && body && tmpl) {
    addBtn.addEventListener('click', () => {
      const html = tmpl.innerHTML.replace(/__INDEX__/g, String(detailIndex));
      body.insertAdjacentHTML('beforeend', html);
      detailIndex++;
    });

    body.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-detail-remove');
      if (!btn) return;
      const row = btn.closest('.detail-row');
      if (row) {
        // minimal 1 baris, jangan dihapus semua
        if (body.querySelectorAll('.detail-row').length > 1) {
          row.remove();
        }
      }
    });
  }

  // UI file upload: update nama file
  const input = document.getElementById('bukti_file');
  const wrap  = document.getElementById('ff-bukti');

  if (input && wrap) {
    const nameEl = wrap.querySelector('.file-name');
    input.addEventListener('change', () => {
      const f = input.files && input.files[0] ? input.files[0].name : '';
      if (!nameEl) return;

      if (!f) {
        nameEl.textContent = 'Belum ada file dipilih';
        nameEl.classList.add('is-empty');
      } else {
        nameEl.textContent = f;
        nameEl.classList.remove('is-empty');
      }
    });
  }
})();
</script>
@endpush
