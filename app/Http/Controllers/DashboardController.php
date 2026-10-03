<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $stats = match (true) {
            $user->isAdmin() => [
                'Total produk'      => Product::count(),
                'Total pengguna'    => User::count(),
                'Total pesanan'     => Order::count(),
                'Pendapatan (lunas)' => 'Rp '.number_format((float) Payment::where('status', 'paid')->sum('amount'), 0, ',', '.'),
            ],
            $user->isEditor() => [
                'Produk saya'         => $user->products()->count(),
                'Stok menipis (< 10)' => $user->products()->where('stock', '<', 10)->count(),
            ],
            default => [
                'Pesanan saya'        => $user->orders()->count(),
                'Menunggu pembayaran' => $user->orders()->status('pending')->count(),
            ],
        };

        return view('dashboard', compact('stats'));
    }
}
