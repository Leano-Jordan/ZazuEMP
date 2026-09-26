<?php

namespace App\Http\Controllers;

use App\Models\ComplianceDocument;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplianceDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $business = app(CurrentBusiness::class)->model($request->user());
        $business->loadMissing('taxProfile');

        $documents = $business->relationLoaded('complianceDocuments')
            ? $business->complianceDocuments()->latest()->get()
            : ComplianceDocument::where('business_id', $business->id)->latest()->get();

        $flags = $business->taxProfile?->activity_flags ?? [];

        $checklist = [
            ['type' => 'cipc_registration', 'label' => 'Business registration proof', 'when' => true, 'reason' => 'Core identity evidence for a registered entity.'],
            ['type' => 'cipc_annual_return', 'label' => 'CIPC annual return proof', 'when' => ($business->taxProfile?->registration_type ?? '') !== 'sole_proprietor', 'reason' => 'Companies and close corporations must maintain annual returns.'],
            ['type' => 'cipc_beneficial_ownership', 'label' => 'Beneficial ownership records', 'when' => ($business->taxProfile?->registration_type ?? '') !== 'sole_proprietor', 'reason' => 'Keep the current CIPC beneficial ownership evidence where applicable.'],
            ['type' => 'sars_income_tax', 'label' => 'SARS income tax evidence', 'when' => true, 'reason' => 'Keep the business tax reference/registration evidence available.'],
            ['type' => 'vat_certificate', 'label' => 'VAT registration certificate', 'when' => ($business->taxProfile?->vat_status ?? '') === 'registered', 'reason' => 'Needed when the business represents itself as VAT registered.'],
            ['type' => 'sars_tcs_good_standing', 'label' => 'SARS TCS Good Standing', 'when' => !empty($flags['government_supply']) || !empty($flags['tendering']), 'reason' => 'Common government/tender compliance evidence; TCS Tender was replaced by Good Standing.'],
            ['type' => 'csd_registration', 'label' => 'CSD registration/report', 'when' => !empty($flags['government_supply']) || !empty($flags['tendering']), 'reason' => 'Government supplier information is centralised in the CSD.'],
            ['type' => 'bbbEE', 'label' => 'B-BBEE evidence', 'when' => !empty($flags['government_supply']) || !empty($flags['tendering']), 'reason' => 'Store the certificate or affidavit when a procurement process requires it.'],
            ['type' => 'bank_confirmation', 'label' => 'Bank confirmation', 'when' => !empty($flags['government_supply']) || !empty($flags['tendering']), 'reason' => 'Frequently requested in supplier onboarding and tender packs.'],
            ['type' => 'uif_registration', 'label' => 'UIF registration evidence', 'when' => !empty($flags['employees']), 'reason' => 'Employer compliance where UIF obligations apply.'],
            ['type' => 'sdl_registration', 'label' => 'SDL evidence', 'when' => !empty($flags['employees']), 'reason' => 'Store evidence where the business is liable for SDL.'],
            ['type' => 'compensation_fund', 'label' => 'Compensation Fund / COID evidence', 'when' => !empty($flags['employees']), 'reason' => 'Employer registration is required where the Compensation Fund regime applies.'],
            ['type' => 'food_certificate', 'label' => 'Certificate of Acceptability', 'when' => !empty($flags['food_handling']), 'reason' => 'Food handling premises require a valid certificate under R638, subject to the applicable local process.'],
            ['type' => 'business_licence', 'label' => 'Municipal business licence', 'when' => !empty($flags['food_handling']), 'reason' => 'Licensing can apply to meal/perishable-food businesses and depends on the local authority.'],
            ['type' => 'insurance', 'label' => 'Insurance / public liability', 'when' => !empty($flags['tendering']), 'reason' => 'Keep cover evidence where the client or tender requires it.'],
            ['type' => 'tender_forms', 'label' => 'Tender-specific declarations', 'when' => !empty($flags['tendering']), 'reason' => 'Tender packs can require MBD and other declarations that vary by opportunity.'],
            ['type' => 'proof_of_address', 'label' => 'Business proof of address', 'when' => true, 'reason' => 'Frequently needed for registrations, supplier onboarding and licensing.'],
            ['type' => 'owner_id', 'label' => 'Owner/director/member ID evidence', 'when' => true, 'reason' => 'Keep identity evidence only where the process legitimately requires it.'],
        ];

        return view('settings.compliance', compact('business', 'documents', 'checklist'));
    }

    public function pack(Request $request): View
    {
        $business = app(CurrentBusiness::class)->model($request->user());
        $business->loadMissing('taxProfile');

        $documents = ComplianceDocument::where('business_id', $business->id)
            ->latest()
            ->get();

        return view('settings.compliance-pack', [
            'business' => $business,
            'documents' => $documents,
            'generatedAt' => now(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = app(CurrentBusiness::class)->model($request->user());

        $validated = $request->validate([
            'document_type' => ['required', Rule::in(array_keys(config('zazu.compliance.document_types')))],
            'title' => ['required', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', 'in:current,pending,expired,not_applicable'],
            'source_reference' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string'],
            'verified' => ['nullable', 'boolean'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $path = null;

        try {
            if ($request->hasFile('document')) {
                $path = $request->file('document')->store('business-compliance/'.$business->id, 'local');
            }

            ComplianceDocument::create([
                'business_id' => $business->id,
                'document_type' => $validated['document_type'],
                'title' => trim($validated['title']),
                'reference_number' => $validated['reference_number'] ?? null,
                'issue_date' => $validated['issue_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'status' => $validated['status'],
                'storage_path' => $path,
                'source_reference' => $validated['source_reference'] ?? null,
                'verified_at' => $request->boolean('verified') ? now() : null,
                'notes' => $validated['notes'] ?? null,
            ]);
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $e;
        }

        return redirect()->route('settings.compliance')->with('success', 'Compliance document recorded.');
    }

    public function download(Request $request, ComplianceDocument $document)
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        abort_unless((int) $document->business_id === $businessId, 404);
        abort_unless($document->storage_path && Storage::disk('local')->exists($document->storage_path), 404);

        return Storage::disk('local')->download($document->storage_path, basename($document->storage_path));
    }

    public function destroy(Request $request, ComplianceDocument $document): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        abort_unless((int) $document->business_id === $businessId, 404);

        if ($document->storage_path) {
            Storage::disk('local')->delete($document->storage_path);
        }

        $document->delete();

        return redirect()->route('settings.compliance')->with('success', 'Compliance document removed.');
    }
}