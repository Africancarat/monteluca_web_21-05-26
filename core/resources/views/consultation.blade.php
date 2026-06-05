@extends('master.front')

@section('title')
    {{ __('Book a Consultation') }}
@endsection

@section('meta')
<meta name="description" content="Book a private jewellery consultation with African Carat — virtual or in-store.">
@endsection

@section('styleplugins')
<style>
/* ── Booking Method Selector ─────────────────────────────────────────────── */
.bm-selector {
    display: flex;
    gap: 16px;
    margin-bottom: 32px;
}
.bm-card {
    flex: 1;
    border: 2px solid #e5e0da;
    border-radius: 8px;
    padding: 24px 20px;
    cursor: pointer;
    text-align: center;
    transition: border-color .2s, background .2s;
    background: #fff;
}
.bm-card:hover {
    border-color: var(--primary-color, #b8a88a);
}
.bm-card.is-active {
    border-color: var(--primary-color, #b8a88a);
    background: #faf8f5;
}
.bm-card__icon {
    font-size: 28px;
    margin-bottom: 10px;
    color: var(--primary-color, #b8a88a);
}
.bm-card__title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 4px;
    color: #1a1a1a;
}
.bm-card__sub {
    font-family: 'Jost', sans-serif;
    font-size: 13px;
    color: #777;
}

/* ── Consultation Type Selector ─────────────────────────────────────────── */
.ct-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 24px;
}
.ct-btn {
    border: 1px solid #e5e0da;
    border-radius: 4px;
    padding: 8px 18px;
    font-family: 'Jost', sans-serif;
    font-size: 13px;
    font-weight: 500;
    letter-spacing: .04em;
    text-transform: uppercase;
    cursor: pointer;
    background: #fff;
    color: #555;
    transition: border-color .2s, color .2s, background .2s;
}
.ct-btn:hover,
.ct-btn.is-active {
    border-color: var(--primary-color, #b8a88a);
    color: var(--primary-color, #b8a88a);
    background: #faf8f5;
}

/* ── Slot Grid ───────────────────────────────────────────────────────────── */
.slot-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}
.slot-pill {
    border: 1px solid #e5e0da;
    border-radius: 20px;
    padding: 6px 16px;
    font-family: 'Jost', sans-serif;
    font-size: 13px;
    cursor: pointer;
    background: #fff;
    color: #555;
    transition: border-color .2s, color .2s, background .2s;
}
.slot-pill:hover,
.slot-pill.is-active {
    border-color: var(--primary-color, #b8a88a);
    color: var(--primary-color, #b8a88a);
    background: #faf8f5;
}

/* ── Store info card ─────────────────────────────────────────────────────── */
.store-info-card {
    background: #faf8f5;
    border: 1px solid #e5e0da;
    border-radius: 8px;
    padding: 20px 24px;
    margin-top: 28px;
}
.store-info-card h4 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 12px;
}
.store-info-card p {
    font-family: 'Jost', sans-serif;
    font-size: 14px;
    color: #555;
    margin-bottom: 4px;
}

/* ── Confirmation banner ─────────────────────────────────────────────────── */
.booking-success {
    display: none;
    background: #f0faf4;
    border: 1px solid #a8d5b5;
    border-radius: 8px;
    padding: 24px;
    text-align: center;
    margin-top: 24px;
}
.booking-success.is-visible { display: block; }
.booking-success p {
    font-family: 'Jost', sans-serif;
    font-size: 15px;
    color: #2d6a4f;
    margin: 0;
}

/* ── Step labels ─────────────────────────────────────────────────────────── */
.booking-step-label {
    font-family: 'Jost', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #aaa;
    margin-bottom: 8px;
}

@media (max-width: 480px) {
    .bm-selector { flex-direction: column; }
}
</style>
@endsection

@section('content')
<div class="page-title">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <ul class="breadcrumbs">
                    <li><a href="{{ route('front.index') }}">{{ __('Home') }}</a></li>
                    <li class="separator"></li>
                    <li>{{ __('Book a Consultation') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container padding-bottom-3x mb-1">
    <div class="row">

        {{-- Left column: intro copy --}}
        <div class="col-lg-4 col-md-5 order-lg-1 order-md-2 order-2 mb-4">
            <section class="widget widget-featured-posts card rounded p-4">
                <h3 class="widget-title padding-bottom-1x">{{ __('Private consultation') }}</h3>
                <p style="font-family:'Jost',sans-serif;font-size:14px;color:#555;line-height:1.7;">
                    {{ __('Meet with our jewellery expert one-on-one. Whether you are designing a bespoke piece, choosing a diamond, or planning a surprise engagement, we are here to guide you.') }}
                </p>
                <ul class="list-unstyled mt-3" style="font-family:'Jost',sans-serif;font-size:13px;color:#555;">
                    <li class="mb-2"><i class="icon-check" style="color:var(--primary-color,#b8a88a);margin-right:8px;"></i>{{ __('Engagement ring design') }}</li>
                    <li class="mb-2"><i class="icon-check" style="color:var(--primary-color,#b8a88a);margin-right:8px;"></i>{{ __('Bespoke jewellery') }}</li>
                    <li class="mb-2"><i class="icon-check" style="color:var(--primary-color,#b8a88a);margin-right:8px;"></i>{{ __('Diamond selection') }}</li>
                    <li class="mb-2"><i class="icon-check" style="color:var(--primary-color,#b8a88a);margin-right:8px;"></i>{{ __('Existing piece redesign') }}</li>
                </ul>
            </section>
        </div>

        {{-- Right column: booking form --}}
        <div class="col-lg-8 col-md-7 order-lg-2 order-md-1 order-1">
            <div class="contact-form-box card p-4">

                {{-- JS check: visible if JS fails to load, hidden by JS on load --}}
                <div id="jsCheck" style="background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:10px 14px;margin-bottom:16px;font-family:'Jost',sans-serif;font-size:13px;color:#856404;">
                    JavaScript is not loaded yet. Please do a hard refresh: <strong>Ctrl + Shift + R</strong>
                </div>

                <h2 class="h4 mb-4" style="font-family:'Cormorant Garamond',serif;">
                    {{ __('Book your consultation') }}
                </h2>

                {{-- ── STEP 1: Booking method ── --}}
                <div class="booking-step-label">{{ __('Step 1 — How would you like to meet?') }}</div>
                <div class="bm-selector" id="bmSelector">
                    <div class="bm-card is-active" data-type="virtual" role="button" tabindex="0">
                        <div class="bm-card__icon"><i class="icon-video"></i></div>
                        <div class="bm-card__title">{{ __('Virtual consultation') }}</div>
                        <div class="bm-card__sub">{{ __('We meet over Google Meet') }}</div>
                    </div>
                    <div class="bm-card" data-type="store_visit" role="button" tabindex="0">
                        <div class="bm-card__icon"><i class="icon-map-pin"></i></div>
                        <div class="bm-card__title">{{ __('Visit our store') }}</div>
                        <div class="bm-card__sub">{{ __('Come see the pieces in person') }}</div>
                    </div>
                </div>
                <input type="hidden" id="bookingType" value="virtual">

                {{-- ── STEP 2: Consultation type ── --}}
                <div class="booking-step-label mt-3">{{ __('Step 2 — What are you looking for?') }}</div>
                <div class="ct-selector" id="ctSelector">
                    <button type="button" class="ct-btn is-active" data-ct="Engagement Ring">{{ __('Engagement ring') }}</button>
                    <button type="button" class="ct-btn" data-ct="Bespoke Design">{{ __('Bespoke design') }}</button>
                    <button type="button" class="ct-btn" data-ct="Diamond Selection">{{ __('Diamond selection') }}</button>
                    <button type="button" class="ct-btn" data-ct="Other">{{ __('Other') }}</button>
                </div>
                <input type="hidden" id="eventType" value="Engagement Ring">

                {{-- ── STEP 3: Date ── --}}
                <div class="booking-step-label mt-3">{{ __('Step 3 — Choose a date') }}</div>
                <div class="form-group">
                    <input type="date"
                           id="bookingDate"
                           class="form-control form-control-rounded"
                           style="max-width:220px;"
                           min="{{ now()->addDay()->toDateString() }}"
                           placeholder="{{ __('Select date') }}">
                </div>

                {{-- ── STEP 4: Time slot ── --}}
                <div id="slotSection" style="display:none;">
                    <div class="booking-step-label mt-3">{{ __('Step 4 — Choose a time slot') }}</div>
                    <div class="slot-grid" id="slotGrid"></div>
                    <input type="hidden" id="selectedSlot" value="">
                </div>

                <div id="slotLoadingMsg" style="display:none;font-family:'Jost',sans-serif;font-size:13px;color:#999;margin-top:8px;">
                    {{ __('Loading available times…') }}
                </div>
                <div id="slotNoneMsg" style="display:none;font-family:'Jost',sans-serif;font-size:13px;color:#999;margin-top:8px;">
                    {{ __('No slots available on this date. Please choose another day.') }}
                </div>

                {{-- ── STEP 5: Your details ── --}}
                <div id="detailsSection" style="display:none;">
                    <div class="booking-step-label mt-4">{{ __('Step 5 — Your details') }}</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="guestName">{{ __('Full name') }}</label>
                                <input type="text" id="guestName" class="form-control form-control-rounded" placeholder="{{ __('Your name') }}">
                                <div class="text-danger small mt-1" id="err_guest_name"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="guestPhone">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="#25D366" style="margin-right:4px;vertical-align:middle;margin-top:-2px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    {{ __('WhatsApp Number') }}
                                </label>
                                <input type="tel" id="guestPhone" class="form-control form-control-rounded"
                                       placeholder="{{ __('+91 98765 43210') }}">
                                <small style="font-family:'Jost',sans-serif;font-size:11px;color:#888;margin-top:4px;display:block;">
                                    {{ __('Your booking confirmation will be sent here via WhatsApp.') }}
                                </small>
                                <div class="text-danger small mt-1" id="err_guest_phone"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="guestEmail">{{ __('Email') }}</label>
                                <input type="email" id="guestEmail" class="form-control form-control-rounded" placeholder="{{ __('your@email.com') }}">
                                <div class="text-danger small mt-1" id="err_guest_email"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="guestNotes">{{ __('Notes (optional)') }}</label>
                                <textarea id="guestNotes" class="form-control form-control-rounded" rows="3"
                                    placeholder="{{ __('Tell us a little about what you have in mind…') }}"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Meet info (virtual only) --}}
                    <div id="virtualInfo" class="store-info-card" style="background:#f5f9ff;border-color:#c5d9f0;">
                        <h4 style="font-family:'Cormorant Garamond',serif;font-size:16px;margin-bottom:6px;">
                            <i class="icon-video" style="margin-right:6px;color:var(--primary-color,#b8a88a);"></i>
                            {{ __('Google Meet link') }}
                        </h4>
                        <p style="font-family:'Jost',sans-serif;font-size:13px;color:#555;">
                            {{ __('A unique Google Meet link will be sent to your WhatsApp and email after booking.') }}
                        </p>
                    </div>

                    {{-- Store info (store_visit only, hidden by default) --}}
                    <div id="storeInfo" class="store-info-card" style="display:none;">
                        <h4><i class="icon-map-pin" style="margin-right:6px;color:var(--primary-color,#b8a88a);"></i>{{ $storeConfig['name'] }}</h4>
                        <p><i class="icon-home" style="margin-right:6px;"></i>{{ $storeConfig['address'] }}{{ $storeConfig['city'] ? ', ' . $storeConfig['city'] : '' }}</p>
                        @if($storeConfig['phone'])
                        <p><i class="icon-phone" style="margin-right:6px;"></i>{{ $storeConfig['phone'] }}</p>
                        @endif
                        <p style="margin-top:10px;font-size:13px;color:#888;">
                            {{ __('Please bring any reference images, inspiration photos, or existing jewellery pieces you would like us to look at.') }}
                        </p>
                    </div>

                    <div class="text-right mt-4">
                        <div class="text-danger small mb-2" id="err_global"></div>
                        <button class="btn btn-luxury" id="submitBooking" type="button">
                            <span>{{ __('Confirm booking') }}</span>
                        </button>
                    </div>
                </div>

                {{-- ── Success banner ── --}}
                <div class="booking-success" id="bookingSuccess">
                    <i class="icon-check-circle" style="font-size:32px;color:#2d6a4f;display:block;margin-bottom:10px;"></i>
                    <p id="successMsg"></p>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // JS-loaded indicator — disappears once JS runs successfully
    var jsCheck = document.getElementById('jsCheck');
    if (jsCheck) jsCheck.style.display = 'none';

    const availableDatesUrl = '{{ route('booking.available-dates') }}';
    const slotsUrl          = '{{ route('booking.slots') }}';
    const storeUrl          = '{{ route('booking.store') }}';
    const csrfToken         = '{{ csrf_token() }}';

    let availableDates = [];

    // ── Booking method selector ─────────────────────────────────────────────
    document.querySelectorAll('.bm-card').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('.bm-card').forEach(function (c) { c.classList.remove('is-active'); });
            card.classList.add('is-active');
            document.getElementById('bookingType').value = card.dataset.type;
            updateBookingTypeUI(card.dataset.type);
        });
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { card.click(); }
        });
    });

    function updateBookingTypeUI(type) {
        var virtualInfo = document.getElementById('virtualInfo');
        var storeInfo   = document.getElementById('storeInfo');
        if (type === 'store_visit') {
            virtualInfo.style.display = 'none';
            storeInfo.style.display   = 'block';
        } else {
            virtualInfo.style.display = 'block';
            storeInfo.style.display   = 'none';
        }
    }

    // ── Consultation type selector ──────────────────────────────────────────
    document.querySelectorAll('.ct-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.ct-btn').forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            document.getElementById('eventType').value = btn.dataset.ct;
        });
    });

    // ── Load available dates, then restrict date picker ─────────────────────
    fetch(availableDatesUrl)
        .then(function (r) { return r.json(); })
        .then(function (dates) {
            availableDates = dates;
        })
        .catch(function () {});

    // ── Date picker change ──────────────────────────────────────────────────
    document.getElementById('bookingDate').addEventListener('change', function () {
        var date = this.value;
        if (! date) return;

        document.getElementById('slotSection').style.display = 'none';
        document.getElementById('detailsSection').style.display = 'none';
        document.getElementById('slotGrid').innerHTML = '';
        document.getElementById('selectedSlot').value = '';
        document.getElementById('slotNoneMsg').style.display = 'none';
        document.getElementById('slotLoadingMsg').style.display = 'block';

        fetch(slotsUrl + '?date=' + encodeURIComponent(date))
            .then(function (r) { return r.json(); })
            .then(function (slots) {
                document.getElementById('slotLoadingMsg').style.display = 'none';
                var grid = document.getElementById('slotGrid');

                if (! slots.length) {
                    document.getElementById('slotNoneMsg').style.display = 'block';
                    return;
                }

                slots.forEach(function (slot) {
                    var pill = document.createElement('button');
                    pill.type = 'button';
                    pill.className = 'slot-pill';
                    pill.dataset.slotId = slot.id;
                    pill.textContent = slot.slot_time.substring(0, 5);
                    pill.addEventListener('click', function () {
                        document.querySelectorAll('.slot-pill').forEach(function (p) { p.classList.remove('is-active'); });
                        pill.classList.add('is-active');
                        document.getElementById('selectedSlot').value = slot.id;
                        document.getElementById('detailsSection').style.display = 'block';
                    });
                    grid.appendChild(pill);
                });

                document.getElementById('slotSection').style.display = 'block';
            })
            .catch(function () {
                document.getElementById('slotLoadingMsg').style.display = 'none';
                document.getElementById('slotNoneMsg').style.display = 'block';
            });
    });

    // ── Submit booking ──────────────────────────────────────────────────────
    document.getElementById('submitBooking').addEventListener('click', function () {
        clearErrors();
        var btn = this;
        btn.disabled = true;
        btn.querySelector('span').textContent = '{{ __('Confirming…') }}';

        var payload = {
            slot_id:      document.getElementById('selectedSlot').value,
            guest_name:   document.getElementById('guestName').value.trim(),
            guest_email:  document.getElementById('guestEmail').value.trim(),
            guest_phone:  document.getElementById('guestPhone').value.trim(),
            event_type:   document.getElementById('eventType').value,
            booking_type: document.getElementById('bookingType').value,
            notes:        document.getElementById('guestNotes').value.trim(),
            _token:       csrfToken,
        };

        fetch(storeUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(payload),
        })
        .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
        .then(function (res) {
            if (res.ok && res.data.success) {
                document.getElementById('detailsSection').style.display   = 'none';
                document.getElementById('slotSection').style.display      = 'none';
                document.getElementById('successMsg').textContent         = res.data.message;
                document.getElementById('bookingSuccess').classList.add('is-visible');
                window.scrollTo({ top: document.getElementById('bookingSuccess').offsetTop - 80, behavior: 'smooth' });
            } else {
                btn.disabled = false;
                btn.querySelector('span').textContent = '{{ __('Confirm booking') }}';
                var errors = res.data.errors || {};
                Object.keys(errors).forEach(function (field) {
                    var el = document.getElementById('err_' + field);
                    if (el) el.textContent = errors[field][0];
                });
                if (res.data.message) {
                    document.getElementById('err_global').textContent = res.data.message;
                }
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.querySelector('span').textContent = '{{ __('Confirm booking') }}';
            document.getElementById('err_global').textContent = '{{ __('Something went wrong. Please try again.') }}';
        });
    });

    function clearErrors() {
        ['guest_name','guest_email','guest_phone','global'].forEach(function (k) {
            var el = document.getElementById('err_' + k);
            if (el) el.textContent = '';
        });
    }

});
</script>
@endsection
