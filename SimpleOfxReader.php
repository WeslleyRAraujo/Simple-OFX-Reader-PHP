<?php

/**
 * @author WeslleyRAraujo <weslleyronaldaraujodev@gmail.com>
 */
class SimpleOfxReader
{
    public $accountId;
    public $bankId;
    public $org;
    public $dateStart;
    public $dateEnd;
    public $transactionList;

    private $ofxContent;
    private $XMLOfxRaw;

    /**
     *
     * @param string $ofxContent OFX content
     * 
     * @return SimpleOfxReader
     */
    public function __construct($ofxContent)
    {
        $this->ofxContent = $ofxContent;
        return $this->ofxFormatted();
    }

    private function ofxFormatted() 
    {
        $ofxLines = array_map(function($lineContent) {
            $lineContent = preg_replace("/(\r\n|\r|\n)/", '', $lineContent);
            if(empty(trim($lineContent))) {
                return null;
            }
            $elementName = preg_replace("/[^A-Za-z0-9]/", '' , reset(explode('>', $lineContent)));
            $hasClosedTag = strpos($this->ofxContent, "</{$elementName}>") !== false;
            if(!$hasClosedTag) {
               return "{$lineContent}</{$elementName}>";
            }
            return $lineContent;
        }, explode("\n", substr($this->ofxContent, strpos($this->ofxContent, '<OFX>'))));

        $this->XMLOfxRaw        = new SimpleXMLElement(implode('', $ofxLines));
        $this->transactionList  = $this->XMLOfxRaw->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN;
        $this->dateStart        = $this->XMLOfxRaw->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->DTSTART;
        $this->dateEnd          = $this->XMLOfxRaw->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->DTEND;
        $this->org              = $this->XMLOfxRaw->SIGNONMSGSRSV1->SONRS->FI->ORG;
        $this->accountId        = $this->XMLOfxRaw->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKACCTFROM->ACCTID;
        $this->bankId           = $this->XMLOfxRaw->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKACCTFROM->BANKID;

        return $this;
    }
}