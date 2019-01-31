<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Util;

use Doctrine\ORM\Query;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;

/**
 * Trait LocaleAwareRepositoryTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait LocaleAwareRepositoryTrait
{
    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;


    /**
     * Sets the locale provider.
     *
     * @param LocaleProviderInterface $provider
     *
     * @return $this
     */
    public function setLocaleProvider(LocaleProviderInterface $provider)
    {
        $this->localeProvider = $provider;

        return $this;
    }

    /**
     * Returns the current/fallback locale condition.
     *
     * @param string $alias
     *
     * @return Query\Expr\Base|Query\Expr\Comparison
     */
    protected function getLocaleCondition($alias = 'translation')
    {
        if (!$this->localeProvider) {
            return null;
        }

        $expr = new Query\Expr();

        // TODO This may change between master/sub requests
        $current = $this->localeProvider->getCurrentLocale();
        $fallback = $this->localeProvider->getFallbackLocale();

        if ($current != $fallback) {
            return $expr->orX(
                $expr->eq($alias . '.locale', $expr->literal($this->localeProvider->getCurrentLocale())),
                $expr->eq($alias . '.locale', $expr->literal($this->localeProvider->getFallbackLocale()))
            );
        }

        return $expr->eq($alias . '.locale', $expr->literal($this->localeProvider->getCurrentLocale()));
    }
}
