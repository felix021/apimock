<?php

//util

class Util
{
    const EXC_DETAIL    = true;
    const EXC_SIMPLE    = false;
    public static function excMessage($e, $detailed = true)
    {
        if (!$detailed) {
            return sprintf("%s(%d, %s)", get_class($e), $e->getCode(), $e->getMessage());
        } else {
            return sprintf("%s(%d, %s):\n%s", get_class($e), $e->getCode(), $e->getMessage(), $e->getTraceAsString());
        }
    }

    public static function result($code, $message = '', $data = [])
    {
        return [
            'code'      => $code,
            'message'   => $message,
            'data'      => $data
        ];
    }

    const RUN_EXLUSIVE  = true;
    const RUN_WHATEVER  = false;
    public static function runInBackground($argv = [], $exclusive = self::RUN_EXLUSIVE)
    {
        foreach ($argv as &$arg) {
            $arg = escapeshellarg($arg);
        }
        $run_cmd = join(" ", $argv);

        if ($exclusive == self::RUN_EXLUSIVE) {
            $lock_file = sprintf('/tmp/runInBackground_%s.lock', crc32($run_cmd));
            $lock_fp = fopen($lock_file, 'a');
            if (!flock($lock_fp, LOCK_EX | LOCK_NB)) {
                throw new CException("conflicted");
            }
            flock($lock_fp, LOCK_UN);
            fclose($lock_fp);

            $run_cmd = sprintf("flock -nx %s -c %s", $lock_file, escapeshellarg($run_cmd));
        }

        Log::trace("%s: %s", __METHOD__, $run_cmd);

        $p = popen("nohup $run_cmd &", 'r');
        if (!$p) {
            throw new CException(__METHOD__ . ": execute $run_cmd failed: popen");
        }

        if (pclose($p) != 0) {
            throw new CException(__METHOD__ . ": execute $run_cmd failed: pclose");
        }
    }

    public static function curl($url, $request_data, $extra_opts = [])
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_TIMEOUT         => 10, //seconds
        ]);
        if (is_array($request_data)) {
            curl_setopt_array($ch, [
                CURLOPT_POST        => true,
                CURLOPT_POSTFIELDS  => http_build_query($request_data),
            ]);
        }
        curl_setopt_array($ch, $extra_opts);

        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            $err = sprintf("curl[%d]%s", curl_errno($ch), curl_error($ch));
            curl_close($ch);
            throw new CException($err);
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 200) {
            curl_close($ch);
            throw new CHttpException($httpcode, $data, $httpcode);
        }
        curl_close($ch);
        return $data;
    }

    public static function post($url, $request_data, $extra_opts = [])
    {
        return self::curl($url, $request_data, $extra_opts);
    }

    public static function get($url, $request_data, $extra_opts = [])
    {
        if (is_array($request_data)) {
            $request_data = http_build_query($request_data);
        }
        if (strpos($url, '?') === false) {
            $url = $url . '?' . $request_data;
        } else {
            $url = $url . '&' . $request_data;
        }
        return self::curl($url, null, $extra_opts);
    }

    public static function xml2array($xml)
    {
        libxml_use_internal_errors(true);
        $xml_obj = simplexml_load_string($xml);
        if ($xml_obj === false or get_class($xml_obj) != 'SimpleXMLElement') {
            $err = '';
            foreach (libxml_get_errors() as $xmlerr) {
                $err .= trim($xmlerr->message) . "\n";
            }
            throw new CException("invalid xml: " . $err);
        }
        $xml_arr = json_decode(str_replace('{}', '""', json_encode($xml_obj)), true);
        return $xml_arr;
    }
}
