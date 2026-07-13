<?php

namespace App\Http\Livewire\Web;

use App\Models\Web\News;
use Livewire\Component;
use Livewire\WithPagination;

class NewsList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.web.news-list', [
            'newsItems' => News::public()->latestFirst()->paginate(12),
        ]);
    }
}
