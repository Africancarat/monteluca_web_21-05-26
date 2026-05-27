@if (!isset($error))
    @php
        $mainSteps = [
            'Pending' => [
                'label' => __('Pending'),
                'description' => __('Order received and awaiting processing.'),
                'icon' => 'fas fa-receipt',
            ],
            'In Progress' => [
                'label' => __('In Progress'),
                'description' => __('Your order is being prepared.'),
                'icon' => 'fas fa-gem',
            ],
            'Ready to Ship' => [
                'label' => __('Ready to Ship'),
                'description' => __('Your order is ready to be shipped.'),
                'icon' => 'fas fa-truck',
            ],
            'Shipped' => [
                'label' => __('Shipped'),
                'description' => __('Your order has left our studio.'),
                'icon' => 'fas fa-shipping-fast',
            ],
            'Delivered' => [
                'label' => __('Delivered'),
                'description' => __('Your order has been delivered.'),
                'icon' => 'fas fa-check',
            ],
        ];

        $terminalSteps = [
            'Canceled' => [
                'label' => __('Cancelled'),
                'description' => __('This order has been cancelled.'),
                'class' => 'is-cancelled',
                'icon' => 'fas fa-times',
            ],
            'Cancelled' => [
                'label' => __('Cancelled'),
                'description' => __('This order has been cancelled.'),
                'class' => 'is-cancelled',
                'icon' => 'fas fa-times',
            ],
            'Refunded' => [
                'label' => __('Refunded'),
                'description' => __('A refund has been issued for this order.'),
                'class' => 'is-refunded',
                'icon' => 'fas fa-undo',
            ],
        ];

        $tracks = collect($track_orders ?? []);
        $trackByTitle = $tracks->keyBy('title');
        $terminalTitle = $tracks->pluck('title')->first(fn ($title) => isset($terminalSteps[$title]));
        $currentTitle = $terminalTitle ?: ($tracks->last()->title ?? ($order->order_status ?? 'Pending'));
        $currentMainIndex = array_search($currentTitle, array_keys($mainSteps), true);
        $currentMainIndex = $currentMainIndex === false ? -1 : $currentMainIndex;
    @endphp

    <style>
        .luxury-order-timeline {
            background: #fff;
            border: 1px solid rgba(184, 134, 11, .18);
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(10, 10, 10, .06);
            padding: 28px;
        }

        .luxury-order-timeline__header {
            border-bottom: 1px solid rgba(10, 10, 10, .08);
            margin-bottom: 24px;
            padding-bottom: 16px;
        }

        .luxury-order-timeline__eyebrow {
            color: #9a7a31;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .16em;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .luxury-order-timeline__order {
            color: #111;
            font-size: 20px;
            margin: 0;
        }

        .luxury-progress {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            list-style: none;
            margin: 0;
            padding: 0;
            position: relative;
        }

        .luxury-progress__item {
            background: #faf8f3;
            border: 1px solid rgba(10, 10, 10, .08);
            border-radius: 16px;
            padding: 18px 14px;
            position: relative;
        }

        .luxury-progress__icon {
            align-items: center;
            background: #fff;
            border: 1px solid rgba(10, 10, 10, .15);
            border-radius: 999px;
            color: #777;
            display: inline-flex;
            height: 42px;
            justify-content: center;
            margin-bottom: 12px;
            width: 42px;
        }

        .luxury-progress__item.is-complete,
        .luxury-progress__item.is-current {
            border-color: rgba(184, 134, 11, .55);
        }

        .luxury-progress__item.is-complete .luxury-progress__icon,
        .luxury-progress__item.is-current .luxury-progress__icon {
            background: #0a0a0a;
            border-color: #0a0a0a;
            color: #fff;
        }

        .luxury-progress__item.is-current {
            box-shadow: 0 10px 28px rgba(184, 134, 11, .14);
        }

        .luxury-progress__title {
            color: #111;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .luxury-progress__date {
            color: #9a7a31;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .luxury-progress__desc {
            color: #777;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
        }

        .luxury-terminal-status {
            border-radius: 16px;
            margin-top: 20px;
            padding: 18px;
        }

        .luxury-terminal-status.is-cancelled {
            background: #fff5f5;
            border: 1px solid rgba(220, 38, 38, .25);
            color: #991b1b;
        }

        .luxury-terminal-status.is-refunded {
            background: #fff7ed;
            border: 1px solid rgba(234, 88, 12, .25);
            color: #9a3412;
        }

        @media (max-width: 767px) {
            .luxury-progress {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="progress-area-step pt-4">
        <div class="luxury-order-timeline">
            <div class="luxury-order-timeline__header">
                <div class="luxury-order-timeline__eyebrow">{{ __('Order Tracking') }}</div>
                <h4 class="luxury-order-timeline__order">{{ __('Order') }} #{{ $order->transaction_number }}</h4>
            </div>

            <ul class="luxury-progress">
                @foreach ($mainSteps as $title => $step)
                    @php
                        $track = $trackByTitle->get($title);
                        $stepIndex = $loop->index;
                        $isComplete = (bool) $track || ($currentMainIndex >= $stepIndex && ! $terminalTitle);
                        $isCurrent = $currentTitle === $title && ! $terminalTitle;
                    @endphp
                    <li class="luxury-progress__item {{ $isComplete ? 'is-complete' : '' }} {{ $isCurrent ? 'is-current' : '' }}">
                        <div class="luxury-progress__icon"><i class="{{ $step['icon'] }}"></i></div>
                        <div class="luxury-progress__title">{{ $step['label'] }}</div>
                        <div class="luxury-progress__date">
                            @if ($track)
                                {{ $track->created_at->format('l, d M, Y') }}
                            @else
                                {{ __('Soon') }}
                            @endif
                        </div>
                        <p class="luxury-progress__desc">{{ $step['description'] }}</p>
                    </li>
                @endforeach
            </ul>

            @if ($terminalTitle)
                @php
                    $terminal = $terminalSteps[$terminalTitle];
                    $terminalTrack = $trackByTitle->get($terminalTitle);
                @endphp
                <div class="luxury-terminal-status {{ $terminal['class'] }}">
                    <strong><i class="{{ $terminal['icon'] }}"></i> {{ $terminal['label'] }}</strong>
                    @if ($terminalTrack)
                        <div>{{ $terminalTrack->created_at->format('l, d M, Y') }}</div>
                    @endif
                    <div>{{ $terminal['description'] }}</div>
                </div>
            @endif
        </div>
    </div>
@else
    <p>{{ __('Order Not Found') }}</p>
@endif
