<?php

namespace Beself\Customer\Block\Account;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class AdditionalInfo extends Template
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param GroupRepositoryInterface $groupRepository
     * @param array $data
     */
    public function __construct(
        Context                  $context,
        CustomerSession          $customerSession,
        GroupRepositoryInterface $groupRepository,
        array                    $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        parent::__construct($context, $data);
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getAdditionalInfo()
    {
        $customer = $this->customerSession->getCustomer();
        $groupId = $customer->getGroupId();
        $group = $this->groupRepository->getById($groupId);

        $preferredProductType = $customer->getPreferredProductType();
        $isDistributor = $group->getIsDistributor();

        $info = [];

        $info['distributor'] = $isDistributor ? __('You are included in our B2B program.') :
            __('You are not included in our B2B program');

        $info['type'] = [
            'hasType' => (bool)$preferredProductType,
            'text' => $preferredProductType? __('Your favorite type of product is:') : __('You don\'t have a preferred product type'),
            'type' => $preferredProductType
        ];

        return $info;
    }
}
