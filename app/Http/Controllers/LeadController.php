<?php

namespace App\Http\Controllers;
use AmoCRM\Client\AmoCRMApiClient;
use App\Http\Requests\CreateLeadRequest;
use App\Http\Requests\CreateLeadsFromFileRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Services\AmoLeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    protected AmoLeadService $leadService;

    public function __construct(protected AmoCRMApiClient $apiClient)
    {
        $this->leadService = new AmoLeadService($this->apiClient);
    }

    public function getAllLeads()
    {
       $leads = $this->leadService->getAllLeads();

       return response($leads->toArray());
    }

    public function createLead(CreateLeadRequest $request)
    {
        $lead = $this->leadService
            ->createLead(
                $request->validated('name'),
                $request->validated('price'),
                $request->validated('cost_price')
            );

        return response($lead->toArray(),201);
    }

    public function createLeadsFromFile(CreateLeadsFromFileRequest $request)
    {
        $file = $request->validated('file');

        $jsonLeads = $file->get();

        $leadsArray = json_decode($jsonLeads,true);

        $createdLeads = $this->leadService->createLeadsFromArray($leadsArray);

        return response($createdLeads->toArray(),201);
    }

    public function updateLead(UpdateLeadRequest $request,int $leadId)
    {
        $updatedLead = $this->leadService
            ->updateLead(
                $leadId,
                $request->validated('name'),
                $request->validated('price'),
                $request->validated('cost_price')
            );

        return response($updatedLead->toArray(),200);
    }
}
