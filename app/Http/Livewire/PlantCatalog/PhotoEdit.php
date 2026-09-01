<?php

namespace App\Http\Livewire\PlantCatalog;

use App\Models\PlantCatalog\SiteSpecies;
use App\Models\Web\DisNote;
use App\Models\Web\Photo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithFileUploads;

class PhotoEdit extends Component
{
    use WithFileUploads;

    public string $spcode = '';
    public string $catalogCode = '';
    public array $speciesinfo = [];
    public array $editingPhotos = [];
    public array $editingDisNotes = [];
    public array $disNoteTypeOptions = [];
    public array $disNoteType2Options = [];
    public array $typeOptions = [];
    public array $type2Options = [];
    public array $freshOptions = [];
    public array $statusOptions = [];
    public string $newDisNoteType = '';
    public string $newDisNoteType2 = '';
    public string $newDisNoteNote = '';
    public $newPhotoFile;
    public string $disNoteMessage = '';
    public array $disNoteMessages = [];
    public string $photoUploadMessage = '';
    public array $photoMessages = [];
    public int $messageVersion = 0;

    public function mount(string $spcode): void
    {
        $this->spcode = $spcode;
        $species = SiteSpecies::query()
            ->fushan()
            ->withChecklistTaxonomy()
            ->where('site_species.spcode', $spcode)
            ->firstOrFail();
        $this->speciesinfo = $species->toArray();
        $this->catalogCode = (string) $species->code;
        $this->loadOptions();
        $this->reloadDisNotes();
        $this->reloadPhotos();
    }

    public function saveDisNote(string $noteKey): void
    {
        $this->clearMessages();

        if (!isset($this->editingDisNotes[$noteKey])) {
            return;
        }

        $this->validate([
            "editingDisNotes.{$noteKey}.type" => ['nullable', 'string', 'max:100'],
            "editingDisNotes.{$noteKey}.type2" => ['nullable', 'string', 'max:100'],
            "editingDisNotes.{$noteKey}.note" => ['nullable', 'string', 'max:2000'],
        ]);

        $note = $this->editingDisNotes[$noteKey];

        $this->disNoteQuery($noteKey)
            ->update($this->filterDisNoteColumns([
                'type' => $note['type'] ?? '',
                'type2' => $note['type2'] ?? '',
                'note' => $note['note'] ?? '',
                'updated_id' => $this->userName(),
                'updated_at' => now(),
            ]));

        $this->loadOptions();
        $this->reloadDisNotes();
        $this->disNoteMessages[$noteKey] = '辨識要點已儲存。';
        $this->messageVersion++;
    }

    public function createDisNote(): void
    {
        $this->clearMessages();

        $this->validate([
            'newDisNoteType' => ['required', 'string', 'max:100'],
            'newDisNoteType2' => ['nullable', 'string', 'max:100'],
            'newDisNoteNote' => ['required', 'string', 'max:2000'],
        ], [], [
            'newDisNoteType' => '類型',
            'newDisNoteType2' => '種子雨分類',
            'newDisNoteNote' => '辨識要點',
        ]);

        DisNote::query()->create($this->filterDisNoteColumns([
            'spcode' => $this->spcode,
            'type' => $this->newDisNoteType,
            'type2' => $this->newDisNoteType2,
            'note' => $this->newDisNoteNote,
            'updated_id' => $this->userName(),
            'updated_at' => now(),
        ]));

        $this->newDisNoteType = '';
        $this->newDisNoteType2 = '';
        $this->newDisNoteNote = '';
        $this->reloadDisNotes();
        $this->disNoteMessage = '辨識要點已新增。';
        $this->messageVersion++;
    }

    public function deleteDisNote(string $noteKey): void
    {
        $this->clearMessages();
        $this->disNoteQuery($noteKey)->delete();
        $this->reloadDisNotes();
        $this->disNoteMessage = '辨識要點已刪除。';
        $this->messageVersion++;
    }

