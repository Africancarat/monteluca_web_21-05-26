@extends('master.back')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-0 bc-title"><b>{{ __('Inventory Reservations') }}</b></h3>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="status">{{ __('Status') }}</label>
                        <select name="status" id="status" class="form-control">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                            <option value="reserved" {{ $status === 'reserved' ? 'selected' : '' }}>{{ __('Reserved') }}</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                            <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                            <option value="released" {{ $status === 'released' ? 'selected' : '' }}>{{ __('Released') }}</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Variant') }}</th>
                            <th>{{ __('Reserved Qty') }}</th>
                            <th>{{ __('Expires') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Session') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($locks as $lock)
                            <tr>
                                <td>
                                    @if ($lock->item)
                                        {{ $lock->item->name }}
                                        @if ($lock->item->sku)
                                            <small class="text-muted d-block">{{ $lock->item->sku }}</small>
                                        @endif
                                    @else
                                        #{{ $lock->item_id }}
                                    @endif
                                </td>
                                <td>{{ $lock->variant_key ?: '—' }}</td>
                                <td>{{ $lock->qty }}</td>
                                <td>
                                    {{ $lock->expires_at?->format('Y-m-d H:i') }}
                                    @if ($lock->status === 'reserved' && $lock->expires_at && $lock->expires_at->isPast())
                                        <span class="badge badge-warning">{{ __('Expired (pending job)') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($lock->user)
                                        {{ $lock->user->first_name ?? $lock->user->email }}
                                    @else
                                        {{ __('Guest') }}
                                    @endif
                                </td>
                                <td><small>{{ Str::limit($lock->session_id, 16) }}</small></td>
                                <td><span class="badge badge-secondary">{{ $lock->status }}</span></td>
                                <td>
                                    @if ($lock->status === 'reserved')
                                        <form action="{{ route('back.inventory.release', $lock->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">{{ __('Release') }}</button>
                                        </form>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">{{ __('No reservations found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $locks->links() }}
        </div>
    </div>
</div>
@endsection
