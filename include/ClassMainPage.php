<?php
Class MainPage
{
    private string $Form;
    private object $RequestDate;
    private object $PreviousDate;
    private object $MinDate;
    private const CURRENCY_ARRAY = ARRAY('EUR', 'USD', 'CHF');
    private string $Currency;
    private const NBP_API = 'http://api.nbp.pl/api/exchangerates/rates/c/'; // {code}/{startDate}/{endDate}/ //tutaj mozesz uzyc sprintf i przekazywać do niego argumenty w koncowym wywołaniu
    private string $ConnectNBP;
    private string $NBPData;
    private object $XMLObject;
    private object $CurrencyObject;    
    private string $Table = '';
    private function setDates()
    {
        $this->RequestDate = new DateTimeImmutable();
        $this->MinDate = $this->RequestDate->modify('-7 day');

        if(isset($_POST['formDate'])){
            $this->RequestDate = new DateTimeImmutable($_POST['formDate']);
        }
        /* Czy takie przypisanie zmiennej jest zgodne? */
        $this->PreviousDate = $this->getDateW() == 1 ? $this->modifyRequestDate(-3) : $this->modifyRequestDate(-1);

        /* Czy lepiej rozpisać to w ten sposób? */
        $this->getDateW() == 1 ? $this->PreviousDate = $this->modifyRequestDate(-3) : $this->PreviousDate = $this->modifyRequestDate(-1);
    }
    public function setCurrency() : string
    {
        isset($_POST['formCurrency']) ? $this->Currency = $_POST['formCurrency'] : $this->Currency = '';
        return $this->Currency;
    }
    public function getCurrency() : string
    {
        return $this->Currency;
    }    
    public function getDateW() : int
    {
        return $this->RequestDate->format('w');
    }
    public function modifyRequestDate(int $Days) : object
    {
        return $this->RequestDate->modify($Days.' day');
    }
    public function getDateFormat(string $Date, string $Format) : string
    {
        return $this->$Date->format($Format);
    }
    public function formDateCurrency(): string
    {
	    $this->setDates();
        $this->setCurrency();

        $this->Form =     '<form method="post">'; //to jest nadal źle. Ogólnie w "nowym" php nie piszemy html w czystym php. Powinno być html => php. Czyli te tresci jak form, powinny być budowane na zwyklym html, a wpinane w nie powinny być kwestie php
        $this->Form .=    '<input type="date" name="formDate" value="'.$this->getDateFormat('RequestDate','Y-m-d').'" min="'.$this->getDateFormat('MinDate','Y-m-d').'" max="'.date('Y-m-d').'"></input>';      //Czy w takim miejscu użycie date() jest ok?
        $this->Form .=    '<select name="formCurrency">"';
        foreach($this::CURRENCY_ARRAY as $Currency){
            $this->Form .= '<option value="'.$Currency.'"';
            $this->getCurrency() == $Currency ? $this->Form .= ' selected ': $this->Form .= '';
            $this->Form .= '>'.$Currency.'</option>';
        }
        $this->Form .=    '</select>';
        $this->Form .=    '<input type="submit" name="formSubmit" value="Pobierz Dane">';
        $this->Form .=    '</form>';
        return $this->Form;
    }
    public function isDateCurrencySet() : bool
    {
        return $this->isPostSet('formDate') && $this->isPostSet('formCurrency') ? true : false;
    }
    private static function isPostSet(string $Form_Name) : bool
    {
        return isset($_POST[$Form_Name]) ? true : false;
    }
    private function setConnectNBP() : string{ //https://www.php.net/manual/en/function.http-build-query.php
        $this->ConnectNBP = $this::NBP_API;
        $this->ConnectNBP .= $this->getCurrency().'/';
        $this->ConnectNBP .= $this->getDateFormat('PreviousDate','Y-m-d').'/';
        $this->ConnectNBP .= $this->getDateFormat('RequestDate','Y-m-d');
        $this->ConnectNBP .= '?format=xml';
        return $this->ConnectNBP;
    }
    private function getNBPData() : string
    {
        $this->NBPData = file_get_contents($this->ConnectNBP);
        return $this->NBPData;
    }
    private function setXMLObject() : object
    {
        $this->XMLObject = simplexml_load_string($this->NBPData);
        return $this;
    }    
    private function createCurrencyObject() : object
    {
        include_once 'include/ClassCurrencyObject.php';
        $this->CurrencyObject = new CurrencyObject($this->XMLObject, $this->getDateFormat('RequestDate','Y-m-d'));
        $this->CurrencyObject   ->createCurrencyRates();
        return $this->CurrencyObject;
    }
    private function isNotWeekend() : bool
    {
        return $this->RequestDate->format('w') != 6 && $this->RequestDate->format('w') != 0 ? true : false;
    }
    public function prepareNBPData() : object
    {
            $this->setConnectNBP();
            $this->getNBPData();
            $this->setXMLObject()
                    ->createCurrencyObject()
                    ->setPriceDifference();
            return $this;
    }
    public function showData() : string
    {
        if($this->isNotWeekend()){
            $this->prepareNBPData();
            $this->openTable();
            $this->setTableHeader();
            $this->setTableRow();
            $this->closeTable();
            return $this->Table;            
        }
        else
        {
            return '<h1>Wybierz datę pomiędzy poniedziałkiem a piątkiem.</h1>';
        }
    }
    public function openTable() : string
    {
        $this->Table .= '<table>';
        return $this->Table;
    }
    public function setTableHeader() : string
    {
        $this->Table .= '<tr>';
        $this->Table .= '<th>Data kursu</th>';
        $this->Table .= '<th>Cena kupna</th>';
        $this->Table .= '<th>Cena sprzedaży</th>';
        $this->Table .= '<th>Różnica<br>Ceny zakupu</th>';
        $this->Table .= '<th>Różnica<br>Ceny sprzedaży</th>';
        $this->Table .= '</tr>';
        return $this->Table;
    }
    public function setTableRow() : string
    {
        $this->Table .= '<tr>';
        $this->Table .= '<td>'.$this->getDateFormat('RequestDate','Y-m-d').'</td>';
        $this->Table .= $this->CurrencyObject->setTableRow();
        $this->Table .= '</tr>';
        return $this->Table;
    }
    public function closeTable() : string
    {
        $this->Table .= '</table>';        
        return $this->Table;
    }
    public function showMessage() : string
    {
        return '<h1>Wybierz datę oraz walutę.</h1>';
    }

}