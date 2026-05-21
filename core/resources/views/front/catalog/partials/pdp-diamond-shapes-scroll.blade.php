@php
    $diamondShapes = $diamondShapes ?? collect();
    $selectedDiamondShape = (string) ($selectedDiamondShape ?? $diamondShapes->first() ?? '');
@endphp

<div class="pdp-diamond-shape-block" data-option-group="diamond_shape">
    <h4 class="pdp-diamond-shape-heading">
        <span class="pdp-diamond-shape-heading__label">{{ __('Diamond Shape') }}</span>
        <span class="pdp-diamond-shape-heading__sep"> : </span>
        <span class="pdp-diamond-shape-heading__value" id="pdp_diamond_shape_value">{{ $selectedDiamondShape }}</span>
    </h4>

    <div class="diamond-shapes-carousel">
        <button type="button"
            class="diamond-shapes-nav diamond-shapes-nav--prev"
            aria-label="{{ __('Previous shapes') }}"
            data-diamond-shapes-nav="prev">
            <span aria-hidden="true">&lsaquo;</span>
        </button>

        <div class="diamond-shapes-scroll" role="listbox" aria-label="{{ __('Diamond shape') }}">
            @foreach ($diamondShapes as $shapeLabel)
                @php
                    $shapeLabel = (string) $shapeLabel;
                    $isActive = strcasecmp($selectedDiamondShape, $shapeLabel) === 0;
                @endphp
                <button type="button"
                    class="diamond-shape-btn diamond-shape-btn--icon-only {{ $isActive ? 'is-active' : '' }}"
                    role="option"
                    aria-selected="{{ $isActive ? 'true' : 'false' }}"
                    aria-label="{{ $shapeLabel }}"
                    data-shape="{{ $shapeLabel }}"
                    title="{{ $shapeLabel }}">
                    @include('front.diamonds.partials.shape-icon', ['key' => $shapeLabel])
                </button>
            @endforeach
        </div>

        <button type="button"
            class="diamond-shapes-nav diamond-shapes-nav--next"
            aria-label="{{ __('Next shapes') }}"
            data-diamond-shapes-nav="next">
            <span aria-hidden="true">&rsaquo;</span>
        </button>
    </div>
</div>

@once
    <script>
        (function () {
            function pdpUpdateDiamondShapeValue(shape) {
                var valueEl = document.getElementById('pdp_diamond_shape_value');
                if (valueEl) valueEl.textContent = shape;

                var hidden = document.getElementById('pdp_selected_diamond_shape');
                if (hidden) hidden.value = shape;
            }

            function pdpSelectDiamondShape(btn) {
                if (!btn) return;

                var shape = btn.getAttribute('data-shape') || '';
                var scroll = btn.closest('.diamond-shapes-scroll');
                if (scroll) {
                    scroll.querySelectorAll('.diamond-shape-btn').forEach(function (b) {
                        var active = b === btn;
                        b.classList.toggle('is-active', active);
                        b.setAttribute('aria-selected', active ? 'true' : 'false');
                    });
                }

                pdpUpdateDiamondShapeValue(shape);
                if (typeof window.pdpUpdateGallery === 'function') {
                    window.pdpUpdateGallery();
                }
                document.dispatchEvent(new CustomEvent('pdp-jewelry-variant-change', {
                    bubbles: true,
                    detail: { source: 'shape' },
                }));
                btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }

            function pdpScrollDiamondShapes(carousel, direction) {
                var scroll = carousel && carousel.querySelector('.diamond-shapes-scroll');
                if (!scroll) return;
                var step = Math.max(120, Math.round(scroll.clientWidth * 0.65));
                scroll.scrollBy({ left: direction === 'next' ? step : -step, behavior: 'smooth' });
            }

            document.addEventListener('click', function (e) {
                var nav = e.target.closest('[data-diamond-shapes-nav]');
                if (nav) {
                    e.preventDefault();
                    pdpScrollDiamondShapes(nav.closest('.diamond-shapes-carousel'), nav.getAttribute('data-diamond-shapes-nav'));
                    return;
                }

                var btn = e.target.closest('.pdp-diamond-shape-block .diamond-shape-btn');
                if (!btn) return;
                e.preventDefault();
                pdpSelectDiamondShape(btn);
            });
        })();
    </script>
@endonce
