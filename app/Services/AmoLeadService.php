<?php

namespace App\Services;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\EntitiesServices\Leads;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;


class AmoLeadService
{

    const COST_PRICE_CUSTOM_FIELD_ID = 265083;

    const PROFIT_CUSTOM_FIELD_ID = 265133;

    const COST_PRICE_CUSTOM_FIELD_NAME = 'Себестоимость';

    const PROFIT_CUSTOM_FIELD_NAME = 'Прибыль';

    protected Leads $leadService;

    public function __construct(
        protected AmoCRMApiClient $apiClient
    )
    {
       $this->leadService = $this->apiClient->leads();
    }

    public function getAllLeads(): LeadsCollection
    {
        return $this->leadService->get();
    }

    public function createLead(string $leadName, int $price = null, int $costPrice = null): LeadModel
    {
        $profit = $this->calculateLeadProfit($price, $costPrice);

        $leadService = $this->leadService;

        $leadModel = new LeadModel();

        $leadModel->setName($leadName);
        $leadModel->setPrice($price);

        $leadCustomFieldsValuesCollection = new CustomFieldsValuesCollection();

        $costPriceModel = $this->setCustomNumericField(
            $costPrice,
            self::COST_PRICE_CUSTOM_FIELD_NAME,
            self::COST_PRICE_CUSTOM_FIELD_ID
        );

        $profitModel    = $this->setCustomNumericField(
            $profit,
            self::PROFIT_CUSTOM_FIELD_NAME,
            self::PROFIT_CUSTOM_FIELD_ID
        );

        $leadCustomFieldsValuesCollection
            ->add($costPriceModel)
            ->add($profitModel);

        $leadModel->setCustomFieldsValues($leadCustomFieldsValuesCollection);

        try {
           $createdLead = $leadService->addOne($leadModel);
        } catch (AmoCRMApiException $e) {
            throw new AmoCRMApiException($e);
        }

        return $createdLead;
    }

    public function updateLead(int $id,string $name, int|null $price = null, int|null $costPrice = null): LeadModel
    {
       $profit = $this->calculateLeadProfit($price, $costPrice);

       $leadModel = $this->leadService->getOne($id);

       $leadModel->setPrice($price)->setName($name);

       $leadCustomFields = $leadModel->getCustomFieldsValues();

       $leadCustomFieldsValuesCollection = new CustomFieldsValuesCollection();


           $oldPriceCostField = $leadCustomFields->getBy('fieldId', self::COST_PRICE_CUSTOM_FIELD_ID);
           $oldProfitField    = $leadCustomFields->getBy('fieldId', self::PROFIT_CUSTOM_FIELD_ID);

               $newPriceCostField = $oldPriceCostField->setValues(
                   (new NumericCustomFieldValueCollection())
                       ->add((new NumericCustomFieldValueModel())
                           ->setValue($costPrice)
                       )
               );

               $newProfitField = $oldProfitField->setValues(
                   (new NumericCustomFieldValueCollection())
                       ->add((new NumericCustomFieldValueModel())
                           ->setValue($profit)
                       )
               );

               $leadCustomFieldsValuesCollection
                   ->add($newPriceCostField)
                   ->add($newProfitField);

        $leadModel->setCustomFieldsValues($leadCustomFieldsValuesCollection);

        try {
            $updatedLead = $this->leadService->updateOne($leadModel);
        } catch (AmoCRMApiException $e) {
            throw new AmoCRMApiException($e);
        }

        return $updatedLead;
    }

    /**
     * @throws AmoCRMoAuthApiException
     * @throws FileNotFoundException
     * @throws AmoCRMApiException
     */
    public function createLeadsFromArray(array $leadsArray): Application|ResponseFactory|\Illuminate\Foundation\Application|Response|LeadsCollection
    {
       $leadsCollection = new LeadsCollection();

       if (count($leadsArray) > 50) {
            return response(['message' => 'Too many leads for one request'], 400);
       }

           foreach ($leadsArray as $leadItem) {

              $profit = $this->calculateLeadProfit($leadItem['price'],$leadItem['cost_price']);

               $lead = (new LeadModel())
                   ->setName($leadItem['name'])
                   ->setPrice($leadItem['price']);

               $leadCustomFieldsValuesCollection = new CustomFieldsValuesCollection();


               $costPriceModel = $this->setCustomNumericField(
                   $leadItem['cost_price'],
                   self::COST_PRICE_CUSTOM_FIELD_NAME,
                   self::COST_PRICE_CUSTOM_FIELD_ID
               );

               $profitModel = $this->setCustomNumericField(
                   $profit,
                   self::PROFIT_CUSTOM_FIELD_NAME,
                   self::PROFIT_CUSTOM_FIELD_ID
               );

               $leadCustomFieldsValuesCollection
                   ->add($costPriceModel)
                   ->add($profitModel);

               $lead->setCustomFieldsValues($leadCustomFieldsValuesCollection);

               $leadsCollection->add($lead);
           }

        try {
            $leadsCollection  = $this->leadService->add($leadsCollection);
        } catch (AmoCRMApiException $e) {
               throw new AmoCRMApiException($e);
        }

      return $leadsCollection;
    }

    public function setCustomNumericField(int|null $customFieldValue,string $customFieldName, int $customFieldId): NumericCustomFieldValuesModel
    {
        $customNumericValuesModel = new NumericCustomFieldValuesModel();

        $customNumericValuesModel
            ->setFieldId($customFieldId)
            ->setFieldName($customFieldName);

        $customNumericValuesModel
            ->setValues(
                (new NumericCustomFieldValueCollection())
                    ->add((new NumericCustomFieldValueModel())
                        ->setValue($customFieldValue)
                    )
            );

        return $customNumericValuesModel;
    }

    public function calculateLeadProfit(int|null $price, int|null $costPrice): int|null
    {
        if (is_null($price) && is_null($costPrice)) {
            return null;
        }
        else if (is_null($costPrice)) {
            $costPrice = 0;
        }
        else if (is_null($price)) {
            $price = 0;
        }
        return $price - (int)$costPrice;
    }

}
