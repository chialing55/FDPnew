<?php

namespace App\Http\Livewire\Fushan;

use App\Models\FsWebUnk;
use App\Models\FsWebUnkPhoto;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\WithFileUploads;

//unk種子資訊
class SeedsUnknown extends Component
{
    use WithFileUploads;

    public $unk = '%';
    public $unkdes = [];
    public $unkphoto = [];
    public $unklist = [];
    public $user;

    public $editorOpen = false;
    public $editorMode = 'edit';
    public $editingUnkName = '';
    public $editingUnkDes = '';
    public $editingPhotos = [];
    public $newUnkName = '';
    public $newUnkDes = '';
    public $newPhotoCode = '1';
    public $newPhotoCensusTime = '';
    public $newPhotoPhotoby = '';
    public $newPhotoDes = '';
    public $newPhotoFile;
    public $editorMessage = '';

    public $codelist = [
        '1' => '果',
        '2' => '種子',
        '3' => '附屬物',
        '4' => '碎片',
        '5' => '未熟果',
        '6' => '花',
    ];

    public function mount($user = null, $site = null): void
    {
        $this->user = $user;
        $request = request();

        $unkParam = $request->hasSession()
            ? $request->session()->get('unk', 'no')
            : 'no';

        if ($unkParam != 'no') {
            $this->unk = $unkParam;
            $request->session()->forget('unk');
        }

        $this->reloadData();
    }

    public function openData($url, $unk){
        $request = request();
        $request->session()->put('unk', $unk);

        return redirect()->to($url);
    }

    public function search(): void
    {
        $this->resetEditorState();
        $this->reloadData();
    }

    public function openCreateUnknown(): void
    {
        $this->resetEditorState();
        $this->editorMode = 'create';
        $this->newUnkName = $this->suggestNextUnkName();
        $this->editorOpen = true;
        $this->dispatch('unknown-editor-opened');
    }

    public function openEditor(string $unkname): void
    {
        $this->resetEditorState();

        $unk = FsWebUnk::query()->where('unkname', $unkname)->first();

        if (!$unk) {
            $this->editorMessage = "找不到 {$unkname}。";
            return;
        }

        $this->editorMode = 'edit';
        $this->editingUnkName = $unk->unkname;
        $this->editingUnkDes = $unk->des ?? '';
        $this->editingPhotos = FsWebUnkPhoto::query()
            ->where('unkname', $unkname)
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function ($photo) {
                return [
                    $photo->id => [
                        'id' => $photo->id,
                        'code' => (string) $photo->code,
                        'censustime' => $this->normalizePhotoDate($photo->censustime ?? ''),
                        'filename' => $photo->filename,
                        'des' => $photo->des ?? '',
                        'photoby' => $photo->photoby ?? '',
                    ],
                ];
            })
            ->toArray();

