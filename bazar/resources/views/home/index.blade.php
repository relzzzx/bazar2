@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-bold mb-6 text-center text-[#153448]">Bazar SMKN 8 Jakarta</h1>

    {{-- Menu Utama --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-10">
        <a href="{{ route('home.section', 'nasi_uduk') }}" class="w-full bg-[#f5f0e1] border border-[#3c5b6f] rounded-xl shadow-sm hover:shadow-md hover:bg-[#eae1cf] transition duration-200 overflow-hidden">
            <img src="{{ asset('storage/nasiuduk.jpg') }}" alt="Nasi Uduk" class="w-full h-40 object-cover">
            <div class="p-3 text-center">
                <h2 class="text-base font-semibold text-[#153448]">Nasi Uduk</h2>
            </div>
        </a>
        <a href="{{ route('home.section', 'aneka_semur') }}" class="w-full bg-[#f5f0e1] border border-[#3c5b6f] rounded-xl shadow-sm hover:shadow-md hover:bg-[#eae1cf] transition duration-200 overflow-hidden">
            <img src="{{ asset('storage/semur.jpg') }}" alt="Aneka Semur" class="w-full h-40 object-cover">
            <div class="p-3 text-center">
                <h2 class="text-base font-semibold text-[#153448]">Aneka Semur</h2>
            </div>
        </a>
    </div>

    {{-- Menu Bundling --}}
    <h2 class="text-xl font-bold mb-4 text-[#153448] text-center">Menu Bundling</h2>
    <div class="flex justify-center">
        <a href="{{ route('home.section', 'multiple') }}" class="w-full max-w-sm bg-[#f1e5c6] border border-[#3c5b6f] rounded-xl shadow-sm hover:shadow-md hover:bg-[#f6ead7] transition duration-200 overflow-hidden">
            <img src="{{ asset('storage/bundling.jpg') }}" alt="Bundling Menu" class="w-full h-40 object-cover">
            <div class="p-3 text-center">
                <h3 class="text-base font-semibold text-[#153448]">Bundling Menu</h3>
                <p class="text-sm text-gray-700">Gabungan Nasi Uduk & Aneka Semur</p>
            </div>
        </a>
    </div>
</div>
@endsection
