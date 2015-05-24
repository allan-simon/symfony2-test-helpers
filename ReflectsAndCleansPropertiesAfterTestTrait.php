<?php

namespace AllanSimon\TestHelpers;

trait ReflectsAndCleansPropertiesAfterTestTrait
{
    /** @after */
    public function cleanUpTestAndContainerProperties()
    {
        if (($container = $this->getContainer()) != null) {
            $refl = new \ReflectionObject($container);
            foreach ($refl->getProperties() as $prop) {
                $prop->setAccessible(true);
                $prop->setValue($container, null);
            }
        }

        $refl = new \ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic()) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }
}
