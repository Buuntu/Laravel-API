<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

/**
 * Class ApiController
 * @package App\Http\Controllers
 */
abstract class ApiController extends Controller
{

    /**
     * @var _statusCode
     *
     */
    protected $_statusCode = 200;
    /**
     * @var
     */
    protected $_request;

    /**
     *
     */
    public function __construct() {
        $this->_request = $request = \App::make('Illuminate\Http\Request');
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->_statusCode = $statusCode;

        return $this;
    }

    /**
     * @param $data
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond($data, $headers = [])
    {
        return response()->json($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respondWithData($data, $headers = [])
    {
        return $this->respond([
            'post' => $this->_request->all(),
            'data' => $data,
            'error' => [
                'global' => ''
            ]
        ], $headers);
    }


    /**
     * @param Paginator $data
     * @param TransformerAbstract $transformer
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithPaginator(LengthAwarePaginator $data, TransformerAbstract $transformer, $headers = [])
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());

        $resource = new Collection($data->getCollection(), $transformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($data));

        $response = $manager->createData($resource)->toArray();

        return $this->respond([
            'post' => $this->_request->all(),
            'data' => $response['data'],
            'meta' => $response['meta'],
            'error' => [
                'global' => ''
            ]
        ], $headers);
    }

    /**
     * @param $data
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithFractalData($data, $headers = [])
    {
        return $this->respond([
            'post' => $this->_request->all(),
            $data,
            'error' => [
                'global' => ''
            ]
        ], $headers);
    }

    /**
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondNotFound($message = 'Not found') {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    /**
     * @param $message
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithError($message, $headers = [])
    {
        return $this->respond([
            'post' => $this->_request->all(),
            'data' => '',
            'error' => [
                'global' => $message
            ]
        ], $headers);
    }

    /**
     * @param $serverError
     * @param string $globalMessage
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithServerError($serverError, $globalMessage = 'Internal server error')
    {
        return $this->setStatusCode(500)->respond([
            'post' => $this->_request->all(),
            'data' => [],
            'error' => [
                'global' => $globalMessage,
                'server_error' => $serverError
            ]
        ]);
    }

    /**
     * @param $errors
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithValidationErrors($errors, $message = 'There were validation errors on your request')
    {
        return $this->setStatusCode(422)->respond([
            'post' => $this->_request->all(),
            'data' => [],
            'error' => [
                'global' => $message,
                'validation' => $errors
            ]
        ]);
    }

}