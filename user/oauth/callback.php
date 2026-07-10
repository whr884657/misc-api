<?php
/**
 * 文件：user/oauth/callback.php
 * 作用：OAuth 授权回调
 */

define('VS_ROOT', dirname(dirname(__DIR__)));
require_once VS_ROOT . '/core/bootstrap.php';

InstallChecker::requireInstalled();

$provider = isset($_GET['provider']) ? $_GET['provider'] : '';
$code = isset($_GET['code']) ? $_GET['code'] : '';
$state = isset($_GET['state']) ? $_GET['state'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

$base = vs_base_url();
$loginUrl = $base . '/user/login.php';
$accountUrl = $base . '/user/account.php';

/**
 * @param string $msg
 * @param string $fallbackUrl
 * @return void
 */
function vs_oauth_redirect_error($msg, $fallbackUrl)
{
    vs_redirect($fallbackUrl . '?oauth_error=' . rawurlencode($msg));
}

$peek = OAuthState::peek($provider, $state);
$errorFallback = ($peek !== false && isset($peek['intent']) && $peek['intent'] === 'bind')
    ? $accountUrl
    : $loginUrl;

if ($error !== '') {
    vs_oauth_redirect_error('授权已取消或失败', $errorFallback);
}

$result = OAuthService::handleCallback($provider, $code, $state);

if ($result['status'] === 'login' || $result['status'] === 'bind' || $result['status'] === 'done') {
    vs_redirect(isset($result['redirect']) ? $result['redirect'] : $loginUrl);
}

$msg = isset($result['msg']) ? $result['msg'] : '第三方登录失败';
$redirect = isset($result['redirect']) ? $result['redirect'] : $errorFallback;
vs_redirect($redirect . (strpos($redirect, '?') !== false ? '&' : '?') . 'oauth_error=' . rawurlencode($msg));
