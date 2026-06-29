<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Detainee;
use App\Models\Document;
use App\Services\AuditService;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Detainee $detainee)
    {
        $documents = $detainee->documents()->with('uploadedByUser')->latest()->get();

        return view('detainees.show', compact('detainee', 'documents'));
    }

    public function store(StoreDocumentRequest $request, Detainee $detainee)
    {
        $file = $request->file('file');
        $path = $file->store("documents/{$detainee->id}", 'local');

        Document::create([
            'detainee_id' => $detainee->id,
            'file_path' => $path,
            'doc_type' => $request->input('doc_type'),
            'phase_number' => $request->input('phase_number'),
            'uploaded_by' => $request->user()->id,
            'uploaded_at' => now(),
        ]);

        AuditService::log(
            'document_uploaded',
            "Document ({$request->input('doc_type')}) uploaded for detainee {$detainee->full_name}",
            $detainee->id
        );

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Detainee $detainee, Document $document)
    {
        if ($document->detainee_id !== $detainee->id) {
            abort(404);
        }

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        AuditService::log(
            'document_deleted',
            "Document ({$document->doc_type}) deleted for detainee {$detainee->full_name}",
            $detainee->id
        );

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    public function show(Detainee $detainee, Document $document)
    {
        if ($document->detainee_id !== $detainee->id) {
            abort(404);
        }

        return Storage::disk('local')->download($document->file_path);
    }
}
