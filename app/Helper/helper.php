<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('uploadFile')) {
    function uploadFile($file, $path, $oldFile = null, $disk = 'public')
    {
        if (isset($oldFile)) {
            deleteFile($oldFile);
        }

        $fileName = uniqid().'_'.$file->getClientOriginalName();

        Storage::disk($disk)->putFileAs(
            $path,
            $file,
            $fileName,
        );

        return $path.$fileName;
    }
}

if (! function_exists('deleteFile')) {
    function deleteFile($filePath, $disk = 'public')
    {
        if ($disk == 'public') {
            if (file_exists('storage/'.$filePath)) {
                Storage::disk($disk)->delete($filePath);

                // unlink('storage/' . $filePath);
                return true;
            }
        }

        if ($disk == 'local') {
            if (file_exists(base_path('/storage/app/'.$filePath))) {
                Storage::disk($disk)->delete($filePath);

                return true;

            }
        }

        return false;
    }
}
