@foreach ($datas as $data)
    <tr>
        <td>{{ $data->referral_code }}</td>
        <td>{{ $data->owner ? $data->owner->displayName() : '—' }}</td>
        <td>{{ $data->discount_percent }}%</td>
        <td>{{ $data->cashback_percent }}%</td>
        <td>{{ $data->total_usage }}</td>
        <td>{{ PriceHelper::adminCurrencyPrice($data->total_earned_cashback) }}</td>
        <td>
            <div class="dropdown">
                <button class="btn btn-{{ $data->status == 1 ? 'success' : 'danger' }} btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ $data->status == 1 ? __('Enabled') : __('Disabled') }}
                </button>
                <div class="dropdown-menu animated--fade-in">
                    <a class="dropdown-item" href="{{ route('back.referral-code.status', [$data->id, 1]) }}">{{ __('Enable') }}</a>
                    <a class="dropdown-item" href="{{ route('back.referral-code.status', [$data->id, 0]) }}">{{ __('Disable') }}</a>
                </div>
            </div>
        </td>
        <td>{{ $data->created_at->format('M d, Y') }}</td>
        <td>
            <div class="action-list">
                <a class="btn btn-secondary btn-sm" href="{{ route('back.referral-code.edit', $data->id) }}">
                    <i class="fas fa-edit"></i>
                </a>
                <a class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-delete" href="javascript:;"
                    data-href="{{ route('back.referral-code.destroy', $data->id) }}">
                    <i class="fas fa-trash-alt"></i>
                </a>
            </div>
        </td>
    </tr>
@endforeach
