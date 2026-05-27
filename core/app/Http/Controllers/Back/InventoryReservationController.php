<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\InventorySoftLock;
use Illuminate\Http\Request;

class InventoryReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }

    public function release(int $id)
    {
        $lock = InventorySoftLock::findOrFail($id);

        if ($lock->status !== InventorySoftLock::STATUS_RESERVED) {
            return back()->withError(__('Reservation is not active or was already released.'));
        }

        $lock->update(['status' => InventorySoftLock::STATUS_RELEASED]);

        return back()->withSuccess(__('Reservation released successfully.'));
    }

    public function index(Request $request)
    {
        $status = $request->get('status', InventorySoftLock::STATUS_RESERVED);

        $query = InventorySoftLock::query()
            ->with(['item:id,name,sku', 'user:id,first_name,email'])
            ->orderByDesc('expires_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $locks = $query->paginate(50)->withQueryString();

        return view('back.inventory.reservations', [
            'locks' => $locks,
            'status' => $status,
        ]);
    }
}
