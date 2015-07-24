<?php

function smarty_modifier_oxdatetime($sString, $blSeconds = false)
{
    $aIncorrect = array('0000-00-00', '0000-00-00 00:00:00', '0000-00-00 00:00', '0000-00-00 00');
    if(in_array($sString, $aIncorrect)) {
        return null;
    }

    $oDateTime = new DateTime($sString);
    return ($blSeconds ? $oDateTime->format('Y-m-d H:i:s') : $oDateTime->format('Y-m-d H:i'));
}

?>
