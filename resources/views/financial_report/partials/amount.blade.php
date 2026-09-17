{{--
    Renders a statement figure the way the printed accounts do: a dash for nil
    and negatives wrapped in parentheses.

    $value - the figure to show
--}}
@php
    $value = round((float) ($value ?? 0), 2);
@endphp
@if ($value == 0)
    -
@elseif ($value < 0)
    ({{ number_format(abs($value), session('business.currency_precision', 2)) }})
@else
    {{ number_format($value, session('business.currency_precision', 2)) }}
@endif
