<?php



namespace App\Classes\LendMNApi;

use Exception;

class CurlClient implements HttpClientInterface
{
    const HTTP_CODE_OK = 200;

    protected function initCurl($url)
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_HEADER, false);

        return $curl_handle;
    }

    /**
     * TODO
     * @param type $curl_handle
     * @return mixed
     * @throws Exception
     */
    protected function executeCurl($curl_handle)
    {
        $buffer = curl_exec($curl_handle);
        $errno = curl_errno($curl_handle);
        $httpcode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

        $result = json_decode($buffer, true);

        curl_close($curl_handle);

        // error occured while initializing curl
        if ($errno) {
            $error_message = curl_strerror($errno);
            // $message = "Холболтын алдаа гарлаа";
            throw new Exception($error_message);
        }


        if (self::HTTP_CODE_OK == $httpcode && array_key_exists('code', $result) && 0 != $result['code']) {
            $code = $result['code'];

            //$message = json_encode($result);
            $message = $result['response']['error_description'];
            throw new Exception($message, $code);
        }

        // not normal
        if (self::HTTP_CODE_OK != $httpcode) {
            throw new Exception(json_encode($result), $httpcode);
        }

        return $result;
    }

    public function post($url, $options = [])
    {
        $header = isset($options['header']) ? $options['header'] : [];
        $data = isset($options['data']) ? $options['data'] : [];

        // init curl
        $curl_handle = $this->initCurl($url);

        // curl options
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl_handle, CURLOPT_POST, true);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

        // execute curl
        return $this->executeCurl($curl_handle);
    }

    public function get($url, $options = [])
    {
        $header = isset($options['header']) ? $options['header'] : [];
        $data = isset($options['data']) ? $options['data'] : [];

        // command url
        $url = $url . (count($data) ? '?' . http_build_query($data, '', '&') : '');

        // init curl
        $curl_handle = $this->initCurl($url);

        // set options
        curl_setopt($curl_handle, CURLOPT_POST, false);
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);

        // execute curl
        return $this->executeCurl($curl_handle);
    }


    public function delete($url, $options = [])
    {
        $header = isset($options['header']) ? $options['header'] : [];
        $data = isset($options['data']) ? $options['data'] : [];

        // init curl
        $curl_handle = $this->initCurl($url);

        // curl options
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

        // execute curl
        return $this->executeCurl($curl_handle);
    }
}
