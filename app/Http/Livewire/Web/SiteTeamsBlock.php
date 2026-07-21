<?php
namespace App\Http\Livewire\Web;

use Livewire\Component;
use App\Models\Web\Site;
use App\Models\Web\SiteTeam;

class SiteTeamsBlock extends Component
{
    // 傳進來的條件（字串）
    public ?string $currentSite = null;  // site ID
    public ?string $currentRole = null;  // 例如 'plot_manager'

    // 查出來的資料
    public $teams = [];                  // SiteTeam(+team+site) 的集合
    public ?Site $site = null;           // 目前樣區的 Site model（如果有）

    public bool $showSiteLabel = true;   // 是否顯示樣區標籤（預設顯示）
    public bool $showRoleLabel = true;   // 是否顯示角色標籤（預設顯示）

    /**
     * 初始載入
     * 例如：
     * @livewire('web.site-teams-block', ['currentSite' => '1', 'currentRole' => 'plot_manager'])
     */
    public function mount(?string $currentSite = null, ?string $currentRole = null): void
    {
        // 先把參數記到屬性
        $this->currentSite = $currentSite;
        $this->currentRole = $currentRole;

        // 傳了 site / role 進來，就關掉對應的 label
        if ($this->currentSite) {
            $this->showSiteLabel = false;
        }

        if ($this->currentRole) {
            $this->showRoleLabel = false;
        }

        // 再根據目前條件去撈資料
        $this->loadTeams($this->currentSite, $this->currentRole);
    }

    /**
     * 共用查詢邏輯：可依 site、role（其中一個或兩個都有）過濾
     */
    protected function loadTeams(?string $siteId, ?string $role): void
    {
        $query = SiteTeam::with(['team', 'site'])
            ->orderBy('sort_order', 'asc');

        // 先處理 site 過濾
        if ($siteId) {
            $this->site = Site::findOrFail((int) $siteId);
            $this->currentSite = (string) $this->site->id;
            $query->where('site_id', $this->site->id);
        } else {
            $this->site = null;
            $this->currentSite = null;
        }

        // 再處理 role 過濾
        if ($role) {
            $this->currentRole = $role;
            $query->where('role', $role);
        } else {
            $this->currentRole = null;
        }

        // 先撈出所有 SiteTeam
        $siteTeams = $query->get();

        // 依 team_id 合併成「卡片」
        $this->teams = $siteTeams
            ->groupBy('team_id')
            ->map(function ($group) {
                $first = $group->first();

                return (object) [
                    // 這個團隊本身
                    'team'  => $first->team,

                    // 這個團隊在目前查詢結果中涉及到的所有樣區（去重）
                    'sites' => $group->pluck('site')
                        ->filter()
                        ->unique('id')
                        ->values(),

                    // 這個團隊在目前查詢結果中扮演過的角色（去重）
                    'roles' => $group->pluck('role')
                        ->filter()
                        ->unique()
                        ->values(),
                ];
            })
            ->values();
    }


    /**
     * 依樣區切換（例如前端點樣區 tab）
     */
    public function site(string $site): void
    {
        // 切換樣區時，保留目前角色
        $this->loadTeams($site, $this->currentRole);
    }

    /**
     * 依角色切換（例如前端點「樣區負責人」、「合作單位」）
     */
    public function role(string $role): void
    {
        // 切換角色時，保留目前樣區（優先用 currentSite，其次用已載入的 Site model）
        $siteId = $this->currentSite ?? ($this->site ? (string) $this->site->id : null);

        $this->loadTeams($siteId, $role);
    }

    public function render()
    {
        return view('livewire.web.site-teams-block');
    }
}
