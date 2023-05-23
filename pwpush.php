<?php
/*
Plugin Name: PasswordPush
Plugin URI: https://plugins.club
Description: On Password reset sends the new password to PasswordPush API and emails the URL token to the WordPress user.
Version: 1.0
Author: pluginsclub, stefanpejcic
Author URI: https://plugins.club
License: GPL2
*/


add_action('password_reset', 'send_password_to_api', 10, 2);

function send_password_to_api($user, $new_pass) {
	// API endpoint
	$url = 'https://pwpush.com/p.json';
	// Get user email
	$user_email = $user->user_email;
	// Set Token
	$headers = array(
		'X-User-Email: ' . $user_email,
		'X-User-Token: YOUR-API-TOKEN-HERE',
	);

	$data = array(
		'password[payload]' => $new_pass,
		'password[expire_after_days]' => 2,
		'password[expire_after_views]' => 10,
	);

	// Send request
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	$response = curl_exec($ch);
	curl_close($ch);

	// Parse response for url_token
	$json = json_decode($response, true);
	$url_token = $json['url_token'];

	// Email the link to user
	$site_name = get_bloginfo('name');
	$subject = 'PasswordPush - New Password for ' . $site_name;
    $message = 'Click on the following link to view the new password: https://pwpush.com/en/p/' . $url_token;
	wp_mail($user_email, $subject, $message);
}
