<script>
  (function () {
    if (window.KasirRawBTReceipt && window.KasirRawBTReceipt.cetakRawBT) {
      window.cetakRawBT = window.KasirRawBTReceipt.cetakRawBT;
      return;
    }

    const RECEIPT_WIDTH = 32;
    const fallbackMessage = 'RawBT tidak terbuka. Pastikan RawBT sudah terinstall, printer Bluetooth sudah pairing, dan printer sudah diset di RawBT.';

    function isAndroidDevice() {
      return /Android/i.test(navigator.userAgent || '');
    }

    function cleanText(text = '') {
      return String(text || '')
        .replace(/\r/g, '')
        .replace(/[^\S\n]+/g, ' ')
        .trim();
    }

    function formatRupiah(value) {
      const number = Number(value || 0);
      return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
    }

    function padRight(text = '', length = RECEIPT_WIDTH, char = ' ') {
      const safeText = cleanText(text);
      if (safeText.length >= length) return safeText.substring(0, length);
      return safeText + String(char).repeat(length - safeText.length);
    }

    function padLeft(text = '', length = RECEIPT_WIDTH, char = ' ') {
      const safeText = cleanText(text);
      if (safeText.length >= length) return safeText.substring(safeText.length - length);
      return String(char).repeat(length - safeText.length) + safeText;
    }

    function centerText(text = '', width = RECEIPT_WIDTH) {
      const safeText = cutText(text, width);
      const left = Math.max(0, Math.floor((width - safeText.length) / 2));
      return ' '.repeat(left) + safeText;
    }

    function line(width = RECEIPT_WIDTH) {
      return '-'.repeat(width);
    }

    function cutText(text = '', maxLength = RECEIPT_WIDTH) {
      const safeText = cleanText(text);
      return safeText.length > maxLength ? safeText.substring(0, maxLength) : safeText;
    }

    function pairText(left = '', right = '', width = RECEIPT_WIDTH) {
      const safeRight = cleanText(right);
      const safeLeft = cleanText(left);
      const rightWidth = Math.min(width - 1, safeRight.length);
      const leftWidth = Math.max(1, width - rightWidth);

      return padRight(safeLeft, leftWidth) + padLeft(safeRight, rightWidth);
    }

    function labelText(label = '', value = '', width = RECEIPT_WIDTH) {
      const prefix = padRight(cutText(label, 6), 6) + ': ';
      return prefix + cutText(value || '-', Math.max(1, width - prefix.length));
    }

    function wrapText(text = '', width = RECEIPT_WIDTH) {
      const words = cleanText(text).split(/\s+/).filter(Boolean);
      const rows = [];
      let current = '';

      words.forEach(function (word) {
        if (word.length > width) {
          if (current) {
            rows.push(current);
            current = '';
          }

          for (let i = 0; i < word.length; i += width) {
            rows.push(word.substring(i, i + width));
          }
          return;
        }

        const next = current ? current + ' ' + word : word;
        if (next.length > width) {
          rows.push(current);
          current = word;
        } else {
          current = next;
        }
      });

      if (current) rows.push(current);
      return rows.length ? rows : ['-'];
    }

    function formatReceiptItem(item, width = RECEIPT_WIDTH) {
      const qty = Number(item && item.qty ? item.qty : 1);
      const price = Number(item && item.price ? item.price : 0);
      const total = Number(item && item.total != null ? item.total : price * qty);
      const name = item && item.name ? item.name : '-';
      const rows = wrapText(name, width);

      rows.push(pairText(qty + ' x ' + formatRupiah(price), formatRupiah(total), width));
      return rows.join('\n');
    }

    function buildThermalReceipt(dataTransaksi) {
      const width = RECEIPT_WIDTH;
      const data = dataTransaksi || {};
      const outletName = data.outlet_name || data.outletName || 'Nama Outlet';
      const outletAddress = data.outlet_address || data.outletAddress || '';
      const receiptTitle = data.receipt_title || data.receiptTitle || 'STRUK PEMBAYARAN';
      const code = data.transaction_code || data.transactionCode || data.booking_code || data.code || '-';
      const date = data.date || data.created_at || data.createdAt || '-';
      const customer = data.customer_name || data.customerName || '-';
      const staff = data.staff_name || data.staffName || '-';
      const paymentMethod = data.payment_method || data.paymentMethod || '-';
      const paymentStatus = data.payment_status || data.paymentStatus || '-';
      const subtotal = Number(data.subtotal || data.total || 0);
      const discount = Number(data.discount || 0);
      const discountPercent = Number(data.discount_percent || data.discountPercent || 0);
      const total = Number(data.total || subtotal);
      const bayar = Number(data.bayar || data.paid || 0);
      const kembalian = Number(data.kembalian || data.change || 0);
      const items = Array.isArray(data.items) ? data.items : [];

      const rows = [
        centerText(outletName, width),
        centerText(receiptTitle, width),
      ];

      if (outletAddress) rows.push(centerText(outletAddress, width));

      rows.push(
        line(width),
        labelText('No', code, width),
        labelText('Tgl', date, width),
        labelText('Cust', customer, width),
        labelText('Staff', staff, width),
        line(width)
      );

      if (items.length) {
        rows.push(items.map(function (item) {
          return formatReceiptItem(item, width);
        }).join('\n'));
      } else {
        rows.push('-');
      }

      rows.push(
        line(width),
        pairText('Subtotal', formatRupiah(subtotal), width)
      );

      if (discount > 0) {
        const discountLabel = discountPercent > 0
          ? 'Diskon ' + discountPercent.toLocaleString('id-ID') + '%'
          : 'Diskon';
        rows.push(pairText(discountLabel, '-' + formatRupiah(discount), width));
      }

      rows.push(pairText('Total', formatRupiah(total), width));

      if (bayar > 0 || kembalian > 0) {
        rows.push(pairText('Bayar', formatRupiah(bayar), width));
        rows.push(pairText('Kembalian', formatRupiah(kembalian), width));
      }

      rows.push(
        labelText('Metode', paymentMethod, width),
        labelText('Status', paymentStatus, width)
      );

      if (data.due_date || data.dueDate) rows.push(labelText('Tempo', data.due_date || data.dueDate, width));

      rows.push(
        line(width),
        centerText('Terima kasih', width),
        centerText('atas kunjungan Anda', width),
        ''
      );

      return rows.join('\n').replace(/\n+$/g, '\n');
    }

    function cetakRawBT(dataTransaksi, options = {}) {
      const setStatus = typeof options.onStatus === 'function' ? options.onStatus : function () {};

      try {
        const receiptText = buildThermalReceipt(dataTransaksi);
        const encodedText = encodeURIComponent(receiptText);
        let openedRawBT = false;

        function markOpened() {
          openedRawBT = true;
        }

        function markHidden() {
          if (document.hidden) openedRawBT = true;
        }

        function cleanup() {
          window.removeEventListener('blur', markOpened);
          window.removeEventListener('pagehide', markOpened);
          document.removeEventListener('visibilitychange', markHidden);
        }

        window.addEventListener('blur', markOpened);
        window.addEventListener('pagehide', markOpened);
        document.addEventListener('visibilitychange', markHidden);

        setStatus('Membuka RawBT...');
        window.location.href = 'rawbt:' + encodedText;

        window.setTimeout(function () {
          cleanup();

          if (!openedRawBT) {
            setStatus(fallbackMessage);
            alert(fallbackMessage);
            return;
          }

          setStatus('Struk dikirim ke RawBT.');
        }, 2000);
      } catch (error) {
        console.error('Gagal mencetak via RawBT:', error);
        setStatus('Gagal mencetak struk thermal.');
        alert('Gagal mencetak struk thermal. Pastikan RawBT sudah terinstall dan printer Bluetooth sudah terhubung.');
      }
    }

    window.cetakRawBT = cetakRawBT;
    window.KasirRawBTReceipt = {
      buildThermalReceipt: buildThermalReceipt,
      cetakRawBT: cetakRawBT,
      printWithRawBT: cetakRawBT,
      formatRupiah: formatRupiah,
      padRight: padRight,
      padLeft: padLeft,
      centerText: centerText,
      line: line,
      isAndroidDevice: isAndroidDevice,
      fallbackMessage: fallbackMessage
    };
  })();
</script>
