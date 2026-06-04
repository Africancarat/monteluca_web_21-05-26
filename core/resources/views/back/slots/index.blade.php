@extends('master.back')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/* ── Monteluca brand tokens ─────────────────────────────────────────────── */
:root {
    --ml-gold:   #b8a88a;
    --ml-dark:   #1a1a1a;
    --ml-light:  #faf8f5;
    --ml-border: #e5e0da;
    --ml-text:   #555;
}

.ml-page-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px;
    font-weight: 600;
    color: var(--ml-dark);
    letter-spacing: .01em;
}

.ml-label {
    font-family: 'Jost', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #aaa;
    margin-bottom: 6px;
}

/* ── Luxury button ──────────────────────────────────────────────────────── */
.btn-ml {
    font-family: 'Jost', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: .08em;
    text-transform: uppercase;
    border: 1.5px solid var(--ml-dark);
    background: var(--ml-dark);
    color: #fff;
    padding: 8px 20px;
    border-radius: 3px;
    transition: background .2s, color .2s;
    text-decoration: none;
    display: inline-block;
}
.btn-ml:hover { background: #333; color: #fff; text-decoration: none; }

.btn-ml-outline {
    background: transparent;
    color: var(--ml-dark);
    border: 1.5px solid var(--ml-dark);
}
.btn-ml-outline:hover { background: var(--ml-dark); color: #fff; }

.btn-ml-danger {
    border-color: #c0392b;
    background: #c0392b;
    color: #fff;
}
.btn-ml-danger:hover { background: #a93226; border-color: #a93226; color: #fff; }

/* ── Filter bar ─────────────────────────────────────────────────────────── */
.ml-filter-bar {
    background: var(--ml-light);
    border: 1px solid var(--ml-border);
    border-radius: 6px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}
.ml-filter-bar .form-control {
    font-family: 'Jost', sans-serif;
    font-size: 13px;
    border-color: var(--ml-border);
    border-radius: 4px;
    height: 36px;
}

/* ── Bulk action bar ────────────────────────────────────────────────────── */
.ml-bulk-bar {
    display: none;
    background: #fff8f0;
    border: 1px solid #ffc107;
    border-radius: 6px;
    padding: 12px 18px;
    margin-bottom: 16px;
    align-items: center;
    gap: 14px;
    font-family: 'Jost', sans-serif;
    font-size: 13px;
}
.ml-bulk-bar.is-visible { display: flex; }

/* ── Table ──────────────────────────────────────────────────────────────── */
.ml-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Jost', sans-serif;
    font-size: 13px;
}
.ml-table thead tr {
    border-bottom: 2px solid var(--ml-dark);
}
.ml-table thead th {
    font-family: 'Jost', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #888;
    padding: 10px 12px;
    background: #fff;
    white-space: nowrap;
}
.ml-table tbody tr {
    border-bottom: 1px solid var(--ml-border);
    transition: background .1s;
}
.ml-table tbody tr:hover { background: var(--ml-light); }
.ml-table tbody td {
    padding: 10px 12px;
    color: var(--ml-text);
    vertical-align: middle;
}
.ml-table tbody tr.is-selected { background: #fff8f0; }

/* ── Status badges ──────────────────────────────────────────────────────── */
.ml-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-family: 'Jost', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .04em;
}
.ml-badge-available { background: #e8f5e9; color: #2e7d32; }
.ml-badge-booked    { background: #fce4e4; color: #c62828; }

/* ── Checkbox ───────────────────────────────────────────────────────────── */
.ml-checkbox {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: var(--ml-dark);
}

/* ── Pagination override ─────────────────────────────────────────────────── */
.ml-pagination .page-link {
    font-family: 'Jost', sans-serif;
    font-size: 12px;
    color: var(--ml-dark);
    border-color: var(--ml-border);
}
.ml-pagination .page-item.active .page-link {
    background: var(--ml-dark);
    border-color: var(--ml-dark);
}

/* ── Card wrapper ───────────────────────────────────────────────────────── */
.ml-card {
    background: #fff;
    border: 1px solid var(--ml-border);
    border-radius: 8px;
    padding: 28px;
    margin-bottom: 24px;
}
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="ml-card" style="padding:20px 28px;">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">
            <div>
                <div class="ml-label">Consultation</div>
                <h1 class="ml-page-title mb-0">Consultant Slots</h1>
            </div>
            <a href="{{ route('back.slots.create') }}" class="btn-ml">
                + Open new slots
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success" style="font-family:'Jost',sans-serif;font-size:13px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="font-family:'Jost',sans-serif;font-size:13px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('back.slots.index') }}" class="ml-filter-bar">
        <div>
            <div class="ml-label" style="margin-bottom:3px;">Date</div>
            <input type="date" name="date" class="form-control" style="width:160px;"
                   value="{{ request('date') }}">
        </div>
        <div>
            <div class="ml-label" style="margin-bottom:3px;">Status</div>
            <select name="status" class="form-control" style="width:140px;">
                <option value="">All statuses</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="booked"    {{ request('status') === 'booked'    ? 'selected' : '' }}>Booked</option>
            </select>
        </div>
        <div style="align-self:flex-end;display:flex;gap:8px;">
            <button type="submit" class="btn-ml" style="padding:7px 18px;">Filter</button>
            <a href="{{ route('back.slots.index') }}" class="btn-ml btn-ml-outline" style="padding:7px 18px;">Reset</a>
        </div>
    </form>

    {{-- Bulk action bar --}}
    <form method="POST" action="{{ route('back.slots.bulk-destroy') }}" id="bulkForm">
        @csrf
        <div class="ml-bulk-bar" id="bulkBar">
            <span id="selectedCount" style="font-weight:500;"></span>
            <button type="submit" class="btn-ml btn-ml-danger" style="padding:6px 16px;"
                    onclick="return confirm('Delete selected available slots? Booked slots will be skipped.')">
                Delete selected
            </button>
            <button type="button" class="btn-ml btn-ml-outline" style="padding:6px 16px;" onclick="clearSelection()">
                Clear selection
            </button>
        </div>

        {{-- Slots table --}}
        <div class="ml-card" style="padding:0;overflow:hidden;">
            <table class="ml-table">
                <thead>
                    <tr>
                        <th style="width:40px;padding-left:20px;">
                            <input type="checkbox" class="ml-checkbox" id="selectAll"
                                   title="Select all available slots on this page">
                        </th>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th style="width:80px;">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slots as $slot)
                    <tr id="row-{{ $slot->id }}" class="{{ $slot->is_booked ? '' : 'selectable-row' }}">
                        <td style="padding-left:20px;">
                            @if(! $slot->is_booked)
                            <input type="checkbox"
                                   class="ml-checkbox row-check"
                                   name="ids[]"
                                   value="{{ $slot->id }}">
                            @endif
                        </td>
                        <td style="font-weight:500;color:var(--ml-dark);">
                            {{ \Carbon\Carbon::parse($slot->slot_date)->format('d M Y') }}
                        </td>
                        <td style="color:#888;">
                            {{ \Carbon\Carbon::parse($slot->slot_date)->format('l') }}
                        </td>
                        <td style="font-family:'Cormorant Garamond',serif;font-size:15px;font-weight:600;">
                            {{ \Carbon\Carbon::parse($slot->slot_time)->format('H:i') }}
                        </td>
                        <td>
                            @if($slot->is_booked)
                                <span class="ml-badge ml-badge-booked">Booked</span>
                            @else
                                <span class="ml-badge ml-badge-available">Available</span>
                            @endif
                        </td>
                        <td>
                            @if(! $slot->is_booked)
                            <button type="button"
                                    class="btn-ml btn-ml-danger"
                                    style="padding:4px 12px;font-size:11px;"
                                    onclick="deleteSingle({{ $slot->id }})">
                                Delete
                            </button>
                            @else
                                <span style="color:#ccc;font-size:12px;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:40px;color:#aaa;font-family:'Jost',sans-serif;">
                            No slots found.
                            <a href="{{ route('back.slots.create') }}" style="color:var(--ml-dark);">Open new slots</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </form>

    {{-- Single-slot delete (hidden form) --}}
    <form method="POST" id="singleDeleteForm" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- Pagination --}}
    <div class="ml-pagination mt-3">
        {{ $slots->links() }}
    </div>

</div>

<script>
// ── Select all ───────────────────────────────────────────────────────────────
document.getElementById('selectAll').addEventListener('change', function () {
    var checked = this.checked;
    document.querySelectorAll('.row-check').forEach(function (cb) {
        cb.checked = checked;
        cb.closest('tr').classList.toggle('is-selected', checked);
    });
    updateBulkBar();
});

// ── Individual row check ─────────────────────────────────────────────────────
document.querySelectorAll('.row-check').forEach(function (cb) {
    cb.addEventListener('change', function () {
        cb.closest('tr').classList.toggle('is-selected', cb.checked);
        updateBulkBar();

        // Sync select-all state
        var all  = document.querySelectorAll('.row-check');
        var done = document.querySelectorAll('.row-check:checked');
        document.getElementById('selectAll').indeterminate = done.length > 0 && done.length < all.length;
        document.getElementById('selectAll').checked = done.length === all.length && all.length > 0;
    });
});

function updateBulkBar() {
    var count = document.querySelectorAll('.row-check:checked').length;
    var bar   = document.getElementById('bulkBar');
    var label = document.getElementById('selectedCount');
    if (count > 0) {
        bar.classList.add('is-visible');
        label.textContent = count + ' slot' + (count > 1 ? 's' : '') + ' selected';
    } else {
        bar.classList.remove('is-visible');
    }
}

function clearSelection() {
    document.querySelectorAll('.row-check').forEach(function (cb) {
        cb.checked = false;
        cb.closest('tr').classList.remove('is-selected');
    });
    document.getElementById('selectAll').checked = false;
    document.getElementById('selectAll').indeterminate = false;
    updateBulkBar();
}

// ── Single delete ────────────────────────────────────────────────────────────
function deleteSingle(id) {
    if (! confirm('Delete this slot?')) return;
    var form = document.getElementById('singleDeleteForm');
    form.action = '/admin/slots/' + id;
    form.submit();
}
</script>
@endsection
