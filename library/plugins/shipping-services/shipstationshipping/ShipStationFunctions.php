<?php

trait ShipStationFunctions
{
    /**
     * getResponse
     *
     * @param  bool $decodeJson
     * @return mixed
     */
    public function getResponse(bool $decodeJson = true)
    {
        if (empty($this->resp)) {
            return false;
        }
        return (true === $decodeJson ? json_decode($this->resp, true) : $this->resp);
    }
    
    /**
     * unsetResponse
     *
     * @return void
     */
    public function unsetResponse(): void
    {
        $this->resp = null;
    }

    /**
     * formatError
     *
     * @return mixed
     */
    public function formatError()
    {
        $exceptionMsg = isset($this->error['ExceptionMessage']) ? ' ' . $this->error['ExceptionMessage'] : '';
        return (isset($this->error['Message']) ? $this->error['Message'] : $this->error) . $exceptionMsg;
    }

    /**
     * call - Call ShipStation
     *
     * @return void
     */
    private function call(string $requestType, array $requestParam = [])
    {
        $ch = curl_init();
        $authToken = base64_encode($this->settings['api_key'] . ':' . $this->settings['api_secret_key']);
        $request = [
            CURLOPT_URL => self::PRODUCTION_URL . $this->endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $requestType,
            CURLOPT_HTTPHEADER => [
                'Host: ' . self::HOST,
                'Authorization: Basic ' . $authToken
            ],
        ];

        if (!empty($requestParam)) {
            $requestParam = json_encode($requestParam);
            $request[CURLOPT_POSTFIELDS] = $requestParam;
            $request[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, $request);

        $this->resp = curl_exec($ch);
        if (false === $this->resp) {
            throw new Exception(curl_error($ch));
        } else if (false === $this->validateResponse($this->resp)) {
            throw new Exception($this->resp);
        }

        curl_close($ch);
        return true;
    }

    /**
     * get - To hit get request
     *
     * @return void
     */
    private function get(): bool
    {
        return $this->call('GET');
    }

    /**
     * post - To hit post request
     *
     * @return void
     */
    private function post(array $requestParam): bool
    {
        return $this->call('POST', $requestParam);
    }

    /**
     * carrierList
     *
     * @return bool
     */
    private function carrierList(): bool
    {
        $this->endpoint = 'carriers';
        return $this->get();
    }

    /**
     * shippingRates
     *
     * @param  array $requestParam
     * @return bool
     */
    private function shippingRates(array $requestParam): bool
    {
        $this->endpoint = 'shipments/getrates';
        return $this->post($requestParam);
    }

    /**
     * createOrder
     *
     * @param  array $requestParam
     * @return bool
     */
    private function createOrder(array $requestParam): bool
    {
        $this->endpoint = 'orders/createorder';
        return $this->post($requestParam);
    }

    /**
     * createLabel
     *
     * @param  array $requestParam
     * @return bool
     */
    private function createLabel(array $requestParam): bool
    {
        $this->endpoint = 'orders/createlabelfororder';
        $requestParam['testLabel'] = isset($this->settings['environment']) && 0 < $this->settings['environment'] ? false : true;
        return $this->post($requestParam);
    }

    /**
     * fulfillments
     *
     * @param  array $requestParam
     * @return bool
     */
    private function fulfillments(array $requestParam): bool
    {
        $this->endpoint = 'fulfillments?' . http_build_query($requestParam);
        return $this->get();
    }

    /**
     * getOrder
     *
     * @param  array $requestParam
     * @return bool
     */
    private function getOrder(array $requestParam): bool
    {
        $this->endpoint = 'orders/' . current($requestParam);
        return $this->get();
    }

    /**
     * markAsShipped
     *
     * @param  array $requestParam
     * @return bool
     */
    private function markAsShipped(array $requestParam): bool
    {
        $this->endpoint = 'orders/markasshipped';
        return $this->post($requestParam);
    }

    /**
     * validateResponse
     *
     * @param  string $response
     * @return bool
     */
    private function validateResponse(string $response): bool
    {
        $errors = [
            "400 Bad Request",
            "401 Unauthorized",
            "403 Forbidden",
            "404 Not Found",
            "429 Too Many Requests",
            "500 Internal Server Error",
            "502 Bad Gateway",
            "503 Service Unavailable",
            "504 Gateway Timeout",
            "SSL/TLS Error",
            "Invalid XML Error",
            "Remote Name Error",
            "Object Reference Error",
            "Input String Error",
            "Inner Exception Error",
        ];

        if (in_array($response, $errors)) {
            return false;
        }
        return true;
    }
}
