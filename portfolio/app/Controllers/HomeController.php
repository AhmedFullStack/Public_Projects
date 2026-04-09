<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Lang;
use App\Models\ProjectModel;
use App\Models\SkillModel;
use App\Models\SettingsModel;
use App\Models\CvModel;
use App\Helpers\SeoHelper;

class HomeController extends Controller
{
    public function index(array $params = []): void
    {
        $locale   = Lang::get();
        $settings = (new SettingsModel())->getAll();
        $projects = (new ProjectModel())->getFeatured($locale, 6);
        $skills   = (new SkillModel())->getGroupedWithTrans($locale);
        $cv       = (new CvModel())->getActive($locale);

        // SEO
        SeoHelper::set([
            'title'       => $settings['owner_name'] ?? Lang::t('nav.home'),
            'title_raw'   => true,
            'description' => SeoHelper::trimDesc(
                Lang::choose(
                    'مهندس إلكتروميكانيكس محترف — ' . ($settings['owner_title'] ?? ''),
                    'Professional Electromechanical Engineer — ' . ($settings['owner_title'] ?? '')
                )
            ),
            'schema'      => [
                SeoHelper::schemaPerson($settings),
                SeoHelper::schemaWebSite($settings),
            ],
        ]);

        $this->view('pages/home', compact('settings', 'projects', 'skills', 'cv', 'locale'));
    }
}