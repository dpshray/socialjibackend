<?php

use App\Models\Logging;
use Illuminate\Support\Facades\Log;
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

if (! function_exists('logError')) {
    function logError($methodName, $requestPayload, $responsePayload, $message = 'An error occurred')
    {
        Log::error($methodName, [
            'request_payload' => $requestPayload,
            'response_payload' => $responsePayload,
        ]);

        Logging::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'level' => 'error',
            'message' => $message,
            'request_payload' => json_encode($requestPayload),
            'response_payload' => json_encode($responsePayload),
            'ip_address' => request()->ip(),
        ]);
    }
}

if (! function_exists('logInfo')) {
    function logInfo($methodName, $requestPayload, $responsePayload, $message = 'Information logged')
    {
        Log::info($methodName, [
            'request_payload' => $requestPayload,
            'response_payload' => $responsePayload,
        ]);
        $user_id = null;
        if (auth()->check()) {
            $user_id = auth()->id();
        }else if (array_key_exists('user_id', $responsePayload)) {
            $user_id = $responsePayload['user_id'];
        }
        Logging::create([
            'user_id' =>  $user_id,
            'level' => 'info',
            'message' => $message,
            'request_payload' => json_encode($requestPayload),
            'response_payload' => json_encode($responsePayload),
            'ip_address' => request()->ip(),
        ]);
    }
}
