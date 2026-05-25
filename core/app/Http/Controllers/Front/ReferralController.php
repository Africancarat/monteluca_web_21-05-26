<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyReferralRequest;
use App\Services\Referral\ReferralApplyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function __construct(
        protected ReferralApplyService $referralApplyService
    ) {
        $this->middleware('localize');
    }

    public function apply(ApplyReferralRequest $request): JsonResponse
    {
        $result = $this->referralApplyService->applyToSession(
            $request->referral_code,
            (float) $request->order_amount
        );

        return response()->json($result);
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->referralApplyService->clearSession();

        return response()->json([
            'success' => true,
            'message' => __('Referral code removed'),
        ]);
    }
}
