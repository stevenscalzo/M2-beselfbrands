<?php

namespace Beself\Customer\Api\Data;

interface GroupInterface extends \Magento\Customer\Api\Data\GroupInterface
{
    /**
     * Returns if the group is marked as a distributor.
     *
     * @return string|null
     */
    public function getIsDistributor();

    /**
     * Mark the type of distributor
     *
     * @param string $isDistributor
     * @return string|null
     */
    public function setIsDistributor($isDistributor);
}
