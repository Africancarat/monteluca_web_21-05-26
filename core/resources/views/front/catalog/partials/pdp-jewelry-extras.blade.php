@php
    $metalList = collect($item->pdp_metal_variants ?? [])->filter(function ($v) {
        return is_array($v) && (! empty($v['image']) || ! empty($v['photo']));
    });

    $metalVariantMap = $metalList
        ->map(function ($variant) {
            $mid = (string) ($variant['key'] ?? $variant['slug'] ?? '');
            $mlabel = (string) ($variant['label'] ?? $variant['name'] ?? $mid);
            $mimg = (string) ($variant['image'] ?? $variant['photo'] ?? '');
            $full = \App\Helpers\ImageHelper::storageImageUrl((string) $mimg);

            $imgs = $variant['images'] ?? $variant['gallery'] ?? null;
            $gallery = [];
            if (is_array($imgs)) {
                foreach ($imgs as $gi) {
                    $gi = (string) $gi;
                    if ($gi === '') continue;
                    $gallery[] = \App\Helpers\ImageHelper::storageImageUrl($gi);
                }
            }

            return [
                'key' => $mid,
                'label' => $mlabel,
                'image' => $full,
                'images' => $gallery,
            ];
        })
        ->values()
        ->all();

    $shapeVariantMap = collect($item->pdpShapeVariantRows())
        ->map(function ($row) {
            $urls = \App\Models\Item::resolveVariantImageUrls($row);

            return [
                'shape' => $row['shape'],
                'metal' => $row['metal'],
                'images' => $urls,
            ];
        })
        ->filter(function ($row) {
            return ! empty($row['images']);
        })
        ->values()
        ->all();
@endphp

@once
    <script>
        // Server-rendered metal → image mapping (items.pdp_metal_variants).
        // Each entry supports { key|slug, label|name, image|photo }.
        window.__pdpMetalVariantMap = @json($metalVariantMap);
        // Shape × metal → gallery URLs (items.pdp_shape_variants).
        window.__pdpShapeVariantMap = @json($shapeVariantMap);
        // External jewelry pricing API (override via .env JEWELRY_DYNAMIC_PRICE_URL).
        window.__jewelryDynamicPriceUrl = @json(rtrim((string) env('JEWELRY_DYNAMIC_PRICE_URL', 'http://localhost/jewelry-api/dynamic-price.php'), '/'));
    </script>
@endonce

{{-- Metal finish chips hidden: we keep pdp_metal_variants as a mapping for Metal Type → image swap. --}}
@php
    $optionList = function ($val) {
        if (is_array($val)) {
            return collect($val)
                ->map(function ($v) { return trim((string) $v); })
                ->filter(function ($v) { return $v !== ''; })
                ->values();
        }

        return collect(explode(',', (string) $val))
            ->map(function ($v) { return trim((string) $v); })
            ->filter(function ($v) { return $v !== ''; })
            ->values();
    };

    $metalTypes = $optionList($item->metal_type ?? '');
    $goldKarats = $optionList($item->gold_karat ?? '');
    $da = $item->diamondAttribute ?? null;
    $diamondColorGrades = $optionList($da?->color_grade ?? '');
    $diamondClarityGrades = $optionList($da?->clarity_grade ?? '');
    $diamondShapes = $optionList($da?->shape ?? '');

    // Default selection: first value (or empty). Keeps UI deterministic without affecting cart/pricing.
    $selectedMetal = (string) ($metalTypes->first() ?? '');
    $selectedKarat = (string) ($goldKarats->first() ?? '');
    $selectedDiamondColor = (string) ($diamondColorGrades->first() ?? '');
    $selectedDiamondClarity = (string) ($diamondClarityGrades->first() ?? '');
    $selectedDiamondShape = (string) ($diamondShapes->first() ?? '');

    $pdpEnableDynamicApiPrice = $goldKarats->isNotEmpty()
        || $diamondColorGrades->isNotEmpty()
        || $diamondClarityGrades->isNotEmpty()
        || $diamondShapes->isNotEmpty()
        || ($da && $da->carat_weight !== null && (float) $da->carat_weight > 0);
@endphp

