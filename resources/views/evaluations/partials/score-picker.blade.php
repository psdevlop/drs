@php
    $pickerName = $name ?? '';
    $pickerValue = (string) ($value ?? '');
    $pickerWidth = $width ?? '120px';
@endphp
<div class="score-picker" style="width:{{ $pickerWidth }}">
    <input type="hidden" name="{{ $pickerName }}" value="{{ $pickerValue }}" data-required="1">
    <button type="button" class="score-picker-toggle form-control">
        <span class="sp-value">{{ $pickerValue !== '' ? $pickerValue : '— Select —' }}</span>
        <span class="sp-caret" aria-hidden="true">▾</span>
    </button>
    <div class="score-picker-list" hidden role="listbox">
        <div class="sp-option {{ $pickerValue === '' ? 'selected' : '' }}" data-value="" role="option">— Select —</div>
        @for($v = 50; $v >= 10; $v--)
            @php $sv = number_format($v / 10, 1); @endphp
            <div class="sp-option {{ $pickerValue === $sv ? 'selected' : '' }}" data-value="{{ $sv }}" role="option">{{ $sv }}</div>
        @endfor
    </div>
</div>
