<?php

namespace App\Models;

use App\Core\Model;

/** ──────────────────────────────────────────────────────────
 *  SettingsModel – site-wide configuration key/value store
 * ───────────────────────────────────────────────────────── */
class SettingsModel extends Model
{
    protected string $table    = 'settings';
    protected array  $fillable = ['key','value','group','is_translatable'];

    /** Return all settings as flat key=>value map */
    public function getAll(): array
    {
        $rows = $this->db->fetchAll("SELECT `key`, `value` FROM settings");
        $out  = [];
        foreach ($rows as $r) $out[$r['key']] = $r['value'];
        return $out;
    }

    public function getValue(string $key, mixed $default = null): mixed
    {
        $row = $this->db->fetchOne("SELECT `value` FROM settings WHERE `key` = ? LIMIT 1", [$key]);
        return $row ? $row['value'] : $default;
    }

    public function setValue(string $key, string $value): void
    {
        $exists = $this->db->fetchOne("SELECT id FROM settings WHERE `key` = ?", [$key]);
        if ($exists) {
            $this->db->execute("UPDATE settings SET `value` = ?, updated_at = NOW() WHERE `key` = ?", [$value, $key]);
        } else {
            $this->db->execute("INSERT INTO settings (`key`, `value`) VALUES (?,?)", [$key, $value]);
        }
    }

    public function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->setValue($key, (string) $value);
        }
    }

    public function getTranslation(int $settingId, string $locale): ?string
    {
        $row = $this->db->fetchOne(
            "SELECT value FROM settings_translations WHERE setting_id = ? AND locale = ?",
            [$settingId, $locale]
        );
        return $row ? $row['value'] : null;
    }

    public function getTranslatable(string $locale): array
    {
        $rows = $this->db->fetchAll(
            "SELECT s.key, COALESCE(st.value, s.value) AS value
             FROM settings s
             LEFT JOIN settings_translations st ON st.setting_id = s.id AND st.locale = ?
             WHERE s.is_translatable = 1",
            [$locale]
        );
        $out = [];
        foreach ($rows as $r) $out[$r['key']] = $r['value'];
        return $out;
    }
}


/** ──────────────────────────────────────────────────────────
 *  MessageModel – contact form inbox
 * ───────────────────────────────────────────────────────── */
class MessageModel extends Model
{
    protected string $table    = 'messages';
    protected array  $fillable = ['name','email','phone','subject','body','ip_address','user_agent','locale','status','is_spam'];
    protected array  $casts    = ['is_spam' => 'bool'];

    public function getInbox(int $page = 1, int $perPage = 15, string $status = ''): array
    {
        $where  = 'is_spam = 0';
        $params = [];
        if ($status) {
            $where   .= ' AND status = ?';
            $params[] = $status;
        }

        $total  = $this->db->count('messages', $where, $params);
        $offset = ($page - 1) * $perPage;
        $items  = $this->db->fetchAll(
            "SELECT * FROM messages WHERE {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );
        $pages  = (int) ceil($total / $perPage);
        return compact('items', 'total', 'pages', 'page', 'perPage');
    }

    public function countUnread(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages WHERE status = 'new' AND is_spam = 0"
        );
    }

    public function markRead(int $id): void
    {
        $this->db->execute("UPDATE messages SET status = 'read' WHERE id = ? AND status = 'new'", [$id]);
    }
}


/** ──────────────────────────────────────────────────────────
 *  SkillModel – technical skills with groups
 * ───────────────────────────────────────────────────────── */
class SkillModel extends Model
{
    protected string $table    = 'skills';
    protected array  $fillable = ['group_id','slug','proficiency','icon','sort_order','is_active'];
    protected array  $casts    = ['proficiency' => 'int', 'is_active' => 'bool', 'sort_order' => 'int'];

    public function getGroupedWithTrans(string $locale): array
    {
        $groups = $this->db->fetchAll(
            "SELECT sg.*, sgt.name
             FROM skill_groups sg
             LEFT JOIN skill_groups_translations sgt ON sgt.group_id = sg.id AND sgt.locale = ?
             ORDER BY sg.sort_order ASC",
            [$locale]
        );

        foreach ($groups as &$group) {
            $group['skills'] = $this->db->fetchAll(
                "SELECT s.*, skt.name
                 FROM skills s
                 LEFT JOIN skills_translations skt ON skt.skill_id = s.id AND skt.locale = ?
                 WHERE s.group_id = ? AND s.is_active = 1
                 ORDER BY s.sort_order ASC",
                [$locale, $group['id']]
            );
        }
        unset($group);
        return $groups;
    }
}


/** ──────────────────────────────────────────────────────────
 *  CategoryModel
 * ───────────────────────────────────────────────────────── */
class CategoryModel extends Model
{
    protected string $table    = 'categories';
    protected array  $fillable = ['slug','icon','color','sort_order','is_active'];

    public function getAllWithTrans(string $locale): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, ct.name, ct.description
             FROM categories c
             LEFT JOIN categories_translations ct ON ct.category_id = c.id AND ct.locale = ?
             WHERE c.is_active = 1
             ORDER BY c.sort_order ASC",
            [$locale]
        );
    }
}


/** ──────────────────────────────────────────────────────────
 *  ExperienceModel – career timeline
 * ───────────────────────────────────────────────────────── */
class ExperienceModel extends Model
{
    protected string $table    = 'experiences';
    protected array  $fillable = ['start_date','end_date','sort_order','is_active'];

    public function getAllWithTrans(string $locale): array
    {
        return $this->db->fetchAll(
            "SELECT e.*, et.job_title, et.company, et.location, et.description
             FROM experiences e
             LEFT JOIN experiences_translations et ON et.experience_id = e.id AND et.locale = ?
             WHERE e.is_active = 1
             ORDER BY e.start_date DESC",
            [$locale]
        );
    }
}


/** ──────────────────────────────────────────────────────────
 *  CvModel – uploaded CV files
 * ───────────────────────────────────────────────────────── */
class CvModel extends Model
{
    protected string $table    = 'cv_files';
    protected array  $fillable = ['locale','filename','original_name','file_size','is_active'];

    public function getActive(string $locale): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM cv_files WHERE locale = ? AND is_active = 1 ORDER BY uploaded_at DESC LIMIT 1",
            [$locale]
        );
    }

    public function incrementDownload(int $id): void
    {
        $this->db->execute("UPDATE cv_files SET download_count = download_count + 1 WHERE id = ?", [$id]);
    }
}