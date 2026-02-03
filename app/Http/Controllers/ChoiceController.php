<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChoiceController extends Controller
{
    public function check(Request $request)
    {
         /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login'); // 或你的後台登入 route
        }

        // 1) 研究工作（依權限過濾）
        // dd($user->id, $user->canScope('fushan', 'tree'), config('work'));

        $workItems = collect(config('work', []))
            ->filter(function ($w) use ($user) {
                return $user->canScope($w['site'] ?? '', $w['module'] ?? '');
            })
            ->map(function ($w) {
                $w['url'] = route($w['route']);
                return $w;
            })
            ->values()
            ->all();


        // 2) 其它「不走 scope 的固定入口」（例如研究成果平台 / 網頁後台平台）
        $fixedItems = [
            [
                'key'   => 'webhome',
                'title' => '研究成果平台',
                'url'   => url('/'),
                'img'   => asset('/images/research/splist.png'),
                'class' => 'box3',
            ],
            [
                'key'   => 'webplatform',
                'title' => '網頁後端管理平台',
                'url'   => url('/web/splist'),
                'img'   => asset('/images/research/DSCN6021.JPG'),
                'class' => 'box3',
            ],
        ];

        return view('choice', [
            'authUserName' => $user->name,   // 這樣 blade 不會把 model 印出錯誤
            'fixedItems'   => $fixedItems,
            'workItems'    => $workItems,
        ]);
    }
}
