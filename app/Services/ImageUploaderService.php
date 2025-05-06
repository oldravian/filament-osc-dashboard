<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use App\Models\Project;

class ImageUploaderService
{
    private $fileName;
    public $uploadError;
    private $fileSize;
    public $errorMessage;

    /**
     * Generate an unique name for storing file
     *
     * @param Illuminate\Http\UploadedFile|string $uploadedFile
     *
     * @return string
     */
    protected function getUniqueName($uploadedFile, $source="url")
    {
        if($source == "url"){
            $originName = $this->getOriginName($uploadedFile);
            $uniqueString = uniqid(rand(), true) . "_" . $originName . "_" . getmypid() . "_" . gethostname() . "_" . time();
            return md5($uniqueString) . "." . $this->getExtension($uploadedFile);
        }
        else{
            //form src
            $fileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = $fileName . "-" . uniqid(rand(9, 999)) . "." . $this->getExtension($uploadedFile);
            return $fileName;
        }
    }

    /**
     * Return the file extension as extracted from the origin file name
     *
     * @param Illuminate\Http\UploadedFile|string $uploadedFile
     *
     * @return string
     */
    protected function getExtension($uploadedFile)
    {
        $name = substr($uploadedFile, strrpos($uploadedFile, '/') + 1);
        $this->fileName = pathinfo($name, PATHINFO_FILENAME);
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        // example: png?raw=true
        if (strpos($ext, "?") !== false) {
            $ext = substr($ext, 0, strpos($ext, "?"));
        }

        if (empty($ext)) {
            $ext = "png";
        }
        return $ext;
    }


    /**
     * Return the original filename
     *
     * @param Illuminate\Http\UploadedFile|string $uploadedFile
     *
     * @return string
     */
    protected function getOriginName($uploadedFile)
    {
        return $this->fileName;
    }

    protected function isValid($uploadedFile)
    {
        //https://stackoverflow.com/a/52368686/10029265
        $ch = curl_init($uploadedFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        $data = curl_exec($ch);
        $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $info =  [
            'fileExists' => (int) $httpResponseCode == 200,
            'fileSize' => (int) $fileSize
        ];
        $this->fileSize = $info['fileSize'];

        if ($info['fileExists'] == false) {
            $this->uploadError = "File doesn't exist on given url";
        }
        return $info['fileExists'];
    }



    /**
     * Physically store the  uploaded file
     *
     * @param Illuminate\Http\UploadedFile|string $uploadedFile
     * @param array $settings
     *
     * @return array|boolean
     */
    public function storeFile($uploadedFile, $settings, $upload_with_name = null, $source="url")
    {
        if($source=="form"){
            if (!$uploadedFile->isValid()) {
                $this->errorMessage = $uploadedFile->getErrorMessage();
                return false;
            }
        }
        else if($source=="url"){
            if (!$this->isValid($uploadedFile)) {
                return false;
            }
        }
        

        if ($upload_with_name) {
            $name = $upload_with_name . "." . $this->getExtension($uploadedFile);
        } else {
            $name = $this->getUniqueName($uploadedFile, $source);
        }
        $storeLocation = $settings['directory'] . DIRECTORY_SEPARATOR . $name;

        $optimizerChain = OptimizerChainFactory::create();
        
        if($source=="form"){
            $uploadedFile->storeAs($settings['directory'], $name, $settings['disk']);
            $optimizerChain->optimize(Storage::disk($settings['disk'])->path($storeLocation));
        }
        else if($source=="url"){
            Storage::disk($settings['disk'])->put($storeLocation, file_get_contents($uploadedFile));
            $optimizerChain->optimize(Storage::disk($settings['disk'])->path($storeLocation));            
        }

        return [
            'filename' => $name,
            'path' => $storeLocation,
        ];
    }

    public function deleteFile($disk, $path)
    {
        return Storage::disk($disk)->delete($path);
    }

    public function generateThumbnailForSingleProject($project){

        $thumbnailsDir = public_path("projects-storage/thumbnails");
        $path = public_path("projects-storage/".$project->primaryMedia->path);
        
        // Open the image using Intervention
        $image = \Intervention\Image\ImageManagerStatic::make($path);

        // Generate thumbnail
        $thumbnail = $image->resize(600, 300);

        $filename = basename($path);
        $thumbnailPath = $thumbnailsDir . '/' . $filename;
        // Save the thumbnail
        $thumbnail->save($thumbnailPath);

        $actualSize=filesize($path) / 1024;
        $thumbnailSize=filesize($thumbnailPath) / 1024;

        // Compare sizes, if thumbnail is larger, replace with original
        if ($thumbnailSize > $actualSize) {
            unlink($thumbnailPath); // Delete the larger thumbnail
            copy($path, $thumbnailPath); // Copy the original image as thumbnail
        }

        $project->thumbnail = $filename;
        $project->save();
        return [$actualSize, $path, $thumbnailSize, $thumbnailPath];
    }
}