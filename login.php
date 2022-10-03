<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'login.php');
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();

$action          = KT_Filter::post('action');
$user_realname   = KT_Filter::post('user_realname');
$user_name       = KT_Filter::post('user_name', KT_REGEX_USERNAME);
$user_email      = KT_Filter::post('user_email', KT_REGEX_EMAIL);
$user_password01 = KT_Filter::post('user_password01', KT_REGEX_PASSWORD);
$user_password02 = KT_Filter::post('user_password02', KT_REGEX_PASSWORD);
$user_comments   = KT_Filter::post('user_comments');
$user_password   = KT_Filter::post('user_password', KT_REGEX_UNSAFE); // Can use any password that was previously stored
$user_hashcode   = KT_Filter::post('user_hashcode');
$url             = KT_Filter::post('url', KT_REGEX_URL);
$username        = KT_Filter::post('username', KT_REGEX_USERNAME);
$password        = KT_Filter::post('password',KT_REGEX_UNSAFE); // Can use any password that was previously stored
$usertime        = KT_Filter::post('usertime');
$termsConditions = KT_Filter::post('termsConditions', '1', '0');

// These parameters may come from the URL which is emailed to users.
if (!$action) {
    $action = KT_Filter::get('action');
}
if (!$user_name) {
    $user_name = KT_Filter::get('user_name', KT_REGEX_USERNAME);
}
if (!$user_hashcode) {
    $user_hashcode = KT_Filter::get('user_hashcode');
}
if (!$url) {
    $url = KT_Filter::get('url', KT_REGEX_URL);
}

$message = '';
$days    = (KT_Site::preference('VERIFY_DAYS') ? KT_Site::preference('VERIFY_DAYS') : 7);

// If we are already logged in, then go to the home page
if (KT_USER_ID && KT_GED_ID && !in_array($action, array('verify_hash', 'userverify'))) {
    header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);
    exit;
}

