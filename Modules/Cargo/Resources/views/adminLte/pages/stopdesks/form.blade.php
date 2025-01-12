@csrf


<!--begin::Input group -- From Country -->
<div class="row">

    <!--begin::Input group-->
    <div class="col-md-12 fv-row form-group">
        <!--begin::Label-->
        <label class="col-form-label required fw-bold fs-6">{{ __('cargo::view.from_country') }}</label>
        <!--end::Label-->
        <select
            class="form-control  @error('country_id') is-invalid @enderror"
            name="country_id"
            data-control="select2"
            data-placeholder="{{ __('cargo::view.choose_country') }}"
            data-allow-clear="true"
            id="change-country"
        >
            <option></option>
            @foreach($countries as $country)
                <option value="{{ $country->id }}" 
                    {{ old('country_id') == $country->id ? 'selected' : '' }}
                    @if($typeForm == 'edit')
                        {{ $model->state->country_id == $country->id ? 'selected' : '' }}
                    @endif
                >{{ $country->name }}</option>
            @endforeach
        </select>
        @error('country_id') 
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>
    <!--end::Input group-->
</div>
<!--end::Input group-->

<!--begin::Input group -- From Country -->
<div class="row">

    <!--begin::Input group-->
    <div class="col-md-12 fv-row form-group">
        <!--begin::Label-->
        <label class="col-form-label required fw-bold fs-6">{{ __('cargo::view.from_region') }}</label>
        <!--end::Label-->
        <select
            class="form-control  @error('state_id') is-invalid @enderror"
            name="state_id"
            data-control="select2"
            data-placeholder="{{ __('cargo::view.choose_region') }}"
            data-allow-clear="true"
        >
            <option></option>
            @if($typeForm == 'edit')
                @foreach($states as $state)
                    <option value="{{ $state->id }}" 
                        {{ old('state_id') == $state->id ? 'selected' : '' }}
                        {{ $model->state_id == $state->id ? 'selected' : '' }}
                    >{{ $state->name }}</option>
                @endforeach
            @endif
        </select>
        @error('state_id') 
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>
    <!--end::Input group-->
</div>
<!--end::Input group-->

<!--begin::Input group --  stopdesk Name -->
<div class="row mb-6">

        <!--begin::Input group-->
        <div class="col-md-12 fv-row form-group">
            <!--begin::Label-->
            <label class="col-form-label required fw-bold fs-6">{{ __('API') }}</label>
            <!--end::Label-->
            <select
                class="form-control  @error('api_id') is-invalid @enderror"
                name="country_id"
                data-control="select2"
                data-placeholder="{{ __('cargo::view.choose_country') }}"
                data-allow-clear="true"
                id="change-model"
            >
                <option></option>
                @foreach($apiModels as $apiModel)
                    <option value="{{ $apiModel->id }}" 
                        {{ old('model_id') == $apiModel->id ? 'selected' : '' }}
                        @if($typeForm == 'edit')
                            {{ $model->model_id == $apiModel->id ? 'selected' : '' }}
                        @endif
                    >{{ $apiModel->name }}</option>
                @endforeach
            </select>
            @error('model_id') 
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <!--end::Input group-->
    </div>
    <!--end::Input group-->

    <!--begin::Input group-->
    <div class="col-lg-12 fv-row">
        <!--begin::Label-->
        <label class="col-form-label required fw-bold fs-6">{{ __('stopdesk name') }}</label>
        <!--end::Label-->
        <div class="input-group lang_container" id="lang_container_name">
            
            <input
                type="text"
                placeholder="{{ __('cargo::view.table.name') }}"
                name="name"
                title="stopdesk"
                class="form-control section-title form-control-multilingual is-invalid"
                value="{{ isset($model) && isset($model->name ) ? $model->name : '' }}"
            >
        </div>
    </div>
    <div class="col-lg-12 fv-row">
        <!--begin::Label-->
        <label class="col-form-label required fw-bold fs-6">{{ __('phone') }}</label>
        <!--end::Label-->
        <div class="input-group lang_container" id="lang_container_phone">
            
            <input
                type="text"
                placeholder="{{ __('phone') }}"
                name="phone"
                title="stopdesk"
                class="form-control section-title form-control-multilingual is-invalid"
                value="{{ isset($model) && isset($model->phone ) ? $model->phone : '' }}"
            >
        </div>
    </div>
    <div class="col-lg-12 fv-row">
        <!--begin::Label-->
        <label class="col-form-label required fw-bold fs-6">{{ __('address') }}</label>
        <!--end::Label-->
        <div class="input-group lang_container" id="lang_container_addr">
            
            <input
                type="text"
                placeholder="{{ __('address') }}"
                name="address"
                title="stopdesk"
                class="form-control section-title form-control-multilingual is-invalid"
                value="{{ isset($model) && isset($model->address ) ? $model->address : '' }}"
            >
        </div>
    </div>
    <!--end::Input group-->
</div>
<!--end::Input group-->


{{-- Inject Scripts --}}
@push('js-component')

<script>

		// get-states-ajax
		$('#change-country').change(function() {
            var id = $(this).val();
            console.log(id);
            $.get("{{route('ajax.getStates')}}?country_id=" + id, function(data) {
                console.log(data);
                $('select[name ="state_id"]').empty();
                
                for (let index = 0; index < data.length; index++) {
                    const element = data[index];

                    $('select[name ="state_id"]').append('<option value="' + element['id'] + '">' + element['name'] + '</option>');
                    
                }
            });
        });
        // end get-states-ajax

</script>

@endpush

