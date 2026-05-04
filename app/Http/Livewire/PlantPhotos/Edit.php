<?php

namespace App\Http\Livewire\PlantPhotos;

use App\Models\FsBaseSpinfo;
use App\Models\Web\DisNote;
use App\Models\Web\Photo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public string $spcode = '';
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
    public string $message = '';

    public function mount(string $spcode): void
    {
        $this->spcode = $spcode;
        $species = FsBaseSpinfo::query()->where('spcode', $spcode)->firstOrFail();
        $this->speciesinfo = $species->toArray();
        $this->loadOptions();
        $this->reloadDisNotes();
        $this->reloadPhotos();
    }

    public function saveDisNote(string $noteKey): void
    {
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
        $this->message = '辨識要點已儲存。';
    }

    public function createDisNote(): void
    {
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
        $this->message = '辨識要點已新增。';
    }

    public function deleteDisNote(string $noteKey): void
    {
        $this->disNoteQuery($noteKey)->delete();
        $this->reloadDisNotes();
        $this->message = '辨識要點已刪除。';
    }

    public function savePhoto(string $photoKey): void
    {
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
            "editingPhotos.{$photoKey}.photoby" => ['nullable', 'string', 'max:100'],
            "editingPhotos.{$photoKey}.des" => ['nullable', 'string', 'max:1000'],
        ]);

        $photo = $this->editingPhotos[$photoKey];

        $this->photoQuery($photoKey)
            ->update($this->filterPhotoColumns([
                'type' => $photo['type'] ?? '',
                'type2' => $photo['type2'] ?? '',
                'fresh' => $photo['fresh'] ?? '',
                'status' => $photo['status'] ?? '',
                'photo_date' => ($photo['photo_date'] ?? '') !== '' ? $photo['photo_date'] : null,
                'is_public' => (int) ($photo['is_public'] ?? 1),
                'photoby' => $photo['photoby'] ?? '',
                'des' => $photo['des'] ?? '',
                'updated_id' => $this->userName(),
                'updated_at' => now(),
            ]));

        $this->reloadPhotos();
        $this->message = '照片描述已儲存。';
    }

    public function deletePhoto(string $photoKey): void
    {
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
        $this->message = '照片已刪除。';
    }

    public function updatedNewPhotoFile(): void
    {
        if ($this->newPhotoFile) {
            $this->uploadPhoto();
        }
    }

    public function uploadPhoto(): void
    {
        $this->validate([
            'newPhotoFile' => ['required', 'image', 'max:10240'],
        ], [], [
            'newPhotoFile' => '照片檔案',
        ]);

        $filename = $this->storePlantPhoto();

        Photo::query()->create($this->filterPhotoColumns([
            'spcode' => $this->spcode,
            'filename' => $filename,
            'type' => '',
            'type2' => '',
            'fresh' => '',
            'status' => '',
            'photo_date' => null,
            'is_public' => 1,
            'photoby' => '',
            'des' => '',
            'uploaded_id' => $this->userName(),
            'uploaded_at' => now()->toDateString(),
            'updated_id' => $this->userName(),
            'updated_at' => now(),
        ]));

        $this->newPhotoFile = null;
        $this->reloadPhotos();
        $this->message = '照片已新增。';
    }

    public function render()
    {
        return view('livewire.plant-photos.edit');
    }

    private function reloadPhotos(): void
    {
        $this->editingPhotos = Photo::query()
            ->where('spcode', $this->spcode)
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
                        'filename' => $photo->filename,
                        'type' => $photo->type ?? '',
                        'type2' => $photo->type2 ?? '',
                        'fresh' => $photo->fresh ?? '',
                        'status' => $photo->status ?? '',
                        'photo_date' => $this->normalizeDate((string) ($photo->photo_date ?? '')),
                        'is_public' => (string) (int) ($photo->is_public ?? 1),
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

        $query = Photo::query()->where('spcode', $this->spcode);

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

        \imagejpeg($image, "{$directory}/{$filename}", 90);
        $this->saveThumbnail($image, "{$directory}/s_{$filename}", 500);
        \imagedestroy($image);

        return $filename;
    }

    private function saveThumbnail($source, string $targetPath, int $maxWidth): void
    {
        $width = \imagesx($source);
        $height = \imagesy($source);

        if ($width <= $maxWidth) {
            \imagejpeg($source, $targetPath, 85);
            return;
        }

        $newWidth = $maxWidth;
        $newHeight = (int) round($height * ($newWidth / $width));
        $thumbnail = \imagecreatetruecolor($newWidth, $newHeight);

        \imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        \imagejpeg($thumbnail, $targetPath, 85);
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
}
