<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    public function index()
{
    $cart = session('cart', []);
    $total = 0;

    foreach ($cart as $section => $items) {
        if (!is_array($items)) continue;

        foreach ($items as $item) {
            if (!is_array($item)) continue;
            $total += $item['price'] * $item['quantity'];
        }
    }

    // Definisikan bundling secara manual (ID produk)
    $bundlings = [
        'Bundling 1' => [1, 3],
        'Bundling 2' => [1, 4],
        'Bundling 3' => [1, 5],
    ];

    return view('cart.index', compact('cart', 'total', 'bundlings'));
}




    public function add(Request $request, $productId)
{
    $product  = Product::findOrFail($productId);
    $section  = $product->section;

    // Ambil quantity dari form, fallback 1, pastikan integer ≥ 1
    $qty = (int) $request->input('quantity', 1);
    if ($qty < 1) { $qty = 1; }

    // (Opsional) Validasi stok
    if ($qty > $product->stock) {
        return redirect()->back()->with('error', 'Stok tidak mencukupi.');
    }

    $cart = session()->get('cart', []);

    if (!isset($cart[$section])) {
        $cart[$section] = [];
    }

    if (isset($cart[$section][$productId])) {
        $cart[$section][$productId]['quantity'] += $qty;   // ⬅️ tambahkan qty
    } else {
        $cart[$section][$productId] = [
            'name'       => $product->name,
            'price'      => $product->price,
            'quantity'   => $qty,                          // ⬅️ simpan qty
            'image_path' => $product->image_path,
            'section'    => $section,
        ];
    }

    session(['cart' => $cart]);

    return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
}


    public function update(Request $request, $section, $productId)
    {
        $cart = $request->session()->get('cart', []);

        if (isset($cart[$section][$productId])) {
            $cart[$section][$productId]['quantity'] = $request->input('quantity', 1);
            $request->session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Jumlah produk diperbarui.');
    }

    public function remove(Request $request, $section, $productId)
    {
        $cart = $request->session()->get('cart', []);

        if (isset($cart[$section][$productId])) {
            unset($cart[$section][$productId]);

            // Hapus section jika kosong
            if (empty($cart[$section])) {
                unset($cart[$section]);
            }

            $request->session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Produk dihapus dari keranjang.');
    }

    public function clear(Request $request)
    {
        $request->session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'Keranjang dikosongkan.');
    }

    public function addBundling(Request $request)
{
    $productIds = explode(',', $request->product_ids);
    $cart = session()->get('cart', []);

    foreach ($productIds as $productId) {
        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        $section = $product->section; // ✅ pakai section asli per produk

        if (!isset($cart[$section])) {
            $cart[$section] = [];
        }

        if (isset($cart[$section][$productId])) {
            $cart[$section][$productId]['quantity']++;
        } else {
            $cart[$section][$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image_path' => $product->image_path,
                'section' => $section // tetap simpan section asli
            ];
        }
    }

    session(['cart' => $cart]);

    return redirect()->back()->with('success', 'Bundling berhasil ditambahkan ke keranjang!');
}


}
