@extends('layouts.app')
@section('title', 'Terms & Conditions')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Terms and Conditions</h1>

</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'All Terms and Conditions'])
    @can('brand.create')
    @slot('tool')
    <div class="box-tools">
        <button type="button" class="btn btn-block btn-primary btn-modal"
            data-href="{{action([\App\Http\Controllers\TermsConditionController::class, 'create'])}}"
            data-container=".terms_modal">
            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
    </div>
    @endslot
    @endcan

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="terms_table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descriptions</th>
                    <th>@lang( 'messages.action' )</th>
                </tr>
            </thead>
        </table>
    </div>

    @endcomponent

    <div class="modal fade terms_modal" tabindex="-1" role="dialog"
        aria-labelledby="gridSystemModalLabel">
    </div>
    
    <div class="modal fade edit_terms_modal" tabindex="-1" role="dialog" aria-labelledby="editTermsModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <!-- Content will be loaded here via AJAX -->
            </div>
        </div>
    </div>

</section>
<!-- /.content -->

@endsection

@push('script')


@endpush