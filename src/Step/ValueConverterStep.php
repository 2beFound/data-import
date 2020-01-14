<?php

namespace Ddeboer\DataImport\Step;

use Ddeboer\DataImport\Step;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ValueConverterStep implements Step
{
    /**
     * @var array
     */
    private $converters = [];

    /**
     * @param string   $property
     * @param callable $converter
     *
     * @return $this
     */
    public function add($property, callable $converter)
    {
        $this->converters[$property][] = $converter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process(&$item)
    {
        if (is_array($item)) {
            foreach ($this->converters as $property => $converters) {
                foreach ($converters as $converter) {
                    if (array_key_exists($property, $item)) {
                        $orgValue = $item[$property];
                        $value = call_user_func($converter, $orgValue);
                        $item[$property] = $value;
                    }
                }
            }
        } elseif (is_object($item)) {
            $accessor = new PropertyAccessor();

            foreach ($this->converters as $property => $converters) {
                foreach ($converters as $converter) {
                    $orgValue = $accessor->getValue($item, $property);
                    $value = call_user_func($converter, $orgValue);
                    $accessor->setValue($item,$property,$value);
                }
            }
        } else {
            throw new \RuntimeException('neither object nor array, unable to process');
        }

        return true;
    }
}
