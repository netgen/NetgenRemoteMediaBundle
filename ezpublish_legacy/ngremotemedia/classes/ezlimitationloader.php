<?php

class eZLimitationLoader
{
    /**
     * Fetches the functions that can be limited.
     *
     * @static
     *
     * @return array
     */
    static public function fetchLimitations()
    {
        $returnArray = array( 'name' => 'Browse', 'id' => 'browse' );

        return $returnArray;
    }
}
?>
