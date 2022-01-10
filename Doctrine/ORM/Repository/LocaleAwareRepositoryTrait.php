<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Repository;

use Doctrine\ORM\Query;
use Ekyna\Component\Resource\Locale\LocaleProviderAwareTrait;

/**
 * Trait LocaleAwareRepositoryTrait
 * @package Ekyna\Component\Resource\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait LocaleAwareRepositoryTrait
{
    use LocaleProviderAwareTrait;

    /**
     * Returns the current/fallback locale condition.
     *
     * @param string $alias
     *
     * @return Query\Expr\Base|Query\Expr\Comparison|null
     */
    protected function getLocaleCondition(string $alias = 'translation'): ?object
    {
        $provider = $this->getLocaleProvider();

        $expr = new Query\Expr();

        $current = $provider->getCurrentLocale();
        $fallback = $provider->getFallbackLocale();

        if ($current !== $fallback) {
            return $expr->orX(
                $expr->eq($alias . '.locale', $expr->literal($current)),
                $expr->eq($alias . '.locale', $expr->literal($fallback))
            );
        }

        return $expr->eq($alias . '.locale', $expr->literal($current));
    }
}
