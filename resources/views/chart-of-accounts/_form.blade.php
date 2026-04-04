@php
  $selName  = old('name',        $row->name        ?? '');
  $selType  = old('type',        $row->type        ?? '');
  $selNorm  = old('normal_side', $row->normal_side ?? '');
  $selAct   = old('is_active',   isset($row) ? (bool) $row->is_active : true);
@endphp

{{-- ERROR --}}
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
        ? route('chart-of-accounts.store')
        : route('chart-of-accounts.update', $row) }}"
      class="form">
  @csrf
  @if($mode === 'edit') @method('PUT') @endif

  <div class="form-section">
    <div class="form-title">Data Akun</div>

    <div class="form-grid">

      {{-- KODE --}}
      <div class="form-field">
        <label>Kode Akun <span style="color:#ef4444">*</span></label>
        <div class="field-with-icon">
          <input
            class="form-input"
            name="code"
            value="{{ old('code', $row->code ?? '') }}"
            placeholder="Misal: 1001"
            maxlength="20"
            required
          >
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-5"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
          </span>
        </div>
      </div>

      {{-- NAMA AKUN --}}
      <div class="form-field">
        <label>Nama Akun <span style="color:#ef4444">*</span></label>
        <div class="dd" data-select="name">
          <button type="button" class="dd-toggle">
            <span class="dd-label">
              {{ $selName ? ($opsiAccountNames[$selName] ?? $selName) : 'Pilih nama akun' }}
            </span>
            <span class="dd-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-4"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
            </span>
          </button>

          <div class="dd-menu">
            <div class="dd-item {{ $selName==='' ? 'active':'' }}" data-value="">Pilih nama akun</div>
            @foreach($opsiAccountNames as $k => $label)
              <div class="dd-item {{ $selName===$k ? 'active':'' }}" data-value="{{ $k }}">
                {{ $label }}
              </div>
            @endforeach
          </div>

          <input type="hidden" name="name" value="{{ $selName }}" required>
        </div>
      </div>

      {{-- TIPE --}}
      <div class="form-field">
        <label>Tipe Akun <span style="color:#ef4444">*</span></label>
        <div class="dd" data-select="type">
          <button type="button" class="dd-toggle">
            <span class="dd-label">
              {{ $selType ? ($opsiType[$selType] ?? ucfirst($selType)) : 'Pilih tipe akun' }}
            </span>
            <span class="dd-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-4"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
            </span>
          </button>

          <div class="dd-menu">
            <div class="dd-item {{ $selType==='' ? 'active':'' }}" data-value="">Pilih tipe akun</div>
            @foreach($opsiType as $k => $label)
              <div class="dd-item {{ $selType===$k ? 'active':'' }}" data-value="{{ $k }}">
                {{ $label }}
              </div>
            @endforeach
          </div>

          <input type="hidden" name="type" value="{{ $selType }}" required>
        </div>
      </div>

      {{-- SALDO NORMAL --}}
      <div class="form-field">
        <label>Saldo Normal <span style="color:#ef4444">*</span></label>
        <div class="dd" data-select="normal_side">
          <button type="button" class="dd-toggle">
            <span class="dd-label">
              {{ $selNorm ? ($opsiNormalSide[$selNorm] ?? ucfirst($selNorm)) : 'Pilih saldo normal' }}
            </span>
            <span class="dd-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-4"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
            </span>
          </button>

          <div class="dd-menu">
            <div class="dd-item {{ $selNorm==='' ? 'active':'' }}" data-value="">Pilih saldo normal</div>
            @foreach($opsiNormalSide as $k => $label)
              <div class="dd-item {{ $selNorm===$k ? 'active':'' }}" data-value="{{ $k }}">
                {{ $label }}
              </div>
            @endforeach
          </div>

          <input type="hidden" name="normal_side" value="{{ $selNorm }}" required>
        </div>
      </div>

      {{-- AKTIF --}}
      <div class="form-check">
        <label class="form-switch">
          <input type="checkbox" name="is_active" value="1" {{ $selAct ? 'checked' : '' }}>
          <span class="form-switch-track">
            <span class="form-switch-thumb"></span>
          </span>
          <span class="form-switch-label">Aktif</span>
        </label>
      </div>

    </div>
  </div>

  <div class="form-section" style="padding-bottom:0;">
    <div class="form-actions">
      <a class="btn btn--danger" href="{{ route('chart-of-accounts.index') }}">Batal</a>
      <button type="submit" class="btn btn--success">Simpan</button>
    </div>
  </div>
</form>

@push('scripts')
<script>
(() => {
  const closeAll = () => document.querySelectorAll('.dd.open').forEach(dd => dd.classList.remove('open'));

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
})();
</script>
@endpush
