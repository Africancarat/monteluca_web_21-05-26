<?php

namespace App\Http\Controllers\User;

use App\{
    Http\Requests\ProfileUpdateRequest,
    Http\Requests\UserBillingAddressRequest,
    Http\Requests\UserShippingAddressRequest,
    Http\Controllers\Controller,
    Repositories\Front\UserRepository
};
use App\Helpers\ImageHelper;
use App\Models\Order;
use App\Models\ReferralCode;
use Illuminate\Http\Request;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AccountController extends Controller
{

    /**
     * Constructor Method.
     *
     * Setting Authentication
     *
     * @param  \App\Repositories\Back\UserRepository $repository
     *
     */
    public function __construct(UserRepository $repository)
    {
        $this->middleware('auth');
        $this->middleware('localize');
        $this->repository = $repository;
    }

    public function index()
    {
        $user = Auth::user();

        return view('user.dashboard.dashboard',[
            'allorders' => Order::whereUserId($user->id)->count(),
            'pending' => Order::whereUserId($user->id)->whereOrderStatus('pending')->count(),
            'progress' => Order::whereUserId($user->id)->whereOrderStatus('In Progress')->count(),
            'delivered' => Order::whereUserId($user->id)->whereOrderStatus('Delivered')->count(),
            'canceled' => Order::whereUserId($user->id)->whereOrderStatus('Canceled')->count(),
            'Manufacturing' => Order::whereUserId($user->id)->whereOrderStatus('Manufacturing')->count(),
            'shipped' => Order::whereUserId($user->id)->whereOrderStatus('shipped')->count(),
            'Refunded' => Order::whereUserId($user->id)->whereOrderStatus('Refunded')->count(),

            'assignedReferralCodes' => $user->referralCodes()
                ->where('status', ReferralCode::STATUS_ACTIVE)
                ->latest()
                ->get(),

        ]);

    }


    public function profile()
    {
        $user = Auth::user();
        $check_newsletter = Subscriber::where('email',$user->email)->exists();
        return view('user.dashboard.index',[
            'user' => $user,
            'check_newsletter' => $check_newsletter,
        ]);
    }



    public function profileUpdate(ProfileUpdateRequest $request)
    {   
     
        $this->repository->profileUpdate($request);
        Session::flash('success',__('Profile Updated Successfully.'));
        return redirect()->back();
    }

    public function addresses()
    {
        $user = Auth::user();
        return view('user.dashboard.address',[
            'user' => $user
        ]);
    }

    public function billingSubmit(UserBillingAddressRequest $request)
    {
        Auth::user()->update($request->safe()->only(UserBillingAddressRequest::ALLOWED_KEYS));
        Session::flash('success',__('Address update successfully'));
        return back();
    }

    public function shippingSubmit(UserShippingAddressRequest $request)
    {
        Auth::user()->update($request->safe()->only(UserShippingAddressRequest::ALLOWED_KEYS));
        Session::flash('success',__('Address update successfully'));
        return back();
    }


    public function removeAccount()
    {
        $user = User::where('id',Auth::user()->id)->first();
        ImageHelper::handleDeletedImage($user,'photo','assets/images/');
        $user->delete();
        Session::flash('success',__('Your account successfully remove'));
        return redirect(route('front.index'));
    }


}
