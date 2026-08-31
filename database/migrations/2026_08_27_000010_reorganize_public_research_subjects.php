<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $webConnection = 'mysql_web';

    public function up(): void
    {
        $db = DB::connection($this->webConnection);

        $db->transaction(function () use ($db): void {
            $tree = $this->renameSubject(
                'subjects/tree',
                'subjects/long-term-tree-dynamics',
                '樹木長期動態',
                'Long-term Tree Dynamics',
                1
            );
            $forestRegeneration = $this->renameSubject(
                'subjects/seedling',
                'subjects/long-term-seedling-dynamics',
                '幼苗長期動態',
                'Long-term Seedling Dynamics',
                2
            );
            $plantDiversity = $this->renameSubject(
                'subjects/understory',
                'subjects/plant-diversity-community',
                '植物多樣性與群聚',
                'Plant Diversity & Community Ecology',
                3
            );
            $functionalTraits = $this->renameSubject(
                'subjects/functionaltraits',
                'subjects/functional-traits',
                '功能性狀',
                'Functional Traits',
                4
            );
            $biomassCarbon = $this->createSubject(
                'subjects/biomass-carbon',
                '生物量與碳循環',
                'Biomass & Carbon Cycling',
                5
            );
            $plantReproduction = $this->renameSubject(
                'subjects/seeds',
                'subjects/plant-reproduction-phenology',
                '植物繁殖與物候',
                'Plant Reproduction & Phenology',
                6
            );
            $soilNutrients = $this->createSubject(
                'subjects/soil-nutrient-cycling',
                '土壤與養分循環',
                'Soil & Nutrient Cycling',
                7
            );
            $forestEnvironment = $this->createSubject(
                'subjects/forest-environment-microclimate',
                '森林環境與微氣候',
                'Forest Environment & Microclimate',
                8
            );

            // Keep variables explicit so the intended eight active subjects
            // remain visible during future maintenance of this migration.
            unset(
                $forestRegeneration,
                $functionalTraits,
                $biomassCarbon,
                $plantReproduction,
                $soilNutrients,
                $forestEnvironment
            );

            $mortality = $this->subjectBySlug('subjects/mortality');
            if ($mortality !== null) {
                $this->moveRelations((int) $mortality->id, (int) $tree->id);
                $this->deactivateSubject((int) $mortality->id, 91);
            }

            $epiphytes = $this->subjectBySlug('subjects/epiphytes');
            if ($epiphytes !== null) {
                $this->moveRelations((int) $epiphytes->id, (int) $plantDiversity->id);
                $this->deactivateSubject((int) $epiphytes->id, 92);
            }

            $canopy = $this->subjectBySlug('subjects/canopy');
            if ($canopy !== null) {
                $this->detachRelations((int) $canopy->id);
                $this->deactivateSubject((int) $canopy->id, 93);
            }
        });
    }

    public function down(): void
    {
        // Content and relationship curation is intentionally not reversed.
    }

    private function renameSubject(
        string $oldSlug,
        string $newSlug,
        string $nameZh,
        string $nameEn,
        int $order
    ): object {
        $subject = $this->subjectBySlug($oldSlug) ?? $this->subjectBySlug($newSlug);

        if ($subject === null) {
            throw new \RuntimeException("找不到研究主題頁面：{$oldSlug}");
        }

        DB::connection($this->webConnection)->table('pages')->where('id', $subject->page_id)->update([
            'slug' => $newSlug,
            'title_zh_tw' => $nameZh,
            'title_en' => $nameEn,
            'nav_group' => 'subjects',
            'nav_order' => $order,
            'updated_at' => now(),
        ]);
        DB::connection($this->webConnection)->table('subjects')->where('id', $subject->id)->update([
            'name_zh_tw' => $nameZh,
            'name_en' => $nameEn,
            'short_name_zh_tw' => $nameZh,
            'short_name_en' => $nameEn,
            'sort_order' => $order,
            'is_active' => true,
            'updated_at' => now(),
        ]);

        return DB::connection($this->webConnection)->table('subjects')->where('id', $subject->id)->first();
    }

    private function createSubject(string $slug, string $nameZh, string $nameEn, int $order): object
    {
        $db = DB::connection($this->webConnection);
        $page = $db->table('pages')->where('slug', $slug)->first();

        if ($page === null) {
            $pageId = $db->table('pages')->insertGetId([
                'slug' => $slug,
                'title_zh_tw' => $nameZh,
                'title_en' => $nameEn,
                'nav_group' => 'subjects',
                'nav_order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $pageId = (int) $page->id;
            $db->table('pages')->where('id', $pageId)->update([
                'title_zh_tw' => $nameZh,
                'title_en' => $nameEn,
                'nav_group' => 'subjects',
                'nav_order' => $order,
                'updated_at' => now(),
            ]);
        }

        $subject = $db->table('subjects')->where('page_id', $pageId)->first();
        $values = [
            'name_zh_tw' => $nameZh,
            'name_en' => $nameEn,
            'short_name_zh_tw' => $nameZh,
            'short_name_en' => $nameEn,
            'sort_order' => $order,
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($subject === null) {
            $subjectId = $db->table('subjects')->insertGetId($values + [
                'page_id' => $pageId,
                'created_at' => now(),
            ]);
        } else {
            $subjectId = (int) $subject->id;
            $db->table('subjects')->where('id', $subjectId)->update($values);
        }

        return $db->table('subjects')->where('id', $subjectId)->first();
    }

    private function subjectBySlug(string $slug): ?object
    {
        return DB::connection($this->webConnection)->table('subjects as s')
            ->join('pages as p', 'p.id', '=', 's.page_id')
            ->where('p.slug', $slug)
            ->select('s.*')
            ->first();
    }

    private function moveRelations(int $fromSubjectId, int $toSubjectId): void
    {
        $relations = [
            'project_subject' => 'project_id',
            'publication_subject' => 'publication_id',
            'research_output_subject' => 'research_output_id',
        ];

        $db = DB::connection($this->webConnection);
        foreach ($relations as $table => $entityColumn) {
            $rows = $db->table($table)->where('subject_id', $fromSubjectId)->get();
            foreach ($rows as $row) {
                $values = (array) $row;
                unset($values['id']);
                $values['subject_id'] = $toSubjectId;
                $db->table($table)->insertOrIgnore($values);
            }
            $db->table($table)->where('subject_id', $fromSubjectId)->delete();
        }
    }

    private function detachRelations(int $subjectId): void
    {
        $db = DB::connection($this->webConnection);
        foreach (['project_subject', 'publication_subject', 'research_output_subject'] as $table) {
            $db->table($table)->where('subject_id', $subjectId)->delete();
        }
    }

    private function deactivateSubject(int $subjectId, int $order): void
    {
        $db = DB::connection($this->webConnection);
        $subject = $db->table('subjects')->where('id', $subjectId)->first();

        if ($subject === null) {
            return;
        }

        $db->table('subjects')->where('id', $subjectId)->update([
            'is_active' => false,
            'sort_order' => $order,
            'updated_at' => now(),
        ]);
        $db->table('pages')->where('id', $subject->page_id)->update([
            'nav_order' => $order,
            'updated_at' => now(),
        ]);
    }
};
