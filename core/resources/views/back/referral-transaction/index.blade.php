@extends('master.back')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-0 bc-title"><b>{{ __('Referral Transactions') }}</b></h3>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="gd-responsive-table">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('Order') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Referral Code') }}</th>
                            <th>{{ __('Discount') }}</th>
                            <th>{{ __('Cashback') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($datas as $row)
                            <tr>
                                <td>
                                    @if ($row->order)
                                        <a href="{{ route('back.order.edit', $row->order_id) }}">#{{ $row->order->transaction_number }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($row->customer)
                                        {{ $row->customer->displayName() }}
                                    @else
                                        {{ __('Guest') }}
                                    @endif
                                </td>
                                <td>{{ $row->referralCode?->referral_code ?? '—' }}</td>
                                <td>{{ PriceHelper::adminCurrencyPrice($row->discount_amount) }}</td>
                                <td>{{ PriceHelper::adminCurrencyPrice($row->cashback_amount) }}</td>
                                <td><span class="badge badge-secondary text-capitalize">{{ $row->status }}</span></td>
                                <td>{{ $row->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">{{ __('No referral transactions yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $datas->links() }}</div>
        </div>
    </div>
</div>
@endsection
