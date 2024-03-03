<!DOCTYPE html> 
<head>	
    <meta http-equiv="Content-Language" content="pl">
    <meta charset="UTF-8">
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" href="style.css">
	
	<title>Zadanie testowe v2</title>
</head>
<?php
	include_once 'include/ClassMainPage.php';
	$Page = new MainPage();
?>
<?=$Page->formDateCurrency()?>
<?=$Page->isDateCurrencySet() ? $Page->showData() : $Page->showMessage()?>
