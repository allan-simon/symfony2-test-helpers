<?php

namespace AllanSimon\TestHelpers;

trait ClosesConnectionsAfterTestTrait
{
    public static $dbConnections = [];

    /** @afterClass */
    public static function ensureDbConnectionsGetClosed()
    {
        while ($conn = array_pop(self::$dbConnections)) {
            $conn->close();
        }
    }

    /** @before */
    public function saveDbConnectionInClass()
    {
        self::$dbConnections[] = $this->getContainer()->get('doctrine')->getConnection();
        if (property_exists($this, 'client')) {
            self::$dbConnections[] = $this->client->getKernel()->getContainer()->get('doctrine')->getConnection();
        }
    }
}
