@extends('master.back')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Jost:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
:root { --ml-gold:#b8a88a; --ml-dark:#1a1a1a; --ml-light:#faf8f5; --ml-border:#e5e0da; --ml-text:#555; }

.ml-page-title { font-family:'Cormorant Garamond',serif; font-size:26px; font-weight:600; color:var(--ml-dark); }
.ml-label      { font-family:'Jost',sans-serif; font-size:11px; font-weight:500; letter-spacing:.1em; text-transform:uppercase; color:#aaa; margin-bottom:6px; display:block; }
.ml-section-h  { font-family:'Cormorant Garamond',serif; font-size:20px; font-weight:600; color:var(--ml-dark); margin-bottom:4px; }
.ml-card       { background:#fff; border:1px solid var(--ml-border); border-radius:8px; padding:28px; margin-bottom:20px; }
.ml-input      { font-family:'Jost',sans-serif; font-size:13px; border:1.5px solid var(--ml-border); border-radius:4px; padding:9px 12px; width:100%; color:var(--ml-dark); transition:border-color .15s; }
.ml-input:focus{ outline:none; border-color:var(--ml-dark); }

.btn-ml        { font-family:'Jost',sans-serif; font-size:12px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; border:2px solid var(--ml-dark); background:var(--ml-dark); color:#fff; padding:10px 24px; border-radius:3px; cursor:pointer; transition:background .15s; display:inline-block; text-decoration:none; }
.btn-ml:hover  { background:#333; color:#fff; text-decoration:none; }
.btn-ml-outline{ background:transparent; color:var(--ml-dark); }
.btn-ml-outline:hover { background:var(--ml-dark); color:#fff; }

/* Day toggles */
.day-toggle    { display:inline-flex; align-items:center; justify-content:center; min-width:58px; padding:9px 14px; border:2px solid #ccc; border-radius:4px; cursor:pointer; font-family:'Jost',sans-serif; font-size:13px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#1a1a1a; background:#fff; user-select:none; transition:all .15s; margin:3px; }
.day-toggle:hover    { border-color:#1a1a1a; }
.day-toggle.selected { background:#1a1a1a; border-color:#1a1a1a; color:#fff; }
.day-toggle input    { display:none; }

/* Time toggles */
.time-toggle   { display:inline-flex; align-items:center; justify-content:center; min-width:70px; padding:9px 12px; border:2px solid #ccc; border-radius:4px; cursor:pointer; font-family:'Jost',sans-serif; font-size:14px; font-weight:700; color:#1a1a1a; background:#fff; user-select:none; transition:all .15s; margin:3px; }
.time-toggle:hover    { border-color:#1a1a1a; }
.time-toggle.selected { background:#1a1a1a; border-color:#1a1a1a; color:#fff; }
.time-toggle input    { display:none; }

.quick-btn     { font-family:'Jost',sans-serif; font-size:11px; font-weight:600; letter-spacing:.04em; text-transform:uppercase; border:1.5px solid var(--ml-border); background:#fff; color:var(--ml-text); padding:5px 12px; border-radius:3px; cursor:pointer; transition:all .15s; }
.quick-btn:hover { border-color:var(--ml-dark); color:var(--ml-dark); }

.whole-day-card { background:var(--ml-light); border:2px solid var(--ml-border); border-radius:6px; padding:14px 18px; margin-bottom:14px; display:flex; align-items:center; gap:14px; cursor:pointer; transition:border-color .15s; }
.whole-day-card:hover   { border-color:var(--ml-dark); }
.whole-day-card.selected{ border-color:var(--ml-dark); background:#f0f0f0; }
.whole-day-tag  { background:var(--ml-dark); color:#fff; font-family:'Jost',sans-serif; font-size:10px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; padding:2px 8px; border-radius:2px; }

.info-card     { background:var(--ml-light); border:1px solid var(--ml-border); border-radius:8px; padding:24px; }
.info-card h5  { font-family:'Cormorant Garamond',serif; font-size:20px; font-weight:600; margin-bottom:12px; }
.info-card li  { font-family:'Jost',sans-serif; font-size:13px; color:var(--ml-text); line-height:2.2; }
.info-example  { background:#fff; border-left:3px solid var(--ml-gold); padding:12px 16px; margin-top:16px; border-radius:0 4px 4px 0; font-family:'Jost',sans-serif; font-size:13px; color:var(--ml-text); }

.ml-alert-err  { background:#fce4e4; border:1.5px solid #e57373; border-radius:6px; padding:14px 18px; margin-bottom:16px; font-family:'Jost',sans-serif; font-size:13px; color:#b71c1c; }
.ml-alert-ok   { background:#e8f5e9; border:1.5px solid #81c784; border-radius:6px; padding:14px 18px; margin-bottom:16px; font-family:'Jost',sans-serif; font-size:13px; color:#1b5e20; }
.ml-warn       { background:#fff8e1; border:1.5px solid #ffc107; border-radius:6px; padding:12px 16px; margin-bottom:14px; font-family:'Jost',sans-serif; font-size:13px; color:#e65100; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    <div class="ml-card" style="padding:20px 28px;">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">
            <div>
                <span class="ml-label">Consultation</span>
                <h1 class="ml-page-title mb-0">Open New Slots</h1>
            </div>
            <a href="{{ route('back.slots.index') }}" class="btn-ml btn-ml-outline">← Back to slots</a>
        </div>
    </div>

    {{-- Errors --}}
    @if($errors->any())
        <div class="ml-alert-err">
            <strong>Could not create slots:</strong>
            <ul class="mb-0 mt-1" style="padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif
    @if(session('success'))
        <div class="ml-alert-ok">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('back.slots.store') }}" id="slotForm">
                @csrf

                {{-- Date range --}}
                <div class="ml-card">
                    <div class="ml-section-h">Date range</div>
                    <p style="font-family:'Jost',sans-serif;font-size:13px;color:#888;margin-bottom:16px;">Same date in both fields = single day only.</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <span class="ml-label">From</span>
                            <input type="date" name="date_from" id="dateFrom" class="ml-input"
                                   value="{{ old('date_from', now()->toDateString()) }}"
                                   min="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="ml-label">To</span>
                            <input type="date" name="date_to" id="dateTo" class="ml-input"
                                   value="{{ old('date_to', now()->toDateString()) }}"
                                   min="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    {{-- Date summary --}}
                    <div id="dateSummary" style="font-family:'Jost',sans-serif;font-size:12px;color:#888;margin-top:4px;"></div>
                </div>

                {{-- Available days — NO default, user must choose --}}
                <div class="ml-card">
                    <div class="ml-section-h">Available days</div>
                    <p style="font-family:'Jost',sans-serif;font-size:13px;color:#888;margin-bottom:14px;">
                        Select the days the consultant is available. <strong style="color:#c62828;">Nothing is preselected — you must choose.</strong>
                    </p>

                    @php $oldDays = old('available_days', []); @endphp

                    <div id="dayToggles" style="margin-bottom:14px;">
                        @foreach([1=>'Mon', 2=>'Tue', 3=>'Wed', 4=>'Thu', 5=>'Fri', 6=>'Sat', 0=>'Sun'] as $num => $name)
                        <label class="day-toggle {{ in_array($num, array_map('intval', $oldDays)) ? 'selected' : '' }}">
                            <input type="checkbox" name="available_days[]" value="{{ $num }}"
                                   {{ in_array($num, array_map('intval', $oldDays)) ? 'checked' : '' }}>
                            {{ $name }}
                        </label>
                        @endforeach
                    </div>

                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        <button type="button" class="quick-btn" onclick="selectAllDays()">All days</button>
                        <button type="button" class="quick-btn" onclick="selectWeekdays()">Mon – Fri</button>
                        <button type="button" class="quick-btn" onclick="selectWeekend()">Sat – Sun</button>
                        <button type="button" class="quick-btn" onclick="clearDays()">Clear</button>
                    </div>

                    {{-- Mismatch warning --}}
                    <div id="mismatchWarn" class="ml-warn" style="display:none;margin-top:14px;"></div>

                    {{-- No day selected warning --}}
                    <div id="noDayWarn" class="ml-warn" style="display:none;margin-top:14px;">
                        ⚠ No days selected. Please select at least one day.
                    </div>
                </div>

                {{-- Time slots --}}
                <div class="ml-card">
                    <div class="ml-section-h">Time slots</div>
                    <p style="font-family:'Jost',sans-serif;font-size:13px;color:#888;margin-bottom:14px;">Select which times to open on each available day.</p>

                    {{-- Whole day shortcut --}}
                    <div class="whole-day-card" id="wholeDayCard">
                        <div style="flex:1;">
                            <div style="font-family:'Jost',sans-serif;font-size:14px;font-weight:600;color:var(--ml-dark);">
                                Available whole day
                                <span class="whole-day-tag" style="margin-left:8px;">Shortcut</span>
                            </div>
                            <div style="font-family:'Jost',sans-serif;font-size:12px;color:#888;margin-top:2px;">Selects all times at once (09:00 – 17:00)</div>
                        </div>
                        <input type="checkbox" id="wholeDayToggle"
                               style="width:18px;height:18px;accent-color:var(--ml-dark);cursor:pointer;"
                               onclick="event.stopPropagation(); applyWholeDay(this.checked);">
                    </div>

                    @php
                        $preset   = ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
                        $oldTimes = old('times', []);
                    @endphp
                    <div id="timeToggles" style="margin-bottom:14px;">
                        @foreach($preset as $t)
                        <label class="time-toggle {{ in_array($t, $oldTimes) ? 'selected' : '' }}">
                            <input type="checkbox" name="times[]" value="{{ $t }}"
                                   {{ in_array($t, $oldTimes) ? 'checked' : '' }}>
                            {{ $t }}
                        </label>
                        @endforeach
                    </div>

                    <div id="customTimes"></div>
                    <button type="button" class="quick-btn" onclick="addCustomTime()">+ Custom time</button>

                    {{-- No time warning --}}
                    <div id="noTimeWarn" class="ml-warn" style="display:none;margin-top:14px;">
                        ⚠ No times selected. Please select at least one time.
                    </div>
                </div>

                {{-- Preview of what will be created --}}
                <div id="previewCard" class="ml-card" style="display:none;">
                    <div class="ml-section-h" style="margin-bottom:8px;">Preview</div>
                    <div id="previewText" style="font-family:'Jost',sans-serif;font-size:13px;color:var(--ml-text);"></div>
                </div>

                <button type="submit" class="btn-ml" id="submitBtn" style="padding:12px 32px;font-size:13px;">
                    Create slots
                </button>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="info-card">
                <h5>How it works</h5>
                <ul style="padding-left:18px;">
                    <li>Set a date range</li>
                    <li>Select the days the consultant <strong>is available</strong> — nothing is preselected, choose explicitly</li>
                    <li>Select times, or use <strong>Available whole day</strong> for all</li>
                    <li>The preview below the form shows exactly what will be created</li>
                    <li>Click <strong>Create slots</strong></li>
                    <li>Duplicates are skipped automatically</li>
                    <li>Booked slots cannot be deleted</li>
                </ul>
                <div class="info-example">
                    <strong>Example:</strong> From 9 Jun → To 13 Jun, select Fri,
                    times 10:00 and 14:00 → creates 2 slots (1 day × 2 times) if there is only 1 Friday in that range.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ── Day toggles ──────────────────────────────────────────────────────────────
// Use 'change' on the checkbox instead of 'click' on the label
// to avoid the double-fire bug caused by click bubbling from input → label.
document.querySelectorAll('.day-toggle input[type=checkbox]').forEach(function (cb) {
    cb.addEventListener('change', function () {
        cb.closest('.day-toggle').classList.toggle('selected', cb.checked);
        updateWarnings();
        updatePreview();
    });
});

// ── Time toggles ─────────────────────────────────────────────────────────────
document.querySelectorAll('#timeToggles .time-toggle input[type=checkbox]').forEach(function (cb) {
    cb.addEventListener('change', function () {
        cb.closest('.time-toggle').classList.toggle('selected', cb.checked);
        syncWholeDayToggle();
        updateWarnings();
        updatePreview();
    });
});

// ── Whole day ────────────────────────────────────────────────────────────────
function applyWholeDay(checked) {
    document.querySelectorAll('#timeToggles .time-toggle input[type=checkbox]').forEach(function (cb) {
        cb.checked = checked;
        cb.closest('.time-toggle').classList.toggle('selected', checked);
    });
    document.getElementById('wholeDayCard').classList.toggle('selected', checked);
    updateWarnings();
    updatePreview();
}

function syncWholeDayToggle() {
    var all  = document.querySelectorAll('#timeToggles .time-toggle input[type=checkbox]');
    var done = document.querySelectorAll('#timeToggles .time-toggle input[type=checkbox]:checked');
    var allChecked = all.length > 0 && done.length === all.length;
    document.getElementById('wholeDayToggle').checked = allChecked;
    document.getElementById('wholeDayCard').classList.toggle('selected', allChecked);
}

// ── Day quick-selects ─────────────────────────────────────────────────────────
function selectAllDays() {
    document.querySelectorAll('.day-toggle input[type=checkbox]').forEach(function (cb) {
        cb.checked = true;
        cb.closest('.day-toggle').classList.add('selected');
    });
    updateWarnings(); updatePreview();
}
function selectWeekdays() {
    document.querySelectorAll('.day-toggle input[type=checkbox]').forEach(function (cb) {
        var v  = parseInt(cb.value);
        var ok = v >= 1 && v <= 5;
        cb.checked = ok;
        cb.closest('.day-toggle').classList.toggle('selected', ok);
    });
    updateWarnings(); updatePreview();
}
function selectWeekend() {
    document.querySelectorAll('.day-toggle input[type=checkbox]').forEach(function (cb) {
        var v  = parseInt(cb.value);
        var ok = v === 0 || v === 6;
        cb.checked = ok;
        cb.closest('.day-toggle').classList.toggle('selected', ok);
    });
    updateWarnings(); updatePreview();
}
function clearDays() {
    document.querySelectorAll('.day-toggle input[type=checkbox]').forEach(function (cb) {
        cb.checked = false;
        cb.closest('.day-toggle').classList.remove('selected');
    });
    updateWarnings(); updatePreview();
}

// ── Custom time ───────────────────────────────────────────────────────────────
function addCustomTime() {
    var wrap = document.createElement('div');
    wrap.style.cssText = 'display:inline-block;margin:3px;';
    var input = document.createElement('input');
    input.type = 'time';
    input.name = 'times[]';
    input.style.cssText = 'border:2px solid #ccc;border-radius:4px;padding:8px 10px;font-family:Jost,sans-serif;font-size:14px;font-weight:700;width:110px;';
    input.addEventListener('change', function () { updatePreview(); });
    wrap.appendChild(input);
    document.getElementById('customTimes').appendChild(wrap);
}

// ── Date inputs ───────────────────────────────────────────────────────────────
document.getElementById('dateFrom').addEventListener('change', function () {
    // Ensure dateTo >= dateFrom
    var to = document.getElementById('dateTo');
    if (to.value && to.value < this.value) to.value = this.value;
    updateWarnings(); updatePreview(); updateDateSummary();
});
document.getElementById('dateTo').addEventListener('change', function () {
    updateWarnings(); updatePreview(); updateDateSummary();
});

// ── Warnings ──────────────────────────────────────────────────────────────────
function getSelectedDays() {
    return Array.from(document.querySelectorAll('.day-toggle input[type=checkbox]:checked'))
        .map(function (cb) { return parseInt(cb.value); });
}
function getSelectedTimes() {
    var preset = Array.from(document.querySelectorAll('#timeToggles .time-toggle input[type=checkbox]:checked'))
        .map(function (cb) { return cb.value; });
    var custom = Array.from(document.querySelectorAll('#customTimes input[type=time]'))
        .map(function (i) { return i.value; }).filter(Boolean);
    return preset.concat(custom);
}

function updateWarnings() {
    var from  = document.getElementById('dateFrom').value;
    var to    = document.getElementById('dateTo').value;
    var days  = getSelectedDays();
    var times = getSelectedTimes();

    // No day selected
    document.getElementById('noDayWarn').style.display = days.length === 0 ? 'block' : 'none';
    // No time selected
    document.getElementById('noTimeWarn').style.display = times.length === 0 ? 'block' : 'none';

    // Day/range mismatch — timezone-safe: parse YYYY-MM-DD manually
    var mismatchEl = document.getElementById('mismatchWarn');
    if (from && to && days.length > 0) {
        var found   = false;
        var current = parseLocalDate(from);
        var end     = parseLocalDate(to);
        while (current <= end) {
            if (days.indexOf(current.getDay()) !== -1) { found = true; break; }
            current.setDate(current.getDate() + 1);
        }
        if (!found) {
            var dayNames  = {0:'Sun',1:'Mon',2:'Tue',3:'Wed',4:'Thu',5:'Fri',6:'Sat'};
            var selNames  = days.map(function(d){ return dayNames[d]; }).join(', ');
            var startDay  = dayNames[parseLocalDate(from).getDay()];
            mismatchEl.innerHTML =
                '⚠ <strong>No matching days.</strong> ' +
                'You selected ' + selNames + ', but ' + from + ' is a ' + startDay + '. ' +
                'Either widen the date range or select ' + startDay + '.';
            mismatchEl.style.display = 'block';
        } else {
            mismatchEl.style.display = 'none';
        }
    } else {
        mismatchEl.style.display = 'none';
    }
}

// Parse YYYY-MM-DD as LOCAL date (avoids UTC timezone shift bug)
function parseLocalDate(str) {
    var parts = str.split('-');
    return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
}

// ── Preview ───────────────────────────────────────────────────────────────────
function updatePreview() {
    var from  = document.getElementById('dateFrom').value;
    var to    = document.getElementById('dateTo').value;
    var days  = getSelectedDays();
    var times = getSelectedTimes();

    if (!from || !to || days.length === 0 || times.length === 0) {
        document.getElementById('previewCard').style.display = 'none';
        return;
    }

    var dayNames = {0:'Sun',1:'Mon',2:'Tue',3:'Wed',4:'Thu',5:'Fri',6:'Sat'};
    var count    = 0;
    var dateList = [];

    var current = parseLocalDate(from);
    var end     = parseLocalDate(to);
    while (current <= end) {
        if (days.indexOf(current.getDay()) !== -1) {
            count += times.length;
            var d = current.getFullYear() + '-' +
                    String(current.getMonth()+1).padStart(2,'0') + '-' +
                    String(current.getDate()).padStart(2,'0');
            dateList.push(dayNames[current.getDay()] + ' ' + d);
        }
        current.setDate(current.getDate() + 1);
    }

    var card = document.getElementById('previewCard');
    var text = document.getElementById('previewText');

    if (count === 0) {
        card.style.display = 'none';
        return;
    }

    var html = '<strong>' + count + ' slot(s)</strong> will be created across ' +
               dateList.length + ' day(s): ' + dateList.join(', ') + '.<br>' +
               'Times: ' + times.join(', ') + '.';
    text.innerHTML = html;
    card.style.display = 'block';
}

// ── Date summary ──────────────────────────────────────────────────────────────
function updateDateSummary() {
    var from = document.getElementById('dateFrom').value;
    var to   = document.getElementById('dateTo').value;
    var el   = document.getElementById('dateSummary');
    if (!from || !to) { el.textContent = ''; return; }
    var dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    var f = parseLocalDate(from);
    var t = parseLocalDate(to);
    var diff = Math.round((t - f) / 86400000) + 1;
    el.textContent = dayNames[f.getDay()] + ' ' + from + ' → ' + dayNames[t.getDay()] + ' ' + to +
                     ' (' + diff + ' day' + (diff !== 1 ? 's' : '') + ')';
}

// ── Remove empty custom times before submit ───────────────────────────────────
document.getElementById('slotForm').addEventListener('submit', function () {
    document.querySelectorAll('#customTimes input[type=time]').forEach(function (input) {
        if (! input.value.trim()) {
            input.disabled = true; // disabled inputs are not submitted
        }
    });
});

// ── Init ─────────────────────────────────────────────────────────────────────
syncWholeDayToggle();
updateWarnings();
updatePreview();
updateDateSummary();
</script>
@endsection
