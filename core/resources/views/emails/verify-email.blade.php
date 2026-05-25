<p>{{ __('Hello') }} {{ $user->displayName() }},</p>
<p>{{ __('Please confirm your email address for') }} <strong>{{ $siteTitle }}</strong>.</p>
<p>
    <a href="{{ $url }}" style="display:inline-block;padding:12px 24px;background:#1a1a1a;color:#fff;text-decoration:none;border-radius:4px;">
        {{ __('Verify Email Address') }}
    </a>
</p>
<p>{{ __('This link expires in :minutes minutes and can only be used once.', ['minutes' => $expireMinutes]) }}</p>
<p>{{ __('If you did not create an account, no further action is required.') }}</p>
<p style="font-size:12px;color:#666;">{{ __('If the button does not work, copy and paste this URL into your browser:') }}<br>{{ $url }}</p>
