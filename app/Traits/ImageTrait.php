<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

trait ImageTrait
{
    private $article_img_size = ['lg' => [850, 480], 'md' => [640, 360], 'sm' => [120, 80]];
    private $slider_img_size = ['lg' => [740, 420], 'md' => [620, 320], 'sm' => [380, 220]];

    function imageResizeAndSave(Request $request, $directory, $type = null)
    {
        $img = $request->img ?? $request->image;
        $img_name = pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME) . '_' . Carbon::now()->format('YmdHis');
        $filePath = storage_path() . '/app/public/' . $directory . '/';

        File::ensureDirectoryExists($filePath);

        if ($type == 'post') {
            $size = $this->article_img_size;
        } else {
            $size = $this->slider_img_size;
        }

        Image::make($img)->encode('webp', 100)->save($filePath . $img_name . '.webp', 100);
        Image::make($img)->resize($size['lg'][0], null, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('webp', 100)->save($filePath . $img_name . '-lg.webp', 100);
        Image::make($img)->resize($size['md'][0], null, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('webp', 100)->save($filePath . $img_name . '-md.webp', 100);
        Image::make($img)->resize($size['sm'][0], null, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('webp', 100)->save($filePath . $img_name . '-sm.webp', 100);

        return '/' . $directory . '/' . $img_name . '.webp';
    }

    function deleteImage($image, $directory)
    {
        $fileName = pathinfo($image, PATHINFO_FILENAME);
        Storage::delete('public/' . $image);
        Storage::delete('public/' . $directory . '/' . $fileName . '-lg.webp');
        Storage::delete('public/' . $directory . '/' . $fileName . '-md.webp');
        Storage::delete('public/' . $directory . '/' . $fileName . '-sm.webp');
    }
}
