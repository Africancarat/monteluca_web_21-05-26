<aside class="sidebar">
    <div class="padding-top-2x hidden-lg-up"></div>
    <!-- Items in Cart Widget-->

    <section class="card widget widget-featured-posts widget-order-summary p-4">
        <h3 class="widget-title">{{ __('Order Summary') }}</h3>
        @php
            $free_shipping = DB::table('shipping_services')->whereStatus(1)->whereIsCondition(1)->first();
        @endphp

        @if ($free_shipping && $free_shipping->minimum_price !== null)
            @if ($free_shipping->minimum_price > $cart_total)
                <p class="free-shippin-aa"><em>{{ __('Free Shipping After Order') }}
                        {{ PriceHelper::setCurrencyPrice($free_shipping->minimum_price) }}</em></p>
            @endif
        @endif

        <table class="table">
            <tr>
                <td>{{ __('Cart subtotal') }}:</td>
                <td class="text-gray-dark">{{ PriceHelper::setCurrencyPrice($cart_total) }}</td>
            </tr>

            @if (!empty($gst) && ($gst['total_tax'] ?? 0) != 0)
                <tr class="cgst-row">
                    <td>{{ __('CGST') }} ({{ $gst['cgst_percent'] ?? 0 }}%):</td>
                    <td class="text-gray-dark cgst-amount">{{ PriceHelper::setCurrencyPrice($gst['cgst_amount'] ?? 0) }}</td>
                </tr>
                <tr class="sgst-row">
                    <td>{{ __('SGST') }} ({{ $gst['sgst_percent'] ?? 0 }}%):</td>
                    <td class="text-gray-dark sgst-amount">{{ PriceHelper::setCurrencyPrice($gst['sgst_amount'] ?? 0) }}</td>
                </tr>
            @endif

            @if (DB::table('states')->count() > 0)
                <tr class="{{ Auth::check() && Auth::user()->state_id ? '' : 'd-none' }} set__state_price_tr">
                    <td>{{ __('State tax') }}:</td>
                    <td class="text-gray-dark set__state_price">
                        {{ PriceHelper::setCurrencyPrice(Auth::check() && Auth::user()->state_id ? ($cart_total * Auth::user()->state->price) / 100 : 0) }}
                    </td>
                </tr>
            @endif

            @if ($discount)
                <tr>
                    <td>{{ __('Coupon discount') }}:</td>
                    <td class="text-danger">-
                        {{ PriceHelper::setCurrencyPrice($discount ? $discount['discount'] : 0) }}</td>
                </tr>
            @endif

            @if (!empty($referral))
                <tr class="referral-discount-row">
                    <td>{{ __('Referral discount') }} ({{ $referral['code']['referral_code'] ?? '' }}):</td>
                    <td class="text-danger referral-discount-amount">-
                        {{ PriceHelper::setCurrencyPrice($referral['discount'] ?? 0) }}</td>
                </tr>
            @endif

            @if (!empty($referral_balance_applied))
                <tr class="referral-balance-discount-row">
                    <td>{{ __('Referral balance') }}:</td>
                    <td class="text-danger">-
                        {{ PriceHelper::setCurrencyPrice($referral_balance_applied['discount'] ?? 0) }}</td>
                </tr>
            @endif

            <tr class="set__shipping_price_tr">
                <td>{{ __('Shipping') }}:</td>
                <td class="text-gray-dark set__shipping_price">
                    {{ PriceHelper::setCurrencyPrice($shipping ? $shipping->price : 0) }}</td>
            </tr>
            <tr>
                <td class="text-lg text-primary">{{ __('Order total') }}</td>
                <td class="text-lg text-primary grand_total_set" data-cart-subtotal="{{ $cart_total + ($tax ?? 0) }}">
                    {{ PriceHelper::setCurrencyPrice($grand_total) }}
                </td>
            </tr>
        </table>

        <div class="mt-3">
            <label class="small text-muted mb-1">{{ __('Referral Code') }}</label>
            <form id="referral_apply_form" class="d-flex gap-2" action="{{ route('front.referral.apply') }}" method="POST">
                @csrf
                <input type="text" name="referral_code" class="form-control form-control-sm text-uppercase"
                    placeholder="{{ __('e.g. MONTE123') }}"
                    value="{{ $referral['code']['referral_code'] ?? '' }}">
                <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('Apply') }}</button>
            </form>
            @if (!empty($referral))
                <button type="button" class="btn btn-link btn-sm text-danger p-0 mt-1" id="referral_remove_btn"
                    data-url="{{ route('front.referral.destroy') }}">{{ __('Remove referral') }}</button>
            @endif
            <p class="small text-muted mb-0 mt-1 referral-apply-message" role="status"></p>
        </div>

        @if (!empty($has_assigned_referral_code))
            <div class="mt-3 pt-3 border-top">
                <label class="small text-muted mb-1 d-block">{{ __('Referral balance') }}</label>
                <p class="small mb-2">
                    {{ __('Available') }}:
                    <strong class="referral-balance-available">{{ PriceHelper::setCurrencyPrice($referral_balance_available ?? 0) }}</strong>
                </p>
                @if (empty($referral_balance_applied))
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="referral_balance_apply_btn"
                        data-url="{{ route('front.referral-balance.apply') }}">
                        {{ __('Use referral balance') }}
                    </button>
                @else
                    <button type="button" class="btn btn-link btn-sm text-danger p-0" id="referral_balance_remove_btn"
                        data-url="{{ route('front.referral-balance.destroy') }}">
                        {{ __('Remove referral balance') }}
                    </button>
                @endif
                <p class="small text-muted mb-0 mt-1 referral-balance-message" role="status"></p>
            </div>
        @endif
    </section>

    @if (PriceHelper::CheckDigital() == true)
    <section class="card widget widget-featured-posts widget-order-summary p-4">
        <h3 class="widget-title">{{ __('Shipping Options') }}</h3>
        <div class="row">
            <div class="col-sm-12 mb-3">
                @if (PriceHelper::CheckDigital() == true)
                    @php
                        $free_shipping = DB::table('shipping_services')->whereStatus(1)->whereIsCondition(1)->first();
                    @endphp

                    <select name="shipping_id" class="form-control" id="shipping_id_select" required>
                        <option value="" selected disabled>{{ __('Select Shipping Method') }}*</option>
                        @foreach (DB::table('shipping_services')->whereStatus(1)->get() as $shipping)
                            @if ($shipping->id == 1 && isset($free_shipping) && $free_shipping->minimum_price !== null && $free_shipping->minimum_price <= $cart_total)
                                <option value="{{ $shipping->id }}" data-href="{{ route('front.shipping.setup') }}">
                                    {{ $shipping->title }}
                                </option>
                            @else
                                @if ($shipping->id != 1 && (!isset($free_shipping) || $free_shipping->minimum_price === null || $free_shipping->minimum_price > $cart_total))
                                    <option value="{{ $shipping->id }}"
                                        data-href="{{ route('front.shipping.setup') }}">{{ $shipping->title }}
                                        ({{ PriceHelper::setCurrencyPrice($shipping->price) }})
                                    </option>
                                @endif
                            @endif
                        @endforeach
                    </select>
                    @error('shipping_id')
                        <p class="text-danger shipping_message">{{ $message }}</p>
                    @enderror
                @endif
            </div>
            <div class="col-sm-12 mb-3">
                @if (PriceHelper::CheckDigital() == true)
                    @if (DB::table('states')->whereStatus(1)->count() > 0)
                        <select name="state_id" class="form-control" id="state_id_select" required>
                            <option value="" selected disabled>{{ __('Select Shipping State') }}*</option>
                            @foreach (DB::table('states')->whereStatus(1)->get() as $state)
                                <option value="{{ $state->id }}" data-href="{{ route('front.state.setup') }}"
                                    {{ Auth::check() && Auth::user()->state_id == $state->id ? 'selected' : '' }}>
                                    {{ $state->name }}
                                    @if ($state->type == 'fixed')
                                        ({{ PriceHelper::setCurrencyPrice($state->price) }})
                                    @else
                                        ({{ $state->price }}%)
                                    @endif

                                </option>
                            @endforeach
                        </select>
                        @error('state_id')
                            <p class="text-danger state_message">{{ $message }}</p>
                        @enderror
                    @endif
                @endif
            </div>
        </div>

    </section>
    @endif



    <!-- Order Summary Widget-->
    <section class="card widget  widget-order-summary p-4 mb-0">
        <h3 class="widget-title">{{ __('Pay now') }}</h3>
        <div class="row">
            <div class="col-sm-12">
                @php
                    $gateways = DB::table('payment_settings')->whereStatus(1)->get();
                @endphp
                <select class="form-control payment_gateway" required>
                    <option value="" selected disabled>{{ __('Select a payment method') }}</option>
                    @foreach ($gateways as $gateway)
                        @if (PriceHelper::CheckDigitalPaymentGateway())
                            @if ($gateway->unique_keyword != 'cod')
                                <option value="{{ $gateway->unique_keyword }}">{{ $gateway->name }}</option>
                            @endif
                        @else
                            <option value="{{ $gateway->unique_keyword }}">{{ $gateway->name }}</option>
                        @endif
                    @endforeach
                </select>

                @if ($setting->is_privacy_trams == 1)
                    <div class="form-group mt-4">
                        <div class="custom-control d-flex custom-checkbox">
                            <input class="custom-control-input me-2" type="checkbox" id="trams__condition_single"
                                value="">
                            <label class="custom-control-label flex-1" for="trams__condition">
                                {{ __('This site is protected by reCAPTCHA and the') }} <a href="{{ $setting->policy_link }}" target="_blank">{{ __('Privacy Policy') }}</a> {{ __('and') }} <a
                                    href="{{ $setting->terms_link }}" target="_blank">{{ __('Terms of Service') }}</a>
                                {{ __('apply.') }}</label>
                        </div>
                    </div>
                @endif
                @if ($setting->is_privacy_trams == 1)
                    <button id="single_checkout_payment" disabled="true"
                        class="btn btn-primary mt-4 single_checkout_payment" type="submit"><span>{{ __('Pay now') }}</span></button>
                @endif
                @if ($setting->is_privacy_trams == 0)
                    <button id="single_checkout_payment"
                        class="btn btn-primary mt-4 single_checkout_payment" type="submit"><span>{{ __('Pay now') }}</span></button>
                @endif
            </div>

        </div>
    </section>

