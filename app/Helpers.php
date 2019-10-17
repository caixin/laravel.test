<?php

if (!function_exists("encode_search_params")) {
    function encode_search_params($params, $zero=[])
    {
        foreach ($params as $key => $value) {
            if ($key == '_token' || $value == '' || $value == 'all' || (in_array($key, $zero) && $value == '0')) {
                unset($params[$key]);
            } else {
                if (is_array($value)) {
                    $value = implode('|,|', $value).'array';
                }
                $params[$key] = urlencode($value);
            }
        }

        return $params;
    }
}

if (!function_exists("decode_search_params")) {
    function decode_search_params($params)
    {
        foreach ($params as $key => $value) {
            $value = urldecode($value);
            if (strpos($value, 'array') !== false) {
                $value = explode('|,|', substr($value, 0, strpos($value, 'array')));
            }
            $params[$key] = $value;
        }

        return $params;
    }
}

if (!function_exists("get_search_uri")) {
    function get_search_uri($params, $uri, $zero=[])
    {
        $params = encode_search_params($params, $zero);
        $arr = [];
        foreach ($params as $k => $v) {
            $arr[] = "$k=$v";
        }
        return $uri.'?'.implode('&', $arr);
    }
}

if (!function_exists("get_page")) {
    function get_page($params)
    {
        return isset($params['page']) ? (int) $params['page'] : 1;
    }
}

if (!function_exists("get_order")) {
    function get_order($params, $default_order = [])
    {
        $order = $default_order;

        isset($params['asc']) && $order = [$params['asc'], 'asc'];
        isset($params['desc']) && $order = [$params['desc'], 'desc'];

        return $order;
    }
}

if (!function_exists("param_process")) {
    function param_process($params, $default_order = [])
    {
        $result['page']  = $params['page'] ?? 1;
        $result['order'] = get_order($params, $default_order);

        unset($params['page']);

        $uri = [];
        foreach ($params as $k => $v) {
            $uri[] = "$k=$v";
        }
        $result['params_uri'] = implode('&', $uri);

        unset($params['asc']);
        unset($params['desc']);

        $result['search'] = decode_search_params($params);

        return $result;
    }
}

if (!function_exists("sort_title")) {
    function sort_title($key, $name, $base_uri, $order, $where = [])
    {
        $where = encode_search_params($where);
        $uri = [];
        foreach ($where as $k => $v) {
            $uri[] = "$k=$v";
        }
        $uri[] = $order[1] === 'asc' ? 'desc='.$key : 'asc='.$key;
        $uri_str = '?'.implode('&', $uri);

        $class = ($order[0] === $key) ? 'sort '.$order[1] : 'sort';

        return '<a class="'.$class.'" href="'.route($base_uri).$uri_str.'">'.$name.'</a>';
    }
}

if (!function_exists("lists_message")) {
    function lists_message($type='success')
    {
        if (session('message')) {
            return '<div id="message" class="alert alert-'.$type.' alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-check"></i> 讯息!</h4>
                '.session('message').'
            </div>
            <script>
                setTimeout(function() { $(\'#message\').slideUp(); }, 3000);
                $(\'#message .close\').click(function() { $(\'#message\').slideUp(); });
            </script>';
        }
        return '';
    }
}

if (!function_exists("auth_code")) {
    /**  摘自 discuz
     * @param string $string 明文或密文
     * @param string $operation 加密ENCODE或解密DECODE
     * @param string $key 密鑰
     * @param integer $expiry 密鑰有效期，默認是一直有效
     */
    function auth_code($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        /*
         動態密匙長度，相同的明文會生成不同密文就是依靠動態密匙
        加入隨機密鑰，可以令密文無任何規律，即便是原文和密鑰完全相同，加密結果也會每次不同，增大破解難度。
        取值越大，密文變動規律越大，密文變化 = 16 的 $ckey_length 次方
        當此值爲 0 時，則不產生隨機密鑰
         */
        $ckey_length = 4;
        $key = md5($key != '' ? $key : "JliNlk1i1103141220171231"); //此處的key可以自己進行定義，寫到配置文件也可以
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        //明文，前10位用來保存時間戳，解密時驗證數據有效性，10到26位用來保存$keyb(密匙b)，解密時會通過這個密匙驗證數據完整性
        //如果是解碼的話，會從第$ckey_length位開始，因爲密文前$ckey_length位保存 動態密匙，以保證解密正確
        $string = $operation == 'DECODE' ? base64_decode(substr(str_replace(['-', '_'], ['+', '/'], $string), $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = [];
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            //把動態密匙保存在密文裏，這也是爲什麼同樣的明文，生產不同密文後能解密的原因
            //因爲加密後的密文可能是一些特殊字符，複製過程可能會丟失，所以用base64編碼
            return $keyc . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($result));
        }
    }
}

if (! function_exists('randPwd')) {
    /**
     * 隨機產生密碼
     * @param integer $pwd_len 密碼長度
     * @param integer $type
     * @return string
     */
    function randPwd($pwd_len, $type=0)
    {
        $password = '';
        if (!in_array($type, [0,1,2,3])) {
            return '';
        }

        // remove o,0,1,l
        if ($type == 0) {
            $word = 'abcdefghijkmnpqrstuvwxyz-ABCDEFGHIJKLMNPQRSTUVWXYZ_23456789';
        }
        if ($type == 1) {
            $word = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($type == 2) {
            $word = '123456789';
        }
        if ($type == 3) {
            $word = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
        }

        $len = strlen($word);

        for ($i = 0; $i < $pwd_len; $i++) {
            $password .= $word[rand(1, 99999) % $len];
        }

        return $password;
    }
}
