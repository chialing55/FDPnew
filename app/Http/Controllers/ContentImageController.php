<?php
namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class ContentImageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:4'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:12288'],
        ]);
        $directory = 'content-images/' . now()->format('Y/m');
        $files = [];
        foreach ($validated['images'] as $image) {
            $filename = Str::uuid() . '.' . $image->guessExtension();
            Storage::disk('public')->putFileAs($directory, $image, $filename);
            $files[] = $filename;
        }
        $baseUrl = '/storage/' . $directory . '/';
        return response()->json(['success' => true, 'data' => ['baseurl' => $baseUrl, 'files' => $files, 'isImages' => array_fill(0, count($files), true), 'messages' => []]]);
    }
}
