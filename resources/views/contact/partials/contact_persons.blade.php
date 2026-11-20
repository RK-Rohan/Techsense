@php
    $people = isset($contact) ? $contact->contactPersons : collect();
    if ($people->isEmpty()) $people = collect([(object) ['name' => '', 'mobile' => '']]);
@endphp
<div class="col-md-8 business contact-persons-section" style="display: none;">
    <label>Contact persons</label>
    <div class="contact-person-rows">
        @foreach($people as $index => $person)
            <div class="row contact-person-row" style="margin-bottom: 5px;">
                <div class="col-xs-5" style="padding-right: 4px;">
                    <input type="text" name="contact_persons[{{$index}}][name]" value="{{$person->name}}" class="form-control" placeholder="Contact person">
                </div>
                <div class="col-xs-5" style="padding-left: 4px; padding-right: 4px;">
                    <input type="text" name="contact_persons[{{$index}}][mobile]" value="{{$person->mobile}}" class="form-control" placeholder="Mobile number">
                </div>
                <div class="col-xs-2" style="padding-left: 4px; white-space: nowrap;">
                    <button type="button" class="btn btn-default btn-sm add-contact-person"><i class="fa fa-plus"></i></button>
                    <button type="button" class="btn btn-danger btn-sm remove-contact-person"><i class="fa fa-minus"></i></button>
                </div>
            </div>
        @endforeach
    </div>
</div>
