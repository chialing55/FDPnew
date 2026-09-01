<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        $sourceDirectory = public_path('FDPfiles/splist/leafphoto');

        if (! File::isDirectory($sourceDirectory)) {
            return;
        }

        $aliases = ['南領灰木' => '南嶺灰木', '白臼' => '白桕'];
        $speciesByName = DB::connection('plant_catalog')->table('site_species')
            ->where('site', 'fushan')->whereNotNull('code')->where('code', '<>', '')
            ->get(['csp', 'spcode', 'code'])->keyBy('csp');

        foreach (File::files($sourceDirectory) as $source) {
            $photoName = pathinfo($source->getFilename(), PATHINFO_FILENAME);
            $species = $speciesByName->get($aliases[$photoName] ?? $photoName);

            if (! $species) {
                continue;
            }

            $filename = 'leaf_'.$species->spcode.'.jpg';
            $alreadyImported = DB::connection('mysql_web')->table('photo')
                ->where('site', 'fushan')
                ->where('spcode', $species->spcode)
                ->where('filename', $filename)
                ->exists();

            if ($alreadyImported) {
                continue;
            }

            $targetDirectory = public_path('FDPfiles/splist/photo/'.$species->spcode);
            File::ensureDirectoryExists($targetDirectory);
            $image = @imagecreatefromstring(File::get($source->getPathname()));

            if (! $image) {
                continue;
            }

            imagejpeg($image, $targetDirectory.'/'.$filename, 90);
            $this->writeThumbnail($image, $targetDirectory.'/s_'.$filename);
            imagedestroy($image);

            DB::connection('mysql_web')->transaction(function () use ($species, $filename): void {
                DB::connection('mysql_web')->table('photo')
                    ->where('code', $species->code)->update(['is_featured' => 0]);
                DB::connection('mysql_web')->table('photo')->updateOrInsert(
                    ['site' => 'fushan', 'spcode' => $species->spcode, 'filename' => $filename],
                    [
                        'code' => $species->code, 'type' => '葉片', 'type2' => '',
                        'photoby' => '', 'fresh' => '', 'status' => '', 'photo_date' => null,
                        'is_public' => 1, 'is_featured' => 1,
                        'des' => '福山植物名錄葉片照片', 'uploaded_id' => 'legacy-import',
                        'uploaded_at' => now()->toDateString(), 'updated_id' => 'legacy-import',
                        'updated_at' => now(),
                    ]
                );
            });
        }
    }

    public function down(): void
    {
        // Keep imported records and files; rollback must not remove photographs.
    }

    private function writeThumbnail($source, string $target): void
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $targetWidth = min(500, $width);
        $targetHeight = (int) round($height * ($targetWidth / $width));
        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagejpeg($thumbnail, $target, 85);
        imagedestroy($thumbnail);
    }
};
