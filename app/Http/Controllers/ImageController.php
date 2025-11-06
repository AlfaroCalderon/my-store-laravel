<?php

namespace App\Http\Controllers;
use Cloudinary\Cloudinary;
use Exception;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function upload(Request $request){
        //validate that an image is being sent
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10240'//10MB
        ]);
        try {
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET')
                ]
            ]);

            //Upload the image to Cloudinary
            $upladedFile = $request->file('image');
            $result = $cloudinary->uploadApi()->upload($upladedFile->getRealPath());

            return response()->json([
                'status' => 'success',
                'message' => 'Image uploaded successfully',
                'data' => $result
            ], 201);

        } catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Image upload failed',
                'error' => $error->getMessage()
            ], 500);
        }
    }
}
