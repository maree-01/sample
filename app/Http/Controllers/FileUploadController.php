<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\View;


class FileUploadController extends Controller
{
    public function showUploadForm()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');

        $bucketName = env('AWS_BUCKET');
        $accessKeyId = env('AWS_ACCESS_KEY_ID');
        $secretAccessKey = env('AWS_SECRET_ACCESS_KEY');
        $region = env('AWS_DEFAULT_REGION');

        if (!$bucketName || !$accessKeyId || !$secretAccessKey || !$region) {
            return redirect()->back()->with('error', 'AWS credentials or bucket name is missing in configuration.');
        }

        try {
            // Create S3 client
            $s3 = new S3Client([
                'version'     => 'latest',
                'region'      => $region,
                'credentials' => [
                    'key'    => $accessKeyId,
                    'secret' => $secretAccessKey,
                ],
            ]);

            // Upload the file to S3
            $s3->putObject([
                'Bucket' => $bucketName, // Set the bucket name
                'Key'    => $file->getClientOriginalName(),
                'Body'   => fopen($file->getRealPath(), 'r'),
                'ACL'    => 'public-read', // Make uploaded file publicly accessible
            ]);
            
            // Redirect back with success message if successful
            return redirect()->back()->with('success', 'File uploaded successfully.');
        } catch (AwsException $e) {
            // Redirect back with error message if an error occurs
            return redirect()->back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'file_key' => 'required|string', // Assuming 'file_key' is the key of the file to delete
        ]);

        $fileKey = $request->input('file_key');

        $bucketName = env('AWS_BUCKET');
        $accessKeyId = env('AWS_ACCESS_KEY_ID');
        $secretAccessKey = env('AWS_SECRET_ACCESS_KEY');
        $region = env('AWS_DEFAULT_REGION');

        if (!$bucketName || !$accessKeyId || !$secretAccessKey || !$region) {
            return redirect()->back()->with('error', 'AWS credentials or bucket name is missing in configuration.');
        }

        try {
            // Create S3 client
            $s3 = new S3Client([
                'version'     => 'latest',
                'region'      => $region,
                'credentials' => [
                    'key'    => $accessKeyId,
                    'secret' => $secretAccessKey,
                ],
            ]);

            // Delete the file from S3
            $s3->deleteObject([
                'Bucket' => $bucketName, // Set the bucket name
                'Key'    => $fileKey, // Specify the key of the file to delete
            ]);
            
            // Redirect back with success message if successful
            return redirect()->back()->with('success', 'File deleted successfully.');
        } catch (AwsException $e) {
            // Redirect back with error message if an error occurs
            return redirect()->back()->with('error', 'Error deleting file: ' . $e->getMessage());
        }
    }

    public function showDeleteForm()
    {
        $bucketName = env('AWS_BUCKET');
        $accessKeyId = env('AWS_ACCESS_KEY_ID');
        $secretAccessKey = env('AWS_SECRET_ACCESS_KEY');
        $region = env('AWS_DEFAULT_REGION');

        if (!$bucketName || !$accessKeyId || !$secretAccessKey || !$region) {
            return redirect()->back()->with('error', 'AWS credentials or bucket name is missing in configuration.');
        }

        try {
            // Create S3 client
            $s3 = new S3Client([
                'version'     => 'latest',
                'region'      => $region,
                'credentials' => [
                    'key'    => $accessKeyId,
                    'secret' => $secretAccessKey,
                ],
            ]);

            // Retrieve the list of objects (files) from the S3 bucket
            $objects = $s3->listObjectsV2([
                'Bucket' => $bucketName,
            ]);

            $fileKeys = [];
            foreach ($objects['Contents'] as $object) {
                // Add each object key (file key) to the fileKeys array
                $fileKeys[] = $object['Key'];
            }

            // Pass the fileKeys array to the view
            return view('delete', ['fileKeys' => $fileKeys]);
        } catch (AwsException $e) {
            // Redirect back with error message if an error occurs
            return redirect()->back()->with('error', 'Error retrieving file list: ' . $e->getMessage());
        }
    }

    public function showFileList()
    {
        $bucketName = env('AWS_BUCKET');
        $accessKeyId = env('AWS_ACCESS_KEY_ID');
        $secretAccessKey = env('AWS_SECRET_ACCESS_KEY');
        $region = env('AWS_DEFAULT_REGION');

        if (!$bucketName || !$accessKeyId || !$secretAccessKey || !$region) {
            return redirect()->back()->with('error', 'AWS credentials or bucket name is missing in configuration.');
        }

        try {
            // Create S3 client
            $s3 = new S3Client([
                'version'     => 'latest',
                'region'      => $region,
                'credentials' => [
                    'key'    => $accessKeyId,
                    'secret' => $secretAccessKey,
                ],
            ]);

            // Retrieve objects (files) from the bucket
            $objects = $s3->listObjectsV2([
                'Bucket' => $bucketName,
            ]);

            // Extract the keys of the objects
            $fileKeys = collect($objects['Contents'])->pluck('Key');

            // Pass the file keys to the view
            return View::make('file_list')->with('fileKeys', $fileKeys);
        } catch (AwsException $e) {
            // Redirect back with error message if an error occurs
            return redirect()->back()->with('error', 'Error retrieving file list: ' . $e->getMessage());
        }
    }


}
