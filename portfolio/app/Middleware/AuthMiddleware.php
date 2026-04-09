<?php

namespace App\Middleware;

use App\Core\Security;

class AuthMiddleware
{
    public function handle(): void
    {
        Security::sessionStart();

        if (!empty($_SESSION['admin_id'])) return;

        // Check remember-me cookie
        $token = $_COOKIE['_rm'] ?? '';
        if ($token) {
            $db = \App\Core\Database::getInstance();
            $hash = hash('sha256', $token);
            $sess = $db->fetchOne(
                "SELECT as.*, a.id AS admin_id, a.is_active
                 FROM admin_sessions as
                 JOIN admins a ON a.id = as.admin_id
                 WHERE as.token_hash = ? AND as.expires_at > NOW()",
                [$hash]
            );
            if ($sess && $sess['is_active']) {
                $_SESSION['admin_id']   = $sess['admin_id'];
                $_SESSION['admin_role'] = $db->fetchColumn(
                    "SELECT role FROM admins WHERE id = ?", [$sess['admin_id']]
                );
                // Rotate session id
                session_regenerate_id(true);
                return;
            }
            // Invalid cookie – remove
            setcookie('_rm', '', time() - 3600, '/', '', true, true);
        }

        // Not authenticated
        $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect(base_url('admin/login'));
    }
}