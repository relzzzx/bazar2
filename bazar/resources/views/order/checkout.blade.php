@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-4 text-center">Konfirmasi Pesanan</h1>

    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-md mx-auto border rounded-lg p-6">
        <form action="{{ route('order.place') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Input Nama Pembeli --}}
            <div class="mb-4">
                <label for="customer_name" class="block font-medium">Nama Pembeli</label>
                <input type="text" name="customer_name" id="customer_name" class="border rounded w-full p-2" required>
            </div>

            {{-- Daftar Produk --}}
            <div class="mb-4">
                <label class="block font-medium">
                    Produk (section:
                    @if (count($sections) > 1)
                        Multiple Booth
                    @else
                        {{ ucfirst(str_replace('_', ' ', $sections[0])) }}
                    @endif
                    )
                </label>
                <ul class="list-disc list-inside">
                    @foreach ($cart as $item)
                        <li>{{ $item['name'] }} × {{ $item['quantity'] }} → Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</li>
                    @endforeach
                </ul>
            </div>

            {{-- Total Keseluruhan --}}
            <div class="mb-4">
                <p class="font-semibold">Total: Rp {{ number_format($total, 0, ',', '.') }}</p>
            </div>

            {{-- Rincian Transfer per Booth --}}
            @foreach ($sectionTotals as $section => $totalPerSection)
                @if ($section !== 'multiple')
                    <div class="mb-2">
                        <p class="text-sm">
                            Transfer ke <strong>{{ ucfirst(str_replace('_', ' ', $section)) }}</strong> sebesar:
                            <strong>Rp {{ number_format($totalPerSection, 0, ',', '.') }}</strong>
                        </p>
                    </div>
                @endif
            @endforeach

            {{-- Pesan khusus untuk multiple booth --}}
            @if (count($sections) > 1)
                <div class="mb-4 p-4 bg-yellow-100 border border-yellow-300 rounded text-yellow-800 text-center">
                    <strong>Perhatian:</strong> Karena Anda memesan dari <em>multiple booth</em>, Anda harus melakukan <u>pembayaran dua kali</u>, yaitu transfer ke masing-masing QRIS sesuai booth yang tertera di bawah dengan jumlah yang sudah dihitung per booth.
                </div>
            @endif

            {{-- Gambar QRIS --}}
            <div class="my-6">
                <h2 class="text-lg font-semibold mb-2 text-center">Scan QRIS:</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-center">
                    @foreach ($sections as $section)
                        @if ($section !== 'multiple')
                            <div class="border p-4 rounded">
                                <h3 class="font-semibold capitalize mb-2">{{ str_replace('_', ' ', $section) }}</h3>
                                <img src="{{ asset('storage/qris/' . $section . '.jpeg') }}"
                                     alt="QRIS {{ $section }}"
                                     class="w-full max-w-xs mx-auto">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Upload Bukti Pembayaran --}}
            <div class="mb-4">
                <label class="block font-medium">
                    Upload Bukti Pembayaran
                    @if (count($sections) > 1)
                        (masing-masing booth)
                    @endif
                </label>

                @foreach ($sections as $index => $section)
                    @if ($section !== 'multiple')
                        <div class="mb-2">
                            <label class="block text-sm font-medium mb-1">
                                Bukti Pembayaran untuk {{ str_replace('_', ' ', $section) }}
                            </label>
                            <input type="file"
                                   name="payment_proof[{{ $section }}]"
                                   accept="image/*"
                                   class="border rounded w-full"
                                   required>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Tombol Submit --}}
            <div class="text-center">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Kirim Pesanan & Bukti</button>
            </div>
        </form>
    </div>
</div>
@endsection
