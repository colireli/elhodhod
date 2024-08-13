
    <!-- REQUIRED CDN  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8-beta.1/inputmask.js" integrity="sha512-aSxEzzrnqlqgASdjAelu/V291nzZNygMSFMJ0h4PFQ+uwdEz6zKkgsIMbcv0O0ZPwFRNPFWssY7gcL2gZ6/t9A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js" crossorigin="anonymous"></script>

@section('scripts')

<style>
    .input-group .iti--allow-dropdown,.phone_input {
        width: 100% !important;
    }
</style>

<script>
    $(function () {
        
        var phoneNumbers = $('.phone_input'),
            wrong_number = window.wrong_number_msg,
            required_phone = window.required_phone



        phoneNumbers.each(function () {
            var self = $(this),
                input = self[0],
                type = self.attr('data-type');
                // initialise plugin
            var iti = window.intlTelInput(input, {
                separateDialCode: true,
                utilsScript: window.static_asset_utils_file,
                initialCountry: "ng",
                preferredCountries: ["eg","ng","ke"],
                autoPlaceholder: "aggressive"
            });
            input.addEventListener("countrychange", function() {
                $('.phone_input').filter(`[data-reflection="${type}"]`).val(iti.getSelectedCountryData().dialCode);
                $('.country_code').val('+'+iti.getSelectedCountryData().dialCode);
            });
            var reset = function() {
                self.parent().next('.invalid-feedback').remove();
                self.parent().removeClass('not-valid');
                self.removeClass("error is-invalid");
            };

            var addError = function(msg) {
                self.addClass('error is-invalid');
                self.parent().addClass('not-valid');
                self.parent().after("<span style='display: block' class=\"invalid-feedback\" role=\"alert\">\n" +
                    " <strong>" + msg + "</strong>\n" +
                    " </span>");
                return false;
            };
            // on blur: validate
            input.addEventListener('blur', function() {
                reset();

                if (self.attr('required')) {
                    if (input.value.trim() == '') {
                        return addError('field is empty')
                    }
                }

                if (input.value.trim() && !iti.isValidNumber()) {
                    return addError('reqierd')
                }
                // run code if verified
            });
            // on keyup / change flag: reset
            input.addEventListener('change', reset);
            input.addEventListener('keyup', reset);
        })


        $(".number-only").keypress(function(event){
            var ewn = event.which;
            if(ewn >= 48 && ewn <= 57) {
                return true;
            }
            return false;
        });


        $(".phone-validation").on("submit", function(evt) {
            var phoneField = $(this).find(".phone_input");
            if (phoneField.hasClass('error')) {
                evt.preventDefault();
                return false
            } else {
                //do the rest of your validations here
                $(this).submit();
            }
        });

    });
</script>


@endsection
