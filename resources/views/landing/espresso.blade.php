@extends('layouts.main')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/espresso.css') }}">

<div class="espresso-card">
    <div class="espresso-bottle">
        <div class="espresso-fill" id="espresso-fill"></div>
        <div class="espresso-glass"></div>
    </div>

    <div class="espresso-info">
        <h2 id="espresso-count">{{ $fill }}/30 shot</h2>
        <p>Hari ini</p>
    </div>
</div>

<script>
    // Function buat ambil data terbaru dari backend
    async function fetchEspressoData() {
        const res = await fetch("{{ route('espresso.data') }}");
        const data = await res.json();

        // Update isi botol dan teksnya
        document.getElementById('espresso-fill').style.height = data.percent + '%';
        document.getElementById('espresso-count').innerText = Math.min(data.fill, 30) + '/30 shot';
    }

    // Jalankan pertama kali
    fetchEspressoData();

    // Update otomatis setiap 5 detik
    setInterval(fetchEspressoData, 5000);
</script>
@endsection
