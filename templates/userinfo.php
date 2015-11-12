<?php
$defaults = get_option('pbs_passport_authenticate');
$passport = new PBS_Passport_Authenticate(dirname(__FILE__));

$pluginImageDir = $passport->assets_url . 'img';
$station_nice_name = $defaults['station_nice_name'];
// ADD A NONCE!!
$laas_client = $passport->get_laas_client();
$userinfo = $laas_client->check_pbs_login();
if (empty($userinfo['first_name'])) {
  // not logged in, redirect to loginform
  wp_redirect(site_url('pbsoauth/loginform'));
  exit();
}
$mvault_client = new PBS_MVault_Client($defaults['mvault_client_id'], $defaults['mvault_client_secret'],$defaults['mvault_endpoint'], $defaults['station_call_letters']);
$mvaultinfo = array();
$mvaultinfo = $mvault_client->get_membership_by_uid($userinfo['pid']);
$userinfo["membership_info"] = array("offer" => null, "status" => "Off");
if (isset ($mvaultinfo["membership_id"])) {
  $userinfo["membership_info"] = $mvaultinfo;
}
$success = $laas_client->validate_and_append_userinfo($userinfo);


get_header();
?>
<div class='pbs-passport-authenticate-wrap cf'>
<div class="pbs-passport-authenticate userinfo-block">
<div class='passport-middle'>
<?php if (!empty($defaults['station_passport_logo'])) {
  echo '<img src="' . $defaults['station_passport_logo'] . '" />'; 
}

echo '<h2>Welcome ' . $userinfo['first_name'] . ' ' . $userinfo['last_name'] . '</h2>';

if (!empty($userinfo['membership_info']['first_name'])) {
  ?><p>Your membership info is connected to your login.  Hooray!</p><?php
}



 echo "<hr>";
  echo print_r($userinfo['membership_info']);    
  echo "<hr>";

  
  $station_nice_name = $defaults['station_nice_name'];
  $join_url = $defaults['join_url'];
  

if ($userinfo['membership_info']['offer'] != "" && $userinfo['membership_info']['status'] == "On") {
	// active member
	echo "<p>$station_nice_name Passport <i class='fa fa-check-circle passport-green'></i></p>";
	if ($defaults['watch_url'] != "") {echo "<p><a href='".$defaults['watch_url']."' class='passport-txt-button'>Watch Programs</a></p>";}
}

elseif ($userinfo['membership_info']['offer'] == "" && $userinfo['membership_info']['status'] == "Off") {
	// not a member
	echo "<p>" . $defaults['station_nice_name'] . " Passport <i class='fa fa-times-circle passport-red'></i></p>";
	if (!$membership_id){ 
		$active_url = site_url('pbsoauth/activate');
		echo "<li class='service-section-label'>$station_nice_name Members who have not activated their accounts:</li>";
		echo "<li class='service-login-link activate'><a href='$active_url'><img src='$pluginImageDir/button-activate-account.png' alt='Activate Account'/></a></li>";
		 if (!empty($join_url)) { echo "<li class='service-section-label'>Not a Member?</li>
<li class='service-login-link becomemember'><a href='$join_url'><img src='$pluginImageDir/button-become-a-member.png' alt='Become a member'/></a></li>";}
	}

else {
	// expired member
	echo "<p>" . $defaults['station_nice_name'] . " Passport <i class='fa fa-times-circle passport-red'></i></p>";
	if (!empty($join_url)) {echo "<p><a href='$join_url' class='passport-txt-button'>Renew Membership</a></p>";}
}




echo "<p>" . $defaults['help_text'] . "</p>"; ?>





</div>
</div>
</div>
<?php get_footer();
