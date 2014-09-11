<?php
class Luhn {
    public static function encode($account) {
        for($total=0,$i=0;$i<strlen($account);$i++) $total += $account[$i];
        return $account . substr(strrev($total), 0, 1);
    }

    public static function decode($account) {
        $length = strlen($account);
        if ($length > 1) {
            $cut = substr($account, 0, strlen($account) - 1);
            if (Luhn::encode($cut) == $account) return $cut;
        } 
        return $account;
    }
}