        $this->editorOpen = true;
        $this->dispatch('unknown-editor-opened');
    }

    public function closeEditor(): void
    {
        $this->resetEditorState();
    }

    public function createUnknown(): void
    {
        $this->validate([
            'newUnkName' => ['required', 'string', 'max:20'],
            'newUnkDes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'newUnkName' => 'UNKNOWN 編號',
            'newUnkDes' => '物種描述',
        ]);

        $name = strtoupper(trim($this->newUnkName));

        if (FsWebUnk::query()->where('unkname', $name)->exists()) {
            $this->addError('newUnkName', "{$name} 已存在。");
            return;
        }

        FsWebUnk::query()->create([
            'unkname' => $name,
            'des' => $this->newUnkDes ?? '',
            'updated_id' => $this->userName(),
            'updated_at' => now(),
        ]);

        $this->unk = $name;
        $this->reloadData();
        $this->openEditor($name);
        $this->editorMessage = "{$name} 已新增。";
    }

    public function saveUnknownDescription(): void
    {
        $this->validate([
            'editingUnkDes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'editingUnkDes' => '物種描述',
        ]);

        FsWebUnk::query()
            ->where('unkname', $this->editingUnkName)
            ->update([
                'des' => $this->editingUnkDes ?? '',
                'updated_id' => $this->userName(),
                'updated_at' => now(),
            ]);

        $this->reloadData();
        $this->editorMessage = "{$this->editingUnkName} 描述已儲存。";
    }

    public function savePhoto(int $photoId): void
    {
        if (!isset($this->editingPhotos[$photoId])) {
            return;
        }

        $this->validate([
            "editingPhotos.{$photoId}.code" => ['required', 'in:' . implode(',', array_keys($this->codelist))],
            "editingPhotos.{$photoId}.censustime" => ['nullable', 'date_format:Y-m-d'],
            "editingPhotos.{$photoId}.photoby" => ['nullable', 'string', 'max:100'],
            "editingPhotos.{$photoId}.des" => ['nullable', 'string', 'max:1000'],
        ]);

        $photo = $this->editingPhotos[$photoId];

        FsWebUnkPhoto::query()
            ->where('id', $photoId)
            ->update([
                'code' => $photo['code'],
                'censustime' => $photo['censustime'] ?? '',
                'photoby' => $photo['photoby'] ?? '',
                'des' => $photo['des'] ?? '',
                'updated_id' => $this->userName(),
                'updated_at' => now(),
            ]);

        $currentUnk = $this->editingUnkName;
        $this->reloadData();
        $this->openEditor($currentUnk);
        $this->editorMessage = '照片描述已儲存。';
    }

    public function deletePhoto(int $photoId): void
    {
        $photo = FsWebUnkPhoto::query()
            ->where('id', $photoId)
            ->where('unkname', $this->editingUnkName)
            ->first();

        if (!$photo) {
            return;
        }

        $directory = public_path("FDPfiles/splist/photo/unknown/{$photo->unkname}");
        File::delete([
            "{$directory}/{$photo->filename}",
            "{$directory}/s_{$photo->filename}",
        ]);

        $photo->delete();

        $currentUnk = $this->editingUnkName;
        $this->reloadData();
        $this->openEditor($currentUnk);
        $this->editorMessage = '照片已刪除。';
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

        $filename = $this->storeUnknownPhoto($this->editingUnkName);

        FsWebUnkPhoto::query()->create([
            'unkname' => $this->editingUnkName,
            'code' => '1',
            'censustime' => '',
            'filename' => $filename,
            'des' => '',
            'photoby' => '',
            'uploaded_id' => $this->userName(),
            'uploaded_at' => now()->toDateString(),
            'updated_id' => $this->userName(),
            'updated_at' => now(),
        ]);

        $currentUnk = $this->editingUnkName;
        $this->newPhotoCode = '1';
        $this->newPhotoCensusTime = '';
        $this->newPhotoPhotoby = '';
        $this->newPhotoDes = '';
        $this->newPhotoFile = null;

        $this->reloadData();
        $this->openEditor($currentUnk);
        $this->editorMessage = '照片已新增。';
    }

    public function render()
    {
        return view('livewire.fushan.seeds-unknown');
    }

    private function reloadData(): void
    {
        $this->unklist = FsWebUnk::query()
            ->orderBy('unkname')
            ->pluck('unkname')
            ->toArray();

        $this->unkdes = FsWebUnk::query()
            ->where('unkname', 'like', $this->unk)
            ->orderBy('unkname')
            ->get()
            ->toArray();

        $this->unkphoto = [];
        FsWebUnkPhoto::query()
            ->orderBy('unkname')
            ->orderBy('id')
            ->get()
            ->each(function ($photo) {
                $this->unkphoto[$photo->unkname][] = $photo->toArray();
            });
    }

    private function resetEditorState(): void
    {
        $this->editorOpen = false;
        $this->editorMode = 'edit';
        $this->editingUnkName = '';
        $this->editingUnkDes = '';
        $this->editingPhotos = [];
        $this->newUnkName = '';
        $this->newUnkDes = '';
        $this->newPhotoCode = '1';
        $this->newPhotoCensusTime = '';
        $this->newPhotoPhotoby = '';
        $this->newPhotoDes = '';
        $this->newPhotoFile = null;
        $this->editorMessage = '';
        $this->resetErrorBag();
    }

    private function suggestNextUnkName(): string
    {
        $max = collect($this->unklist)
            ->map(function ($name) {
                return preg_match('/^UNK(\d+)$/', $name, $matches) ? (int) $matches[1] : null;
            })
            ->filter()
            ->max() ?? 0;

        return 'UNK' . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    private function storeUnknownPhoto(string $unkname): string
    {
        $directory = public_path("FDPfiles/splist/photo/unknown/{$unkname}");
        File::ensureDirectoryExists($directory);

        $filename = sprintf('fs_%s_%05d.jpg', $unkname, random_int(0, 99999));

        while (File::exists("{$directory}/{$filename}")) {
            $filename = sprintf('fs_%s_%05d.jpg', $unkname, random_int(0, 99999));
        }

        $image = imagecreatefromstring(file_get_contents($this->newPhotoFile->getRealPath()));

        if (!$image) {
            $this->addError('newPhotoFile', '無法讀取這張照片。');
            throw new \RuntimeException('Unable to read uploaded unknown photo.');
        }

        imagejpeg($image, "{$directory}/{$filename}", 90);
        $this->saveThumbnail($image, "{$directory}/s_{$filename}", 500);
        imagedestroy($image);

        return $filename;
    }

    private function saveThumbnail($source, string $targetPath, int $maxWidth): void
    {
        $width = imagesx($source);
        $height = imagesy($source);

        if ($width <= $maxWidth) {
            imagejpeg($source, $targetPath, 85);
            return;
        }

        $newWidth = $maxWidth;
        $newHeight = (int) round($height * ($newWidth / $width));
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($thumbnail, $targetPath, 85);
        imagedestroy($thumbnail);
    }

    private function userName(): string
    {
        return $this->user ?: (auth()->user()?->name ?? 'system');
    }

    private function normalizePhotoDate(string $date): string
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
}