switch ($action) {
    case 'login':
    default:
        if ($action == 'login') {
            $user_id = authenticateUser($username, $password);
            switch ($user_id) {
            case -1: // not validated
                $message = KT_I18N::translate('This account has not been verified. Please check your email for a verification message.');
                break;
            case -2: // not approved
                $message = KT_I18N::translate('This account has not been approved. Please wait for an administrator to approve it.');
                break;
            case -3: // bad password
            case -4: // bad username
                $message = KT_I18N::translate('The username or password is incorrect.');
                break;
            case -5: // no cookies
                $message = KT_I18N::translate('You cannot login because your browser does not accept cookies.');
                break;
            default: // Success
                if ($usertime) {
                    $KT_SESSION->timediff = KT_TIMESTAMP - strtotime($usertime);
                } else {
                    $KT_SESSION->timediff = 0;
                }
                $KT_SESSION->locale    = get_user_setting($user_id, 'language');
                $KT_SESSION->theme_dir = get_user_setting($user_id, 'theme');
                $KT_SESSION->gedcomid  = get_gedcomid($user_id, KT_GED_ID);
                if (KT_GED_ID == "") {
                    $KT_SESSION->rootid = $KT_SESSION->gedcomid;
                    $PEDIGREE_ROOT_ID   = $KT_SESSION->gedcomid;
                } else {
                    $KT_SESSION->rootid = $KT_TREE->userPreference($user_id, 'rootid');
                    $PEDIGREE_ROOT_ID   = get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID');
                }

                // If we’ve clicked login from the login page, we don’t want to go back there.
                if (strpos('index.php', $url) === 0) {
                    if ($KT_SESSION->gedcomid) {
                        $url = 'individual.php?pid=' . $KT_SESSION->gedcomid . '&amp;ged=' . KT_GEDURL;
                    } elseif ($KT_SESSION->rootid) {
                        $url = 'individual.php?pid=' . $KT_SESSION->rootid . '&amp;ged=' . KT_GEDURL;
                    } elseif ($PEDIGREE_ROOT_ID) {
                        $url = 'individual.php?pid=' . $PEDIGREE_ROOT_ID . '&amp;ged=' . KT_GEDURL;
                    } else {
                        $url = 'index.php?ged=' . KT_GEDURL;
                    }
                }

                // Redirect to the target URL
                header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . $url);
                // Explicitly write the session data before we exit,
                // as it doesn’t always happen when using APC.
                Zend_Session::writeClose();
                exit;
            }
        }

        $controller
            ->setPageTitle(KT_I18N::translate('Login'))
            ->pageHeader(true); ?>

        <div id="login-register-page">
            <div id="login-text">
                <?php switch (KT_Site::preference('WELCOME_TEXT_AUTH_MODE')) {
                case 1:
                    echo KT_I18N::translate('<center><b>Welcome to this Genealogy website</b></center><br />Access to this site is permitted to every visitor who has a user account.<br /><br />If you have a user account, you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br /><br />After verifying your application, the site administrator will activate your account. You will receive an email when your application has been approved.');
                    break;
                case 2:
                    echo KT_I18N::translate('<center><b>Welcome to this Genealogy website</b></center><br />Access to this site is permitted to <u>authorized</u> users only.<br /><br />If you have a user account you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br /><br />After verifying your information, the administrator will either approve or decline your account application. You will receive an email message when your application has been approved.');
                    break;
                case 3:
                    echo KT_I18N::translate('<center><b>Welcome to this Genealogy website</b></center><br />Access to this site is permitted to <u>family members only</u>.<br /><br />If you have a user account you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br /><br />After verifying the information you provide, the administrator will either approve or decline your request for an account. You will receive an email when your request is approved.');
                    break;
                case 4:
                    // use the default language of the tree if the user's language does not exist
                    if (KT_Site::preference('WELCOME_TEXT_AUTH_MODE_' . KT_LOCALE) && KT_Site::preference('WELCOME_TEXT_AUTH_MODE_' . KT_LOCALE) !== '') {
                        echo '<p style="white-space: pre-wrap;">', KT_Site::preference('WELCOME_TEXT_AUTH_MODE_' . KT_LOCALE), '</p>';
                    } else {
                        $lang = get_gedcom_setting(KT_GED_ID, 'LANGUAGE');
                        echo '<p style="white-space: pre-wrap;">', KT_Site::preference('WELCOME_TEXT_AUTH_MODE_' . $lang), '</p>';
                    }
                    break;
                } ?>

            </div>
            <div id="login-box">
                <form id="login-form" name="login-form" method="post" action="<?php echo KT_LOGIN_URL; ?>" onsubmit="t = new Date(); this.usertime.value=t.getFullYear()+\'-\'+(t.getMonth()+1)+\'-\'+t.getDate()+\' \'+t.getHours()+\':\'+t.getMinutes()+\':\'+t.getSeconds();return true;">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="url" value="<?php echo htmlspecialchars((string) $url); ?>">
                    <input type="hidden" name="usertime" value="">
                    <?php if (!empty($message)) echo '<span class="error"><br><b>', $message, '</b><br><br></span>'; ?>
                    <div>
                        <label for="loginUsername"><?php echo KT_I18N::translate('Username'); ?>
                        <input type="text" id="loginUsername" name="username" value="<?php echo htmlspecialchars((string) $username); ?>" class="formField" autofocus>
                        </label>
                    </div>
                    <div>
                        <label for="password"><?php echo KT_I18N::translate('Password'); ?>
                            <input type="password" name="password" id="password" class="formField">
                        </label>
                    </div>
                    <div>
                        <input type="submit" value="<?php echo KT_I18N::translate('Login'); ?>">
                    </div>
                    <div>
                        <a href="#" class="passwd_click"><?php echo KT_I18N::translate('Request new password'); ?></a>
                    </div>
                    <?php if (KT_Site::preference('USE_REGISTRATION_MODULE')) { ?>
                        <div>
                            <a href="<?php echo KT_LOGIN_URL; ?>?action=register"><?php echo KT_I18N::translate('Request new user account'); ?></a>
                        </div>
                    <?php } ?>
                </form>

                <?php // hidden New Password block ?>
                <div class="new_passwd">
                    <form class="new_passwd_form" name="new_passwd_form" action="<?php echo KT_LOGIN_URL; ?>" method="post">
                        <input type="hidden" name="action" value="requestpw">
                        <h4><?php echo KT_I18N::translate('Lost password request'); ?></h4>
                        <div>
                            <label><?php echo KT_I18N::translate('Username or email address'); ?>
                                <input type="text" class="new_passwd_username" name="new_passwd_username" value="">
                            </label>
                        </div>
                        <div>
                            <input type="submit" value="<?php echo /* I18N: button label */ KT_I18N::translate('continue'); ?>">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    break;

    case 'requestpw':
        $controller
            ->setPageTitle(KT_I18N::translate('Lost password request'))
            ->pageHeader(); ?>
        <div id="login-register-page">
            <?php $user_name = KT_Filter::post('new_passwd_username');

            $user_id = KT_DB::prepare(
                "SELECT user_id FROM `##user` WHERE ? IN (user_name, email)"
            )->execute(array($user_name))->fetchOne();

            if ($user_id) {
                // random password generator
                $passchars   = 'abcdefghijklmnopqrstuvqxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $max         = strlen($passchars);
                $user_new_pw = '';
                for ($i = 0; $i < KT_MINIMUM_PASSWORD_LENGTH + 2; $i++) {
                    $index = rand(0, $max);
                    $user_new_pw .= $passchars[rand(0, $max - 1)];
                }

                set_user_password($user_id, $user_new_pw);
                set_user_setting($user_id, 'pwrequested', 1);
                $user_name = KT_Filter::escapeHtml(get_user_name($user_id));
                AddToLog('Password request was sent to user: ' . $user_name, 'auth');

                KT_Mail::systemMessage(
                    $KT_TREE,
                    $user_id,
                    KT_I18N::translate('%1$s message', strip_tags(KT_TREE_TITLE)) . ' - ' . KT_I18N::translate('Lost password request'),
                    KT_I18N::translate('Hello %s…', getUserFullName($user_id)) . KT_Mail::EOL . KT_Mail::EOL .
                    KT_I18N::translate('A new password has been requested for your username.') . KT_Mail::EOL . KT_Mail::EOL .
                    KT_I18N::translate('Username') . ': ' . $user_name . KT_Mail::EOL .
                    KT_I18N::translate('Password') . ': ' . $user_new_pw . KT_Mail::EOL . KT_Mail::EOL .
                    KT_I18N::translate('After you have logged in, select the «My Account» link under the your name in the menu and fill in the password fields to change your password.') . KT_Mail::EOL . KT_Mail::EOL .
                    '<a href="' . KT_SERVER_NAME . '/login.php?ged=' . strip_tags(KT_TREE_TITLE) . '">' . KT_SERVER_NAME . '/login.php?ged=' . strip_tags(KT_TREE_TITLE) . '</a>'
                );

                ?>
                <div class="confirm">
                    <p>
                        <?php echo /* I18N: %s is a username */KT_I18N::translate('A new password has been created and emailed to %s. You can change this password after you login.', $user_name); ?>
                    </p>
                </div>
            <?php } else { ?>
                <div class="confirm error">
                    <p>
                        <?php echo /* I18N: %s is a username */KT_I18N::translate('There is no account with the username or email “%s”.', $user_name); ?>
                    </p>
                </div>
            <?php } ?>
        </div>
        <?php
    break;

    case 'register':
        if (!KT_Site::preference('USE_REGISTRATION_MODULE')) {
            header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);
            exit;
        }

        $controller->setPageTitle(KT_I18N::translate('Request new user account'));

        if (KT_Site::preference('USE_RECAPTCHA')) { ?>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php }

        $tree_link = '<a href="' . KT_SERVER_NAME . KT_SCRIPT_PATH . '?ged=' . KT_GEDCOM . '"><strong>' . strip_tags(KT_TREE_TITLE) . '</strong></a>';

        // The form parameters are mandatory, and the validation errors are shown in the client.
        if ($KT_SESSION->good_to_send && $user_name && $user_password01 && $user_password01 == $user_password02 && $user_realname && $user_email && $user_comments) {

            if (KT_Site::preference('USE_RECAPTCHA')) {
                if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
                    // Google reCAPTCHA API secret key
                    $secretKey = KT_Site::preference('RECAPTCHA_SECRET_KEY');

                    // Verify the reCAPTCHA response
                    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $_POST['g-recaptcha-response']);

                    // Decode json data
                    $responseData = json_decode($verifyResponse);

                    // Check reCAPTCHA response
                    if ($responseData->success) {
                        AddToLog('Google reCaptcha valid response from "' . $user_name . '"/"' . $user_email . '", response ="' . $responseData->success . '"', 'auth');
                    } else {
                        AddToLog('Failed Google reCaptcha response from "' . $user_name . '"/"' . $user_email . '"', 'spam');
                        KT_FlashMessages::addMessage(KT_I18N::translate('Google reCaptcha robot verification failed, please try again.'));
                        header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
                        $captcha = false;
                        exit;
                    }
                }
            }

            if (get_user_id($user_name)) {
                KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate user name. A user with that user name already exists. Please choose another user name.'));
                AddToLog('Attempted registration using an existing user name. "' . $user_name . '"/"' . $user_email . '", IP="' . $KT_REQUEST->getClientIp() . '"', 'spam');
            } elseif (findByEmail($user_email)) {
                KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate email address. A user with that email already exists.'));
                AddToLog('Attempted registration using an existing email address. "' . $user_name . '"/"' . $user_email . '", IP="' . $KT_REQUEST->getClientIp() . '"', 'spam');
            } elseif (preg_match('/(?!' . preg_quote(KT_SERVER_NAME, '/') . ')(((?:ftp|http|https):\/\/)[a-zA-Z0-9.-]+)/', $user_comments, $match)) {
                KT_FlashMessages::addMessage(
                    KT_I18N::translate('You are not allowed to send messages that contain external links.') . ' ' .
                    KT_I18N::translate('You should delete the “%1$s” from “%2$s” and try again.', $match[2], $match[1])
                );
                AddToLog('Possible spam registration from "' . $user_name . '"/"' . $user_email . '", IP="' . $KT_REQUEST->getClientIp() . '", comments="' . mb_strimwidth($user_comments, 0, 100, "...") . '"', 'spam');
            } elseif (in_array($user_email, explode(',', KT_Site::preference('BLOCKED_EMAIL_ADDRESS_LIST')))) {
                // This type of validation error should not be shown in the client.
                AddToLog('Possible spam registration from "' . $user_name . '"/"' . $user_email . '", IP="' . $KT_REQUEST->getClientIp() . '", comments="' . mb_strimwidth($user_comments, 0, 100, "...") . '"', 'spam');
                header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);
                exit;
            } else {
                // Everything looks good - create the user
                $controller->pageHeader();

                if ($termsConditions == '0') {
                    AddToLog('User registration requested for: ' . $user_name, 'auth');

                    $user_id = create_user($user_name, $user_realname, $user_email, $user_password01);

                    set_user_setting($user_id, 'language',          KT_LOCALE);
                    set_user_setting($user_id, 'verified',          0);
                    set_user_setting($user_id, 'verified_by_admin', 0);
                    set_user_setting($user_id, 'reg_timestamp',     date('U'));
                    set_user_setting($user_id, 'reg_hashcode',      md5(uniqid(rand(), true)));
                    set_user_setting($user_id, 'contactmethod',     'messaging');
                    set_user_setting($user_id, 'visibleonline',     1);
                    set_user_setting($user_id, 'auto_accept',       0);
                    set_user_setting($user_id, 'canadmin',          0);
                    set_user_setting($user_id, 'sessiontime',       0);

                    // Generate an email in the admin’s language
                    $webmaster_user_id = get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID') ? get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID') : get_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID');
                    if (!$webmaster_user_id) {
                        $webmaster_user_id = get_admin_id(KT_GED_ID);
                    }
                    KT_I18N::init(get_user_setting($webmaster_user_id, 'language'));

                    $mail1_body = KT_I18N::translate('Hello Administrator ...') . KT_Mail::EOL . KT_Mail::EOL .
                        /* I18N: %s is a server name/URL */
                        KT_I18N::translate('A prospective user has registered at %s.', $tree_link) . KT_Mail::EOL . KT_Mail::EOL .
                        KT_I18N::translate('Username') . ': ' . $user_name . KT_Mail::EOL .
                        KT_I18N::translate('Real name') . ': ' . $user_realname . KT_Mail::EOL .
                        KT_I18N::translate('Email Address') . ': ' . $user_email . KT_Mail::EOL . KT_Mail::EOL .
                        KT_I18N::translate('Comments') . ': ' . $user_comments . KT_Mail::EOL . KT_Mail::EOL .
                        KT_I18N::translate('The user has been sent an e-mail with the information necessary to confirm the access request') . KT_Mail::EOL . KT_Mail::EOL .
                        KT_I18N::translate('You will be informed by e-mail when this prospective user has confirmed the request. You can then complete the process by activating the user name. The new user will not be able to login until you activate the account.') . "\r\n";

                    $mail1_subject = /* I18N: %s is a server name/URL */ KT_I18N::translate('New registration at %s', strip_tags(KT_TREE_TITLE));
                    KT_I18N::init(KT_LOCALE);
                } else {
                    AddToLog('Robot registration caught by checkbox (user name: ' . $user_name . ' real name: ' . $user_realname . ' email: ' . $user_email . ')', 'spam');
                } ?>

                <div id="login-register-page">
                    <?php
                    if ($termsConditions == '0') {
                        // Generate an email in the user’s language
                        $mail2_body = KT_I18N::translate('Hello %s ...', $user_realname) . KT_Mail::EOL . KT_Mail::EOL .
                            /* I18N: %1$s is the site URL and %2$s is an email address */
                            KT_I18N::translate('You (or someone claiming to be you) registered an account at %1$s using the email address %2$s.', $tree_link, $user_email) . KT_Mail::EOL . KT_Mail::EOL .
                            KT_I18N::translate('Follow this link to verify your email address.') . KT_Mail::EOL . KT_Mail::EOL .
                            '<a href="' . KT_LOGIN_URL . '?user_name=' . urlencode($user_name) . '&amp;user_hashcode=' . urlencode(get_user_setting($user_id, 'reg_hashcode')) . '&amp;action=userverify">' .
                                KT_LOGIN_URL . '?user_name=' . urlencode($user_name) . '&amp;user_hashcode=' . urlencode(get_user_setting($user_id, 'reg_hashcode')) . '&amp;action=userverify' .
                            '</a>' . KT_Mail::EOL . KT_Mail::EOL .
                            KT_I18N::translate('Username') . ': ' . $user_name . KT_Mail::EOL .
                            KT_I18N::translate('Comments') . ': ' . $user_comments . KT_Mail::EOL . KT_Mail::EOL .
                            KT_I18N::translate('If you didn\'t request an account, you can just delete this message.') . KT_Mail::EOL;
                        $mail2_subject = /* I18N: %s is a server name/URL */ KT_I18N::translate('Your registration at %s', strip_tags(KT_TREE_TITLE));
                        $mail2_to      = $user_email;
                        $mail2_from    = $KIWITREES_EMAIL;

                        // Send user message by email only
                        KT_Mail::send(
                            // “From:” header
                            $KT_TREE,
                            // “To:” header
                            $mail2_to,
                            $mail2_to,
                            // “Reply-To:” header
                            $mail2_from,
                            $mail2_from,
                            // Message body
                            $mail2_subject,
                            // Message content
                            $mail2_body
                        );

                        // Send admin message
                        KT_Mail::send(
                            // “From:” header
                            $KT_TREE,
                            // “To:” header
                            getUserEmail($webmaster_user_id),
                            getUserFullName($webmaster_user_id),
                            // “Reply-To:” header
                            $KIWITREES_EMAIL,
                            $KIWITREES_EMAIL,
                            // Message subject
                            $mail1_subject,
                            // Message content
                            $mail1_body
                        );
                    } ?>

                    <div class="confirm">
                        <p><?php echo KT_I18N::translate('Hello %s ...<br />Thank you for your registration.', $user_realname); ?></p>
                        <p><?php echo KT_I18N::translate('We will now send a confirmation email to the address <b>%1$s</b>. You must verify your account request by following instructions in the confirmation email. If you do not confirm your account request within %2$s days, your application will be rejected automatically. You will have to apply again.<br /><br />After you have followed the instructions in the confirmation email, the administrator still has to approve your request before your account can be used.<br /><br />To login to this site, you will need to know your user name and password.', $user_email, $days); ?></p>
                    </div>
                </div>

                <?php
                return;
            }
        }

        $KT_SESSION->good_to_send = true;

        $controller
            ->pageHeader()
            ->addInlineJavascript('
                function regex_quote(str) {
                    return str.replace(/[\\\\.?+*()[\](){}|]/g, "\\\\$&");
                };

                function recaptcha_callback() {
                   jQuery("#registration-submit-button").removeAttr("disabled");
                };

                jQuery("label[for=termsConditions]").parent().css({
                    "opacity": "0",
                    "position": "absolute",
                    "left": "-2000px",
                });
        '); ?>

        <div id="login-register-page">
            <h2><?php echo $controller->getPageTitle(); ?></h2>
            <?php if (KT_Site::preference('SHOW_REGISTER_CAUTION')) { ?>
                <div id="register-text">
                    <?php echo KT_I18N::translate('<div class="largeError">Notice:</div><div class="error">By completing and submitting this form, you agree:<ul><li>to protect the privacy of living people listed on our site;</li><li>and in the text box below, to explain to whom you are related, or to provide us with information on someone who should be listed on our site.</li></ul></div>'); ?>
                </div>
            <?php } ?>
            <div id="register-box">
                <form id="register-form" name="register-form" method="post" action="<?php echo KT_LOGIN_URL; ?>" autocomplete="off">
                    <input type="hidden" name="action" value="register">
                    <h4><?php echo KT_I18N::translate('All fields must be completed.'); ?></h4>
                    <hr>
                    <div>
                        <label for="user_realname">
                            <?php echo KT_I18N::translate('Real name') . help_link('real_name'); ?>
                            <input type="text" id="user_realname" name="user_realname" required maxlength="64" value="<?php echo htmlspecialchars((string) $user_realname); ?>" autofocus>
                        </label>
                    </div>
                    <div>
                        <label for="user_email"><?php echo KT_I18N::translate('Email address') . help_link('email'); ?>
                            <input type="email" id="user_email" name="user_email" required maxlength="64" value="<?php echo htmlspecialchars((string) $user_email); ?>">
                        </label>
                    </div>
                    <div>
                        <label for="username"><?php echo KT_I18N::translate('Desired user name'), help_link('username'); ?>
                            <input type="text" id="username" name="user_name" required maxlength="32" value="<?php echo htmlspecialchars((string) $user_name); ?>">
                        </label>
                    </div>
                    <div>
                        <label for="user_password01"><?php echo KT_I18N::translate('Desired password') . help_link('password'); ?>
                            <input
                                 type="password"
                                 id="user_password01"
                                 name="user_password01"
                                 value="<?php echo htmlspecialchars((string) $user_password01); ?>"
                                 required placeholder="<?php echo /* I18N: placeholder text for new-password field */ KT_I18N::plural('Use at least %s character.', 'Use at least %s characters.', KT_MINIMUM_PASSWORD_LENGTH, KT_I18N::number(KT_MINIMUM_PASSWORD_LENGTH)); ?>"
                                 pattern="<?php echo KT_REGEX_PASSWORD; ?>"
                                 onchange="form.user_password02.pattern = regex_quote(this.value);"
                             >
                        </label>
                    </div>
                    <div>
                        <label for="user_password02"><?php echo KT_I18N::translate('Confirm password'), help_link('password_confirm'); ?>
                            <input type="password" id="user_password02" name="user_password02" value="<?php echo htmlspecialchars((string) $user_password02); ?>" required placeholder="<?php echo /* I18N: placeholder text for repeat-password field */ KT_I18N::translate('Type the password again.'); ?>" pattern="<?php echo KT_REGEX_PASSWORD; ?>">
                        </label>
                    </div>
                    <div>
                        <label for="user_comments"><?php echo KT_I18N::translate('Comments'), help_link('register_comments'); ?>
                            <textarea id="user_comments" name="user_comments" <?php echo (KT_Site::preference('REQUIRE_COMMENT') ? 'required' : ''); ?>><?php echo htmlspecialchars((string) $user_comments); ?></textarea>
                        </label>
                    </div>
                    <div>
                        <label for="termsConditions">
                            <?php echo /* I18N: for security protection only */ KT_I18N::translate('Confirm your agreement to our <a href="https://www.pandadoc.com/website-standard-terms-and-conditions-template/" >Terms and Conditions.'); ?></a>
                        </label>
                        <?php echo checkbox("termsConditions"); ?>
                    </div>
                    <?php if (KT_Site::preference('USE_RECAPTCHA')) { ?>
                        <div>
                            <label>
                                <div style="margin-left: 160px;" class="g-recaptcha" data-sitekey="<?php echo KT_Site::preference('RECAPTCHA_SITE_KEY'); ?>" data-callback="recaptcha_callback"></div>
                            </label>
                            <hr>
                            <div id="registration-submit">
                                <input id="registration-submit-button" disabled="disabled" type="submit" value="<?php echo KT_I18N::translate('continue'); ?>">
                            </div>
                        </div>
                    <?php } else { ?>
                        <hr>
                        <div id="registration-submit">
                            <input id="registration-submit-button" type="submit" value="<?php echo KT_I18N::translate('continue'); ?>">
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
        <?php
    break;

    case 'userverify':
        if (!KT_Site::preference('USE_REGISTRATION_MODULE')) {
            header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);
            exit;
        }

        // Change to the new user’s language
        $user_id = get_user_id($user_name);
        KT_I18N::init(get_user_setting($user_id, 'language'));

        $controller->setPageTitle(KT_I18N::translate('User verification'));
        $controller->pageHeader(); ?>

        <div id="login-register-page">
            <h2><?php echo KT_I18N::translate('User verification'); ?></h2>
            <div id="verify-box">
                <form id="verify-form" name="verify-form" method="post" action="<?php echo KT_LOGIN_URL; ?>">
                    <input type="hidden" name="action" value="verify_hash">
                    <div>
                        <label for="username">
                            <?php echo KT_I18N::translate('Username'); ?>
                            <input type="text" id="username" name="user_name" value="<?php echo $user_name; ?>">
                        </label>
                    </div>
                    <div>
                        <label for="user_password">
                            <?php echo KT_I18N::translate('Password'); ?>
                            <input type="password" id="user_password" name="user_password" value="" autofocus>
                        </label>
                    </div>
                    <div>
                        <label for="user_hashcode">
                            <?php echo KT_I18N::translate('Verification code:'); ?>
                            <input type="text" id="user_hashcode" name="user_hashcode" value="<?php echo $user_hashcode; ?>">
                        </label>
                    </div>
                    <hr>
                    <div id="verify-submit">
                        <input type="submit" value="<?php echo KT_I18N::translate('Send'); ?>">
                    </div>
                </form>
            </div>
        </div>
        <?php
    break;

    case 'verify_hash':
        if (!KT_Site::preference('USE_REGISTRATION_MODULE')) {
            header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH);
            exit;
        }
        AddToLog('User attempted to verify hashcode: ' . $user_name, 'auth');

        // switch language to webmaster settings
        $webmaster_user_id = get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID') ? get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID') : get_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID');
        if (!$webmaster_user_id) {
            $webmaster_user_id = get_admin_id(KT_GED_ID);
        }
        KT_I18N::init(get_user_setting($webmaster_user_id, 'language'));

        $user_id       = get_user_id($user_name);
        $edit_user_url = KT_SERVER_NAME . '/admin_users.php?action=edit&amp;user_id=' . $user_id;
        $mail1_body    = KT_I18N::translate('Hello administrator…') . KT_Mail::EOL . KT_Mail::EOL .
            /* I18N: %1$s is a real-name, %2$s is a username, %3$s is an email address */ KT_I18N::translate(
                'A new user (%1$s) has requested an account (%2$s) and verified an email address (%3$s).',
                getUserFullName($user_id),
                $user_name,
                getUserEmail($user_id)
            ) . KT_Mail::EOL . KT_Mail::EOL .
            KT_I18N::translate('You need to review the account details.') . KT_Mail::EOL . KT_Mail::EOL .
            '<a href="' . $edit_user_url . '">' . $edit_user_url . '</a>' . KT_Mail::EOL . KT_Mail::EOL .
            /* I18N: You need to: */ KT_I18N::translate('Set the status to “approved”.') . KT_Mail::EOL .
            /* I18N: You need to: */ KT_I18N::translate('Set the access level for each tree.') . KT_Mail::EOL .
            /* I18N: You need to: */ KT_I18N::translate('Set a role for this user.') . KT_Mail::EOL .
            /* I18N: You need to: */ KT_I18N::translate('Link the user account to an individual.');

        $mail1_subject = /* I18N: %s is a server name/URL */ KT_I18N::translate('New user at %s', strip_tags(KT_TREE_TITLE));

        // Change to the new user’s language
        KT_I18N::init(get_user_setting($user_id, 'language'));

        $controller->setPageTitle(KT_I18N::translate('User verification'));
        $controller->pageHeader(); ?>

        <div id="login-register-page">
            <h2><?php echo KT_I18N::translate('User verification'); ?></h2>
            <div id="user-verify">
                <?php if ($user_id && check_user_password($user_id, $user_password) && get_user_setting($user_id, 'reg_hashcode') === $user_hashcode) {
                    KT_Mail::send(
                    // “From:” header
                        $KT_TREE,
                        // “To:” header
                        getUserEmail($webmaster_user_id),
                        getUserFullName($webmaster_user_id),
                        // “Reply-To:” header
                        $KIWITREES_EMAIL,
                        $KIWITREES_EMAIL,
                        // Message body
                        $mail1_subject,
                        $mail1_body
                    );

                    set_user_setting($user_id, 'verified', 1);
                    set_user_setting($user_id, 'reg_timestamp', date("U"));
                    set_user_setting($user_id, 'reg_hashcode', null);
                    AddToLog('User ' . $user_name . ' verified their email address' , 'auth'); ?>

                    <div class="confirm">
                        <p><?php echo KT_I18N::translate('You have confirmed your request to become a registered user.'); ?></p>
                        <p><?php echo KT_I18N::translate('The administrator has been informed. As soon as they give you permission you can login with your user name and password.'); ?></p>
                    </div>
                <?php } else { ?>
                    <div class="warning ">
                        <p>
                            <?php echo KT_I18N::translate('Could not verify the information you entered. Please try again or contact the site administrator for more information.'); ?>
                        </p>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
    break;
}
