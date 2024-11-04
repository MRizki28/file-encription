<?php

namespace App\Http\Controllers;

use App\Models\FileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function uploadFileWithEncription(Request $request)
    {

        try {
            $validation = Validator::make($request->all(), [
                'file_name' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,png|max:2048',
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'message' => $validation->errors()->first()
                ], 400);
            }

            $data = new FileModel();
            if ($request->file('file_name')) {
                $file = $request->file('file_name');
                $fileContent = file_get_contents($file);
                $encrypted = Crypt::encrypt($fileContent);
                $filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
                file_put_contents(public_path('uploads/file/' . $filename), $encrypted);
                $data->file_name = $filename;
            }
            $data->save();

            return response()->json([
                'message' => 'File uploaded successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function downloadFileWithDecription($id)
    {
        try {
            $data = FileModel::find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Data not found'
                ], 404);
            }

            $file = public_path('uploads/file/' . $data->file_name);
            $fileContent = file_get_contents($file);
            $decrypted = Crypt::decrypt($fileContent);

            $extension = pathinfo($data->file_name, PATHINFO_EXTENSION);
            $mimeType = match ($extension) {
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'txt' => 'text/plain',
                'png' => 'image/png',
                default => 'application/octet-stream',
            };

            $url = 'data:' . $mimeType . ';base64,' . base64_encode($decrypted);

            return response()->json([
                'file' => $url
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
