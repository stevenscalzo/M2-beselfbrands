<?php

namespace Beself\Customer\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class PreferredProductType extends AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '', 'label' => __('-- Please Select --')],
                ['value' => 'cardio', 'label' => __('Material de cardio')],
                ['value' => 'musculacion', 'label' => __('Material de musculación')],
                ['value' => 'yoga_pilates', 'label' => __('Yoga y pilates')]
            ];
        }
        return $this->_options;
    }
}
