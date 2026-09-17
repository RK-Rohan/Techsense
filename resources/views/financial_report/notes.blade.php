@extends('financial_report.partials.layout')

@section('statement_period')
For the period ended {{ \Carbon::parse($end_date)->format('F d, Y') }}
@endsection

@section('statement_body')
@include('financial_report.partials.notes_body', [
    'current' => $statements['current'],
    'previous' => $statements['previous'],
    'period' => $statements['period'],
])
@endsection