<input type="hidden" id="pdp_selected_metal_type" value="{{ $selectedMetal }}">
<input type="hidden" id="pdp_selected_gold_karat" value="{{ $selectedKarat }}">
<input type="hidden" id="pdp_selected_diamond_color" value="{{ $selectedDiamondColor }}">
<input type="hidden" id="pdp_selected_diamond_clarity" value="{{ $selectedDiamondClarity }}">
<input type="hidden" id="pdp_selected_diamond_shape" value="{{ $selectedDiamondShape }}">

@if ($diamondShapes->isNotEmpty())
    @include('front.catalog.partials.pdp-diamond-shapes-scroll', [
        'diamondShapes' => $diamondShapes,
        'selectedDiamondShape' => $selectedDiamondShape,
    ])
@endif

@if($item->diamondAttribute)

    @include('front.catalog.partials.pdp-carat-weight-slider', [
        'selectedCarat' => (float) ($item->diamondAttribute->carat_weight ?? 1.0),
    ])
@endif

@if ($metalTypes->isNotEmpty())
  <h4>Metal Type</h4>
  <div class="option-group" data-option-group="metal_type">
  @foreach ($metalTypes as $val)
  <button 
    type="button" 
    class="option-btn metal-btn {{ strtolower($selectedMetal) == strtolower($val) ? 'active' : '' }}" 
    data-value="{{ $val }}">
    {{ $val }}
  </button>
@endforeach
  </div>
@endif

@if ($goldKarats->isNotEmpty())
  <h4>Gold Karat</h4>
  <div class="option-group" data-option-group="gold_karat">
  @foreach ($goldKarats as $val)
  <button 
    type="button" 
    class="option-btn karat-btn {{ strtoupper($selectedKarat) == strtoupper($val) ? 'active' : '' }}" 
    data-value="{{ $val }}">
    {{ $val }}
  </button>
@endforeach
  </div>
@endif

@if ($diamondClarityGrades->isNotEmpty())
  <h4>Diamond Clarity</h4>
  <div class="option-group" data-option-group="diamond_clarity">
  @foreach ($diamondClarityGrades as $val)
  <button
    type="button"
    class="option-btn clarity-btn {{ strtoupper($selectedDiamondClarity) == strtoupper($val) ? 'active' : '' }}"
    data-value="{{ $val }}">
    {{ $val }}
  </button>
@endforeach
  </div>
@endif

@if ($diamondColorGrades->isNotEmpty())
  <h4>Diamond Color</h4>
  <div class="option-group" data-option-group="diamond_color">
  @foreach ($diamondColorGrades as $val)
  <button
    type="button"
    class="option-btn color-btn {{ strtoupper($selectedDiamondColor) == strtoupper($val) ? 'active' : '' }}"
    data-value="{{ $val }}">
    {{ $val }}
  </button>
@endforeach
  </div>
@endif

