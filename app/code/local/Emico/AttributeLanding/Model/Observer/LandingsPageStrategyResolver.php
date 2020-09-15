<?php

/**
 * @author : Edwin Jacobs, email: ejacobs@emico.nl.
 * @copyright : Copyright Emico B.V. 2020.
 */

/**
 * Class Emico_AttributeLanding_Model_Observer_LandingsPageStrategyResolver
 */
class Emico_AttributeLanding_Model_Observer_LandingsPageStrategyResolver
{
    /**
     * Sadly there is no hook in strategy registration to skip registration hence the artificial solution
     * @see Emico_Tweakwise_Helper_UriStrategy::getActiveStrategies()
     *
     * @param Varien_Event_Observer $observer
     */
    public function disableIncorrectAttributeLandingStrategy(Varien_Event_Observer $observer)
    {
        /** @var Emico_Tweakwise_Model_UrlBuilder_Strategy_StrategyInterface $strategy */
        $strategy = $observer->getData('strategy');
        // If tweakwise has query param strategy then disable the attributelanding path slug strategy
        $tweakwiseStrategy = $this->getConfiguredTweakwiseStrategy();
        if ($tweakwiseStrategy === 'queryParam'
            && $strategy instanceof Emico_AttributeLanding_Model_Tweakwise_UrlStrategy_AttributeLandingPathSlugStrategy
        ) {
            $strategy->setStrategyAllowed(false);
        }

        // If tweakwise has path strategy then disable the AttributeLandingStrategy (this works only for query params)
        if ($tweakwiseStrategy === 'path'
            && $strategy instanceof Emico_AttributeLanding_Model_Tweakwise_UrlStrategy_AttributeLandingStrategy
        ) {
            $strategy->setStrategyAllowed(false);
        }

        $routeName = Mage::app()->getRequest()->getRouteName();
        if ($tweakwiseStrategy === 'path'
            && $strategy instanceof Emico_Tweakwise_Model_UrlBuilder_Strategy_PathStrategy
            && $routeName !== 'catalogsearch'
        ) {
            $strategy->setIsAllowedInCurrentContext(true);
        }
    }

    /**
     * @return Emico_Tweakwise_Model_UrlBuilder_Strategy_StrategyInterface
     */
    protected function getConfiguredTweakwiseStrategy()
    {
        return Mage::getStoreConfig('emico_tweakwise/navigation/uri_strategy');
    }
}
