<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Lang;
use App\Core\Database;
use App\Models\SettingsModel;
use App\Models\MessageModel;
use App\Models\ProjectModel;

/* ══════════════════════════════════════════════════════════
 *  AuthController – login / logout
 * ══════════════════════════════════════════════════════════ */
class AuthController extends Controller
{
    protected string $layout = 'admin';

    public function showLogin(array $p = []): void
    {
        Security::sessionStart();
        if (!empty($_SESSION['admin_id'])) {
            redirect(base_url('admin'));
        }
        $this->view('admin/login', ['flash' => self::getFlash()]);
    }

    public function login(array $p = []): void
    {
        Security::verifyCsrf();

        $email    = Security::sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);

        if (!$email || empty($password)) {
            $this->flash('error', Lang::t('auth.invalid_creds'));
            $this->redirect(base_url('admin/login'));
        }

        $cfg = config('app.security');
        $db  = Database::getInstance();

        // Rate limit: 5 attempts per 15 min per IP
        if (!Security::checkRateLimit('admin_login', [$cfg['login_max_attempts'], $cfg['login_lockout_min'] * 60])) {
            $this->flash('error', Lang::t('auth.account_locked', ['min' => $cfg['login_lockout_min']]));
            $this->redirect(base_url('admin/login'));
        }

        $admin = $db->fetchOne(
            "SELECT * FROM admins WHERE email = ? AND is_active = 1 LIMIT 1",
            [$email]
        );

        if (!$admin || !Security::verifyPassword($password, $admin['password_hash'])) {
            // Log failed attempt
            $db->execute(
                "UPDATE admins SET failed_attempts = failed_attempts + 1 WHERE email = ?", [$email]
            );
            $this->flash('error', Lang::t('auth.invalid_creds'));
            $this->redirect(base_url('admin/login'));
        }

        // Lock check
        if (!empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) {
            $this->flash('error', Lang::t('auth.account_locked', ['min' => $cfg['login_lockout_min']]));
            $this->redirect(base_url('admin/login'));
        }

        // Success – establish session
        session_regenerate_id(true);
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'];

        // Update last login
        $db->execute(
            "UPDATE admins SET last_login_at=NOW(), last_login_ip=?, failed_attempts=0 WHERE id=?",
            [Security::clientIp(), $admin['id']]
        );

        // Remember Me
        if ($remember) {
            $token     = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expires   = date('Y-m-d H:i:s', time() + (config('app.security.remember_me_days', 30) * 86400));

            $db->execute(
                "DELETE FROM admin_sessions WHERE admin_id = ?", [$admin['id']]
            );
            $db->insert('admin_sessions', [
                'admin_id'   => $admin['id'],
                'token_hash' => $tokenHash,
                'ip_address' => Security::clientIp(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'expires_at' => $expires,
            ]);

            setcookie('_rm', $token, [
                'expires'  => time() + (config('app.security.remember_me_days', 30) * 86400),
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
        }

        // Redirect to intended URL
        $intended = $_SESSION['_intended'] ?? base_url('admin');
        unset($_SESSION['_intended']);
        $this->redirect($intended);
    }

    public function logout(array $p = []): void
    {
        Security::sessionStart();
        $db = Database::getInstance();

        if (!empty($_SESSION['admin_id'])) {
            $db->execute("DELETE FROM admin_sessions WHERE admin_id=?", [$_SESSION['admin_id']]);
        }

        session_unset();
        session_destroy();

        setcookie('_rm', '', time() - 3600, '/', '', true, true);
        $this->redirect(base_url('admin/login'));
    }
}

/* ══════════════════════════════════════════════════════════
 *  DashboardController
 * ══════════════════════════════════════════════════════════ */
class DashboardController extends Controller
{
    protected string $layout = 'admin';

    public function index(array $p = []): void
    {
        $this->requireAuth();
        $db = Database::getInstance();

        $stats = [
            'total_projects'  => $db->count('projects'),
            'active_projects' => $db->count('projects', 'is_active = 1'),
            'total_messages'  => $db->count('messages', 'is_spam = 0'),
            'unread_messages' => $db->count('messages', "status = 'new' AND is_spam = 0"),
            'total_views'     => (int) $db->fetchColumn("SELECT SUM(views_count) FROM projects") ?: 0,
            'cv_downloads'    => (int) $db->fetchColumn("SELECT SUM(download_count) FROM cv_files") ?: 0,
        ];

        $recentMessages = (new MessageModel())->getInbox(1, 5)['items'];
        $recentProjects = (new ProjectModel())->getAllForAdmin(1, 5)['items'];

        $this->view('admin/dashboard', compact('stats', 'recentMessages', 'recentProjects'));
    }
}

/* ══════════════════════════════════════════════════════════
 *  SettingsController
 * ══════════════════════════════════════════════════════════ */
class SettingsController extends Controller
{
    protected string $layout = 'admin';

    public function index(array $p = []): void
    {
        $this->requireAuth();
        $settings = (new SettingsModel())->getAll();
        $this->view('admin/settings', compact('settings'));
    }

    public function save(array $p = []): void
    {
        $this->requireAuth();
        Security::verifyCsrf();

        $allowed = [
            'owner_name','owner_title','owner_email','owner_phone',
            'linkedin_url','github_url','whatsapp_number',
            'years_experience','projects_count','clients_count',
            'google_analytics_id','google_adsense_id','maintenance_mode',
        ];

        $data = [];
        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                $data[$key] = Security::sanitizeString($_POST[$key], 500);
            }
        }

        (new SettingsModel())->setMany($data);
        $this->flash('success', Lang::t('admin.saved'));
        $this->redirect(base_url('admin/settings'));
    }
}

