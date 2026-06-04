<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>New Booking Alert</title>
  <style>
    body { margin:0; padding:0; background:#f4f4f4; font-family:'Arial',sans-serif; color:#1a1a1a; }
    .wrapper { max-width:540px; margin:32px auto; background:#fff; border:1px solid #ddd; border-radius:4px; overflow:hidden; }
    .header  { background:#1a1a1a; padding:24px 32px; }
    .header h1 { color:#fff; font-size:18px; font-weight:400; letter-spacing:.04em; margin:0; }
    .header p  { color:#b8a88a; font-size:11px; letter-spacing:.14em; text-transform:uppercase; margin:4px 0 0; }
    .body    { padding:32px; }
    .badge   { display:inline-block; padding:4px 14px; border-radius:20px; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
    .badge-virtual { background:#e3f0ff; color:#1565c0; }
    .badge-store   { background:#e8f5e9; color:#2e7d32; }
    .section-title { font-size:10px; letter-spacing:.14em; text-transform:uppercase; color:#aaa; margin:24px 0 10px; }
    .detail-table { width:100%; border-collapse:collapse; }
    .detail-table tr { border-bottom:1px solid #f0f0f0; }
    .detail-table td { padding:9px 0; font-size:14px; vertical-align:top; }
    .detail-table td:first-child { color:#888; width:36%; font-size:12px; text-transform:uppercase; letter-spacing:.06em; }
    .action-btn { display:inline-block; margin-top:24px; padding:12px 28px; background:#1a1a1a; color:#fff; text-decoration:none; font-size:12px; letter-spacing:.1em; text-transform:uppercase; border-radius:3px; }
    .footer { background:#f9f9f9; border-top:1px solid #eee; padding:16px 32px; font-size:11px; color:#aaa; }
  </style>
</head>
<body>
<div class="wrapper">

  <div class="header">
    <h1>New consultation booking</h1>
    <p>African Carat — internal alert</p>
  </div>

  <div class="body">

    <span class="badge {{ $appointment->isVirtual() ? 'badge-virtual' : 'badge-store' }}">
      {{ $appointment->isVirtual() ? 'Virtual — Google Meet' : 'Store visit' }}
    </span>

    <p class="section-title">Guest details</p>
    <table class="detail-table">
      <tr><td>Name</td><td><strong>{{ $appointment->guest_name }}</strong></td></tr>
      <tr><td>Email</td><td>{{ $appointment->guest_email }}</td></tr>
      <tr><td>Phone</td><td>{{ $appointment->guest_phone }}</td></tr>
      <tr><td>Consultation</td><td>{{ $appointment->event_type }}</td></tr>
    </table>

    <p class="section-title">Booking details</p>
    <table class="detail-table">
      <tr><td>Date</td><td><strong>{{ $appointment->starts_at->format('l, d F Y') }}</strong></td></tr>
      <tr><td>Time</td><td><strong>{{ $appointment->starts_at->format('H:i') }}</strong></td></tr>
      @if($appointment->isVirtual() && $appointment->meeting_url)
      <tr><td>Meet link</td><td><a href="{{ $appointment->meeting_url }}" style="color:#1565c0;">{{ $appointment->meeting_url }}</a></td></tr>
      @endif
      @if($appointment->isStoreVisit())
      <tr><td>Location</td><td>{{ $appointment->store_location }}</td></tr>
      @endif
      @if($appointment->notes)
      <tr><td>Notes</td><td><em>{{ $appointment->notes }}</em></td></tr>
      @endif
    </table>

    <a href="{{ url(route('back.appointments.show', $appointment)) }}" class="action-btn">
      View in admin panel
    </a>

  </div>

  <div class="footer">
    Sent automatically by African Carat booking system &bull; {{ now()->format('d M Y, H:i') }}
  </div>

</div>
</body>
</html>
