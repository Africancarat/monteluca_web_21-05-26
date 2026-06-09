<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Consultation Confirmed</title>
  <style>
    body { margin:0; padding:0; background:#FAF8F3; font-family:'Georgia',serif; color:#0A0A0A; }
    .wrapper { max-width:560px; margin:40px auto; background:#fff; border:1px solid #D4C5A9; }
    .header  { background:#0A0A0A; padding:32px 40px; text-align:center; }
    .header__wordmark { color:#D4C5A9; font-size:11px; letter-spacing:.2em; text-transform:uppercase; margin:0; }
    .header__title    { color:#fff; font-size:22px; font-weight:400; letter-spacing:.04em; margin:8px 0 0; }
    .body    { padding:40px; }
    .eyebrow { font-size:10px; letter-spacing:.16em; text-transform:uppercase; color:#B8860B; margin:0 0 16px; }
    .greeting{ font-size:20px; font-weight:400; margin:0 0 24px; line-height:1.4; }
    .divider { border:none; border-top:1px solid #D4C5A9; margin:24px 0; }
    .detail-table { width:100%; border-collapse:collapse; margin:0 0 24px; }
    .detail-table td { padding:10px 0; border-bottom:1px solid #f0ece4; font-size:14px; vertical-align:top; }
    .detail-table td:first-child { color:#888; font-size:12px; letter-spacing:.08em; text-transform:uppercase; width:38%; font-family:'Arial',sans-serif; }
    .detail-table td:last-child { font-weight:400; color:#1a1a1a; }
    .badge   { display:inline-block; padding:3px 12px; font-size:11px; letter-spacing:.08em; text-transform:uppercase; font-family:'Arial',sans-serif; border-radius:20px; }
    .badge-virtual { background:#e3f0ff; color:#1565c0; }
    .badge-store   { background:#e8f5e9; color:#2e7d32; }
    .meet-btn { display:inline-block; padding:13px 32px; background:#0A0A0A; color:#fff; text-decoration:none; font-size:11px; letter-spacing:.12em; text-transform:uppercase; font-family:'Arial',sans-serif; margin:8px 0; }
    .info-box { background:#FAF8F3; border-left:2px solid #B8860B; padding:16px 20px; margin:20px 0; font-size:13px; line-height:1.7; color:#444; font-family:'Arial',sans-serif; }
    .footer  { background:#0A0A0A; padding:24px 40px; text-align:center; }
    .footer p{ color:#888; font-size:10px; letter-spacing:.08em; text-transform:uppercase; margin:0; font-family:'Arial',sans-serif; }
    .footer a{ color:#D4C5A9; text-decoration:none; }
  </style>
</head>
<body>
<div class="wrapper">

  <div class="header">
    <p class="header__wordmark">African Carat &bull; Monte Luca Diamonds</p>
    <h1 class="header__title">
      @if($appointment->isVirtual())
        Your virtual consultation is confirmed
      @else
        Your store visit is confirmed
      @endif
    </h1>
  </div>

  <div class="body">

    <p class="eyebrow">{{ $appointment->event_type }}</p>
    <p class="greeting">Hello {{ $appointment->guest_name }},<br>
      we look forward to speaking with you.</p>

    <hr class="divider">

    <table class="detail-table">
      <tr>
        <td>Booking type</td>
        <td>
          @if($appointment->isVirtual())
            <span class="badge badge-virtual">Virtual — Google Meet</span>
          @else
            <span class="badge badge-store">Store visit</span>
          @endif
        </td>
      </tr>
      <tr>
        <td>Date</td>
        <td>{{ $appointment->starts_at->format('l, d F Y') }}</td>
      </tr>
      <tr>
        <td>Time</td>
        <td>{{ $appointment->starts_at->format('H:i') }}</td>
      </tr>
      <tr>
        <td>Consultation</td>
        <td>{{ $appointment->event_type }}</td>
      </tr>
      @if($appointment->isVirtual() && $appointment->meeting_url)
      <tr>
        <td>Google Meet</td>
        <td>
          <a href="{{ $appointment->meeting_url }}" class="meet-btn">Join meeting</a>
        </td>
      </tr>
      @endif
      @if($appointment->isStoreVisit())
      <tr>
        <td>Location</td>
        <td>
          {{ config('services.store.name') }}<br>
          {{ config('services.store.address') }}{{ config('services.store.city') ? ', '.config('services.store.city') : '' }}<br>
          @if(config('services.store.phone'))
          {{ config('services.store.phone') }}
          @endif
        </td>
      </tr>
      @endif
    </table>

    @if($appointment->isVirtual())
    <div class="info-box">
      Your Google Meet link is included above. You can also find it in your WhatsApp confirmation message.
      Please join a minute early so we can start on time.
    </div>
    @else
    <div class="info-box">
      Please bring any reference images, inspiration photos, or existing jewellery pieces
      you would like us to look at. We recommend arriving 5 minutes early.
    </div>
    @endif

    <hr class="divider">

    <p style="font-size:12px;color:#888;line-height:1.7;font-family:'Arial',sans-serif;">
      Need to reschedule? Reply to this email or contact us at
      <a href="mailto:{{ config('services.store.email', 'hello@africancarat.com') }}" style="color:#B8860B;">
        {{ config('services.store.email', 'hello@africancarat.com') }}
      </a>
    </p>

  </div>

  <div class="footer">
    <p>&copy; {{ date('Y') }} African Carat &bull; <a href="{{ url('/') }}">africancarat.com</a></p>
  </div>

</div>
</body>
</html>
