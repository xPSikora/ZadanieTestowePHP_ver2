<?php
Class CurrencyObject
{
    private object $XMLObject;
    private string $RequestDate;
    private object $XMLRatesObject;
    private object $ActualRate;
    private object $PreviousRate;    
    public function __construct(object $XMLObject, string $RequestDate)
    {
        $this->XMLObject = $XMLObject;
        $this->RequestDate = $RequestDate;
    }
    private function getXMLRatesObject() : object
    {
        $this->XMLRatesObject = $this->XMLObject->Rates;
        foreach($this->XMLRatesObject->Rate as $RateO)
        {
            $RateO->EffectiveDateYmd = date('Ymd',strtotime($RateO->EffectiveDate));
        }
        return $this;
    }
    public function getIsRequestDate(string $EffectiveDate) : bool
    {
        return $this->RequestDate == $EffectiveDate ? true : false;
    }
    private function createRatesObjects() : object
    {
        foreach($this->XMLRatesObject->Rate as $RateO)
        {
            /* Który wariant jest najbardziej poprawny?*/

            /* Wariant 1 */
            //if($this->getIsRequestDate($RateO->EffectiveDate)){
            //    $this->createRateObject('ActualRate', $RateO);
            //}else{
            //    $this->createRateObject('PreviousRate', $RateO);
            //}

            /* Wariant 2 */
            //$this->getIsRequestDate($RateO->EffectiveDate) ? $this->createRateObject('ActualRate', $RateO) : $this->createRateObject('PreviousRate', $RateO);
        
            /* Wariant 3 */
            $this->createRateObject( $this->getIsRequestDate($RateO->EffectiveDate) ? 'ActualRate' : 'PreviousRate', $RateO);
        }
        return $this;
    }
    private function createRateObject(string $RateType, object $RateO) : object
    {
        $this->$RateType = new RateObject();
        $this->$RateType->setPrice('BuyPrice', $RateO->Bid);
        $this->$RateType->setPrice('SellPrice', $RateO->Ask);
        return $this;
    }
    public function createCurrencyRates() : object
    {
        include_once ('include/ClassRateObject.php');
        $this->getXMLRatesObject();
        $this->createRatesObjects();
        return $this;
    }
    private function getRateObject(string $RateType) : object
    {
        return $this->$RateType;
    }
    public function setPriceDifference() : object
    {
        $this->getRateObject('ActualRate')->setPreviousPriceDifference($this->getRateObject('PreviousRate'));
        return $this;
    }
    public function setTableRow() : string
    {
        return $this->ActualRate->getTableData();        
    }
}