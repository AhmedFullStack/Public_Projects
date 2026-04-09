<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Lang;
use App\Core\Security;
use App\Models\ProjectModel;
use App\Models\CategoryModel;
use App\Models\SettingsModel;
use App\Helpers\SeoHelper;

/* ══════════════════════════════════════════════════════════
 *  PortfolioController
 * ══════════════════════════════════════════════════════════ */
class PortfolioController extends Controller
{
    public function index(array $params = []): void
    {
        $locale     = Lang::get();
        $page       = $this->currentPage();
        $perPage    = config('app.pagination.projects_per_page', 9);
        $categoryId = Security::sanitizeInt($this->get('cat', 0), 0) ?: null;
        $settings   = (new SettingsModel())->getAll();
        $categories = (new CategoryModel())->getAllWithTrans($locale);
        $result     = (new ProjectModel())->getActivePaginated($locale, $page, $perPage, $categoryId);

        SeoHelper::set([
            'title'       => Lang::t('portfolio.title'),
            'description' => SeoHelper::trimDesc(Lang::t('portfolio.subtitle')),
        ]);

        $this->view('pages/portfolio', array_merge($result, compact('categories', 'settings', 'locale', 'categoryId')));
    }

    public function show(array $params = []): void
    {
        $locale  = Lang::get();
        $slug    = Security::sanitizeString($params['slug'] ?? '', 200);
        $project = (new ProjectModel())->getBySlug($slug, $locale);

        if (!$project) {
            http_response_code(404);
            $this->view('pages/404');
            return;
        }

        // Increment views (non-blocking)
        (new ProjectModel())->incrementViews((int) $project['id']);

        // Related
        $related = $project['category_id']
            ? (new ProjectModel())->getRelated($project['id'], $project['category_id'], $locale)
            : [];

        $settings = (new SettingsModel())->getAll();

        SeoHelper::set([
            'title'       => $project['meta_title'] ?: $project['title'],
            'description' => SeoHelper::trimDesc($project['meta_description'] ?: $project['summary']),
            'canonical'   => base_url('portfolio/' . $project['slug']),
            'schema'      => [SeoHelper::schemaProject($project, $project)],
        ]);

        $this->view('pages/project', compact('project', 'related', 'settings', 'locale'));
    }
}


/* ══════════════════════════════════════════════════════════
 *  ContactController
 * ══════════════════════════════════════════════════════════ */
class ContactController extends Controller
{
    public function index(array $params = []): void
    {
        $locale   = Lang::get();
        $settings = (new SettingsModel())->getAll();
        $flash    = self::getFlash();

        SeoHelper::set([
            'title'       => Lang::t('contact.title'),
            'description' => SeoHelper::trimDesc(Lang::t('contact.subtitle')),
        ]);

        $this->view('pages/contact', compact('settings', 'locale', 'flash'));
    }

    public function send(array $params = []): void
    {
        Security::verifyCsrf();

        $locale = Lang::get();

        // Rate limit: 3 submissions per hour per IP
        if (!Security::checkRateLimit('contact', config('app.security.rate_limit_contact', [3, 3600]))) {
            if ($this->isAjax()) {
                $this->jsonError(Lang::t('contact.rate_limit'), 429);
            }
            $this->flash('error', Lang::t('contact.rate_limit'));
            $this->redirect(base_url('contact'));
        }

        // Validate & sanitize
        $name    = Security::sanitizeString($this->post('name', ''), 100);
        $email   = Security::sanitizeEmail($this->post('email', ''));
        $phone   = Security::sanitizeString($this->post('phone', ''), 30);
        $subject = Security::sanitizeString($this->post('subject', ''), 255);
        $body    = Security::plainText($this->post('message', ''), 5000);

        $errors = [];
        if (empty($name))    $errors[] = Lang::t('validation.required', ['field' => Lang::t('contact.name')]);
        if (!$email)         $errors[] = Lang::t('validation.email');
        if (empty($subject)) $errors[] = Lang::t('validation.required', ['field' => Lang::t('contact.subject')]);
        if (mb_strlen($body) < 10) $errors[] = Lang::t('validation.min', ['field' => Lang::t('contact.message'), 'min' => 10]);

        if (!empty($errors)) {
            if ($this->isAjax()) $this->jsonError(implode(' ', $errors));
            $this->flash('error', implode('<br>', $errors));
            $this->redirect(base_url('contact'));
        }

        // Save to DB
        $db = \App\Core\Database::getInstance();
        $db->insert('messages', [
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone,
            'subject'    => $subject,
            'body'       => $body,
            'ip_address' => Security::clientIp(),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'locale'     => $locale,
        ]);

        if ($this->isAjax()) {
            $this->jsonSuccess(Lang::t('contact.success'));
        }

        $this->flash('success', Lang::t('contact.success'));
        $this->redirect(base_url('contact'));
    }
}


/* ══════════════════════════════════════════════════════════
 *  AboutController
 * ══════════════════════════════════════════════════════════ */
class AboutController extends Controller
{
    public function index(array $params = []): void
    {
        $locale      = Lang::get();
        $settings    = (new SettingsModel())->getAll();
        $skills      = (new \App\Models\SkillModel())->getGroupedWithTrans($locale);
        $experiences = (new \App\Models\ExperienceModel())->getAllWithTrans($locale);
        $cv          = (new \App\Models\CvModel())->getActive($locale);

        SeoHelper::set([
            'title'       => Lang::t('about.title'),
            'description' => SeoHelper::trimDesc($settings['owner_title'] ?? ''),
        ]);

        $this->view('pages/about', compact('settings', 'skills', 'experiences', 'cv', 'locale'));
    }
}


/* ══════════════════════════════════════════════════════════
 *  PrivacyController
 * ══════════════════════════════════════════════════════════ */
class PrivacyController extends Controller
{
    public function index(array $params = []): void
    {
        $locale   = Lang::get();
        $settings = (new SettingsModel())->getAll();

        SeoHelper::set([
            'title'   => Lang::t('privacy.title'),
            'robots'  => 'noindex, follow',
        ]);

        $this->view('pages/privacy', compact('settings', 'locale'));
    }
}


/* ══════════════════════════════════════════════════════════
 *  CvDownloadController
 * ══════════════════════════════════════════════════════════ */
class CvDownloadController extends Controller
{
    public function download(array $params = []): void
    {
        $locale = Lang::get();
        $cv     = (new \App\Models\CvModel())->getActive($locale);

        if (!$cv) {
            http_response_code(404);
            exit('CV not available.');
        }

        $filePath = ROOT_PATH . '/' . config('app.upload.cv_dir') . $cv['filename'];
        if (!is_file($filePath)) {
            http_response_code(404);
            exit('File not found.');
        }

        (new \App\Models\CvModel())->incrementDownload((int)$cv['id']);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . e($cv['original_name']) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }
}