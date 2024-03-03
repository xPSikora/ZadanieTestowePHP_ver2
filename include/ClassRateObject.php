<?php
Class RateObject
{
    private float $BuyPrice;
    private float $SellPrice;
    private float $BuyPriceDiff;
    private float $SellPriceDiff;
    private bool $IsReady = false;
    public function setPrice(string $Price, string $Value) : float
    {
        $this->$Price = $Value;
        return $this->$Price;
    }
    public function setPreviousPriceDifference(object $Previous_Rate) : object
    {
        $this->setPriceDifference('BuyPriceDiff', 'BuyPrice', $Previous_Rate);
        $this->setPriceDifference('SellPriceDiff', 'SellPrice', $Previous_Rate);
        $this->setReadiness();
        return $this;
    }
    public function setReadiness() : bool
    {
        $this->IsReady = true;
        return $this->IsReady;
    }
    public function getPrice(string $Price) : float
    {
        return $this->$Price;
    }
    public function setPriceDifference(string $PriceDiff, string $Price, object $PreviousRate) : object
    {
        $this->$PriceDiff = round($this->getPrice($Price) - $PreviousRate->getPrice($Price),4);
        return $this;
    }
    public function getTableData() : string
    {
        $TableData =    $this->getTableTD($this->getPrice('BuyPrice'));
        $TableData .=   $this->getTableTD($this->getPrice('SellPrice'));
        $TableData .=   $this->getTableTD($this->getPrice('BuyPriceDiff'));
        $TableData .=   $this->getTableTD($this->getPrice('SellPriceDiff'));
        return $TableData;
    }
    public function getTableTD(string $TD_Value) : string
    {
        return '<td>'.$TD_Value.'</td>';
    }
}