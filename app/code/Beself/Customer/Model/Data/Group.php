<?php

namespace Beself\Customer\Model\Data;

use Beself\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Data\Group as MagentoGroup;

class Group extends MagentoGroup implements GroupInterface
{
    /**
     * @inheritDoc
     */
    public function getIsDistributor()
    {
        return $this->_get('is_distributor');
    }

    /**
     * @inheritDoc
     */
    public function setIsDistributor($isDistributor)
    {
        return $this->setData('is_distributor', $isDistributor);
    }
}
