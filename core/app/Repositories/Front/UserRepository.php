<?php



namespace App\Repositories\Front;



use App\{

    Models\User,

    Models\Setting,

    Models\Notification

};

use App\Helpers\ImageHelper;

use App\Models\Subscriber;

use App\Support\EmailVerification;

use Illuminate\Support\Facades\Auth;



class UserRepository

{

    public function register($request): User

    {

        $data = $request->validated();



        $user = new User;

        $user->fill([

            'first_name' => $data['first_name'],

            'last_name' => $data['last_name'],

            'phone' => $data['phone'],

            'email' => $data['email'],

            'password' => bcrypt($data['password']),

            'email_verify' => EmailVerification::isRequired() ? 0 : 1,

            'email_verified_at' => EmailVerification::isRequired() ? null : now(),

        ]);

        $user->save();



        Notification::create(['user_id' => $user->id]);



        if (EmailVerification::isRequired()) {

            $user->sendEmailVerificationNotification();

        }



        return $user;

    }



    public function profileUpdate($request): void

    {

        $data = $request->validated();



        if (! empty($data['user_id']) && Auth::guard('admin')->check()) {

            $user = User::findOrFail($data['user_id']);

        } else {

            $user = Auth::user();

        }



        if (! empty($data['password'])) {

            $user->password = bcrypt($data['password']);

            $user->save();

        }



        if ($file = $request->file('photo')) {

            $data['photo'] = ImageHelper::handleUpdatedUploadedImage($file, 'images', $user, 'images', 'photo');

        }



        if ($request->boolean('newsletter')) {

            if (! Subscriber::where('email', $user->email)->exists()) {

                Subscriber::insert(['email' => $user->email]);

            }

        } else {

            Subscriber::where('email', $user->email)->delete();

        }



        $emailChanged = isset($data['email'])

            && strtolower(trim($data['email'])) !== strtolower($user->email);



        $user->fill(collect($data)->only([

            'first_name',

            'last_name',

            'phone',

            'email',

            'photo',

        ])->filter(fn ($v) => $v !== null)->all())->save();



        if ($emailChanged && EmailVerification::isRequired() && ! Auth::guard('admin')->check()) {

            $user->forceFill([

                'email_verified_at' => null,

                'email_verify' => 0,

            ])->save();

            $user->sendEmailVerificationNotification();

        }

    }

}


