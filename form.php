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

if (isset($_GET["action"])) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array('secret' => $secretKey, 'response' => $_POST["g-recaptcha-response"]);
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        echo '<div class="alert alert-warning alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Error... I guess?</strong> Recaptcha didn\'t return anything!</div>';
    }
    else {
        $decodedresult = json_decode($result, true);
        if($decodedresult['success'] == true) {
            $mName = $_POST['mName'];
            $mEmail = $_POST['mEmail'];
            $mMessage = stripslashes($_POST['mMessage']);
            $mCompiled = $mMessage.' -'.$mName.' ('.$mEmail.')';
            $headers = array('Content-Type: text/html; charset=UTF-8','From: '.$mName.' <website@example.com> ','Reply-To: '.$mEmail);
            wp_mail( $recipients, "Contact from Website Form", $mCompiled, $headers);
            echo '<div class="alert alert-success alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Success!</strong> Your message has been sent!</div>';
        }
        else {
            echo '<div class="alert alert-danger alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>Error!</strong> There was an error sending your message. Please try again.</div>';
        }
    }
}

?>

<form id="usr_contact" action="?action=captchasent" method="post">
    <input type="text" name="mName" placeholder="Name" /><br />
    <input type="email" name="mEmail" placeholder="Email" /><br />
    <textarea name="mMessage" cols="50" rows="5" placeholder="Message"></textarea><br />
    <button class="g-recaptcha btn" data-sitekey="<?php echo $siteKey; ?>" data-callback="onSubmit">Send Message</button>
</form>

<script type="text/javascript">

function onSubmit(token) {
    document.getElementById("usr_contact").submit();
    var c = confirm("By contacting the owner, you agree to our terms and conditions. No fees can be collected. If the owner requested a fee, please notify us immediately. Thank you.");
    return c;
}

</script>

<?php get_footer(); ?>