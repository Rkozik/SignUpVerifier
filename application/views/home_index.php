<?php
$attributes = array('id'=>'form-wrapper');
$validation_open = '<div id="email-error">';
$validation_close = '</div>';

?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>public/css/style.css"/>
    <title>Sign-up Verifier 2000</title>
</head>
<body>

<?php echo form_open('Home/',$attributes); ?>
    <?php echo isset($result)==true?$validation_open.$result.$validation_close:""; ?>
    <?php echo form_error('dummyEmail', $validation_open, $validation_close); ?>
    <input type="text" value="UnderContstructionTemplate.com" disabled />
    <input type="email" id="dummyEmail" name="dummyEmail" value="<?php echo set_value('dummyEmail','Enter your e-mail address.');?>" title="Enter your email address."/>
    <div class="clear-float"></div>
    <input type="submit" value="Verify Sign-up" title="Verify email sign-up"/>
    <div class="clear-float"></div>
<?php echo form_close(); ?>

</body>
</html>