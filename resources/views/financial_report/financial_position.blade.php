@extends('financial_report.partials.layout')

@section('statement_period')
As on {{ \Carbon::parse($end_date)->format('d-F-Y') }}
@endsection

@section('statement_body')
@include('financial_report.partials.financial_position_body', [
    'current' => $statements['current'],
    'previous' => $statements['previous'],
    'period' => $statements['period'],
])
@endsection