@once
  <script>
    (function () {
      function setMainPdpImageSrc(src) {
        if (!src) return;

        // OwlCarousel renders clones; the visible image is usually inside `.owl-item.active`.
        var activeImg = document.querySelector('#productGallery .product-details-slider .owl-item.active img');
        if (activeImg) {
          activeImg.src = src;
          return;
        }

        // Fallback: non-initialized carousel / plain markup.
        var firstImg = document.querySelector('#productGallery .product-details-slider .item:first-child img');
        if (firstImg) {
          firstImg.src = src;
        }
      }

      function setPdpGalleryImages(imgList) {
        // Replace the entire PDP gallery (main slider + thumbs) for metal-specific galleries.
        // Does not touch any pricing/cart logic.
        if (!Array.isArray(imgList) || imgList.length === 0) return false;

        var gallery = document.querySelector('#productGallery .product-details-slider');
        if (!gallery) return false;

        // Update thumbs used by the PDP gallery.
        var legacyThumbs = document.querySelector('[data-lux-thumbs]') || document.querySelector('.gallery-thumbs');
        if (legacyThumbs) {
          legacyThumbs.innerHTML = imgList.map(function (u) {
            return '<button type="button" class="lux-thumb" data-lux-thumb><img src="' + String(u) + '" class="gallery-thumb" loading="lazy" alt=""></button>';
          }).join('');
        }

        // If OwlCarousel is initialized, rebuild via jQuery API (safe refresh).
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.owlCarousel) {
          var $ = window.jQuery;
          var $owl = $(gallery);
          if ($owl.hasClass('owl-loaded')) {
            $owl.trigger('replace.owl.carousel', [imgList.map(function (u) {
              return '<div class="item"><img src="' + String(u) + '" loading="lazy" alt=""></div>';
            }).join('')]);
            $owl.trigger('refresh.owl.carousel');
            if (typeof window.__luxPdpGalleryInit === 'function') {
              window.__luxPdpGalleryInit();
            }
            return true;
          }
        }

        // Non-owl fallback: replace markup.
        gallery.innerHTML = imgList.map(function (u) {
          return '<div class="item"><div class="lux-zoom-wrap" data-lux-zoom><img src="' + String(u) + '" loading="lazy" alt="" class="lux-main-img"></div></div>';
        }).join('');
        if (typeof window.__luxPdpGalleryInit === 'function') {
          window.__luxPdpGalleryInit();
        }
        return true;
      }

      function normalizeMetalToken(str) {
        return String(str || '')
          .toLowerCase()
          .replace(/[\s_-]+/g, ' ')
          .trim();
      }

      function normalizePdpToken(str) {
        return String(str || '')
          .toUpperCase()
          .replace(/\s+/g, ' ')
          .trim();
      }

      function pdpFindShapeMetalGallery(shapeTok, metalTok) {
        var rows = window.__pdpShapeVariantMap || [];
        if (!Array.isArray(rows) || rows.length === 0 || !shapeTok || !metalTok) {
          return null;
        }
        for (var i = 0; i < rows.length; i++) {
          var row = rows[i] || {};
          if (
            normalizePdpToken(row.shape) === shapeTok &&
            normalizePdpToken(row.metal) === metalTok &&
            Array.isArray(row.images) &&
            row.images.length
          ) {
            return row.images;
          }
        }
        return null;
      }

      function pdpUpdateGallery() {
        var shapeEl = document.getElementById('pdp_selected_diamond_shape');
        var metalEl = document.getElementById('pdp_selected_metal_type');
        var shapeTok = normalizePdpToken(shapeEl ? shapeEl.value : '');
        var metalTok = normalizePdpToken(metalEl ? metalEl.value : '');

        var shapeGallery = pdpFindShapeMetalGallery(shapeTok, metalTok);
        if (shapeGallery) {
          setPdpGalleryImages(shapeGallery);
          return;
        }

        if (metalEl && metalEl.value) {
          trySwapMetalImageByLabel(metalEl.value);
        }
      }
      window.pdpUpdateGallery = pdpUpdateGallery;

      function trySwapMetalImageByLabel(label) {
        // Uses items.pdp_metal_variants mapping (if present) to swap hero/gallery images.
        var desired = normalizeMetalToken(label);
        if (!desired) return;

        // If the metal chip UI exists, reuse it (keeps active styles consistent).
        var chips = document.querySelectorAll('#pdpMetalSelector .metal-chip');
        if (chips && chips.length) {
          for (var i = 0; i < chips.length; i++) {
            var c = chips[i];
            var key = normalizeMetalToken(c.getAttribute('data-metal-key') || '');
            var txt = normalizeMetalToken(c.textContent || '');
            if (desired === key || desired === txt) {
              swapPdpMetalImage(c);
              return;
            }
          }
        }

        // Fallback: find variant mapping from server-rendered JSON.
        var raw = window.__pdpMetalVariantMap || [];
        if (!Array.isArray(raw) || raw.length === 0) return;
        for (var j = 0; j < raw.length; j++) {
          var v = raw[j] || {};
          var vKey = normalizeMetalToken(v.key || v.slug || '');
          var vLabel = normalizeMetalToken(v.label || v.name || '');
          if (desired === vKey || desired === vLabel) {
            var src = v.image || v.photo || '';
            var list = Array.isArray(v.images) && v.images.length ? v.images : null;
            if (list) {
              setPdpGalleryImages(list);
            } else if (src) {
              // Fallback: swap only main image if only a single image is provided.
              setMainPdpImageSrc(src);
            }
            return;
          }
        }
      }

      document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest ? e.target.closest('.option-group .option-btn') : null;
        if (!btn) return;

        var group = btn.closest('.option-group');
        if (!group) return;

        group.querySelectorAll('.option-btn').forEach(function (b) {
          b.classList.remove('active');
        });
        btn.classList.add('active');

        var gv = group.getAttribute('data-option-group') || '';
        var v = btn.getAttribute('data-value') || btn.textContent || '';
        v = String(v).trim();
        if (gv === 'metal_type') {
          var el = document.getElementById('pdp_selected_metal_type');
          if (el) el.value = v;
          pdpUpdateGallery();
        } else if (gv === 'gold_karat') {
          var el2 = document.getElementById('pdp_selected_gold_karat');
          if (el2) el2.value = v;
        } else if (gv === 'diamond_color') {
          var el3 = document.getElementById('pdp_selected_diamond_color');
          if (el3) el3.value = v;
        } else if (gv === 'diamond_clarity') {
          var el4 = document.getElementById('pdp_selected_diamond_clarity');
          if (el4) el4.value = v;
        }

        if (gv === 'gold_karat' || gv === 'diamond_color' || gv === 'diamond_clarity') {
          document.dispatchEvent(new CustomEvent('pdp-jewelry-variant-change', {
            bubbles: true,
            detail: { source: gv },
          }));
        }
      });

      document.addEventListener('DOMContentLoaded', function () {
        if ((window.__pdpShapeVariantMap || []).length) {
          pdpUpdateGallery();
        }
      });
    })();
  </script>
