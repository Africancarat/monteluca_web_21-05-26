@php
    $excludeItemId = isset($item) ? (int) $item->id : 0;
@endphp
<script src="{{ asset('assets/back/js/select2.js') }}"></script>
<script>
    (function () {
        var $el = $('#complete_the_look_ids');
        if (!$el.length || typeof $.fn.select2 !== 'function') {
            return;
        }

        $el.select2({
            theme: 'bootstrap',
            width: '100%',
            placeholder: @json(__('Search products by name or SKU…')),
            allowClear: true,
            ajax: {
                url: @json(route('back.item.complete_the_look.search')),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || '',
                        exclude_id: {{ $excludeItemId }}
                    };
                },
                processResults: function (data) {
                    return data;
                },
                cache: true
            },
            minimumInputLength: 0
        });
    })();
</script>
