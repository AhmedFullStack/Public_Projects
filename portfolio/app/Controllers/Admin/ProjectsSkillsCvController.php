<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Lang;
use App\Core\Database;
use App\Models\ProjectModel;
use App\Models\CategoryModel;
use App\Models\SkillModel;
use App\Models\CvModel;

/* ══════════════════════════════════════════════════════════
 *  ProjectsController
 * ══════════════════════════════════════════════════════════ */
class ProjectsController extends Controller
{
    protected string $layout = 'admin';

    public function index(array $p = []): void
    {
        $this->requireAuth();
        $result = (new ProjectModel())->getAllForAdmin($this->currentPage(), 15);
        $this->view('admin/projects/index', $result);
    }

    public function create(array $p = []): void
    {
        $this->requireAuth();
        $categories = (new CategoryModel())->getAllWithTrans('ar');
        $this->view('admin/projects/form', ['project' => null, 'translations' => [], 'categories' => $categories, 'flash' => self::getFlash()]);
    }

    public function store(array $p = []): void
    {
        $this->requireAuth();
        Security::verifyCsrf();

        $data = $this->buildProjectData();
        if (is_string($data)) {               // validation error string
            $this->flash('error', $data);
            $this->redirect(base_url('admin/projects/create'));
        }

        $db = Database::getInstance();
        $db->transaction(function($db) use ($data) {
            $id = (new ProjectModel())->create($data['project']);
            foreach ($data['translations'] as $locale => $trans) {
                (new ProjectModel())->upsertTranslation((int)$id, $locale, $trans);
            }
        });

        $this->flash('success', Lang::t('admin.saved'));
        $this->redirect(base_url('admin/projects'));
    }

    public function edit(array $p = []): void
    {
        $this->requireAuth();
        $id = Security::sanitizeInt($p['id'] ?? 0, 1);
        if (!$id) { redirect(base_url('admin/projects')); }

        $project      = (new ProjectModel())->find($id);
        if (!$project) { redirect(base_url('admin/projects')); }

        $translations = (new ProjectModel())->getTranslations($id);
        $categories   = (new CategoryModel())->getAllWithTrans('ar');

        $this->view('admin/projects/form', compact('project', 'translations', 'categories') + ['flash' => self::getFlash()]);
    }

    public function update(array $p = []): void
    {
        $this->requireAuth();
        Security::verifyCsrf();

        $id = Security::sanitizeInt($p['id'] ?? 0, 1);
        if (!$id) { redirect(base_url('admin/projects')); }

        $data = $this->buildProjectData();
        if (is_string($data)) {
            $this->flash('error', $data);
            $this->redirect(base_url("admin/projects/{$id}/edit"));
        }

        Database::getInstance()->transaction(function() use ($id, $data) {
            (new ProjectModel())->update($id, $data['project']);
            foreach ($data['translations'] as $locale => $trans) {
                (new ProjectModel())->upsertTranslation($id, $locale, $trans);
            }
        });

        // Handle image upload
        if (!empty($_FILES['featured_image']['tmp_name'])) {
            $this->handleImageUpload($id);
        }

        $this->flash('success', Lang::t('admin.saved'));
        $this->redirect(base_url('admin/projects'));
    }

    public function destroy(array $p = []): void
    {
        $this->requireAuth();
        Security::verifyCsrf();
        $id = Security::sanitizeInt($p['id'] ?? 0, 1);
        if ($id) (new ProjectModel())->delete($id);
        $this->flash('success', Lang::t('admin.deleted'));
        $this->redirect(base_url('admin/projects'));
    }

    /* ── Helpers ─────────────────────────────────────────── */

