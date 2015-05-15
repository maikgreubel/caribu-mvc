<?php
namespace Nkey\Caribu\Mvc\Controller;

/**
 * The response is encapsulated in this class
 *
 * @author Maik Greubel <greubel@nkey.de>
 *
 *         This file is part of Caribu MVC package
 */
class Response
{

    /**
     * Response code
     *
     * @var int
     */
    private $code = 200;

    /**
     * Page title
     *
     * @var string
     */
    private $title = '';

    /**
     * Response content
     *
     * @var string
     */
    private $body = '';

    /**
     * Response content type
     *
     * @var string
     */
    private $type = 'text/html';

    /**
     * Response encoding
     *
     * @var string
     */
    private $encoding = 'utf-8';

    /**
     * Additional headers to be send to client
     *
     * @var array
     */
    private $additionalHeaders = array();

    /**
     * Retrieve the response body as string
     *
     * @return string The response content
     */
    public function __toString()
    {
        return $this->body;
    }

    /**
     * Retrieve the response code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the response code
     *
     * @param int $code
     *
     * @return \Nkey\Caribu\Mvc\Controller\Response The response
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Retrieve response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the response body content
     *
     * @param string $body
     *            The content body
     *
     * @return \Nkey\Caribu\Mvc\Controller\Response The response
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Append some content to existing body
     *
     * @param string $body
     *            The content body to append to existing content
     *
     * @return \Nkey\Caribu\Mvc\Controller\Response
     */
    public function appendBody($body)
    {
        return $this->setBody(sprintf("%s%s", $this->getBody(), $body));
    }

    /**
     * Return the reponse as http status code string
     *
     * @return string The status code
     */
    public function getHttpCode()
    {
        return sprintf("HTTP/%s", strval(new \Generics\Client\HttpStatus($this->code)));
    }

    /**
     * Retrieve the content type
     *
     * @return string The mimetype of response
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the response type
     *
     * @param string $type
     *            The mimetype of response
     *
     * @return \Nkey\Caribu\Mvc\Controller\Response The response
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set the page title
     *
     * @param string $title
     *            The page title
     *
     * @return \Nkey\Caribu\Mvc\Controller\Response The response
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Retrieve page title
     *
     * @return string The page title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Append an additional header to list
     *
     * @param string $headerName The name of the header
     * @param string $headerValue The value of the header
     *
     * @return Response The current response
     */
    public function addHeader($headerName, $headerValue)
    {
        $this->additionalHeaders[$headerName] = $headerValue;
        return $this;
    }

    /**
     * Retrieve the list of additional headers
     *
     * @return array The list of headers
     */
    public function getAdditionalHeaders()
    {
        return $this->additionalHeaders;
    }

    /**
     * Set the response content encoding
     *
     * @param string $encoding
     *
     * @return Response The current response
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Retrieve the content encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }
}
