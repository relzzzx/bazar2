@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Dashboard Penjualan</h1>

    {{-- Filter & Search --}}
    <form method="GET" action="{{ route('sales.dashboard') }}" class="mb-4 flex flex-wrap gap-4 items-center">
        <div>
            <label for="section" class="block font-medium">Filter Section</label>
            <select name="section" id="section" class="border rounded p-2">
                <option value="">-- Semua Section --</option>
                <option value="nasi_uduk" {{ request('section') == 'nasi_uduk' ? 'selected' : '' }}>Nasi Uduk</option>
                <option value="aneka_semur" {{ request('section') == 'aneka_semur' ? 'selected' : '' }}>Aneka Semur</option>
            </select>
        </div>

        <div>
            <label for="search" class="block font-medium">Cari</label>
            <input type="text" name="search" id="search" placeholder="Nama pembeli atau kode order" value="{{ request('search') }}" class="border rounded p-2">
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
            <a href="{{ route('sales.dashboard.export', request()->query()) }}" class="ml-2 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Export Excel</a>
        </div>
    </form>

    {{-- Statistik --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['nasi_uduk', 'aneka_semur'] as $sec)
            <div class="border rounded p-4 shadow">
                <h2 class="font-semibold text-lg mb-2">{{ ucfirst(str_replace('_', ' ', $sec)) }}</h2>
                <p>Total Pesanan: {{ $stats[$sec]->total_orders ?? 0 }}</p>
                <p>Total Penjualan: Rp {{ number_format($stats[$sec]->total_sales ?? 0, 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>

    {{-- Tabel Pesanan --}}
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 rounded">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-4 py-2 text-left">Nama Pembeli</th>
                    <th class="border px-4 py-2 text-left">Kode Pesanan</th>
                    <th class="border px-4 py-2 text-left">Section</th>
                    <th class="border px-4 py-2 text-right">Total Harga</th>
                    <th class="border px-4 py-2 text-left">Detail Pesanan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="border px-4 py-2">{{ $order->customer_name }}</td>
                        <td class="border px-4 py-2">{{ $order->order_code }}</td>
                        <td class="border px-4 py-2">
                            {{-- Gabungkan semua section unik di order items --}}
                            {{ $order->items->pluck('section')->unique()->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))->join(', ') }}
                        </td>
                        <td class="border px-4 py-2 text-right">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                        <td class="border px-4 py-2">
                            <ul class="list-disc ml-5">
                                @foreach ($order->items as $item)
                                    <li>
                                        {{ $item->product->name ?? 'Produk tidak ditemukan' }} -
                                        Qty: {{ $item->quantity }} -
                                        Harga: Rp {{ number_format($item->price, 0, ',', '.') }} -
                                        Section: {{ ucfirst(str_replace('_', ' ', $item->section)) }}
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="border px-4 py-2 text-center" colspan="5">Belum ada data pesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