    private function buildProjectData(): array|string
    {
        $locales = config('app.locale.available', ['ar','en']);

        // Validate at least one title
        $hasTitle = false;
        foreach ($locales as $l) {
            if (!empty($_POST["title_{$l}"])) { $hasTitle = true; break; }
        }
        if (!$hasTitle) return Lang::t('validation.required', ['field' => 'title']);

        $slug = Security::sanitizeString($_POST['slug'] ?? '', 200);
        if (!$slug) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9\-]+/', '-', $_POST['title_en'] ?? $_POST['title_ar'] ?? 'project'), '-'));
        }

        $project = [
            'category_id'    => Security::sanitizeInt($_POST['category_id'] ?? 0, 0) ?: null,
            'slug'           => $slug,
            'client'         => Security::sanitizeString($_POST['client'] ?? '', 150),
            'project_url'    => Security::sanitizeUrl($_POST['project_url'] ?? '') ?: null,
            'year'           => Security::sanitizeInt($_POST['year'] ?? '', 1900, 2099) ?: null,
            'duration'       => Security::sanitizeString($_POST['duration'] ?? '', 50),
            'is_featured'    => !empty($_POST['is_featured']) ? 1 : 0,
            'is_active'      => !empty($_POST['is_active']) ? 1 : 0,
            'sort_order'     => Security::sanitizeInt($_POST['sort_order'] ?? 0, 0) ?: 0,
        ];

        $translations = [];
        foreach ($locales as $locale) {
            $title = Security::sanitizeString($_POST["title_{$locale}"] ?? '', 255);
            if (!$title) continue;
            $tech = $_POST["technologies_{$locale}"] ?? '';
            // Normalize tech tags to JSON
            if ($tech && !json_decode($tech)) {
                $tags = array_filter(array_map('trim', explode(',', $tech)));
                $tech = json_encode($tags, JSON_UNESCAPED_UNICODE);
            }
            $translations[$locale] = [
                'title'            => $title,
                'summary'          => Security::sanitizeString($_POST["summary_{$locale}"] ?? '', 500),
                'description'      => Security::plainText($_POST["description_{$locale}"] ?? '', 50000),
                'technologies'     => $tech,
                'challenges'       => Security::plainText($_POST["challenges_{$locale}"] ?? '', 5000),
                'results'          => Security::plainText($_POST["results_{$locale}"] ?? '', 5000),
                'meta_title'       => Security::sanitizeString($_POST["meta_title_{$locale}"] ?? '', 70),
                'meta_description' => Security::sanitizeString($_POST["meta_desc_{$locale}"] ?? '', 165),
                'meta_keywords'    => Security::sanitizeString($_POST["meta_kw_{$locale}"] ?? '', 255),
            ];
        }

        return compact('project', 'translations');
    }

    private function handleImageUpload(int $projectId): void
    {
        $file = $_FILES['featured_image'];
        $valid = Security::validateUpload($file, 'image');
        if ($valid !== true) return;

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = Security::safeFilename($file['name'], $ext);
        $dest     = ROOT_PATH . '/' . config('app.upload.projects_dir') . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            (new ProjectModel())->update($projectId, ['featured_image' => $filename]);
        }
    }
}

/* ══════════════════════════════════════════════════════════
 *  SkillsController
 * ══════════════════════════════════════════════════════════ */
class SkillsController extends Controller
{
    protected string $layout = 'admin';

    public function index(array $p = []): void
    {
        $this->requireAuth();
        $skills = (new SkillModel())->getGroupedWithTrans('ar');
        $db     = Database::getInstance();
        $groups = $db->fetchAll("SELECT sg.*, sgt.name FROM skill_groups sg LEFT JOIN skill_groups_translations sgt ON sgt.group_id=sg.id AND sgt.locale='ar' ORDER BY sg.sort_order");
        $this->view('admin/skills/index', compact('skills', 'groups'));
    }