    public function savePhoto(string $photoKey): void
    {
        $this->clearMessages();

        if (!isset($this->editingPhotos[$photoKey])) {
            return;
        }

        $this->validate([
            "editingPhotos.{$photoKey}.type" => ['nullable', 'string', 'max:100'],
            "editingPhotos.{$photoKey}.type2" => ['nullable', 'string', 'max:100'],
            "editingPhotos.{$photoKey}.fresh" => ['nullable', 'string', 'max:100'],
            "editingPhotos.{$photoKey}.status" => ['nullable', 'string', 'max:100'],
            "editingPhotos.{$photoKey}.photo_date" => ['nullable', 'date_format:Y-m-d'],
            "editingPhotos.{$photoKey}.is_public" => ['required', 'in:0,1'],
            "editingPhotos.{$photoKey}.is_featured" => ['required', 'in:0,1'],
            "editingPhotos.{$photoKey}.photoby" => ['nullable', 'string', 'max:100'],
            "editingPhotos.{$photoKey}.des" => ['nullable', 'string', 'max:1000'],
        ]);

        $photo = $this->editingPhotos[$photoKey];

        DB::connection('mysql_web')->transaction(function () use ($photoKey, $photo): void {
            if ((int) ($photo['is_featured'] ?? 0) === 1) {
                Photo::query()
                    ->where('code', $this->catalogCode)
                    ->update(['is_featured' => 0]);
            }

            $this->photoQuery($photoKey)->update($this->filterPhotoColumns([
                'code' => $this->catalogCode,
                'site' => $photo['site'] ?? 'fushan',
                'type' => $photo['type'] ?? '',
                'type2' => $photo['type2'] ?? '',
                'fresh' => $photo['fresh'] ?? '',
                'status' => $photo['status'] ?? '',
                'photo_date' => ($photo['photo_date'] ?? '') !== '' ? $photo['photo_date'] : null,
                'is_public' => (int) ($photo['is_public'] ?? 1),
                'is_featured' => (int) ($photo['is_featured'] ?? 0),
                'photoby' => $photo['photoby'] ?? '',
                'des' => $photo['des'] ?? '',
                'updated_id' => $this->userName(),
                'updated_at' => now(),
            ]));
        });

        $this->reloadPhotos();
        $this->photoMessages[$photoKey] = '照片資料已儲存。';
        $this->messageVersion++;
    }

    public function deletePhoto(string $photoKey): void
    {
        $this->clearMessages();

        $photo = $this->photoQuery($photoKey)->first();

        if (!$photo) {
            return;
        }

        $directory = public_path("FDPfiles/splist/photo/{$photo->spcode}");
        File::delete([
            "{$directory}/{$photo->filename}",
            "{$directory}/s_{$photo->filename}",
        ]);

        $this->photoQuery($photoKey)->delete();
        $this->reloadPhotos();
        $this->photoUploadMessage = '照片已刪除。';
        $this->messageVersion++;
    }

    public function updatedNewPhotoFile(): void
    {
        if ($this->newPhotoFile) {
            $this->uploadPhoto();
        }
    }

    public function uploadPhoto(): void
    {
        $this->clearMessages();

        $this->validate([
            'newPhotoFile' => ['required', 'image', 'max:10240'],
        ], [], [
            'newPhotoFile' => '照片檔案',
        ]);

        try {
            $filename = $this->storePlantPhoto();

            Photo::query()->create($this->filterPhotoColumns([
                'spcode' => $this->spcode,
                'code' => $this->catalogCode,
                'site' => 'fushan',
                'filename' => $filename,
                'type' => '',
                'type2' => '',
                'fresh' => '',
                'status' => '',
                'photo_date' => null,
                'is_public' => 1,
                'is_featured' => 0,
                'photoby' => '',
                'des' => '',
                'uploaded_id' => $this->userName(),
                'uploaded_at' => now()->toDateString(),
                'updated_id' => $this->userName(),
                'updated_at' => now(),
            ]));
        } catch (\Throwable $exception) {
            if (isset($filename)) {
                $directory = public_path("FDPfiles/splist/photo/{$this->spcode}");
                File::delete(["{$directory}/{$filename}", "{$directory}/s_{$filename}"]);
            }

            report($exception);
            $this->addError('newPhotoFile', '照片新增失敗，請確認檔案格式或聯絡管理員。');

            return;
        }

        $this->newPhotoFile = null;
        $this->reloadPhotos();
        $this->photoUploadMessage = '照片已新增。';
        $this->messageVersion++;
    }

