<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Event;
use App\Models\EventAttachment;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EventAttachmentController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        $business = $this->business($request);
        abort_unless((int) $event->business_id === (int) $business->id, 404);

        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => [
                'required',
                'file',
                'max:20480',
                'mimetypes:image/jpeg,image/png,image/webp,application/pdf,text/plain,text/csv,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $event, $business, $validated, &$storedPaths): void {
                foreach ($request->file('files', []) as $file) {
                    $path = $file->store('jobs/' . $event->id . '/attachments', 'private');
                    $storedPaths[] = $path;

                    EventAttachment::create([
                        'event_id' => $event->id,
                        'business_id' => $business->id,
                        'uploaded_by' => $request->user()->id,
                        'original_name' => $file->getClientOriginalName(),
                        'disk' => 'private',
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'source' => 'upload',
                        'description' => $validated['description'] ?? null,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('private')->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('work.show', $event)
            ->with('success', 'Files added to the job.');
    }

    public function download(Request $request, EventAttachment $attachment)
    {
        $business = $this->business($request);
        abort_unless(
            (int) $attachment->business_id === (int) $business->id
                && (int) $attachment->event?->business_id === (int) $business->id,
            404
        );

        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function destroy(Request $request, EventAttachment $attachment): RedirectResponse
    {
        $business = $this->business($request);
        abort_unless(
            (int) $attachment->business_id === (int) $business->id
                && (int) $attachment->event?->business_id === (int) $business->id,
            404
        );

        Storage::disk($attachment->disk)->delete($attachment->path);
        $event = $attachment->event;
        $attachment->delete();

        return redirect()
            ->route('work.show', $event)
            ->with('success', 'File removed from the job.');
    }

    private function business(Request $request): Business
    {
        return app(CurrentBusiness::class)->model($request->user());
    }
}