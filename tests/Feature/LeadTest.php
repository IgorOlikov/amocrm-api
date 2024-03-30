<?php

namespace Tests\Feature;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AmoLeadService;
use Tests\TestCase;

class LeadTest extends TestCase
{
   public function test_create_lead_with_empty_field(): void
   {
       $costPriceFieldId = 265083;

       $apiClient = app()->get(AmoCRMApiClient::class);

       $name = 'test_create_lead_with_empty_field' . mt_rand(1,99999);

       $price = 2000;

       $costPrice = null;

       $createdLeadModel  = (new AmoLeadService($apiClient))->createLead($name,$price,$costPrice);

       $customFields = $createdLeadModel->getCustomFieldsValues();

       $costPriceCustomField = $customFields->getBy('fieldId', $costPriceFieldId);

       $value = $costPriceCustomField->getValues()->first();

       $value = $value->getValue();


       $this->assertNull($value);
   }

   public function test_updating_lead_budget_field_where_cost_price_filled()
   {
       //create
       $priceFieldId = 265133;

       $apiClient = app()->get(AmoCRMApiClient::class);

       $name = 'test_updating_lead_budget_field_where_cost_price_filled' . mt_rand(1,99999);

       $price = 2000;

       $costPrice = 1000;

       $createdLeadModel  = (new AmoLeadService($apiClient))->createLead($name,$price,$costPrice);

       $customFields = $createdLeadModel->getCustomFieldsValues();

       $priceCustomField = $customFields->getBy('fieldId', $priceFieldId);

       $value = $priceCustomField->getValues()->first();

       $oldPrice = $value->getValue();


        //update
       $createdLeadId = $createdLeadModel->getId();

       $newPrice = 1000;

       $updatedLeadModel = (new AmoLeadService($apiClient))->updateLead($createdLeadId,$name,$newPrice,$costPrice);

       $customFields = $updatedLeadModel->getCustomFieldsValues();

       $priceCustomField = $customFields->getBy('fieldId', $priceFieldId);

       $value = $priceCustomField->getValues()->first();

       $updatedPrice = $value->getValue();

       $this->assertNotEquals($oldPrice, $updatedPrice);
   }

   public function test_updating_lead_cost_price_field_to_value_that_greater_than_budget()
   {
       //create
       $costPriceFieldId = 265083;
       $profitFieldId = 265133;

        //create
       $apiClient = app()->get(AmoCRMApiClient::class);

       $name = 'test_updating_lead_cost_price_field_to_value_that_greater_than_budget' . mt_rand(1,99999);

       $price = 2000;

       $costPrice = 1000;

       $createdLeadModel  = (new AmoLeadService($apiClient))->createLead($name,$price,$costPrice);


       //update
       $createdLeadId = $createdLeadModel->getId();

       $newCostPrice = 5000;

       $updatedLeadModel = (new AmoLeadService($apiClient))->updateLead($createdLeadId,$name,$price,$newCostPrice);


       //fetch updated costPrice
       $customFields = $updatedLeadModel->getCustomFieldsValues();

       $costPriceCustomField = $customFields->getBy('fieldId', $costPriceFieldId);

       $value = $costPriceCustomField->getValues()->first();

       $updatedCostPrice = $value->getValue();

       //fetch price(budget)
       $price = $updatedLeadModel->getPrice();

       $this->assertTrue($updatedCostPrice > $price);
   }

   public function test_create_leads_from_file()
   {
        $apiClient = app()->get(AmoCRMApiClient::class);

        \Artisan::call('db:seed LeadsFileSeeder');

        $filePath = base_path() . '/testleads.txt';

        $jsonLeads = file_get_contents($filePath,true);

        $leadsArray = json_decode($jsonLeads,true);

        $addedLeadsCollection = (new AmoLeadService($apiClient))->createLeadsFromArray($leadsArray);

        $addedLeads = $addedLeadsCollection->toArray();

        $this->assertNotEmpty($addedLeads);
   }

}
