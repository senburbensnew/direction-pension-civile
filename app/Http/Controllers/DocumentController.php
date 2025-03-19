<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        return view('admin.documents.upload');
    }

    public function upload(Request $request)
    {
        // Define custom error messages
        $messages = [
            'pdf_file.mimes' => 'Le fichier doit être un document PDF.',
            'pdf_file.max' => 'Le fichier ne doit pas dépasser 2MB.',
        ];
    
        // Validate input
        $request->validate([
            'pdf_name' => 'required|string',
            'pdf_file' => 'required|file|mimes:pdf|max:2048', // Validate PDF file type
        ], $messages); // Use custom messages

        // If validation passes
        $pdfName = $request->input('pdf_name');
        $pdfFile = $request->file('pdf_file');

        // Define the path where the file will be stored in the 'public/documents' directory
        $destinationPath = public_path('documents');  // public/documents

        // Ensure the 'documents' directory exists, create it if not
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        // Define the full path to the file
        $filePath = $destinationPath . '/' . $pdfName . '.pdf';

        // If the file exists, delete it before moving the new file
        if (file_exists($filePath)) {
            unlink($filePath);  // Delete the existing file
        }

        // Store the new file (this will overwrite the old one if it was deleted)
        $pdfFile->move($destinationPath, $pdfName . '.pdf');
    
        // Return success message
        return back()->with('success', 'Le document a été téléversé avec succès.');
    }    
}