</aside>

@section('script')
    <script>
        // Show the modal on #single_checkout_payment change
        $(document).on("click", "#single_checkout_payment", function() {
            let keyword = $('.payment_gateway').val();
            let modalElement = document.getElementById(keyword);

            if (modalElement) {
                // Open the modal using Bootstrap 5's API
                let modal = new bootstrap.Modal(modalElement);
                modal.show();

                // Get all input fields from the #checkoutBilling form
                let allinput = $("#checkoutBilling input");

                // Clear the modal form before appending new hidden inputs
                $(modalElement).find('form').html(); // Clear modal form content

                // Loop through each input and append a hidden input in the modal form
                allinput.each(function() {
                    // Create a new hidden input field with the same name and value
                    let hiddenInput = $('<input>')
                        .attr('type', 'hidden') // Set the input type to hidden
                        .attr('name', $(this).attr('name')) // Use the same name attribute
                        .val($(this).val()); // Set the value of the hidden input

                    // Append the hidden input to the modal form
                    $(modalElement).find('form').append(hiddenInput);
                });
            }
        });

        // Handle the "Terms and Conditions" checkbox click
        function parseCheckoutAmount(text) {
            if (!text) return 0;
            var n = parseFloat(String(text).replace(/[^0-9.]/g, ''));
            return isNaN(n) ? 0 : n;
        }

        function checkoutOrderSubtotal() {
            var el = $('.grand_total_set');
            var base = parseFloat(el.data('cart-subtotal'));
            if (!isNaN(base) && base > 0) {
                return base;
            }
            return parseCheckoutAmount($('.grand_total_set').first().text());
        }

        $(document).on('submit', '#referral_apply_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var code = form.find('[name=referral_code]').val();
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: {
                    _token: form.find('[name=_token]').val(),
                    referral_code: code,
                    order_amount: checkoutOrderSubtotal()
                },
                success: function(data) {
                    var msg = form.siblings('.referral-apply-message');
                    if (data.success) {
                        msg.removeClass('text-danger').addClass('text-success').text(data.message);
                        location.reload();
                    } else {
                        msg.removeClass('text-success').addClass('text-danger').text(data.message);
                    }
                },
                error: function(xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : '{{ __('Invalid referral code') }}';
                    form.siblings('.referral-apply-message').removeClass('text-success').addClass('text-danger').text(message);
                }
            });
        });

        $(document).on('click', '#referral_remove_btn', function() {
            $.post($(this).data('url'), { _token: '{{ csrf_token() }}' }, function() {
                location.reload();
            });
        });

        $(document).on('click', '#referral_balance_apply_btn', function() {
            var btn = $(this);
            var msg = $('.referral-balance-message');
            msg.removeClass('text-success text-danger').text('');
            $.ajax({
                type: 'POST',
                url: btn.data('url'),
                data: {
                    _token: '{{ csrf_token() }}',
                    order_amount: checkoutOrderSubtotal()
                },
                success: function(data) {
                    if (data.success) {
                        msg.addClass('text-success').text(data.message);
                        location.reload();
                    } else {
                        msg.addClass('text-danger').text(data.message);
                    }
                },
                error: function(xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : '{{ __('Unable to use referral balance. Insufficient balance.') }}';
                    msg.addClass('text-danger').text(message);
                }
            });
        });

        $(document).on('click', '#referral_balance_remove_btn', function() {
            $.post($(this).data('url'), { _token: '{{ csrf_token() }}' }, function() {
                location.reload();
            });
        });

        $(document).on("click", "#trams__condition_single", function() {
            if ($("#trams__condition_single").is(':checked')) {
                console.log("check");
                // Enable the dropdown by assigning the ID and removing the disabled attribute
                $('.single_checkout_payment').attr('id', "single_checkout_payment");
                $('.single_checkout_payment').attr('disabled', false);
            } else {
                // Remove the ID and disable the dropdown when unchecked
                $('.single_checkout_payment').removeAttr('id');
                $('.single_checkout_payment').attr('disabled', true);
            }
        });
    </script>
@endsection
