## Symfony2 functionnal test helpers

This library offer you a set of Traits to ease the creation of
functionnal tests for both REST api and normal HTML controllers of
your symfony2 application. It also take care as much as possible
to close the database connections after the tests are done (which
by default phpunit seems not to do)


## Installation

add in your `composer.json` :

``` json
    "require-dev": {
        ...
        "allan-simon/functionnal-test-helpers": "*",
        "liip/functional-test-bundle": "~1.0",
        ...
    }
```

Then the classes are accessible from the namespace `AllanSimon\TestHelpers`

## Documentation:

A more complete documentation is to come, waiting for that one can directly check
the code, which has been made to be as clear as possible, if you got any specific
question, you can open a ticket, we're quite fast to answer.


### For ApiHelpersTrait

For the moment the Api go with the assumption that except for binary data (images etc.), you want to
send and receive JSON encoded data

#### Perform requests

all these methods perform the request using `$this->client` and it's your responsability
(for the moment) to create it to fit your needs

Once the Request is performed, the raw response is assigned in `$this->response` if the content
is json, the decoded content will in `$this->responseJson`

  * `performGET(string $uri)` GET request to $uri
  * `performDELETE($string $uri)` DELETE request to $uri
  * `performPOSt(string $uri, array $data)` json_encode the $data and POST it to the URI
  * `performPUT(string $uri, array $data)` json_encode the $data and PUT it to the URI
  * `performPATCH(string $uri, array $data)` json_encode the $data and PATCH it to the URI

#### Methods to play with data fixtures

  * `given(string $fixtureName)` , load the entity referenced by $fixtureName and set it in $this->entity
  * `refreshEntiy()`,  resync/refresh the entity in `$this->entity` with the database

#### Assert HTTP status code
`
  * `assertBadRequestError()` => 400
  * `assertPermissionError()` => 401
  * `assertPermissionDenied()` => 403
  * `assertNotFoundError()` => 404
  * `assertResponseUnprocessableEntity()` => 422

  * `assertOkSuccess()` => 200
  * `assertCreatedSuccess()` => 201
  * `assertNoContentResponse()` => 203
`
#### Assert JSON returned

all these assets use the property `$this->responseJson`, which is populated by

 * `assertEmptyList` , check the json returned by a `perform*` is a json Array with 0 element
 * `assertNotEmptyList` , check the json returned by a `perform*`is a json Array with 1+ element

## Usage For REST Api

```php
<?php

namespace YourBundle\Tests\Controller;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class AnalystControllerTest extends WebTestCase
{
    use ApiHelpersTrait;

    private static $ANALYST_FIELDS = [
        'id',
        'title',
        'job_title',
        'biography',
        'registration_code',
        'videos',
        'subscribed',
        'users_subscribed',
    ];

    private $analyst = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testGetAnalystsWithoutSortReturnBadParameter()
    {
        $this->performGetAnalystsWithoutSort();
        $this->assertBadRequestError();
    }
    
    public function testGetAnalystsWithdValidSortReturnListAnalysts()
    {
        //TODO replace by phpunit stuff to feed with data
        foreach (['hot', 'recommended', 'newest'] as $validSort) {
            $this->performGetAnalystsWithSort($validSort);
            $this->assertOkSuccess();
            $this->assertArrayHasKeys(
                self::$ANALYST_FIELDS,
                $this->responseJson[0]
            );
        }
    }

    // conveniency functions
    private function performGetAnalystsWithoutSort()
    {
        $this->response = $this->performClientRequest(
            'GET',
            '/api/analysts'
        );
        $this->assignJsonFromResponse();
    }




    private function performGetAnalystsWithSort($sort)
    {
        $this->response = $this->performClientRequest(
            'GET',
            '/api/analysts?sort='.$sort
        );
        $this->assignJsonFromResponse();
    }
}

```


### Example

## Usage For normal Controllers

```
<?php

namespace YourBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use AllanSimon\TestHelpers\IsControllerTestTrait;

class CommentsControllerTest extends WebTestCase
{
    use IsControllerTestTrait;

    const COMMENT_OF_VIDEO_PAGE = '/backend/comments/of-video/';

    private $video;

    // if you don't use any fixtures declare this array as empty
    // in latter version it will not be needed to declare it if not used
    protected $fixturelist = [
        'YourBundle\DataFixtures\ORM\LoadCommentData',
        'YourBundle\DataFixtures\ORM\LoadBackendCommentData',
    ];

    public function testOpenCommentOfVideoPageShouldHaveAlistOfComment()
    {
        $this->givenVideo('commented-video');
        $this->openCommentOfVideoPage();
        $this->assertPageOpenedSuccessfully();

        $this->assertListofCommentsPresents();
    }

    // conveniency methods

    private function givenVideo($fixturesName)
    {
        $video = $this->fixtures->getReference($fixturesName);
        $this->video = $video;
    }

    private function openCommentOfVideoPage()
    {
        $id = $this->video->getId();
        $this->openPage(self::COMMENT_OF_VIDEO_PAGE."$id");
    }


    // assert

    private function assertListofCommentsPresents()
    {
        $this->assertEquals(
            1,
            $this->getFirstElementByTestName('table-comments-list')->count(),
            'The table containing the list of comments was not found.'
        );

        $this->assertEquals(
            1,
            $this->getFirstElementByTestName('comments-list-record')->count(),
            'There is no comment listed.'
        );
    }
}

```


#### Note

The method `getFirstElementByTestName('example')` takes the first HTML tag with the attribute

`data-for-test-name="example"`

the goal being to put all identifier and selectors based on `data-for-test-*` attributes, the rationnal being
that this way you can modify without fear your `id` and `class` or `tag` types without breaking your tests
(and there's nothing more raging than to break tests because you refactorize the CSS)


## License

MIT


## Contributing

Contributions are warmly welcomed, be it

  * Feature requests
  * Bug reports
  * PR correcting one little typo
  * PR adding some functionnalities

if you would like to contribute but have no clue on how to do it
feel free to open an issue explaining what you want to do, and we
will try to guide you step by step.