    public function save(array $p = []): void
    {
        $this->requireAuth();
        Security::verifyCsrf();

        $db      = Database::getInstance();
        $locales = config('app.locale.available', ['ar','en']);
        $ids     = $_POST['skill_id'] ?? [];
        $names_ar = $_POST['name_ar'] ?? [];
        $names_en = $_POST['name_en'] ?? [];
        $proficiencies = $_POST['proficiency'] ?? [];
        $groupIds = $_POST['group_id'] ?? [];
        $orders  = $_POST['sort_order'] ?? [];

        $db->transaction(function() use ($db, $ids, $names_ar, $names_en, $proficiencies, $groupIds, $orders, $locales) {
            foreach ($ids as $i => $skillId) {
                $skillId = (int) $skillId;
                $pct     = min(100, max(0, (int)($proficiencies[$i] ?? 0)));
                $gid     = (int)($groupIds[$i] ?? 0) ?: null;
                $ord     = (int)($orders[$i] ?? 0);

                if ($skillId > 0) {
                    $db->update('skills', ['proficiency'=>$pct,'group_id'=>$gid,'sort_order'=>$ord], 'id=?', [$skillId]);
                } else {
                    $skillId = (int) $db->insert('skills', ['proficiency'=>$pct,'group_id'=>$gid,'sort_order'=>$ord,'slug'=>'skill-'.time().'-'.$i]);
                }

                $nameMap = ['ar' => $names_ar[$i] ?? '', 'en' => $names_en[$i] ?? ''];
                foreach ($locales as $l) {
                    $name = Security::sanitizeString($nameMap[$l] ?? '', 100);
                    if (!$name) continue;
                    $exists = $db->fetchOne("SELECT id FROM skills_translations WHERE skill_id=? AND locale=?", [$skillId,$l]);
                    if ($exists) {
                        $db->update('skills_translations', ['name'=>$name], 'skill_id=? AND locale=?', [$skillId,$l]);
                    } else {
                        $db->insert('skills_translations', ['skill_id'=>$skillId,'locale'=>$l,'name'=>$name]);
                    }
                }
            }
        });

        $this->flash('success', Lang::t('admin.saved'));
        $this->redirect(base_url('admin/skills'));
    }
}

/* ══════════════════════════════════════════════════════════
 *  CvController
 * ══════════════════════════════════════════════════════════ */
class CvController extends Controller
{
    protected string $layout = 'admin';

    public function index(array $p = []): void
    {
        $this->requireAuth();
        $db   = Database::getInstance();
        $cvs  = $db->fetchAll("SELECT * FROM cv_files ORDER BY uploaded_at DESC");
        $this->view('admin/cv/index', compact('cvs'));
    }

    public function upload(array $p = []): void
    {
        $this->requireAuth();
        Security::verifyCsrf();

        $file = $_FILES['cv_file'] ?? null;
        if (!$file) {
            $this->flash('error', 'No file uploaded.');
            $this->redirect(base_url('admin/cv'));
        }

        $valid = Security::validateUpload($file, 'cv');
        if ($valid !== true) {
            $this->flash('error', $valid);
            $this->redirect(base_url('admin/cv'));
        }

        $locale   = Security::sanitizeString($_POST['locale'] ?? 'ar', 5);
        $filename = Security::safeFilename($file['name'], 'pdf');
        $dest     = ROOT_PATH . '/' . config('app.upload.cv_dir') . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->flash('error', 'Failed to save file.');
            $this->redirect(base_url('admin/cv'));
        }

        // Deactivate old CVs for same locale
        Database::getInstance()->execute(
            "UPDATE cv_files SET is_active=0 WHERE locale=?", [$locale]
        );

        (new CvModel())->create([
            'locale'        => $locale,
            'filename'      => $filename,
            'original_name' => Security::sanitizeString($file['name'], 255),
            'file_size'     => $file['size'],
            'is_active'     => 1,
        ]);

        $this->flash('success', Lang::t('admin.saved'));
        $this->redirect(base_url('admin/cv'));
    }

    public function destroy(array $p = []): void
    {
        $this->requireAuth();
        Security::verifyCsrf();
        $id  = Security::sanitizeInt($p['id'] ?? 0, 1);
        $cv  = (new CvModel())->find($id);

        if ($cv) {
            $filePath = ROOT_PATH . '/' . config('app.upload.cv_dir') . $cv['filename'];
            if (is_file($filePath)) @unlink($filePath);
            (new CvModel())->delete($id);
        }

        $this->flash('success', Lang::t('admin.deleted'));
        $this->redirect(base_url('admin/cv'));
    }
}