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
        $originalOfx = $this->ofxContent;
        $ofxLines = explode("\n", $originalOfx);
        foreach ($ofxLines as $key => $lineContent) {
            if(empty(trim($lineContent))) {
                unset($ofxLines[$key]);
                continue;
            }
            $elementName = preg_replace("/[^A-Za-z0-9]/", '' , reset(explode('>', $lineContent)));
            $hasClosedTag = strpos($originalOfx, "</{$elementName}>") !== false;
            if(!$hasClosedTag) {
                $ofxLines[$key] = "{$lineContent}</{$elementName}>";
            }
        }
        $this->XMLOfxRaw = new SimpleXMLElement(implode('', $ofxLines));
        $this->transactionList  =   $this->XMLOfxRaw->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN;
        $this->dateStart        =   $this->XMLOfxRaw->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->DTSTART;
        $this->dateEnd          =   $this->XMLOfxRaw->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->DTEND;
        $this->org              =   $this->XMLOfxRaw->SIGNONMSGSRSV1->SONRS->FI->ORG;
        $this->accountId        =   $this->XMLOfxRaw->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKACCTFROM->ACCTID;
        $this->bankId           =   $this->XMLOfxRaw->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKACCTFROM->BANKID;
        return $this;
    }
}