<?php

namespace App\Http\Livewire\PlantCatalog;

use App\Services\PlantCatalog\FushanResearchSpeciesSyncService;
use App\Support\EnsuresAdmin;
use Livewire\Component;
use Throwable;

class CatalogMaintenance extends Component
{
    use EnsuresAdmin;

    public array $audit = [];
    public array $syncResult = [];
    public string $message = '';
    public string $messageType = 'info';

    public function mount(FushanResearchSpeciesSyncService $service): void
    {
        $this->ensureAdmin();
        $this->audit = $service->audit();
    }

    public function syncFushanSpecies(FushanResearchSpeciesSyncService $service): void
    {
        $this->ensureAdmin();
        $this->message = '';
        $this->syncResult = [];

        try {
            $this->syncResult = $service->sync();
            $this->audit = $service->audit();
            $this->messageType = 'success';
            $this->message = '福山調查物種整理完成。';
        } catch (Throwable $e) {
            report($e);
            $this->messageType = 'error';
            $this->message = '整理失敗：' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.plant-catalog.catalog-maintenance');
    }
}
