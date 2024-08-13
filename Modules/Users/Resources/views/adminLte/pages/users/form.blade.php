@csrf

@php
    $hasAvatar = isset($model) && $model->avatar;
    $getAvatar = $hasAvatar ? $model->avatarImage : '';
@endphp
<!--css & jq country_code -->
@include('cargo::adminLte.components.inputs.phone')

<!--begin::Col Avatar -->
<div class="row mb-6">
    <!--begin::Label-->
    <label class="col-md-4 col-form-label fw-bold fs-6">{{ __('users::view.table.avatar') }}</label>
    <!--end::Label-->
    <div class="col-md-8">
        <!--begin::Image input-->
        @if(isset($model))
            <x-media-library-collection max-items="1" name="image" :model="$model" collection="avatar" rules="mimes:jpg,jpeg,png,gif,bmp,svg,webp"/>
        @else
            <x-media-library-attachment name="image" rules="mimes:jpg,jpeg,png,gif,bmp,svg,webp"/>
        @endif
        <!--end::Image input-->

        @error('avatar')
            <div class="is-invalid"></div>
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>
</div>
<!--end::Col-->


<!--begin::Input group --  Full name -->
<div class="row mb-6">
    <!--begin::Label-->
    <label class="col-lg-4 col-form-label @if ($typeForm == 'create') required @endif fw-bold fs-6">{{ __('users::view.table.full_name') }}</label>
    <!--end::Label-->

    <!--begin::Input group-->
    <div class="col-lg-8 fv-row">
        <div class="input-group mb-4">
            <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" placeholder="{{ __('users::view.table.full_name') }}" value="{{ old('name', isset($model) ? $model->name : '') }}" />
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
    <!--end::Input group-->
</div>
<!--end::Input group-->



<!--begin::Input group --  Email -->
<div class="row mb-6">
    <!--begin::Label-->
    <label class="col-lg-4 col-form-label @if ($typeForm == 'create') required @endif fw-bold fs-6">{{ __('users::view.table.email') }}</label>
    <!--end::Label-->
    <!--begin::Input group-->
    <div class="col-lg-8 fv-row">
        <div class="input-group mb-4">
            <input type="text" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" placeholder="{{ __('users::view.table.email') }}" value="{{ old('email', isset($model) ? $model->email : '') }}" />
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
    <!--end::Input group-->
</div>
<!--end::Input group-->


<!--begin::Input group --  Password -->
<div class="row mb-6">
    <!--begin::Label-->

        <label class="col-lg-4 col-form-label @if ($typeForm == 'create') required @endif fw-bold fs-6">{{ __('users::view.table.password') }}</label>
        <!--end::Label-->

        <!--begin::Input group-->
        <div class="col-lg-8 fv-row">
            <div class="input-group mb-4">
                <input type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" placeholder="{{ __('users::view.table.password') }}" value="{{ old('password') }}"  />
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>
        <!--end::Input group-->
    </div>
    <!--end::Input group-->


<!-- Show role only in the following cases -->
<!-- if auth is admin only, if user id not equal 1 -->

<!--begin::Input group -- Phone -->
<div class="row mb-6">

    <!--begin::Label-->
    <label class="col-lg-4 col-form-label required fw-bold fs-6">{{ __('cargo::view.table.phone') }}</label>
    <!--end::Label-->

    <!--begin::Input group-->
    <div class="col-lg-8 fv-row">
        <div class="input-group mb-4">
            <input type="tel" name="responsible_mobile"  id="phone" dir="ltr" autocomplete="off" required  class=" phone_input number-only form-control form-control-lg inptFielsd  @error('responsible_mobile') is-invalid @enderror" placeholder="{{ __('cargo::view.table.phone') }}" value="{{ old('responsible_mobile', isset($model) ? $model->country_code.$model->responsible_mobile : base_country_code()) }}" />
            <input type="hidden" class="country_code" name="country_code" value="{{ old('country_code', isset($model) ?$model->country_code : base_country_code() )}}" data-reflection="phone">
            @error('responsible_mobile')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
    <!--end::Input group-->
</div>
<!--end::Input group-->

@if (auth()->user()->role == 1 && ( (isset($model) && $model->id != 1) || !isset($model)))

<!--begin::Input group-->
<div class="row mb-6">
    <!--begin::Label-->
    <label class="col-lg-4 col-form-label @if ($typeForm == 'create') required @endif fw-bold fs-6">{{ __('users::view.table.user_type') }}</label>
    <!--end::Label-->
    <!--begin::Col-->
    <div class="col-lg-8 fv-row">
        <!--begin::Options-->
        <div class="d-flex align-items-center form-group clearfix">
            @foreach (config('cms.user_roles') as $value => $titleRole)
                <!--begin::Option-->
                <div class="form-check form-check-custom form-check-solid me-5">
                    <input
                        class="is_user"
                        name="role"
                        type="radio"
                        value="{{ $value }}"
                        id="{{ $titleRole . $value }}"
                        {{ isset($model) && $model->role == $value ? 'checked="checked"' : ($value == 0 ? 'checked="checked"' : '') }}
                    >
                    <label class="form-check-label" for="{{ $titleRole . $value }}">
                        {{ $titleRole }}
                    </label>
                </div>
                <!--end::Option-->
            @endforeach
        </div>
        <!--end::Options-->
    </div>
    <!--end::Col-->
</div>
<!--end::Input group-->

@endif

@if($typeForm == 'edit')
    @if (isset($model) && $model->role != 1)
        @if (app('hook')->get('end_user_form'))
            @foreach(app('hook')->get('end_user_form') as $componentView)
                {!! $componentView !!}
            @endforeach
        @endif
    @endif
@elseif($typeForm == 'create')
    <div id="user_type">
        @if (app('hook')->get('end_user_form'))
            @foreach(app('hook')->get('end_user_form') as $componentView)
                {!! $componentView !!}
            @endforeach
        @endif
    </div>
@endif

{{-- Inject Scripts --}}
@push('js-component')
<script type="text/javascript">
    $('input[type=radio][class=is_user]:checked').each(function () {
        if(this.value == 0)
        {
            $("#user_type").css("display","block");
        }else{
            $("#user_type").css("display","none");
        }
    });

    $('input[type=radio][class=is_user]').change(function() {
        if(this.value == 0)
        {
            $("#user_type").css("display","block");
        }else{
            $("#user_type").css("display","none");
        }
    });
</script>
@endpush
