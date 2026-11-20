@php
    $people = isset($contact) ? $contact->contactPersons : collect();
    if ($people->isEmpty()) $people = collect([(object) ['name' => '', 'mobile' => '']]);
@endphp
<div class="col-md-12 business contact-persons-section" style="display: none;">
    <label>Contact persons</label>
    <div class="contact-person-rows">
        @foreach($people as $index => $person)
            <div class="row contact-person-row">
                <div class="col-md-5">
                    <input type="text" name="contact_persons[{{$index}}][name]" value="{{$person->name}}" class="form-control" placeholder="Contact person">
                </div>
                <div class="col-md-5">
                    <input type="text" name="contact_persons[{{$index}}][mobile]" value="{{$person->mobile}}" class="form-control" placeholder="Mobile number">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-default add-contact-person"><i class="fa fa-plus"></i></button>
                    <button type="button" class="btn btn-danger remove-contact-person"><i class="fa fa-minus"></i></button>
                </div>
            </div>
        @endforeach
    </div>
</div>
<div class="clearfix business"></div>
