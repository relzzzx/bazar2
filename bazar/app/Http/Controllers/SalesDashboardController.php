<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport; // nanti kita buat export class ini

class SalesDashboardController extends Controller
{
    public function index(Request $request)
{
    $section = $request->input('section'); // bisa null

    $query = Order::where('order_status', 'completed');

    if ($section) {
        $query->where(function($q) use ($section) {
            $q->where('section', $section)
              ->orWhere(function($q2) use ($section) {
                  $q2->where('section', 'multiple')
                     ->whereHas('items', function($q3) use ($section) {
                         $q3->where('section', $section);
                     });
              });
        });
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('customer_name', 'like', "%{$search}%")
              ->orWhere('order_code', 'like', "%{$search}%");
        });
    }

    $orders = $query->with('items.product')->orderBy('created_at', 'desc')->paginate(10);

    // Statistik per section
    $stats = [];
    foreach (['nasi_uduk', 'aneka_semur'] as $sec) {
        $stats[$sec] = Order::where('order_status', 'completed')
            ->where(function($q) use ($sec) {
                $q->where('section', $sec)
                  ->orWhere(function($q2) use ($sec) {
                      $q2->where('section', 'multiple')
                         ->whereHas('items', fn($q3) => $q3->where('section', $sec));
                  });
            })->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(total_price),0) as total_sales')
            ->first();
    }

    return view('sales.dashboard', compact('orders', 'stats'));
}




    public function export(Request $request)
    {
        $section = $request->input('section');

        return Excel::download(new SalesExport($section), 'sales-data.xlsx');
    }
}
