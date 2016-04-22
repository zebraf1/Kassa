<?php

namespace Rotalia\InventoryBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JSendResponse
 * Example usages:
 *      JSendResponse::createSuccess($data);
 *      JSendResponse::createFail($data, 500);
 *      JSendResponse::createError($message, 403);
 *
 * @package Rotalia\InventoryBundle\Component\HttpFoundation
 * @author Jaak Tamre
 * @see http://labs.omniti.com/labs/jsend
 */
class JSendResponse extends JsonResponse
{
    const JSEND_STATUS_SUCCESS = 'success';
    const JSEND_STATUS_FAIL = 'fail';
    const JSEND_STATUS_ERROR = 'error';

    protected $jSendStatus;
    protected $jSendMessage;
    protected $jSendData;
    protected $jSendCode;

    /**
     * General constructor for a JSend response object.
     *
     * @param null $jSendStatus
     * @param null $jSendData
     * @param null $jSendMessage
     * @param null $jSendCode
     * @param int $httpStatus
     * @param array $httpHeaders
     * @throws \Exception
     */
    public function __construct($jSendStatus = null, $jSendData = null, $jSendMessage = null, $jSendCode = null, $httpStatus = 200, $httpHeaders = array())
    {
        if (! in_array($jSendStatus, [self::JSEND_STATUS_SUCCESS, self::JSEND_STATUS_FAIL, self::JSEND_STATUS_ERROR])) {
            throw new \Exception('Invalid JSend status:' .$jSendStatus);
        }

        parent::__construct(null, $httpStatus, $httpHeaders);

        $this->jSendStatus = $jSendStatus;
        $this->jSendData = $jSendData;
        $this->jSendMessage = $jSendMessage;
        $this->jSendCode = $jSendCode;

        $this->updateJSendData();
    }

    /**
     * Creates a JSend successful response.
     * All went well, and (usually) some data was returned.
     *
     * @param mixed $jSendData    required parameter data
     * @param array $httpHeaders  optional HTTP headers
     * @return JSendResponse
     */
    public static function createSuccess($jSendData, $httpHeaders = array())
    {
        return new static(self::JSEND_STATUS_SUCCESS, $jSendData, null, null, 200, $httpHeaders);
    }

    /**
     * Creates a JSend fail response.
     * There was a problem with the data submitted, or some pre-condition of the API call wasn't satisfied
     *
     * @param mixed $jSendData      required parameter data
     * @param int $httpStatus       HTTP response code
     * @param array $httpHeaders    optional HTTP headers
     * @return JSendResponse
     */
    public static function createFail($jSendData, $httpStatus, $httpHeaders = array())
    {
        return new static(self::JSEND_STATUS_FAIL, $jSendData, null, null, $httpStatus, $httpHeaders);
    }

    /**
     * Creates a JSend error response.
     * An error occurred in processing the request, i.e. an exception was thrown
     *
     * @param string $jSendMessage      A meaningful, end-user-readable message, explaining what went wrong.
     * @param int $httpStatus           HTTP response status
     * @param null|mixed $jSendData     optional generic container for any other information about the error
     * @param null|string $jSendCode    optional numeric code corresponding to the error, if applicable
     * @param array $httpHeaders        optional HTTP headers
     * @return JSendResponse
     */
    public static function createError($jSendMessage, $httpStatus, $jSendData = null, $jSendCode = null, $httpHeaders = array())
    {
        return new static(self::JSEND_STATUS_ERROR, $jSendData, $jSendMessage, $jSendCode, $httpStatus, $httpHeaders);
    }

    /**
     * Sets the data parameter within the generic container
     * @param $data
     * @return JSendResponse
     */
    public function setJSendData($data)
    {
        $this->jSendData = $data;
        return $this->updateJSendData();
    }

    /**
     * Sets the message parameter within the generic container
     * @param $message
     * @return JSendResponse
     */
    public function setJSendMessage($message)
    {
        $this->jSendMessage = $message;
        return $this->updateJSendData();
    }

    /**
     * Sets the code parameter within the generic container
     * @param $code
     * @return JSendResponse
     */
    public function setJSendCode($code)
    {
        $this->jSendCode = $code;
        return $this->updateJSendData();
    }

    /**
     * Updates the Response data and content.
     *
     * @return JSendResponse
     */
    protected function updateJSendData()
    {
        $this->setJSendContent($this->jSendStatus, $this->jSendData, $this->jSendMessage, $this->jSendCode);
    }

    /**
     * Set all JSend parameters in the JSON data container
     *
     * @param $status
     * @param $data
     * @param $message
     * @param $code
     */
    protected function setJSendContent($status, $data, $message, $code)
    {
        $content = ['status' => $status];
        if ($data) {
            $content['data'] = $data;
        }
        if ($message) {
            $content['message'] = $message;
        }
        if ($code) {
            $content['code'] = $code;
        }

        $this->setData($content);
    }
}
