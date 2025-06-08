<nav class="bg-[#153448] text-[#dfd1b7] shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <a href="{{ route('home') }}" class="text-2xl font-bold tracking-wide hover:text-[#dfd1b7]">
            Bazar SMKN 8
        </a>

        <div class="space-x-4 text-sm font-medium">
            <a href="{{ route('home') }}" class="hover:text-[#948878] transition">Home</a>
            <a href="{{ route('cart.index') }}" class="hover:text-[#948878] transition">Keranjang</a>
            <a href="{{ route('order.my_orders') }}" class="hover:text-[#948878] transition">Pesanan Saya</a>

            @auth
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#948878] transition">Admin Dashboard</a>
                    <a href="{{ route('admin.orders.create_offline') }}" class="hover:text-[#948878] transition">Input Kasir</a>
                    <a href="{{ route('sales.dashboard') }}" class="px-3 py-2 rounded hover:bg-gray-200">Dashboard Penjualan</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-[#948878] transition">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hover:text-[#948878] transition">Login</a>
            @endauth
        </div>
    </div>
</nav>