@endonce

{{-- Dynamic price: loads after jQuery (footer). Script defers init until jQuery exists. --}}
@if (! empty($pdpEnableDynamicApiPrice))
<script>
/**
 * PDP dynamic pricing — jewelry-api (INR) → session currency.
 * Root cause fix: this partial renders BEFORE footer jQuery; do not call (jQuery)(window.jQuery) inline.
 */
(function () {
  'use strict';

  var API_URL =
    (typeof window.__jewelryDynamicPriceUrl === 'string' && window.__jewelryDynamicPriceUrl) ||
    @json(rtrim((string) env('JEWELRY_DYNAMIC_PRICE_URL', 'http://localhost/jewelry-api/dynamic-price.php'), '/'));

  var DEBOUNCE_OPTION_MS = 280;
  var DEBOUNCE_CARAT_MS = 480;

  function boot($) {
    if (window.__pdpDynamicPriceBooted) {
      return true;
    }

    console.log('[PDP Price] Booting dynamic pricing', { api: API_URL });

    window.__pdpJewelrySelection = window.__pdpJewelrySelection || {};
    window.__pdpDynamicPriceState = {
      loading: false,
      requestId: 0,
      xhr: null,
      debounceTimer: null,
      lastSuccessKey: null,
    };

    function $el(id) {
      return $('#' + id);
    }

    function readSelections() {
      var shapeRaw = String($el('pdp_selected_diamond_shape').val() || '').trim();
      var slider = document.getElementById('pdp_carat_slider');
      var sliderVal = slider ? String(slider.value || '').trim() : '';
      var hiddenCarat = String($el('pdp_selected_carat_weight').val() || '').trim();
      var selectedCarat = hiddenCarat || sliderVal;
      var productId = parseInt($el('item_id').val(), 10);

      return {
        product_id: isFinite(productId) ? productId : null,
        karat: String($el('pdp_selected_gold_karat').val() || '').trim(),
        quality: String($el('pdp_selected_diamond_clarity').val() || '').trim(),
        color: String($el('pdp_selected_diamond_color').val() || '').trim(),
        shape: shapeRaw.toLowerCase(),
        selected_carat: selectedCarat,
      };
    }

    function payloadKey(sel) {
      return JSON.stringify([
        sel.product_id,
        sel.karat,
        sel.quality,
        sel.color,
        sel.shape,
        sel.selected_carat,
      ]);
    }

    function getCurrencyParts() {
      return {
        sign: String($el('set_currency').val() || ''),
        direction: String($el('currency_direction').val() || '0'),
        rate: parseFloat($el('set_currency_val').val()) || 1,
        code: String($el('pdp_currency_code').val() || '').trim(),
      };
    }

    function formatMoneyDisplay(priceInr) {
      var c = getCurrencyParts();
      var conv = Math.round(parseFloat(priceInr) * c.rate * 100) / 100;
      if (!isFinite(conv)) {
        return '';
      }
      var dec = typeof window.decimal_separator !== 'undefined' ? window.decimal_separator : '.';
      var thou = typeof window.thousand_separator !== 'undefined' ? window.thousand_separator : ',';
      var toFixedFix = function (num, p) {
        var k = Math.pow(10, p);
        return '' + Math.round(num * k) / k;
      };
      var s = (toFixedFix(conv, 2) + '').split('.');
      if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thou);
      }
      if ((s[1] || '').length < 2) {
        s[1] = s[1] || '';
        s[1] += new Array(2 - s[1].length + 1).join('0');
      }
      var formatted = s.join(dec);
      if (c.direction === '1' || c.direction === 1) {
        return c.sign + formatted;
      }
      return formatted + c.sign;
    }

    function convertInrToSession(priceInr) {
      var n = Math.round(parseFloat(priceInr) * getCurrencyParts().rate * 100) / 100;
      return isFinite(n) ? n : null;
    }

    function extractApiPrices(json) {
      var data = json;
      if (data && data.data && typeof data.data === 'object') {
        var inner = data.data;
        if (inner.final_price != null || inner.price != null) {
          data = inner;
        }
      }
      var num = parseFloat(data && data.final_price != null ? data.final_price : data.price);
      if (!isFinite(num) || num <= 0) {
        return null;
      }
      return { final_price_inr: num, previous_inr: null };
    }

    function setLoading(on) {
      window.__pdpDynamicPriceState.loading = on;
      $('#pdp_price_area').toggleClass('is-pdp-price-loading', on).toggleClass('is-pdp-price-error', false);
      // Avoid layout shift: keep the element in flow and toggle only visibility.
      $('#pdp_price_loading_msg').css('visibility', on ? 'visible' : 'hidden');
    }

    function setPriceError(message) {
      $('#pdp_price_area').addClass('is-pdp-price-error').removeClass('is-pdp-price-loading');
      // Keep reserved space; hide the message.
      $('#pdp_price_loading_msg').css('visibility', 'hidden');
      console.warn('[PDP Price]', message);
    }

    function applyPrices(priceInr, sel) {
      var converted = convertInrToSession(priceInr);
      if (converted == null) {
        setPriceError('Currency conversion failed');
        return;
      }
      var display = formatMoneyDisplay(priceInr);
      $el('demo_price').val(String(converted));
      $el('pdp_line_base_price').val(String(priceInr));
      $('#main_price, .product-price').text(display);
      $el('pdp_dynamic_price_inr').val(String(priceInr));
      $el('pdp_converted_price').val(String(converted));

      window.__pdpJewelrySelection = {
        product_id: sel.product_id,
        karat: sel.karat,
        shape: sel.shape,
        color: sel.color,
        clarity: sel.quality,
        selected_carat: sel.selected_carat,
        dynamic_price_inr: priceInr,
        converted_price: converted,
        currency_code: getCurrencyParts().code,
      };

      $('.details-page-top-right-content .qtyValue').trigger('keyup');
      $(document).trigger('pdp-jewelry-price-updated', [window.__pdpJewelrySelection]);
      console.log('[PDP Price] DOM updated', { display: display, inr: priceInr });
    }

    function fetchDynamicPrice() {
      var sel = readSelections();
      if (!sel.product_id) {
        console.warn('[PDP Price] #item_id missing — cannot call API');
        return;
      }

      var key = payloadKey(sel);
      if (key === window.__pdpDynamicPriceState.lastSuccessKey) {
        console.log('[PDP Price] Skip — same as last successful payload');
        return;
      }

      if (window.__pdpDynamicPriceState.xhr && window.__pdpDynamicPriceState.xhr.readyState !== 4) {
        window.__pdpDynamicPriceState.xhr.abort();
      }

      var payload = {
        product_id: sel.product_id,
        karat: sel.karat,
        quality: sel.quality,
        color: sel.color,
        shape: sel.shape,
        selected_carat: sel.selected_carat,
      };

      console.log(payload);

      var reqId = ++window.__pdpDynamicPriceState.requestId;
      setLoading(true);

      window.__pdpDynamicPriceState.xhr = $.ajax({
        url: API_URL,
        method: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(payload),
        timeout: 15000,
      })
        .done(function (response) {
          if (reqId !== window.__pdpDynamicPriceState.requestId) {
            return;
          }
          console.log(response);
          var parsed = extractApiPrices(response);
          if (!parsed) {
            setPriceError('API response missing final_price');
            return;
          }
          window.__pdpDynamicPriceState.lastSuccessKey = key;
          applyPrices(parsed.final_price_inr, sel);
        })
        .fail(function (xhr, status, err) {
          if (reqId !== window.__pdpDynamicPriceState.requestId) {
            return;
          }
          var hint = status === 'error' && xhr.status === 0
            ? 'CORS or network — ensure API allows your site origin, or use same host as storefront'
            : (xhr.responseText || err || status);
          setPriceError('API failed: ' + hint);
          console.error('[PDP Price] API error', { status: status, http: xhr.status, body: xhr.responseText, err: err });
        })
        .always(function () {
          if (reqId === window.__pdpDynamicPriceState.requestId) {
            setLoading(false);
          }
        });
    }

    function schedulePriceUpdate(source) {
      console.log('[PDP Price] schedulePriceUpdate', source, readSelections());
      clearTimeout(window.__pdpDynamicPriceState.debounceTimer);
      var delay = source === 'carat' ? DEBOUNCE_CARAT_MS : DEBOUNCE_OPTION_MS;
      window.__pdpDynamicPriceState.debounceTimer = setTimeout(fetchDynamicPrice, delay);
    }

    function variantSource(evt) {
      return (evt && evt.detail && evt.detail.source) ? evt.detail.source : 'option';
    }

    document.addEventListener('pdp-jewelry-variant-change', function (evt) {
      console.log('[PDP Price] event: pdp-jewelry-variant-change', evt.detail);
      schedulePriceUpdate(variantSource(evt));
    });

    window.PdpDynamicPrice = {
      refresh: fetchDynamicPrice,
      readSelections: readSelections,
      schedule: schedulePriceUpdate,
    };

    window.__pdpDynamicPriceBooted = true;
    schedulePriceUpdate('init');
    return true;
  }

  function tryBoot() {
    var $ = window.jQuery;
    if ($ && $.fn) {
      return boot($);
    }
    return false;
  }

  if (!tryBoot()) {
    console.warn('[PDP Price] jQuery not ready yet — waiting for footer scripts…');
    var attempts = 0;
    var timer = setInterval(function () {
      attempts += 1;
      if (tryBoot() || attempts >= 150) {
        clearInterval(timer);
        if (attempts >= 150) {
          console.error('[PDP Price] jQuery never loaded — dynamic pricing disabled');
        }
      }
    }, 50);
  }
})();
</script>
@endif
<div class="mb-3 pdp-ring-size-input">
    <label class="filter-label mb-1">{{ __('Ring size') }}</label>
    <input type="text"
           id="pdp_ring_size"
           class="form-control form-control-sm"
           placeholder="{{ __('Enter your size (e.g. 6, 7, 8)') }}"
           inputmode="decimal"
           autocomplete="off">
    <small class="text-muted d-block mt-1">
        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#ringSizerModal">
            {{ __('Find your ring size') }}
        </a>
    </small>
