<p>{{ __('Hello') }},</p>

<p>

    {{ __('We detected multiple failed login attempts on your account associated with') }}
    <strong>{{ $email }}</strong>.

</p>

<p>

    {{ __('For security reasons, access has been temporarily limited due to too many attempts.') }}

</p>

<p>

    {{ __('If this activity was not performed by you, we strongly recommend updating your password immediately and reviewing your account activity.') }}

</p>

<p>

    <a href="{{ url('/user/login') }}"
       style="display:inline-block;padding:12px 24px;background:#1a1a1a;color:#fff;text-decoration:none;border-radius:4px;">

        {{ __('Secure Your Account') }}

    </a>

</p>

<p style="font-size:12px;color:#666;">

    {{ __('If you recognize this activity, no further action is required.') }}

</p>