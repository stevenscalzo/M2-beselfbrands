<?php

namespace Beself\Customer\Plugin;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupRegistry;
use Magento\Framework\Exception\NoSuchEntityException;

class GroupRepositoryPlugin
{
    /**
     * @param GroupRegistry $groupRegistry
     */
    public function __construct(
        GroupRegistry               $groupRegistry
    )
    {
        $this->groupRegistry = $groupRegistry;
    }

    /**
     * @param GroupRepositoryInterface $subject
     * @param $result
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGetById(GroupRepositoryInterface $subject, $result)
    {
        $groupModel = $this->groupRegistry->retrieve($result->getId());
        $result->setIsDistributor($groupModel->getIsDistributor());

        return $result;
    }
}
