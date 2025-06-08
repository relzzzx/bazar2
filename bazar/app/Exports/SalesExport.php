<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesExport implements FromCollection, WithHeadings
{
    protected $section;

    public function __construct($section = null)
    {
        $this->section = $section;
    }

    public function collection()
    {
        $query = Order::where('status', 'completed');

        if ($this->section) {
            $query->where('section', $this->section);
        }

        return $query->select('customer_name', 'order_code', 'total_price', 'section')->get();
    }

    public function headings(): array
    {
        return [
            'Customer Name',
            'Order Code',
            'Total Price',
            'Section',
        ];
    }
}
