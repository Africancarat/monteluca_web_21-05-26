<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\ReferralTransaction;

class ReferralTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }

    public function index()
    {
        $datas = ReferralTransaction::with(['referralCode', 'order', 'customer', 'referrer'])
            ->orderByDesc('id')
            ->paginate(25);

        return view('back.referral-transaction.index', compact('datas'));
    }
}