/* ══════════════════════════════════════════════════════════
 *  MessagesController
 * ══════════════════════════════════════════════════════════ */
class MessagesController extends Controller
{
    protected string $layout = 'admin';

    public function index(array $p = []): void
    {
        $this->requireAuth();
        $page   = $this->currentPage();
        $status = Security::sanitizeString($this->get('status', ''), 20);
        $result = (new MessageModel())->getInbox($page, 15, $status);
        $this->view('admin/messages/index', $result);
    }

    public function show(array $p = []): void
    {
        $this->requireAuth();
        $id = Security::sanitizeInt($p['id'] ?? 0, 1) ?: 0;
        $msg = (new MessageModel())->find($id);
        if (!$msg) { http_response_code(404); return; }

        (new MessageModel())->markRead($id);
        $this->view('admin/messages/show', compact('msg'));
    }

    public function destroy(array $p = []): void
    {
        $this->requireAuth();
        Security::verifyCsrf();
        $id = Security::sanitizeInt($p['id'] ?? 0, 1) ?: 0;
        (new MessageModel())->delete($id);
        $this->flash('success', Lang::t('admin.deleted'));
        $this->redirect(base_url('admin/messages'));
    }
}

/* ══════════════════════════════════════════════════════════
 *  SeoController
 * ══════════════════════════════════════════════════════════ */
class SeoController extends Controller
{
    protected string $layout = 'admin';

    public function index(array $p = []): void
    {
        $this->requireAuth();
        $db   = Database::getInstance();
        $pages = $db->fetchAll("SELECT * FROM page_seo ORDER BY page_key");
        $this->view('admin/seo', compact('pages'));
    }

    public function save(array $p = []): void
    {
        $this->requireAuth();
        Security::verifyCsrf();

        $db     = Database::getInstance();
        $locales= config('app.locale.available', ['ar','en']);

        $pageKey = Security::sanitizeString($_POST['page_key'] ?? '', 100);
        if (!$pageKey) {
            $this->flash('error', 'Invalid page key');
            $this->redirect(base_url('admin/seo'));
        }

        foreach ($locales as $locale) {
            $title = Security::sanitizeString($_POST["title_{$locale}"] ?? '', 70);
            $desc  = Security::sanitizeString($_POST["desc_{$locale}"] ?? '', 165);
            $kw    = Security::sanitizeString($_POST["keywords_{$locale}"] ?? '', 255);

            $existing = $db->fetchOne(
                "SELECT id FROM page_seo WHERE page_key=? AND locale=?", [$pageKey, $locale]
            );
            if ($existing) {
                $db->update('page_seo',
                    ['title'=>$title,'description'=>$desc,'keywords'=>$kw],
                    'page_key=? AND locale=?', [$pageKey, $locale]
                );
            } else {
                $db->insert('page_seo', [
                    'page_key'=>$pageKey,'locale'=>$locale,
                    'title'=>$title,'description'=>$desc,'keywords'=>$kw,
                ]);
            }
        }

        $this->flash('success', Lang::t('admin.saved'));
        $this->redirect(base_url('admin/seo'));
    }
}