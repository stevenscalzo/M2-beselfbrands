<?php

namespace Beself\Customer\Plugin;

class DataObjectProcessorPlugin
{
    /**
     * Change the type of customer group to the new one with more attributes
     *
     * @param $subject
     * @param $dataObject
     * @param $dataObjectType
     * @return array
     */
    public function beforeBuildOutputDataArray($subject, $dataObject, $dataObjectType)
    {
        if (is_a($dataObjectType, \Magento\Customer\Api\Data\GroupInterface::class, true)) {
            $dataObjectType = \Beself\Customer\Api\Data\GroupInterface::class;
        }

        return [$dataObject, $dataObjectType];
    }
}
