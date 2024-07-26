<?php

namespace Beself\Customer\Api\Data;

interface GroupInterface extends \Magento\Customer\Api\Data\GroupInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return string|null
     */
    public function getIsDistributor();

    /**
     *
     *
     * @param string $isDistributor
     * @return string|null
     */
    public function setIsDistributor($isDistributor);
}
