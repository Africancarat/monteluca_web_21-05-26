<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReferralCodeRequest;
use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Http\Request;

class ReferralCodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }

    public function index()
    {
        $datas = ReferralCode::with('owner')->orderByDesc('id')->get();

        return view('back.referral-code.index', compact('datas'));
    }

    public function create()
    {
        $users = User::orderBy('first_name')->get();

        return view('back.referral-code.create', compact('users'));
    }

    public function store(ReferralCodeRequest $request)
    {
        $input = $request->validated();
        $input['referral_code'] = strtoupper($input['referral_code']);

        ReferralCode::create($input);

        return redirect()->route('back.referral-code.index')
            ->withSuccess(__('Referral code created successfully.'));
    }

    public function edit(ReferralCode $referral_code)
    {
        $users = User::orderBy('first_name')->get();

        return view('back.referral-code.edit', [
            'data' => $referral_code,
            'users' => $users,
        ]);
    }

    public function update(ReferralCodeRequest $request, ReferralCode $referral_code)
    {
        $input = $request->validated();
        $input['referral_code'] = strtoupper($input['referral_code']);

        $referral_code->update($input);

        return redirect()->route('back.referral-code.index')
            ->withSuccess(__('Referral code updated successfully.'));
    }

    public function destroy(ReferralCode $referral_code)
    {
        $referral_code->delete();

        return redirect()->route('back.referral-code.index')
            ->withSuccess(__('Referral code deleted successfully.'));
    }

    public function status($id, $status)
    {
        ReferralCode::findOrFail($id)->update(['status' => (int) $status]);

        return redirect()->route('back.referral-code.index')
            ->withSuccess(__('Status updated successfully.'));
    }
}
