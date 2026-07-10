<?php
/**
 * 文件：user/register.php
 * 作用：用户注册（邮箱验证码）
 */

define('VS_ROOT', dirname(__DIR__));
require_once VS_ROOT . '/core/bootstrap.php';
require_once VS_ROOT . '/admin/includes/auth_layout.php';

InstallChecker::requireInstalled();
UserAuth::redirectIfLoggedIn();

$base = vs_base_url();
$siteName = SiteContext::siteName();
$mailEnabled = Config::isMailEnabled();
$codeTtl = 300;
$mailDisabledMsg = '管理员尚未配置邮箱发信，请联系管理员在后台「系统设置」中配置邮箱后方可注册。';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    vs_auth_require_post();

    $action = (string) $_POST['action'];

    if ($action === 'send_code') {
        if (!$mailEnabled) {
            vs_auth_json(array('code' => 0, 'msg' => $mailDisabledMsg));
        }

        $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');

        if ($username === '') {
            vs_auth_json(array('code' => 0, 'msg' => '请输入用户名'));
        }
        if (strlen($username) < 3) {
            vs_auth_json(array('code' => 0, 'msg' => '用户名至少 3 个字符'));
        }
        if ($email === '') {
            vs_auth_json(array('code' => 0, 'msg' => '请输入邮箱'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            vs_auth_json(array('code' => 0, 'msg' => '请输入有效的邮箱地址'));
        }

        $dupMsg = UserAuth::checkRegisterDuplicate($username, $email);
        if ($dupMsg !== null) {
            vs_auth_json(array('code' => 0, 'msg' => $dupMsg));
        }

        $mailLimitMsg = AuthSecurity::checkMailCodeAllowed($email);
        if ($mailLimitMsg !== null) {
            vs_auth_json(array('code' => 0, 'msg' => $mailLimitMsg));
        }

        try {
            $code = (string) random_int(100000, 999999);
            $_SESSION['user_reg_username'] = $username;
            $_SESSION['user_reg_email'] = $email;
            $_SESSION['user_reg_code'] = $code;
            $_SESSION['user_reg_code_expires'] = time() + $codeTtl;

            $body = '<div style="font-family:sans-serif;line-height:1.8;">';
            $body .= '<p>您好：</p>';
            $body .= '<p>您正在注册 ' . htmlspecialchars($siteName) . ' 用户账号，验证码为：</p>';
            $body .= '<p style="font-size:24px;font-weight:bold;margin:16px 0;">' . htmlspecialchars($code) . '</p>';
            $body .= '<p>验证码 ' . (int) ($codeTtl / 60) . ' 分钟内有效，请勿泄露给他人。</p>';
            $body .= '<p>如非本人操作，请忽略此邮件。</p></div>';

            if (!Mailer::send($email, $siteName . ' 注册验证码', $body)) {
                vs_auth_json(array('code' => 0, 'msg' => '验证码发送失败，请稍后重试'));
            }

            AuthSecurity::recordMailCodeSent($email);

            vs_auth_json(array('code' => 1, 'msg' => '验证码已发送，请查收邮箱（含垃圾箱）'));
        } catch (Exception $e) {
            vs_auth_json(array('code' => 0, 'msg' => '发送失败：' . $e->getMessage()));
        }
    }

    if ($action === 'register') {
        if (!$mailEnabled) {
            vs_auth_json(array('code' => 0, 'msg' => $mailDisabledMsg));
        }

        $resetLimitMsg = AuthSecurity::checkResetSubmitAllowed();
        if ($resetLimitMsg !== null) {
            vs_auth_json(array('code' => 0, 'msg' => $resetLimitMsg));
        }

        $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
        $code = trim(isset($_POST['code']) ? $_POST['code'] : '');
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if ($username === '' || $email === '' || $code === '') {
            vs_auth_json(array('code' => 0, 'msg' => '请完整填写注册信息'));
        }
        if (strlen($password) < 6) {
            vs_auth_json(array('code' => 0, 'msg' => '密码至少 6 位'));
        }
        if ($password !== $confirm) {
            vs_auth_json(array('code' => 0, 'msg' => '两次输入的密码不一致'));
        }

        $savedUsername = isset($_SESSION['user_reg_username']) ? (string) $_SESSION['user_reg_username'] : '';
        $savedEmail = isset($_SESSION['user_reg_email']) ? (string) $_SESSION['user_reg_email'] : '';
        $savedCode = isset($_SESSION['user_reg_code']) ? (string) $_SESSION['user_reg_code'] : '';
        $expires = isset($_SESSION['user_reg_code_expires']) ? (int) $_SESSION['user_reg_code_expires'] : 0;

        if ($savedEmail === '' || $savedCode === '' || $expires < time()) {
            vs_auth_json(array('code' => 0, 'msg' => '验证码已过期，请重新获取'));
        }
        if ($email !== $savedEmail || $username !== $savedUsername || $code !== $savedCode) {
            vs_auth_json(array('code' => 0, 'msg' => '用户名、邮箱或验证码错误'));
        }

        $result = UserAuth::register($username, $email, $password);
        if ($result !== true) {
            vs_auth_json(array('code' => 0, 'msg' => $result));
        }

        AuthSecurity::recordResetSubmit();

        unset(
            $_SESSION['user_reg_username'],
            $_SESSION['user_reg_email'],
            $_SESSION['user_reg_code'],
            $_SESSION['user_reg_code_expires']
        );

        vs_auth_json(array(
            'code' => 1,
            'msg'  => '注册成功，请登录',
            'url'  => $base . '/user/login.php',
        ));
    }

    vs_auth_json(array('code' => 0, 'msg' => '未知操作'), 400);
}

vs_auth_head('用户注册');
?>

<div class="page">
    <?php vs_auth_left_panel(false); ?>

    <div class="right">
        <div class="form-box">
            <div class="header header-desktop">
                <h1>注册账号</h1>
                <p class="header-sub">使用邮箱验证注册，完成验证后即可使用</p>
            </div>

            <div id="formMessage" class="form-message" role="alert" hidden></div>

            <?php if (!$mailEnabled): ?>
                <div class="form-message form-message--error" style="display:block;">
                    <?php echo vs_e($mailDisabledMsg); ?>
                </div>
            <?php endif; ?>

            <form id="registerForm" method="post" action="" novalidate>
                <?php vs_auth_csrf_field(); ?>
                <div class="field">
                    <label for="username">用户名</label>
                    <input id="username" name="username" type="text" placeholder="3～50 个字符" autocomplete="username" maxlength="50" required <?php echo $mailEnabled ? '' : 'disabled'; ?>>
                </div>

                <div class="field">
                    <label for="email">邮箱</label>
                    <input id="email" name="email" type="email" placeholder="请输入邮箱" autocomplete="email" maxlength="64" required <?php echo $mailEnabled ? '' : 'disabled'; ?>>
                </div>

                <div class="field">
                    <label for="code">验证码</label>
                    <div class="input-group">
                        <input id="code" name="code" type="text" placeholder="请输入验证码" autocomplete="one-time-code" maxlength="6" inputmode="numeric" pattern="[0-9]*" <?php echo $mailEnabled ? '' : 'disabled'; ?>>
                        <button type="button" class="code-btn" id="sendCodeBtn" <?php echo $mailEnabled ? '' : 'disabled'; ?>>获取验证码</button>
                    </div>
                </div>

                <div class="field">
                    <label for="password">密码</label>
                    <div class="input-wrap">
                        <input id="password" name="password" type="password" placeholder="请设置密码（至少6位）" autocomplete="new-password" maxlength="64" required <?php echo $mailEnabled ? '' : 'disabled'; ?>>
                        <?php echo vs_auth_toggle_password_html(); ?>
                    </div>
                </div>

                <div class="field">
                    <label for="confirm_password">确认密码</label>
                    <input id="confirm_password" name="confirm_password" type="password" placeholder="请再次输入密码" autocomplete="new-password" maxlength="64" required <?php echo $mailEnabled ? '' : 'disabled'; ?>>
                </div>

                <?php echo vs_auth_submit_btn('立即注册', 'submitBtn'); ?>

                <div class="divider">
                    已有账号？<a href="<?php echo vs_e($base); ?>/user/login.php">返回登录</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var form = document.getElementById('registerForm');
    var messageEl = document.getElementById('formMessage');
    var sendCodeBtn = document.getElementById('sendCodeBtn');
    var submitBtn = document.getElementById('submitBtn');
    var mailEnabled = <?php echo $mailEnabled ? 'true' : 'false'; ?>;
    var countdown = 0;
    var countdownTimer = null;

    if (!form) return;

    function showMessage(text, type) {
        if (text && window.VsToast) {
            VsToast.show(text, type === 'error' ? 'error' : 'success');
            if (messageEl) messageEl.hidden = true;
            return;
        }
        if (!messageEl) return;
        messageEl.textContent = text;
        messageEl.className = 'form-message form-message--' + type;
        messageEl.hidden = false;
    }

    function hideMessage() {
        if (messageEl) messageEl.hidden = true;
    }

    function startCountdown(seconds) {
        countdown = seconds;
        if (!sendCodeBtn) return;
        sendCodeBtn.disabled = true;
        sendCodeBtn.textContent = countdown + 's 后重发';
        countdownTimer = setInterval(function () {
            countdown -= 1;
            if (countdown <= 0) {
                clearInterval(countdownTimer);
                sendCodeBtn.disabled = false;
                sendCodeBtn.textContent = '获取验证码';
                return;
            }
            sendCodeBtn.textContent = countdown + 's 后重发';
        }, 1000);
    }

    if (sendCodeBtn) {
        sendCodeBtn.addEventListener('click', function () {
            hideMessage();
            if (!mailEnabled) return;

            var username = form.username.value.trim();
            var email = form.email.value.trim();

            if (!username) {
                showMessage('请先输入用户名', 'error');
                form.username.focus();
                return;
            }
            if (!email) {
                showMessage('请先输入邮箱', 'error');
                form.email.focus();
                return;
            }

            sendCodeBtn.disabled = true;

            var body = new FormData();
            body.append('action', 'send_code');
            body.append('username', username);
            body.append('email', email);
            if (form.csrf_token) {
                body.append('csrf_token', form.csrf_token.value);
            }

            fetch(window.location.href, {
                method: 'POST',
                body: body,
                credentials: 'same-origin'
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.code === 1) {
                        showMessage(data.msg || '验证码已发送', 'success');
                        startCountdown(60);
                    } else {
                        showMessage(data.msg || '发送失败', 'error');
                        sendCodeBtn.disabled = false;
                    }
                })
                .catch(function () {
                    showMessage('网络异常，请稍后重试', 'error');
                    sendCodeBtn.disabled = false;
                });
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideMessage();

        if (!mailEnabled) return;

        var username = form.username.value.trim();
        var email = form.email.value.trim();
        var code = form.code.value.trim();
        var password = form.password.value;
        var confirm = form.confirm_password.value;

        if (!username) {
            showMessage('请输入用户名', 'error');
            return;
        }
        if (!email) {
            showMessage('请输入邮箱', 'error');
            return;
        }
        if (!code) {
            showMessage('请输入验证码', 'error');
            return;
        }
        if (password.length < 6) {
            showMessage('密码至少 6 位', 'error');
            return;
        }
        if (password !== confirm) {
            showMessage('两次输入的密码不一致', 'error');
            return;
        }

        if (submitBtn) submitBtn.disabled = true;

        var body = new FormData(form);
        body.append('action', 'register');

        fetch(window.location.href, {
            method: 'POST',
            body: body,
            credentials: 'same-origin'
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.code === 1) {
                    showMessage(data.msg || '注册成功', 'success');
                    if (data.url) {
                        setTimeout(function () { window.location.href = data.url; }, 1200);
                    }
                } else {
                    showMessage(data.msg || '注册失败', 'error');
                }
            })
            .catch(function () {
                showMessage('网络异常，请稍后重试', 'error');
            })
            .finally(function () {
                if (submitBtn) submitBtn.disabled = false;
            });
    });
})();
</script>

<?php vs_auth_foot('window.CHARACTER_OPTIONS = { usernameInputId: \'username\', passwordInputId: \'password\' };'); ?>
