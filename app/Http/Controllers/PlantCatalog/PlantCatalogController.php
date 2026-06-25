<?php

namespace App\Http\Controllers\PlantCatalog;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlantCatalogController extends Controller
{
    public function upload(Request $request)
    {
        return view('pages.plant-catalog.upload', $this->viewData($request, '名錄上傳'));
    }

    public function download(Request $request)
    {
        return view('pages.plant-catalog.download', $this->viewData($request, '名錄下載'));
    }

    public function photos(Request $request)
    {
        return view('pages.plant-catalog.photos', $this->viewData($request, '照片編輯'));
    }

    public function editPhoto(Request $request, string $spcode)
    {
        return view('pages.plant-catalog.photo-edit', $this->viewData($request, '照片編輯') + [
            'spcode' => $spcode,
        ]);
    }

    private function viewData(Request $request, string $pageTitle): array
    {
        return [
            'site' => 'plant_catalog',
            'sitec' => '植物資料管理',
            'project' => $pageTitle,
            'user' => $request->user()?->account
                ?: $request->user()?->name
                ?: '',
        ];
    }
}
