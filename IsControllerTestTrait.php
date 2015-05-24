<?php

namespace AllanSimon\TestHelpers;

trait IsControllerTestTrait
{
    use ClosesConnectionsAfterTestTrait;
    use ReflectsAndCleansPropertiesAfterTestTrait;

    private $client;
    private $crawler;

    public function setUp()
    {
        $this->client = static::createClient();

        $fixtureExecutor = $this->loadFixtures($this->fixturelist);
        $this->em = $fixtureExecutor->getObjectManager();
        $this->fixtures = $fixtureExecutor->getReferenceRepository();
    }

    private function openPage($page)
    {
        $this->crawler = $this->client->request('GET', $page);
    }

    private function assertPageOpenedSuccessfully()
    {
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            sprintf(
                'Unexpected HTTP status code %d',
                $this->client->getResponse()->getStatusCode()
            )
        );
    }

    private function assertContainsRecordPropertiesBlock()
    {
        $this->assertEquals(1, $this->crawler->filter('.record_properties')->count());
    }

    private function getFirstElementByTestName($name)
    {
        return $this->crawler
            ->filter("[data-for-test-name='$name']")
            ->eq(0);
    }

    private function assertFormRedirect()
    {
        // we check that we got a redirection
        $this->assertEquals(
            302,
            $this->client->getResponse()->getStatusCode(),
            'The page is not successfully redirect.'
        );
    }

    private function clickFirstLink($name)
    {
        $link = $this->getFirstElementByTestName($name)->link();
        $this->crawler = $this->client->click($link);
    }

    private function followRedirect()
    {
        $this->crawler = $this->client->followRedirect();
    }
}
