@extends('../layout2')
@section('content')

<section class="signup container">
    <div class="signup-logo">
        <img class="logo-dark" src="img/logo_dark.png" alt="logo" style="max-height:80%;">
    </div>

    <form class="signup-form" id="signup-form">


        <input type="text" name="contact" placeholder="MOBILE NO." />

        <!-- 2 column grid layout for inline styling -->
        <div>
            <div class="justify-content-center">
                <!-- Checkbox -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="form2Example3" checked />
                    <label class="form-check-label" for="form2Example3">I've read and accept the <a href="#">Terms and Condition</a></label>
                </div>
            </div>

        </div>

        <!-- Submit button -->
        <button onclick="generateOTP(event)" class="btn btn-primary btn-block mb-4">CREATE</button>


    </form>


    <!-- Modal -->
    <div class="modal fade" id="otpModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div>
                    <h1>Verification Account</h1>
                </div>
                <div>
                    <p>
                        Please enter the One-Time Password(OTP) to verify your account. An OTP has been sent to +919239739
                    </p>
                </div>
                <div class="modal-body">
                    <div class="row otp">
                        <div class="otp-field">
                            <input type="text" maxlength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="smsCode text-center rounded-lg" />
                        </div>
                        <div class="otp-field">
                            <input type="text" maxlength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="smsCode text-center rounded-lg" />
                        </div>
                        <div class="otp-field">
                            <input type="text" maxlength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="smsCode text-center rounded-lg" />
                        </div>
                        <div class="otp-field">
                            <input type="text" maxlength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="smsCode text-center rounded-lg" />
                        </div>

                    </div>
                    <div>
                        <p>
                            Didn't recieve any code? <a href="#">Resend</a>
                        </p>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" onclick="verifyOTP()">Submit</button>
                </div>
            </div>

        </div>
    </div>




</section>


<style>
    .signup {
        background-color: #f1f1f1;
    }

    .signup-form {
        color: red;
        margin: auto;
        max-width: 500px;
    }

    .signup-logo {
        text-align: center;
    }

    .signup-form input {
        border-radius: 20px;
    }

    .signup-form button {
        border-radius: 20px;
    }

    .modal {
        text-align: center;
    }

    @media screen and (min-width: 768px) {
        .modal:before {
            display: inline-block;
            vertical-align: middle;
            content: " ";
            height: 100%;
        }
    }

    .modal-dialog {
        display: inline-block;
        text-align: left;
        vertical-align: middle;
    }

    .smsCode {
        text-align: center;
        line-height: 80px;
        font-size: 50px;
        border: solid 1px #ccc;
        box-shadow: 0 0 5px #ccc inset;
        width: 100%;
        outline: none;


    }

    .otp input {
        background-color: #f1f1f1;
        width: auto;
        border-radius: 3px;
    }

    .otp-field {
        display: inline;
    }

    #otpModal .modal-header {

        border-bottom: none;
    }


    #otpModal .modal-dialog {

        text-align: center;


    }

    #otpModal .modal-content {

        border-radius: 30px;
    }

    .modal-body button {
        border-radius: 30px;
    }
</style>

<script>
    $(function() {
        var smsCodes = $('.smsCode');

        function goToNextInput(e) {
            var key = e.which,
                t = $(e.target),
                // Get the next input
                sib = t.closest('div').next().find('.smsCode');

            // Not allow any keys to work except for tab and number
            if (key != 9 && (key < 48 || key > 57)) {
                e.preventDefault();
                return false;
            }

            // Tab
            if (key === 9) {
                return true;
            }

            // Go back to the first one
            if (!sib || !sib.length) {
                sib = $('.smsCode').eq(0);
            }
            sib.select().focus();
        }

        function onKeyDown(e) {
            var key = e.which;

            // only allow tab and number
            if (key === 9 || (key >= 48 && key <= 57)) {
                return true;
            }

            e.preventDefault();
            return false;
        }

        function onFocus(e) {
            $(e.target).select();
        }

        smsCodes.on('keyup', goToNextInput);
        smsCodes.on('keydown', onKeyDown);
        smsCodes.on('click', onFocus);

    })

    function combineSMSCodes() {
        var otp = "";
        $('.smsCode').each(function(i, element) {
            otp += $(element).val();
        })

        return otp
    }

    function generateOTP(e) {
        e.preventDefault();
        const form = new FormData(document.getElementById("signup-form"))
        $.ajax({
            type: 'POST',
            url: 'https://plentyapp.mvp-apps.ae/api/otp',
            data: {
                contact: form.get('contact')
            },
            dataType: 'JSON',
            success: function(data) {
                $('#otpModal').modal('show');
                console.log(data)
            }
        });
    }

    function verifyOTP() {
        const form = new FormData(document.getElementById("signup-form"))
        $.ajax({
            type: 'POST',
            url: 'https://plentyapp.mvp-apps.ae/api/verify',
            data: {
                contact: form.get('contact'),
                otp: parseInt(combineSMSCodes())
            },
            dataType: 'JSON',
            success: function(data) {
                if (data.success) {
                    //do registration
                } else {
                    //raise error already exist
                }


                console.log(data)
            }
        });
    }
</script>

@endsection