</div>
@if ($show_pdp_engraving ?? false)
    <div class="mb-3 pdp-engrave luxury-pdp-engrave">
        {{-- Serialized engraving for existing cart AJAX (myscript.js reads #pdp_engraving) --}}
        <input type="hidden" id="pdp_engraving" value="" autocomplete="off">
        <p class="luxury-pdp-engrave__kicker mb-2">{{ __('Complimentary engraving (optional)') }}</p>

       
        <div class="engraving-section" id="luxury-engraving-section">
            <div class="engraving-toggle">
                <label class="engraving-toggle__label">
                    <input type="checkbox" id="luxury-engraving-checkbox" class="engraving-toggle__checkbox">
                    <span class="engraving-toggle__text">{{ __('✦ Add Engraving (Free)') }}</span>
                </label>
            </div>

            <div class="engraving-fields" id="luxury-engraving-fields" style="display: none;">

                <div class="engraving-field">
                    <label for="luxury-engraving-text">{{ __('Engraving text') }}</label>
                    <input type="text" id="luxury-engraving-text" maxlength="20"
                        placeholder="{{ __('Enter text (max 20 characters)') }}" class="engraving-input"
                        autocomplete="off">
                    <small class="engraving-char-count"><span id="luxury-engraving-char-count">0</span>/20
                        {{ __('characters') }}</small>
                </div>

                <div class="engraving-field">
                    <label for="luxury-engraving-font">{{ __('Font style') }}</label>
                    <select id="luxury-engraving-font" class="engraving-select">
                        <option value="">-- {{ __('Select font') }} --</option>
                        <option value="Classic Serif">{{ __('Classic Serif') }}</option>
                        <option value="Modern Script">{{ __('Modern Script') }}</option>
                        <option value="Block Print">{{ __('Block Print') }}</option>
                        <option value="Diamond Cut">{{ __('Diamond Cut') }}</option>
                    </select>
                </div>

                <div class="engraving-field">
                    <label for="luxury-engraving-placement">{{ __('Placement') }}</label>
                    <select id="luxury-engraving-placement" class="engraving-select">
                        <option value="">-- {{ __('Select placement') }} --</option>
                        <option value="Inside Band">{{ __('Inside band') }}</option>
                        <option value="Outside Band">{{ __('Outside band') }}</option>
                        <option value="Inside Bracelet">{{ __('Inside bracelet') }}</option>
                    </select>
                </div>

                <div class="engraving-field">
                    <label for="luxury-engraving-logo">{{ __('Upload logo / image (optional)') }}</label>
                    <input type="file" id="luxury-engraving-logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                        class="engraving-file-input">
                    <small class="engraving-char-count">{{ __('Accepted formats: JPG, PNG, SVG (max 2MB)') }}</small>

                    <div class="engraving-logo-preview" id="luxury-engraving-logo-preview-wrap" style="display:none;">
                        <p class="engraving-preview__label">{{ __('Logo preview') }}</p>
                        <img id="luxury-engraving-logo-preview-img" src="" alt="{{ __('Logo preview') }}"
                            style="max-width:120px; max-height:80px;">
                    </div>

                    <input type="hidden" id="luxury-engraving-logo-name" value="">
                </div>

                <div class="engraving-preview" id="luxury-engraving-preview">
                    <p class="engraving-preview__label">{{ __('Preview') }}</p>
                    <p class="engraving-preview__text" id="luxury-engraving-preview-text">{{ __('Your text here') }}</p>
                </div>
            </div>
        </div>
    </div>

    @php
        $luxuryPdpEngraveLabels = [
            'text' => __('Text'),
            'font' => __('Font'),
            'placement' => __('Placement'),
            'logoFile' => __('Logo file'),
            'alertTooLarge' => __('File is too large. Please upload an image under 2MB.'),
        ];
    @endphp

    <script>
        (function() {
            var L = @json($luxuryPdpEngraveLabels);
            var checkbox = document.getElementById('luxury-engraving-checkbox');
            var fields = document.getElementById('luxury-engraving-fields');
            var hidden = document.getElementById('pdp_engraving');
            var textInput = document.getElementById('luxury-engraving-text');
            var fontSelect = document.getElementById('luxury-engraving-font');
            var placementSelect = document.getElementById('luxury-engraving-placement');
            var charCountEl = document.getElementById('luxury-engraving-char-count');
            var previewText = document.getElementById('luxury-engraving-preview-text');
            var logoInput = document.getElementById('luxury-engraving-logo');
            var logoPreviewWrap = document.getElementById('luxury-engraving-logo-preview-wrap');
            var logoPreviewImg = document.getElementById('luxury-engraving-logo-preview-img');
            var logoNameInput = document.getElementById('luxury-engraving-logo-name');

            if (!checkbox || !fields || !hidden || !textInput || !fontSelect || !placementSelect || !previewText) {
                return;
            }

            var fallbackPreview = previewText.textContent || 'Your text here';

            function buildCartString() {
                if (!checkbox.checked) {
                    return '';
                }
                var text = (textInput.value || '').trim();
                var font = fontSelect.value || '';
                var placement = placementSelect.value || '';
                var logo = (logoNameInput && logoNameInput.value) ? String(logoNameInput.value).trim() : '';
                var parts = [];
                if (text) {
                    parts.push(L.text + ': ' + text);
                }
                if (font) {
                    parts.push(L.font + ': ' + font);
                }
                if (placement) {
                    parts.push(L.placement + ': ' + placement);
                }
                if (logo) {
                    parts.push(L.logoFile + ': ' + logo);
                }
                return parts.join(' | ');
            }

            function syncHidden() {
                hidden.value = buildCartString();
            }

            function applyFontPreview() {
                var fontMap = {
                    'Classic Serif': 'Georgia, "Times New Roman", serif',
                    'Modern Script': 'cursive, "Brush Script MT", serif',
                    'Block Print': 'ui-monospace, monospace',
                    'Diamond Cut': 'Copperplate, fantasy, serif'
                };
                previewText.style.fontFamily = fontMap[fontSelect.value] || 'inherit';
            }

            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    fields.style.display = 'block';
                    syncHidden();
                    applyFontPreview();
                } else {
                    fields.style.display = 'none';
                    textInput.value = '';
                    fontSelect.value = '';
                    placementSelect.value = '';
                    if (logoInput) logoInput.value = '';
                    if (logoNameInput) logoNameInput.value = '';
                    if (logoPreviewWrap) logoPreviewWrap.style.display = 'none';
                    if (logoPreviewImg) logoPreviewImg.removeAttribute('src');
                    previewText.textContent = fallbackPreview;
                    if (charCountEl) charCountEl.textContent = '0';
                    previewText.style.fontFamily = 'inherit';
                    syncHidden();
                }
            });

            textInput.addEventListener('input', function() {
                var c = this.value.length;
                if (charCountEl) charCountEl.textContent = String(c);
                previewText.textContent = this.value.trim() ? this.value : fallbackPreview;
                syncHidden();
            });

            fontSelect.addEventListener('change', function() {
                applyFontPreview();
                syncHidden();
            });

            placementSelect.addEventListener('change', syncHidden);

            if (logoInput && logoPreviewWrap && logoPreviewImg && logoNameInput) {
                logoInput.addEventListener('change', function() {
                    var file = this.files && this.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert(L.alertTooLarge);
                            this.value = '';
                            logoNameInput.value = '';
                            logoPreviewWrap.style.display = 'none';
                            logoPreviewImg.removeAttribute('src');
                            syncHidden();
                            return;
                        }
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            logoPreviewImg.src = e.target.result || '';
                            logoPreviewWrap.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                        logoNameInput.value = file.name || '';
                        syncHidden();
                    } else {
                        logoPreviewWrap.style.display = 'none';
                        logoNameInput.value = '';
                        logoPreviewImg.removeAttribute('src');
                        syncHidden();
                    }
                });
            }

            syncHidden();
        })();
    </script>
@endif

@once
    <script>
        function swapPdpMetalImage(btn) {
            var key = btn.getAttribute('data-metal-key') || '';
            var src = btn.getAttribute('data-metal-img');
            if (!src) return;
            var box = document.getElementById('pdpMetalSelector');
            if (box) {
                box.querySelectorAll('.metal-chip').forEach(function (b) {
                    b.classList.remove('metal-chip--active');
                });
            }
            btn.classList.add('metal-chip--active');
            var hidden = document.getElementById('pdp_selected_metal');
            if (hidden) hidden.value = key || '';
            // Swap ONLY the main PDP image (first slide). Do not change thumbnails/secondary gallery.
            setMainPdpImageSrc(src);
        }
    </script>
@endonce
