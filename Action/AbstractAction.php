<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Action;

use Ekyna\Component\Resource\Config\ActionConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractAction
 * @package Ekyna\Component\Resource\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAction implements ActionInterface
{
    protected ActionConfig $config;
    protected Request      $request;
    protected Context      $context;
    protected array        $options;


    /**
     * @inheritDoc
     */
    public function setConfig(ActionConfig $config): ActionInterface
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRequest(Request $request): ActionInterface
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setContext(Context $context): ActionInterface
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOptions(array $options): ActionInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Executes this action.
     *
     * @return Response
     */
    abstract public function __invoke(): Response;

    /**
     * @inheritDoc
     */
    public static function configureOptions(OptionsResolver $resolver): void
    {
    }
}
