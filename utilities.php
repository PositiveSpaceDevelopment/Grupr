<?php
//found at http://stackoverflow.com/questions/4356289/php-random-string-generator
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

//found at http://stackoverflow.com/questions/4356289/php-random-string-generator
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if(!function_exists('hash_equals')) {
    function hash_equals($str1, $str2) {
        if(strlen($str1) != strlen($str2)) {
            return false;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
            return !$ret;
        }
    }
}

function login($login_email, $login_password, $login_dbc) {
    // Using prepared statements means that SQL injection is not possible.
    $query = 'SELECT salt FROM user_info WHERE email = :email LIMIT 1';
    $stmt = $login_dbc->prepare($query);
    $stmt->bindParam(':email', $login_email);
    $stmt->execute();
    $salt = $stmt->fetch(PDO::FETCH_ASSOC);
    $salt = $salt["salt"];

    $query = 'SELECT password FROM user_info WHERE email = :email LIMIT 1';
    $stmt = $login_dbc->prepare($query);
    $stmt->bindParam(':email', $login_email);
    $stmt->execute();
    $db_password = $stmt->fetch(PDO::FETCH_ASSOC);
    $db_password = $db_password["password"];

    $hashed_login_password = crypt($login_password, $salt);
    if (hash_equals($db_password, $hashed_login_password)) {
       return true;
    } else {
        return false;
    }

}
?>
