<?php

namespace App\Http\Controllers\Back;

use App\{
    Models\Admin,
    Repositories\Back\StaffRepository,
    Http\Requests\AdminRequest,
    Http\Controllers\Controller
};
use App\Support\ValidationRules;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Constructor Method.
     *
     * Setting Authentication
     *
     * @param  \App\Repositories\Back\StaffRepository $repository
     *
     */
    public function __construct(StaffRepository $repository)
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('back.staff.index',[
            'datas' => Admin::where('id','!=',1)->orderBy('id','desc')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('back.staff.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|unique:admins|email',
            'phone' => ValidationRules::phone('phone')['phone'],
            'password' => ValidationRules::strongPassword(),
            'role_id' => 'required',
            'photo' => 'required|image',
        ], [
            'phone.digits' => __('Phone number must contain exactly 10 digits.'),
            'password.mixed' => __('Password must contain uppercase and lowercase letters.'),
            'password.numbers' => __('Password must contain at least one number.'),
            'password.symbols' => __('Password must contain at least one symbol.'),
            'password.uncompromised' => __('This password has appeared in a data breach. Please choose a different password.'),
        ]);
        $this->repository->store($request);
        return redirect()->route('back.staff.index')->withSuccess(__('New User Added Successfully.'));
    }




    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Admin $staff)
    {
        $admin = $staff;
        return view('back.staff.edit',compact('admin'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Admin $staff)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|unique:admins,email,'.$staff->id,
            'phone' => ValidationRules::phone('phone')['phone'],
            'password' => ValidationRules::strongPassword(false),
            'role_id' => 'required',
            'photo' => 'image',
        ], [
            'phone.digits' => __('Phone number must contain exactly 10 digits.'),
            'password.mixed' => __('Password must contain uppercase and lowercase letters.'),
            'password.numbers' => __('Password must contain at least one number.'),
            'password.symbols' => __('Password must contain at least one symbol.'),
            'password.uncompromised' => __('This password has appeared in a data breach. Please choose a different password.'),
        ]);
        $this->repository->update($staff, $request);
        return redirect()->route('back.staff.index')->withSuccess(__('User Updated Successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admin $staff)
    {
        $this->repository->delete($staff);
        return redirect()->route('back.staff.index')->withSuccess(__('User Deleted Successfully.'));
    }
}
