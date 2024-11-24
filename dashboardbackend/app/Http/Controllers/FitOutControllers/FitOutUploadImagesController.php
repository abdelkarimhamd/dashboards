<?php

namespace App\Http\Controllers\FitOutControllers;

use App\Http\Controllers\Controller;
use App\Models\FoProjectImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
class FitOutUploadImagesController extends Controller
{
        // Upload images and store them
        public function uploadImages(Request $request)
        {
            // Validate request
            $request->validate([
                'projectId' => 'required|exists:fit_out_projects,id',
                'images.*' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);
    
            $uploadedImages = [];
    
            // Define the directory where images will be stored in the public folder
            $destinationPath = public_path('project_images');
    
            // Ensure the directory exists, if not create it
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
    
            // Loop through the files and store them in the public/project_images folder
            foreach ($request->file('images') as $image) {
                // Generate a unique filename for the image
                $fileName = uniqid() . '_' . trim($image->getClientOriginalName());
                
                // Move the image to the public/project_images directory
                $image->move($destinationPath, $fileName);
    
                // Store the relative path to the public folder in the database
                $relativePath = 'project_images/' . $fileName;
    
                // Store the image path in the database
                $projectImage = FoProjectImage::create([
                    'project_id' => $request->projectId,
                    'image_path' => $relativePath, // Store path relative to public folder
                ]);
    
                $uploadedImages[] = $projectImage;
            }
    
            // Return uploaded images as JSON response
            return response()->json($uploadedImages);
        }
        
        // Fetch all images for a specific project
        public function getProjectImages($projectId)
        {
            $images = FoProjectImage::where('project_id', $projectId)->get();
            return response()->json($images);
        }
    
        // Delete an image
        public function deleteImage($id)
        {
            $image = FoProjectImage::findOrFail($id);
            Storage::delete(str_replace('/storage/', 'public/', $image->image_path)); // Delete from storage
            $image->delete(); // Delete from DB
            return response()->json(['message' => 'Image deleted successfully']);
        }
}