    public function render()
    {
        return view('livewire.plant-catalog.photo-edit');
    }

    private function reloadPhotos(): void
    {
        $this->editingPhotos = Photo::query()
            ->where(function ($query): void {
                $query->where('code', $this->catalogCode)
                    ->orWhere(function ($legacy): void {
                        $legacy->where('spcode', $this->spcode)
                            ->where(function ($missingCode): void {
                                $missingCode->whereNull('code')->orWhere('code', '');
                            });
                    });
            })
            ->orderByDesc('is_featured')
            ->orderBy('type2')
            ->orderBy('filename')
            ->get()
            ->mapWithKeys(function ($photo) {
                $key = isset($photo->id)
                    ? (string) $photo->id
                    : 'photo_' . md5($photo->filename);

                return [
                    $key => [
                        'key' => $key,
                        'id' => $photo->id ?? null,
                        'spcode' => $photo->spcode,
                        'site' => $photo->site ?? '',
                        'filename' => $photo->filename,
                        'type' => $photo->type ?? '',
                        'type2' => $photo->type2 ?? '',
                        'fresh' => $photo->fresh ?? '',
                        'status' => $photo->status ?? '',
                        'photo_date' => $this->normalizeDate((string) ($photo->photo_date ?? '')),
                        'is_public' => (string) (int) ($photo->is_public ?? 1),
                        'is_featured' => (string) (int) ($photo->is_featured ?? 0),
                        'photoby' => $photo->photoby ?? '',
                        'des' => $photo->des ?? '',
                    ],
                ];
            })
            ->toArray();
    }

    private function loadOptions(): void
    {
        $photos = Photo::query()
            ->select('type2', 'type', 'fresh', 'status')
            ->orderBy('type2')
            ->get();

        $disNotes = DisNote::query()
            ->select('type2', 'type')
            ->orderBy('type2')
            ->get();

        $this->typeOptions = collect($photos->pluck('type')->all())
            ->map(fn ($type) => trim((string) $type))
            ->merge(['葉片', '植株'])
            ->filter(fn ($type) => $type !== '')
            ->unique()
            ->values()
            ->all();

        $this->type2Options = $this->distinctOptions($photos->pluck('type2')->all());

        $this->freshOptions = $this->distinctOptions($photos->pluck('fresh')->all());
        $this->statusOptions = $this->distinctOptions($photos->pluck('status')->all());
        $this->disNoteTypeOptions = collect($disNotes->pluck('type')->all())
            ->map(fn ($type) => trim((string) $type))
            ->merge(['葉片', '植株'])
            ->filter(fn ($type) => $type !== '')
            ->unique()
            ->values()
            ->all();
        $this->disNoteType2Options = $this->distinctOptions($disNotes->pluck('type2')->all());
    }

    private function reloadDisNotes(): void
    {
        $this->editingDisNotes = DisNote::query()
            ->where('spcode', $this->spcode)
            ->orderBy('type2')
            ->when(
                Schema::connection('mysql_web')->hasColumn('dis_note', 'id'),
                fn ($query) => $query->orderBy('id')
            )
            ->get()
            ->mapWithKeys(function ($note) {
                $key = isset($note->id)
                    ? (string) $note->id
                    : 'note_' . md5(($note->type2 ?? '') . '|' . ($note->note ?? ''));

                return [
                    $key => [
                        'key' => $key,
                        'id' => $note->id ?? null,
                        'type' => $note->type ?? '',
                        'type2' => $note->type2 ?? '',
                        'note' => $note->note ?? '',
                    ],
                ];
            })
            ->toArray();
    }

