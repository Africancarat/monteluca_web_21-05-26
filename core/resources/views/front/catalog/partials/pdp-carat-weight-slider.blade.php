@php
    $step = 0.5;
    $min = 0.5;
    $max = 5.0;
    $base = (float) ($selectedCarat ?? 1.0);
    if ($base < (float) $min) {
        $base = (float) $min;
    }
    // Allow range to extend past 5 ct when the catalogue stone is larger
    $max = max((float) $max, $base);
    $max = ceil($max / $step) * $step;
    if ($max < $min) {
        $max = $min + $step;
    }
    $snapped = round(round($base / $step) * $step, 2);
    $snapped = max((float) $min, min((float) $max, (float) $snapped));
@endphp

<div class="pdp-carat-slider-block" data-carat-min="{{ $min }}" data-carat-max="{{ $max }}" data-carat-step="{{ $step }}">
    <h4 class="pdp-carat-heading">
        <span class="pdp-carat-heading__label">{{ __('Carat Weight') }}</span>
        <span class="pdp-carat-heading__sep"> : </span>
        <span class="pdp-carat-heading__value" id="pdp_carat_value">{{ number_format($snapped, 1) }}</span>
    </h4>

    <div class="pdp-carat-slider-wrap">
        <input type="range"
            id="pdp_carat_slider"
            class="pdp-carat-range"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            value="{{ $snapped }}"
            aria-valuemin="{{ $min }}"
            aria-valuemax="{{ $max }}"
            aria-valuenow="{{ $snapped }}"
            aria-label="{{ __('Carat weight') }}">
    </div>

    <div class="pdp-carat-labels">
        <span>{{ number_format($min, 1) }} CT</span>
        <span>{{ number_format($max, 1) }} CT</span>
    </div>

    <!-- <div class="pdp-carat-selected">
        {{ __('Selected') }}:
        <strong id="pdp_carat_display">{{ number_format($snapped, 1) }} CT</strong>
    </div> -->
</div>

<input type="hidden" id="pdp_selected_carat_weight" name="pdp_carat_weight" value="{{ number_format($snapped, 1, '.', '') }}">

@once
    <script>
        (function () {
            if (window.__pdpCaratSliderBound) return;
            window.__pdpCaratSliderBound = true;

            function pdpFormatCarat(num) {
                var n = parseFloat(num);
                if (!isFinite(n)) return '0.0';
                return n.toFixed(1);
            }

            function pdpUpdateCaratSliderFill(el) {
                if (!el) return;
                var min = parseFloat(el.min);
                var max = parseFloat(el.max);
                var val = parseFloat(el.value);
                if (!isFinite(min) || !isFinite(max) || max <= min) {
                    el.style.setProperty('--pdp-carat-fill', '0%');
                    return;
                }
                var pct = Math.max(0, Math.min(100, ((val - min) / (max - min)) * 100));
                el.style.setProperty('--pdp-carat-fill', pct + '%');
            }

            function pdpSyncCaratFromSlider(el, triggerPriceRefresh) {
                if (!el || el.id !== 'pdp_carat_slider') return;
                var formatted = pdpFormatCarat(el.value);
                var vNum = parseFloat(formatted);
                el.setAttribute('aria-valuenow', String(vNum));

                var head = document.getElementById('pdp_carat_value');
                if (head) head.textContent = formatted;

                var sel = document.getElementById('pdp_carat_display');
                if (sel) sel.textContent = formatted + ' CT';

                var hidden = document.getElementById('pdp_selected_carat_weight');
                if (hidden) hidden.value = formatted;

                pdpUpdateCaratSliderFill(el);

                if (triggerPriceRefresh) {
                    document.dispatchEvent(new CustomEvent('pdp-jewelry-variant-change', {
                        bubbles: true,
                        detail: { source: 'carat' },
                    }));
                }
            }

            function pdpInitCaratSlider() {
                var el = document.getElementById('pdp_carat_slider');
                if (!el) return;
                pdpUpdateCaratSliderFill(el);
                pdpSyncCaratFromSlider(el, false);
            }

            document.addEventListener('input', function (e) {
                if (e.target && e.target.id === 'pdp_carat_slider') {
                    pdpSyncCaratFromSlider(e.target, true);
                }
            });
            document.addEventListener('change', function (e) {
                if (e.target && e.target.id === 'pdp_carat_slider') {
                    pdpSyncCaratFromSlider(e.target, true);
                }
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', pdpInitCaratSlider);
            } else {
                pdpInitCaratSlider();
            }
        })();
    </script>
@endonce
