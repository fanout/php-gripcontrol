<?php

/*  encoding.php
    ~~~~~~~~~
    This module implements the Encoding class.
    :authors: Konstantin Bokarius.
    :copyright: (c) 2015 by Fanout, Inc.
    :license: MIT, see LICENSE for more details. */

class Encoding
{
    public static function is_binary_data($data)
    {
        if (preg_match('!!u', $data))
            return false;
        $characters = str_split($data);
        foreach($characters as $character)
        {
            $ord_value = ord($character);
            if ($ord_value < 0x20 or $ord_value >= 0x7f)
                return true;
        }
        return false;
    }
}
?>