    private function disNoteQuery(string $noteKey)
    {
        $note = $this->editingDisNotes[$noteKey] ?? null;

        $query = DisNote::query()->where('spcode', $this->spcode);

        if ($note && !empty($note['id']) && Schema::connection('mysql_web')->hasColumn('dis_note', 'id')) {
            return $query->where('id', $note['id']);
        }

        return $query
            ->where('type2', $note['type2'] ?? '')
            ->where('note', $note['note'] ?? '');
    }

    private function photoQuery(string $photoKey)
    {
        $photo = $this->editingPhotos[$photoKey] ?? null;

        $query = Photo::query()->where(function ($query): void {
            $query->where('code', $this->catalogCode)
                ->orWhere(function ($legacy): void {
                    $legacy->where('spcode', $this->spcode)
                        ->where(function ($missingCode): void {
                            $missingCode->whereNull('code')->orWhere('code', '');
                        });
                });
        });

        if ($photo && !empty($photo['id'])) {
            return $query->where('id', $photo['id']);
        }

        return $query->where('filename', $photo['filename'] ?? $photoKey);
    }

    private function storePlantPhoto(): string
    {
        $directory = public_path("FDPfiles/splist/photo/{$this->spcode}");
        File::ensureDirectoryExists($directory);

        $safeSpcode = preg_replace('/[^A-Za-z0-9_-]+/', '_', $this->spcode);
        $filename = sprintf('fs_%s_%05d.jpg', $safeSpcode, random_int(0, 99999));

        while (File::exists("{$directory}/{$filename}")) {
            $filename = sprintf('fs_%s_%05d.jpg', $safeSpcode, random_int(0, 99999));
        }

        $image = \imagecreatefromstring(file_get_contents($this->newPhotoFile->getRealPath()));

        if (!$image) {
            $this->addError('newPhotoFile', '無法讀取這張照片。');
            throw new \RuntimeException('Unable to read uploaded plant photo.');
        }

        if (! \imagejpeg($image, "{$directory}/{$filename}", 90)) {
            \imagedestroy($image);
            throw new \RuntimeException('Unable to write uploaded plant photo.');
        }

        $this->saveThumbnail($image, "{$directory}/s_{$filename}", 500);
        \imagedestroy($image);

        return $filename;
    }

    private function saveThumbnail($source, string $targetPath, int $maxWidth): void
    {
        $width = \imagesx($source);
        $height = \imagesy($source);

        if ($width <= $maxWidth) {
            if (! \imagejpeg($source, $targetPath, 85)) {
                throw new \RuntimeException('Unable to write plant photo thumbnail.');
            }

            return;
        }

        $newWidth = $maxWidth;
        $newHeight = (int) round($height * ($newWidth / $width));
        $thumbnail = \imagecreatetruecolor($newWidth, $newHeight);

        \imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        if (! \imagejpeg($thumbnail, $targetPath, 85)) {
            \imagedestroy($thumbnail);
            throw new \RuntimeException('Unable to write plant photo thumbnail.');
        }
        \imagedestroy($thumbnail);
    }

    private function filterPhotoColumns(array $data): array
    {
        $columns = Schema::connection('mysql_web')->getColumnListing('photo');

        return collect($data)
            ->only($columns)
            ->all();
    }

    private function filterDisNoteColumns(array $data): array
    {
        $columns = Schema::connection('mysql_web')->getColumnListing('dis_note');

        return collect($data)
            ->only($columns)
            ->all();
    }

    private function distinctOptions(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeDate(string $date): string
    {
        $date = trim($date);

        if ($date === '') {
            return '';
        }

        if (preg_match('/^(\d{4})[.\/-](\d{1,2})[.\/-](\d{1,2})$/', $date, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[1], $matches[2], $matches[3]);
        }

        return $date;
    }

    private function userName(): string
    {
        return Auth::user()?->name ?? 'system';
    }

    private function clearMessages(): void
    {
        $this->disNoteMessage = '';
        $this->disNoteMessages = [];
        $this->photoUploadMessage = '';
        $this->photoMessages = [];
    }
}
