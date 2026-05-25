<?php

namespace App\Http\Controllers\Auth\User;

use App\{
    Models\User,
    Http\Controllers\Controller,
    Repositories\Both\ForgotRepository
};

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\PasswordResetRequest;
use Illuminate\Support\Facades\Session;

class ForgotController extends Controller
{

    /**
     * Constructor Method.
     *
     * @param  \App\Repositories\Both\ForgotRepository $repository
     *
     */
    public function __construct(ForgotRepository $repository)
    {
        $this->repository = $repository;
        $this->middleware('guest');
    }

    /**
     * Show the form for requesting forgot password.
     *
     * @return \Illuminate\Http\Response
     */
    public function showForm()
    {
        return view('user.auth.forgot');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forgot(ForgotPasswordRequest $request)
    {
      $email = $request->validated('email');

      if ($data = User::whereEmail($email)->first()){
        $this->repository->forgot($data,$request,'user');
        Session::flash('success',__('We Have Sent a Link To Your Account!. Please Check Your Email.'));
        return redirect()->back();
      }
      else{
        Session::flash('error',__('No Account Found With This Email.'));
        return redirect()->back();
      }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function showChangePassForm($token)
    {
      if($token){
        if( User::whereEmailToken($token)->exists() ){
          return view('user.auth.changepass',compact('token'));
        }
      }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changepass(PasswordResetRequest $request)
    {
      
        $data =  User::whereEmailToken($request->file_token)->first();
       
        $resp = $this->repository->updatePassword($data,$request,'user');
        if($resp['status']){
            return redirect($resp['redurect_url'])->withSuccess($resp['message']);
        }else{
            return redirect()->back()->withErrors($resp['message']);
        }

    }

}
