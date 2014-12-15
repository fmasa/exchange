<?php

namespace Exchange;

use Money;

/**
 * Simple wrapper for converting money/money to different currency using http://rate-exchange.appspot.com/
 * @author František Maša <frantisekmasa1@gmail.com>
 */
class Converter
{
    private $ratio = FALSE;
    
    private $from;
    
    private $to;
    
    /** @var Money\CurrencyPair */
    private $currencyPair;
    
    private $apiUrl = 'http://rate-exchange.appspot.com/currency';
    
    /**
     * 
     * @param string $from - Currency ISO code
     * @param type $to - Currency ISO code
     */
    public function __construct($from, $to)
    {
	// Bitcoin XBT/BTC fix
	$from = strtoupper($from);
	$to = strtoupper($to);
	$this->from = ($from == 'BTC') ? 'XBT' : $from;
	$this->to = ($to == 'BTC') ? 'XBT' : $to;
    }
    
    /**
     * @param Money\Money $money
     * @return Mone\Money
     */
    public function convert(Money\Money $money)
    {
	if(!isset($this->currencyPair)) {
	    $from = new Money\Currency(($this->from == 'XBT') ? 'BTC' : $this->from);
	    $to = new Money\Currency(($this->to == 'XBT') ? 'BTC' : $this->to); 
	    $this->currencyPair =  new Money\CurrencyPair($from, $to, $this->getRatio());
	}	
	
	return $this->currencyPair->convert($money);
    }
    
    public function getRatio()
    {	
	if($this->ratio === FALSE) {
	    $response = json_decode(file_get_contents(
		    $this->apiUrl.'?from='.$this->from.'&to='.$this->to.'&q=1'
		    ));

	    if(isset($response->err)) {
		throw new Exception('You have specified wrong currency.');
	    }

	    $this->ratio = $response->rate;
	}
	
	return $this->ratio;
    }
}

class Exception extends \Exception {}