<?php

class KAOptimized extends KeyDef
{
    public function __construct()
    {
        $this->xmlName = "@optimized";
        $this->type = "button";
        $this->postId = "@optimized";
        $this->size = 1;
        $this->maxsize = 1;
        $this->initAtNewEntry = TRUE;
        $this->initVal = "0";
        $this->isAttr = TRUE;
        $this->showShiftArrows = FALSE;
        $this->hideInEditor = TRUE;
    }

    function addHeadEditor(&$gPar, &$stylesPath, &$cont)
    {}

    function addToEditor(&$gPar, &$cont)
    {
        $gPar->canRemove = false;
        //$df = new KEButton($this, $cont, $gPar);
    }
}

?>