<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Lang;

class ProjectModel extends Model
{
    protected string $table      = 'projects';
    protected string $primaryKey = 'id';
    protected array  $fillable   = [
        'category_id','slug','featured_image','client','project_url',
        'year','duration','is_featured','is_active','sort_order',
    ];
    protected array $casts = [
        'is_featured' => 'bool',
        'is_active'   => 'bool',
        'views_count' => 'int',
        'sort_order'  => 'int',
    ];

    /* ── Public queries ──────────────────────────────────── */

    /** All active projects with translations for current locale */
    public function getActiveWithTrans(string $locale, ?int $categoryId = null): array
    {
        $where  = 'p.is_active = 1';
        $params = [$locale];

        if ($categoryId) {
            $where  .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }

        return $this->db->fetchAll(
            "SELECT p.*, pt.title, pt.summary, pt.meta_title, pt.meta_description,
                    c.slug AS category_slug,
                    ct.name AS category_name
             FROM projects p
             LEFT JOIN projects_translations pt ON pt.project_id = p.id AND pt.locale = ?
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN categories_translations ct ON ct.category_id = c.id AND ct.locale = ?
             WHERE {$where}
             ORDER BY p.is_featured DESC, p.sort_order ASC, p.created_at DESC",
            [$locale, $locale, ...(array_slice($params, 1))]
        );
    }

    /** Fix: correct param order */
    public function getActivePaginated(string $locale, int $page, int $perPage, ?int $categoryId = null): array
    {
        $where  = 'p.is_active = 1';
        $params = [];

        if ($categoryId) {
            $where  .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }

        $total  = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM projects p WHERE {$where}", $params
        );
        $pages  = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $items  = $this->db->fetchAll(
            "SELECT p.*, pt.title, pt.summary,
                    c.slug AS category_slug,
                    ct.name AS category_name
             FROM projects p
             LEFT JOIN projects_translations pt ON pt.project_id = p.id AND pt.locale = ?
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN categories_translations ct ON ct.category_id = c.id AND ct.locale = ?
             WHERE {$where}
             ORDER BY p.is_featured DESC, p.sort_order ASC, p.created_at DESC
             LIMIT ? OFFSET ?",
            [$locale, $locale, ...$params, $perPage, $offset]
        );

        return compact('items', 'total', 'pages', 'page', 'perPage');
    }

    /** Single project by slug with full translation */
    public function getBySlug(string $slug, string $locale): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT p.*,
                    pt.title, pt.summary, pt.description, pt.technologies,
                    pt.challenges, pt.results, pt.meta_title, pt.meta_description, pt.meta_keywords,
                    c.slug AS category_slug,
                    ct.name AS category_name
             FROM projects p
             LEFT JOIN projects_translations pt ON pt.project_id = p.id AND pt.locale = ?
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN categories_translations ct ON ct.category_id = c.id AND ct.locale = ?
             WHERE p.slug = ? AND p.is_active = 1
             LIMIT 1",
            [$locale, $locale, $slug]
        );

        if (!$row) return null;

        // Parse JSON technologies
        if (!empty($row['technologies'])) {
            $row['technologies'] = json_decode($row['technologies'], true) ?? [];
        }

        return $row;
    }

    /** Featured projects for homepage */
    public function getFeatured(string $locale, int $limit = 6): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, pt.title, pt.summary
             FROM projects p
             LEFT JOIN projects_translations pt ON pt.project_id = p.id AND pt.locale = ?
             WHERE p.is_active = 1 AND p.is_featured = 1
             ORDER BY p.sort_order ASC
             LIMIT ?",
            [$locale, $limit]
        );
    }

    public function incrementViews(int $id): void
    {
        $this->db->execute("UPDATE projects SET views_count = views_count + 1 WHERE id = ?", [$id]);
    }

    /** Related projects by category */
    public function getRelated(int $projectId, int $categoryId, string $locale, int $limit = 3): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, pt.title, pt.summary
             FROM projects p
             LEFT JOIN projects_translations pt ON pt.project_id = p.id AND pt.locale = ?
             WHERE p.is_active = 1 AND p.category_id = ? AND p.id != ?
             ORDER BY RAND()
             LIMIT ?",
            [$locale, $categoryId, $projectId, $limit]
        );
    }

    /* ── Admin queries ───────────────────────────────────── */

    public function getAllForAdmin(int $page = 1, int $perPage = 15): array
    {
        $total  = $this->db->count('projects');
        $offset = ($page - 1) * $perPage;

        $items = $this->db->fetchAll(
            "SELECT p.*, pt_ar.title AS title_ar, pt_en.title AS title_en,
                    ct_ar.name AS category_ar
             FROM projects p
             LEFT JOIN projects_translations pt_ar ON pt_ar.project_id = p.id AND pt_ar.locale = 'ar'
             LEFT JOIN projects_translations pt_en ON pt_en.project_id = p.id AND pt_en.locale = 'en'
             LEFT JOIN categories_translations ct_ar ON ct_ar.category_id = p.category_id AND ct_ar.locale = 'ar'
             ORDER BY p.sort_order ASC, p.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        $pages = (int) ceil($total / $perPage);
        return compact('items', 'total', 'pages', 'page', 'perPage');
    }

    public function getTranslations(int $projectId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM projects_translations WHERE project_id = ?",
            [$projectId]
        );
        $out = [];
        foreach ($rows as $row) $out[$row['locale']] = $row;
        return $out;
    }

    public function upsertTranslation(int $projectId, string $locale, array $data): void
    {
        $exists = $this->db->fetchOne(
            "SELECT id FROM projects_translations WHERE project_id = ? AND locale = ?",
            [$projectId, $locale]
        );

        if ($exists) {
            $this->db->update('projects_translations', $data, 'project_id = ? AND locale = ?', [$projectId, $locale]);
        } else {
            $this->db->insert('projects_translations', array_merge($data, ['project_id' => $projectId, 'locale' => $locale]));
        }
    }
}