<!--<p>{{ __('Hello') }} {{ $user->displayName() }},</p>
<p>{{ __('Please confirm your email address for') }} <strong>{{ $siteTitle }}</strong>.</p>
<p>
    <a href="{{ $url }}" style="display:inline-block;padding:12px 24px;background:#1a1a1a;color:#fff;text-decoration:none;border-radius:4px;">
        {{ __('Verify Email Address') }}
</a>
</p>
<p>{{ __('This link expires in :minutes minutes and can only be used once.', ['minutes' => $expireMinutes]) }}</p>
<p>{{ __('If you did not create an account, no further action is required.') }}</p>
<p style="font-size:12px;color:#666;">{{ __('If the button does not work, copy and paste this URL into your browser:') }}<br>{{ $url }}</p>-->

<p>Hello {{ $user->displayName() }},</p>

<p>
    Welcome to <strong>{{ $siteTitle }}</strong>! Thank you for creating your account.
</p>

<p>
    To securely activate your account and start exploring our premium collections,
    please confirm your email address by clicking the button below:
</p>

<a href="{{ $url }}" style="display:inline-block;padding:12px 24px;background:#1a1a1a;color:#fff;text-decoration:none;border-radius:4px;">
    {{ __('Verify Email Address') }}
</a>

<p>
    For your security, this verification link will expire in
    <strong>{{ $expireMinutes }} minutes</strong> and can only be used once.
</p>

<p>
    If you did not create an account on {{ $siteTitle }},
    you can safely ignore this email.
</p>

<hr style="border:none;border-top:1px solid #ddd;margin:30px 0;">

<p style="font-size:13px;color:#666;">
    If the button above does not work, copy and paste this link into your browser:
</p>

<p style="font-size:12px;word-break:break-all;color:#444;">
    {{ $url }}
</p>

<p style="font-size:13px;color:#888;margin-top:30px;">
    Regards,<br>
    <strong>Monte Luca Team</strong><br>
    <a href="https://monteluca.com">www.monteluca.com</a>
</p>