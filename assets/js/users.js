/**
 * 文件：assets/js/users.js
 * 作用：用户管理页 AJAX 封禁/解封/删除（无整页刷新，避免 POST 重复提交）
 */
(function () {
    'use strict';

    function createActionBtn(userId, action, label, className, confirmDelete) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'vs-btn vs-btn--pill ' + className + ' vs-user-action-btn';
        btn.setAttribute('data-user-action', action);
        btn.setAttribute('data-user-id', String(userId));
        if (confirmDelete) {
            btn.setAttribute('data-confirm-delete', '1');
        }
        btn.textContent = label;
        return btn;
    }

    function rebuildActions(container, userId, banned) {
        container.innerHTML = '';
        if (banned) {
            container.appendChild(createActionBtn(userId, 'unban', '解封', 'vs-btn--pill-primary'));
        } else {
            container.appendChild(createActionBtn(userId, 'ban', '封禁', 'vs-btn--pill-danger'));
        }
        container.appendChild(createActionBtn(userId, 'delete', '删除', 'vs-btn--pill-danger', true));
    }

    function ensureBannedTag(nameEl) {
        if (!nameEl || nameEl.querySelector('.vs-users-banned-tag')) {
            return;
        }
        var tag = document.createElement('span');
        tag.className = 'vs-users-banned-tag';
        tag.textContent = '已封禁';
        nameEl.appendChild(tag);
    }

    function removeBannedTag(nameEl) {
        if (!nameEl) {
            return;
        }
        var tag = nameEl.querySelector('.vs-users-banned-tag');
        if (tag) {
            tag.parentNode.removeChild(tag);
        }
    }

    function updateUserRows(userId, action) {
        var rows = document.querySelectorAll('[data-user-row="' + userId + '"]');
        rows.forEach(function (row) {
            if (action === 'delete') {
                if (row.parentNode) {
                    row.parentNode.removeChild(row);
                }
                return;
            }

            var banned = action === 'ban';
            row.classList.toggle('vs-users-row--banned', banned);
            row.classList.toggle('vs-user-card--banned', banned);

            var nameEl = row.querySelector('.vs-users-name');
            if (banned) {
                ensureBannedTag(nameEl);
            } else {
                removeBannedTag(nameEl);
            }

            var actions = row.querySelector('.vs-users-actions, .vs-user-card__actions');
            if (actions) {
                rebuildActions(actions, userId, banned);
            }
        });
    }

    function updateCount(delta) {
        var desc = document.getElementById('usersCountDesc');
        if (!desc) {
            return;
        }
        var match = desc.textContent.match(/(\d+)/);
        if (!match) {
            return;
        }
        var count = Math.max(0, parseInt(match[1], 10) + delta);
        desc.textContent = '共 ' + count + ' 位用户';
        if (count === 0) {
            window.location.reload();
        }
    }

    function confirmDelete() {
        if (window.VsModal && window.VsModal.confirm) {
            return window.VsModal.confirm(
                '删除后该用户的账号与绑定信息将永久移除，且不可恢复。确定删除吗？',
                '确认删除用户',
                { confirmText: '删除', danger: true }
            );
        }
        return Promise.resolve(window.confirm('删除后该用户的账号与绑定信息将永久移除，且不可恢复。确定删除吗？'));
    }

    function postAction(userId, action) {
        var body = new FormData();
        body.append('action', action);
        body.append('user_id', String(userId));

        return window.VS.postForm(body).then(function (data) {
            if (data.code !== 1) {
                throw new Error(data.msg || '操作失败');
            }
            return data;
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.vs-user-action-btn');
        if (!btn || btn.disabled) {
            return;
        }

        var userId = btn.getAttribute('data-user-id');
        var action = btn.getAttribute('data-user-action');
        if (!userId || !action) {
            return;
        }

        function run() {
            btn.disabled = true;
            postAction(userId, action)
                .then(function (data) {
                    updateUserRows(userId, action);
                    if (action === 'delete') {
                        updateCount(-1);
                    }
                    window.VS.showMessage(data.msg || '操作成功', 'success');
                })
                .catch(function (err) {
                    window.VS.showMessage(err.message || '网络异常，请稍后重试', 'error');
                })
                .finally(function () {
                    btn.disabled = false;
                });
        }

        if (action === 'delete') {
            confirmDelete().then(function (ok) {
                if (ok) {
                    run();
                }
            });
            return;
        }

        run();
    });
})();
