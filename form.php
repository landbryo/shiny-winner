<?php get_header();

// recaptcha key variables
$siteKey = '';
$secretKey = '';

// wordpress admin email
$admin_email = get_bloginfo('admin_email');

// owner variables
$owner_email = get_field('owner_email');
$owner_phone = get_field('owner_phone');

// sms variables
$sms_phone = preg_replace('/\D+/', '', $owner_phone);
$sms_carriers = get_field_object('owner_sms_carrier');
$sms_carrier = $sms_carriers['value'];

// find out if user wants sms notifications on/off
$sms_prefs = get_field_object('owner_sms_pref');
$sms_pref = $sms_prefs['value'];

// if user wants sms notifications, combine phone number with selected carrier
if ($sms_pref == 'on') {
  $sms = $sms_phone . $sms_carrier;
} else {
  $sms = '';
}

// array of recipients
$recipients = array(
  $admin_email,
  $owner_email,
  $sms
);

if (isset($_POST['g-recaptcha-response'])) {

    //get verify response data
    $verifyCaptchaResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$_POST['g-recaptcha-response']);
    $responseCaptchaData = json_decode($verifyCaptchaResponse);
    if($responseCaptchaData->success) {
        $mName = $_POST['mName'];
        $mEmail = $_POST['mEmail'];
        $mMessage = stripslashes($_POST['mMessage']);
        $mCompiled = $mMessage.' -'.$mName.' ('.$mEmail.')';
        $headers = array('Content-Type: text/html; charset=UTF-8','From: '.$mName.' <website@example.com> ','Reply-To: '.$mEmail);
        wp_mail( $recipients, "Contact from Website", $mCompiled, $headers);
        echo '<div class="alert alert-success alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Success!</strong> Your message has been sent!</div>';
    } else {
        echo '<div class="alert alert-warning alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Error... I guess?</strong> Recaptcha didn\'t return anything!</div>';
    }

}

?>

<form id="usr_contact" method="post" data-toggle="validator">
    <div class="form-group">
        <input type="text" name="mName" placeholder="Name" />
    </div>
    <div class="form-group">
        <input type="email" name="mEmail" placeholder="Email" data-error="Email address is required" required="required" />
        <div class="help-block with-errors"></div>
    </div>
    <div class="form-group">
        <textarea name="mMessage" cols="50" rows="5" placeholder="Message"></textarea>
    </div>

    <div id='recaptcha' class="g-recaptcha"
        data-sitekey="<?php echo $site_key; ?>"
        data-callback="onCompleted"
        data-size="invisible"></div>

    <div class="form-group">
        <button class="btn submit" type="submit">Send Message</button>
    </div>
</form>

<script type="text/javascript">

    jQuery('#usr_contact').validator().submit(function(event) {
        if (event.isDefaultPrevented()) {
            console.log('captcha not yet completed.');
        } else {
            var c;
            c = confirm('By submitting this form you agree to our terms and conditions. Thank you.');
            if (c == true) {
                event.preventDefault();
                grecaptcha.execute();
            }
        }
    });

    onCompleted = function() {
        console.log('captcha completed.');
        document.getElementById('usr_contact').submit();
    }

</script>

<?php get_footer(); ?>