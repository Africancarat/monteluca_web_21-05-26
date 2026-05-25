<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyReferralBalanceRequest;
use App\Services\Referral\ReferralBalanceCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReferralBalanceController extends Controller
{
    public function __construct(
        protected ReferralBalanceCheckoutService $referralBalanceService
    ) {
        $this->middleware('auth');
        $this->middleware('localize');
    }

    public function apply(ApplyReferralBalanceRequest $request): JsonResponse
    {
        $result = $this->referralBalanceService->apply(
            Auth::user(),
            (float) $request->order_amount
        );

        return response()->json($result);
    }

    public function destroy(): JsonResponse
    {
        $this->referralBalanceService->remove(Auth::user());

        return response()->json([
            'success' => true,
            'message' => __('Referral balance removed'),
        ]);
    }
}
