<?php
/**
 * 文件：core/UserManager.php
 * 作用：管理员用户列表查询
 */

class UserManager
{
    /**
     * @return array
     */
    public static function all()
    {
        try {
            $pdo = Database::connect();
            $table = Database::table('user');
            $stmt = $pdo->query(
                'SELECT `id`, `username`, `email`, `avatar_url`, `oauth_qq_openid`, `oauth_gitee_id`,
                        `status`, `created_at`, `last_login_at`
                 FROM `' . $table . '`
                 ORDER BY `id` DESC'
            );
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : array();
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * @return int
     */
    public static function count()
    {
        try {
            $pdo = Database::connect();
            $table = Database::table('user');
            return (int) $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * @param int $userId
     * @param int $status 1=正常 0=封禁
     * @return true|string
     */
    public static function setStatus($userId, $status)
    {
        $userId = (int) $userId;
        $status = (int) $status;
        if ($userId <= 0) {
            return '无效用户';
        }
        if ($status !== 0 && $status !== 1) {
            return '无效状态';
        }

        try {
            $pdo = Database::connect();
            $table = Database::table('user');
            $stmt = $pdo->prepare('UPDATE `' . $table . '` SET `status` = ? WHERE `id` = ? LIMIT 1');
            $stmt->execute(array($status, $userId));
            if ($stmt->rowCount() === 0 && !self::exists($userId)) {
                return '用户不存在';
            }
            return true;
        } catch (Exception $e) {
            return '操作失败：' . $e->getMessage();
        }
    }

    /**
     * @param int $userId
     * @return true|string
     */
    public static function delete($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return '无效用户';
        }

        try {
            $pdo = Database::connect();
            $table = Database::table('user');
            $stmt = $pdo->prepare('DELETE FROM `' . $table . '` WHERE `id` = ? LIMIT 1');
            $stmt->execute(array($userId));
            if ($stmt->rowCount() === 0) {
                return '用户不存在';
            }
            return true;
        } catch (Exception $e) {
            return '删除失败：' . $e->getMessage();
        }
    }

    /**
     * @param int $userId
     * @return bool
     */
    public static function exists($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return false;
        }

        try {
            $pdo = Database::connect();
            $table = Database::table('user');
            $stmt = $pdo->prepare('SELECT `id` FROM `' . $table . '` WHERE `id` = ? LIMIT 1');
            $stmt->execute(array($userId));
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
}
