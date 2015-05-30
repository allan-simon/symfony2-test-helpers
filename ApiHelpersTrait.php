<?php

namespace AllanSimon\TestHelpers;

use Symfony\Component\HttpFoundation\File\File;

trait ApiHelpersTrait
{
    use ClosesConnectionsAfterTestTrait;
    use ReflectsAndCleansPropertiesAfterTestTrait;

    protected $entity = null;
    protected $entityName = null;
    protected $responseJson = null;
    protected $response = null;

    /**
     * Perform an HTTP request on the HTTP, by default with Accept set to json
     * and an empty body
     */
    protected function performClientRequest(
        $method,
        $urlPath,
        $headers = ['HTTP_ACCEPT' => 'application/json'],
        $rawRequestBody = null
    ) {
        $this->client->request(
            $method,
            $urlPath,
            [],
            [],
            $headers,
            $rawRequestBody
        );

        return $this->client->getResponse();
    }

    //TODO duplicated code between PUT/POST/PATCH

    protected function performPATCH($uri, array $data)
    {
        $jsonHeaders = ['CONTENT_TYPE' => 'application/json'];
        $jsonBody = json_encode($data);

        $this->response = $this->performClientRequest(
            'PUT',
            $uri,
            $jsonHeaders,
            $jsonBody
        );
        $this->assignJsonFromResponse();
    }

    protected function performPUT($uri, array $data)
    {
        $jsonHeaders = ['CONTENT_TYPE' => 'application/json'];
        $jsonBody = json_encode($data);

        $this->response = $this->performClientRequest(
            'PUT',
            $uri,
            $jsonHeaders,
            $jsonBody
        );
        $this->assignJsonFromResponse();
    }

    protected function performPOST($uri, array $data)
    {
        $jsonHeaders = ['CONTENT_TYPE' => 'application/json'];
        $jsonBody = json_encode($data);

        $this->response = $this->performClientRequest(
            'POST',
            $uri,
            $jsonHeaders,
            $jsonBody
        );
        $this->assignJsonFromResponse();
    }

    protected function performGET($uri)
    {
        $this->response = $this->performClientRequest(
            'GET',
            $uri
        );
        $this->assignJsonFromResponse();
    }

    protected function performDELETE($uri)
    {
        $this->response = $this->performClientRequest(
            'DELETE',
            $uri
        );
        $this->assignJsonFromResponse();
    }


    /**
     * Perform an HTTP request with a Bearer token in the HTTP authorization header.
     */
    private function performAuthenticatedClientRequest($method, $urlPath, $username = null)
    {
        $username = $username ?: $this->authAsUser;
        $this->client = static::createClient(
            array(),
            array('HTTP_Authorization' => "Bearer {$username}")
        );

        return $this->performClientRequest($method, $urlPath);
    }

    protected function assertSameVideo($expectedVideo, $actualVideoData)
    {
        $this->assertEquals(
            $expectedVideo->getId(),
            $actualVideoData['id'],
            'match ID of search result'
        );

        $this->assertEquals(
            $expectedVideo->getTitle(),
            $actualVideoData['title'],
            'match title of search result'
        );
    }
    protected function assertJsonResponse(
        $response,
        $statusCode = 200,
        $checkValidJson =  true,
        $contentType = 'application/json'
    ) {
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $response->getContent()
        );

        if ($checkValidJson) {
            $this->assertTrue(
                $response->headers->contains('Content-Type', $contentType),
                $response->headers
            );
            $decode = json_decode($response->getContent());
            $this->assertTrue(
                ($decode !== null && $decode !== false),
                'is response valid json: ['.$response->getContent().']'
            );
        }
    }

    private function givenLoggedInAs($username)
    {
        $this->currentUser = $username;
        $this->client = static::createClient(
            [],
            [
                'HTTP_Authorization' => "Bearer {$username}",
                'HTTP_ACCEPT' => 'application/json',
            ]
        );
    }

    /**
     * Used to load a fixture's entity using its reference name
     */
    private function given($fixtureName)
    {
        $this->entity = $this->fixtures->getReference($fixtureName);
        $this->entityName = $fixtureName;
    }

    private function assignJsonFromResponse()
    {
        $this->responseJson = json_decode(
            $this->response->getContent(),
            true
        );
    }

    private function assertEmptyList()
    {
        $this->assertCount(
            0,
            $this->responseJson,
            'The list should be empty'
        );
    }
    private function assertNotEmptyList()
    {
        $this->assertNotCount(
            0,
            $this->responseJson,
            'The list should not be empty'
        );
    }

    protected function assertArrayHasKeys(
        array $needles,
        array $haystack
    ) {
        foreach ($needles as $oneNeedle) {
            $this->assertArrayHasKey(
                $oneNeedle,
                $haystack,
                'should have property: '.$oneNeedle
            );
        }
    }

    private function assertPermissionError()
    {
        $this->assertJsonResponse($this->response, 401, false);
    }

    private function assertPermissionDenied()
    {
        $this->assertJsonResponse($this->response, 403, false);
    }

    private function assertNotFoundError()
    {
        $this->assertJsonResponse($this->response, 404, false);
    }

    private function assertResponseUnprocessableEntity()
    {
        $this->assertJsonResponse($this->response, 422, false);
    }

    private function assertNoContentResponse()
    {
        $this->assertJsonResponse($this->response, 204, false);
    }

    private function assertBadRequestError()
    {
        $this->assertJsonResponse($this->response, 400, false);
    }

    private function assertCreatedSuccess()
    {
        $this->assertJsonResponse($this->response, 201, false);
    }

    private function assertOkSuccess()
    {
        $this->assertJsonResponse($this->response, 200, false);
    }

    private function assertOutputIsImage(File $image)
    {
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->expectOutputString(
            file_get_contents($image),
            'defaults to default image'
        );
        $this->assertEquals(
            $image->getMimeType(),
            $this->response->headers->get('content-type')
        );
    }
}
