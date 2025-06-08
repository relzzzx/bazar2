<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // Tampilkan form konfirmasi pesanan (checkout)
    public function checkout(Request $request)
    {
        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return redirect('/')->with('error', 'Keranjang kosong!');
        }

        // Flatten cart
        $flatCart = [];
        $sectionTotals = []; // <== Tambahan

        foreach ($cart as $section => $sectionItems) {
            foreach ($sectionItems as $productId => $item) {
                // Tambahkan key unik
                $key = $section . '_' . $productId;

                // Simpan item dengan info section
                $flatCart[$key] = $item;

                // Hitung total per section
                if (!isset($sectionTotals[$section])) {
                    $sectionTotals[$section] = 0;
                }
                $sectionTotals[$section] += $item['price'] * $item['quantity'];
            }
        }

        // Hitung total seluruh cart
        $total = array_sum($sectionTotals);

        // Ambil nama section unik
        $sections = array_keys($sectionTotals);

        return view('order.checkout', [
            'cart' => $flatCart,
            'total' => $total,
            'sections' => $sections,
            'sectionTotals' => $sectionTotals, // <== Kirim ke view
        ]);
    }

    // Proses simpan order
    public function placeOrder(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $bundlingCart = $request->session()->get('bundling_cart', []);
        $mergedCart = array_merge_recursive($cart, $bundlingCart);

        if (empty($mergedCart)) {
            return redirect('/')->with('error', 'Keranjang kosong!');
        }

        // Validasi customer_name dan payment_proof array per booth
        $rules = ['customer_name' => 'required|string'];
        foreach ($mergedCart as $section => $items) {
            $rules["payment_proof.$section"] = 'required|image|mimes:jpeg,png,jpg|max:2048';
        }
        $request->validate($rules);

        // Flatten cart
        $flatCart = [];
        foreach ($mergedCart as $section => $sectionItems) {
            foreach ($sectionItems as $productId => $item) {
                $flatCart[$section . '_' . $productId] = $item;
            }
        }

        // Hitung total dan booth terlibat
        $total = collect($flatCart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $sections = collect($flatCart)->pluck('section')->unique();
        $section = $sections->count() > 1 ? 'multiple' : $sections->first();

        $orderCode = strtoupper(Str::random(8));

        // Simpan order utama
        $order = Order::create([
            'customer_name'      => $request->customer_name,
            'order_code'         => $orderCode,
            'section'            => $section,
            'total_price'        => $total,
            'payment_status'     => 'pending',
            'order_status'       => 'pending',
            'payment_proof_path' => null, // Diisi nanti dengan JSON
        ]);

        // Simpan setiap bukti pembayaran per booth
        $proofPaths = [];
        foreach ($sections as $sec) {
            if ($request->hasFile("payment_proof.$sec")) {
                $file = $request->file("payment_proof.$sec");
                $filename = $sec . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('payment_proofs', $filename, 'public');
                $proofPaths[$sec] = $filename;
            }
        }

        // Update order dengan semua path bukti pembayaran (disimpan JSON)
        $order->update([
            'payment_proof_path' => json_encode($proofPaths),
        ]);

        // Simpan item pesanan dan update stok
        foreach ($flatCart as $compositeKey => $attrs) {
            // Pisahkan section dan productId dari key
            $parts = explode('_', $compositeKey);
            $realProductId = end($parts); // Ambil bagian terakhir sebagai ID produk

            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => (int) $realProductId,  // pastikan integer
                'quantity'   => $attrs['quantity'],
                'price'      => $attrs['price'],
            ]);

            $product = Product::find($realProductId);
            if ($product) {
                $product->stock -= $attrs['quantity'];
                $product->save();
            }
        }

        // Kosongkan session
        $request->session()->forget('cart');
        $request->session()->forget('bundling_cart');
        $request->session()->put('order_code', $orderCode);

        return redirect()->route('order.my_orders')->with('success', 'Pesanan berhasil dibuat. Simpan kode pesanan kamu untuk cek status!');
    }

    // Halaman “Pesanan Saya”
    public function myOrders(Request $request)
    {
        $orderCode = $request->session()->get('order_code');

        if (!$orderCode) {
            return redirect('/')->with('error', 'Tidak ada pesanan yang bisa ditampilkan. Silakan buat pesanan terlebih dahulu.');
        }

        $order = Order::whereRaw('LOWER(order_code) = ?', [strtolower($orderCode)])->first();

        if (!$order) {
            return redirect('/')->with('error', 'Pesanan tidak ditemukan.');
        }

        $orders = collect([$order]);

        return view('order.my_orders', compact('orders'));
    }
}
