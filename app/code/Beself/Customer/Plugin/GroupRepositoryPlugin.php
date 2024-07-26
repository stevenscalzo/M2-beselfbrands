<?php

namespace Beself\Customer\Plugin;

use Beself\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\GroupRegistry;
use Magento\Customer\Model\ResourceModel\Group;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;

class GroupRepositoryPlugin
{
    /**
     * @var GroupRegistry
     */
    protected $groupRegistry;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * @var GroupInterfaceFactory
     */
    protected $groupDataFactory;

    /**
     * @var Group
     */
    protected $groupResourceModel;

    /**
     * @var TaxClassRepositoryInterface
     */
    private $taxClassRepository;

    /**
     * @param GroupRegistry $groupRegistry
     */
    public function __construct(
        GroupRegistry               $groupRegistry,
        DataObjectProcessor         $dataObjectProcessor,
        GroupFactory                $groupFactory,
        Group                       $groupResourceModel,
        GroupInterfaceFactory       $groupDataFactory,
        TaxClassRepositoryInterface $taxClassRepositoryInterface
    )
    {
        $this->groupRegistry = $groupRegistry;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResourceModel;
        $this->groupDataFactory = $groupDataFactory;
        $this->taxClassRepository = $taxClassRepositoryInterface;
    }

    public function afterGetById(GroupRepositoryInterface $subject, $result)
    {
        $groupModel = $this->groupRegistry->retrieve($result->getId());
        $result->setIsDistributor($groupModel->getIsDistributor());

        return $result;
    }

    /**
     * @throws \Zend_Validate_Exception
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws InvalidTransitionException
     */
    public function aroundSave(GroupRepositoryInterface $subject, callable $proceed, $group)
    {
        $this->_validate($group);

        $groupModel = null;
        if ($group->getId() || (string)$group->getId() === '0') {
            $this->_verifyTaxClassModel($group->getTaxClassId(), $group);
            $groupModel = $this->groupRegistry->retrieve($group->getId());
            $groupDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
                $group,
                GroupInterface::class
            );
            foreach ($groupDataAttributes as $attributeCode => $attributeData) {
                $groupModel->setDataUsingMethod($attributeCode, $attributeData);
            }
        } else {
            $groupModel = $this->groupFactory->create();
            $groupModel->setCode($group->getCode());

            $taxClassId = $group->getTaxClassId() ?: $subject::DEFAULT_TAX_CLASS_ID;
            $this->_verifyTaxClassModel($taxClassId, $group);
            $groupModel->setTaxClassId($taxClassId);
        }

        try {
            $this->groupResourceModel->save($groupModel);
        } catch (LocalizedException $e) {
            /**
             * Would like a better way to determine this error condition but
             *  difficult to do without imposing more database calls
             */
            if ($e->getMessage() == (string)__('Customer Group already exists.')) {
                throw new InvalidTransitionException(__('Customer Group already exists.'));
            }
            throw $e;
        }

        $this->groupRegistry->remove($groupModel->getId());

        $groupDataObject = $this->groupDataFactory->create()
            ->setId($groupModel->getId())
            ->setCode($groupModel->getCode())
            ->setTaxClassId($groupModel->getTaxClassId())
            ->setTaxClassName($groupModel->getTaxClassName())
            ->setIsDistributor($groupModel->getIsDistributor());

        if ($group->getExtensionAttributes()) {
            $groupDataObject->setExtensionAttributes($group->getExtensionAttributes());
        }

        return $groupDataObject;
    }

    /**
     * Validate group values.
     *
     * @param GroupInterface $group
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Zend_Validate_Exception
     * @throws InputException
     */
    private function _validate($group)
    {
        $exception = new InputException();
        if (!\Zend_Validate::is($group->getCode(), 'NotEmpty')) {
            $exception->addError(__('"%fieldName" is required. Enter and try again.', ['fieldName' => 'code']));
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }

    /**
     * Verifies that the tax class model exists and is a customer tax class type.
     *
     * @param int $taxClassId The id of the tax class model to check
     * @param GroupInterface $group The original group parameters
     * @return void
     * @throws InputException Thrown if the tax class model is invalid
     */
    protected function _verifyTaxClassModel($taxClassId, $group)
    {
        try {
            /* @var TaxClassInterface $taxClassData */
            $taxClassData = $this->taxClassRepository->get($taxClassId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw InputException::invalidFieldValue('taxClassId', $group->getTaxClassId());
        }
        if ($taxClassData->getClassType() !== TaxClassManagementInterface::TYPE_CUSTOMER) {
            throw InputException::invalidFieldValue('taxClassId', $group->getTaxClassId());
        }
    }
}
