{{-- Redirect guests to login when they try to pay / submit checkout (server also enforces in CheckoutController). --}}
@guest
<script>
(function () {
    var loginUrl = @json(route('user.login', ['redirect' => 'checkout']));
    var checkoutSubmitAction = @json(route('front.checkout.submit'));

    function goLogin(e) {
        if (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
        window.location.href = loginUrl;
        return false;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof jQuery === 'undefined') {
            return;
        }

        $(document).on('click', '#single_checkout_payment, .single_checkout_payment', goLogin);

        $(document).on('submit', 'form', function (e) {
            var action = (this.getAttribute('action') || '').replace(/\/$/, '');
            if (action === checkoutSubmitAction.replace(/\/$/, '')) {
                goLogin(e);
            }
        });
    });
})();
</script>
@endguest
