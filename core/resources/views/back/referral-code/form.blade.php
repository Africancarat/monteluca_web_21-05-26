<div class="form-group">
    <label for="user_id">{{ __('Assigned user') }} *</label>
    <select name="user_id" id="user_id" class="form-control" required>
        <option value="">{{ __('Select user') }}</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}" {{ (int) old('user_id', $data->user_id ?? 0) === $user->id ? 'selected' : '' }}>
                {{ $user->displayName() }} ({{ $user->email }})
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="referral_code">{{ __('Referral code') }} *</label>
    <input type="text" name="referral_code" class="form-control text-uppercase" id="referral_code"
        value="{{ old('referral_code', $data->referral_code ?? '') }}" required>
</div>

<div class="form-group">
    <label for="discount_percent">{{ __('Discount percentage') }} *</label>
    <input type="number" name="discount_percent" class="form-control" id="discount_percent" min="0" max="100" step="0.01"
        value="{{ old('discount_percent', $data->discount_percent ?? 5) }}" required>
</div>

<div class="form-group">
    <label for="cashback_percent">{{ __('Cashback percentage') }} *</label>
    <input type="number" name="cashback_percent" class="form-control" id="cashback_percent" min="0" max="100" step="0.01"
        value="{{ old('cashback_percent', $data->cashback_percent ?? 5) }}" required>
</div>

<div class="form-group">
    <label for="status">{{ __('Status') }} *</label>
    <select name="status" id="status" class="form-control" required>
        <option value="1" {{ (int) old('status', $data->status ?? 1) === 1 ? 'selected' : '' }}>{{ __('Active') }}</option>
        <option value="0" {{ (int) old('status', $data->status ?? 1) === 0 ? 'selected' : '' }}>{{ __('Inactive') }}</option>
    </select>
</div>
