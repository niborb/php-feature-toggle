<?php

namespace Niborb\FeatureToggle;

use Niborb\FeatureToggle\DataProvider\ArrayDataProvider;
use Niborb\FeatureToggle\Entity\FeatureExpressionInterface;
use Niborb\FeatureToggle\Entity\FeatureInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class Toggle
 * @package Niborb\FeatureToggle
 */
class Toggle
{
    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var DataProvider
     */
    private $dataProvider;


    /**
     * @param ExpressionLanguage $expressionLanguage
     *
     * @return $this
     */
    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;

        return $this;
    }

    /**
     * @param FeatureInterface $feature
     *
     * @return $this
     */
    public function addFeature(FeatureInterface $feature)
    {
        $this->getDataProvider()->addFeature($feature);

        return $this;
    }

    /**
     * @param FeatureInterface $feature
     * @return bool
     */
    public function hasFeature(FeatureInterface $feature)
    {
        return null !== $this->getDataProvider()->fetchFeature($feature->getName());
    }

    /**
     * @param string $featureName
     * @param array $context
     *
     * @throws \RuntimeException if expression could not be validated
     *
     * @return bool
     */
    public function isEnabled($featureName, array $context = [])
    {
        $isEnabled = false;
        $feature = $this->getDataProvider()->fetchFeature($featureName);
        if (null !== $feature){
            $isEnabled = $feature->isEnabled();

            if ($isEnabled && $feature instanceof FeatureExpressionInterface) {
                $isEnabled = $this->validate($feature, $context);
            }
        }

        return $isEnabled;
    }

    /**
     * @param FeatureExpressionInterface $feature
     * @param array $context
     *
     * @throws \RuntimeException if expression could not be validated
     *
     * @return bool|string
     */
    private function validate(FeatureExpressionInterface $feature, array $context = [])
    {

        $expression = $feature->getExpression();
        if ('' !== $expression
            && $this->expressionLanguage instanceof ExpressionLanguage
        ) {
            try {
                return (bool) $this->expressionLanguage->evaluate($expression, $context);
            } catch(\Exception $e) {
                throw new \RuntimeException("Could not evaluate expression " . $expression, 0, $e);
            }

        }

        return true; // no expression to validate
    }

    /**
     * @return DataProvider
     */
    private function getDataProvider()
    {
        if (!$this->dataProvider) {
            $this->dataProvider = new ArrayDataProvider();
        }

        return $this->dataProvider;
    }


    /**
     * @param DataProvider $dataProvider
     *
     * @return $this
     */
    public function setDataProvider(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;

        return $this;
    }
}
