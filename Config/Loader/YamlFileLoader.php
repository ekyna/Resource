<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Loader;

use Ekyna\Component\Resource\Exception\ConfigurationException;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

use function array_merge;
use function array_replace;
use function class_exists;
use function dirname;
use function file_exists;
use function file_get_contents;
use function in_array;
use function is_array;
use function is_null;
use function is_string;
use function pathinfo;
use function stream_is_local;

/**
 * Class YamlFileLoader
 * @package Ekyna\Component\Resource\Config\Loader
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class YamlFileLoader extends FileLoader
{
    private ?Parser $parser = null;


    /**
     * @inheritDoc
     */
    public function load($resource, $type = null): void
    {
        $path = $this->locator->locate($resource);

        $content = $this->loadFile($path);

        $this->loader->addFile(new FileResource($path));

        // Empty file
        if (null === $content) {
            return;
        }

        // Imports
        $this->parseImports($content, $path);

        // Extensions
        $this->parseExtensions($content, $path);

        // Permissions configurations
        $this->parsePermissions($content, $resource);

        // Namespaces configurations
        $this->parseNamespaces($content, $resource);

        // Actions configurations
        $this->parseActions($content, $resource);

        // Behaviors configurations
        $this->parseBehaviors($content, $resource);

        // Resources configurations
        $this->parseResources($content, $resource);
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null): bool
    {
        return is_string($resource)
            && in_array(pathinfo($resource, PATHINFO_EXTENSION), ['yml', 'yaml'], true)
            && (!$type || 'yaml' === $type);
    }

    /**
     * Parses all imports.
     *
     * @param array  $content
     * @param string $file
     *
     * @throws ConfigurationException
     */
    private function parseImports(array $content, string $file): void
    {
        if (!isset($content['imports'])) {
            return;
        }

        if (!is_array($content['imports'])) {
            throw new ConfigurationException(
                "The 'imports' key should contain an array in $file. Check your YAML syntax."
            );
        }

        $defaultDirectory = dirname($file);
        foreach ($content['imports'] as $import) {
            $this->setCurrentDir($defaultDirectory);

            $ignoreErrors = isset($import['ignore_errors']) && $import['ignore_errors'];

            try {
                $this->import($import, null, $ignoreErrors, $file);
            } catch (LoaderLoadException $exception) {
                throw new ConfigurationException("Failed to parse $file", 0, $exception);
            }
        }
    }

    /**
     * Parses all extensions.
     *
     * @param array  $content
     * @param string $file
     *
     * @throws ConfigurationException
     */
    private function parseExtensions(array $content, string $file): void
    {
        if (!isset($content['extensions'])) {
            return;
        }

        if (!is_array($content['extensions'])) {
            throw new ConfigurationException(
                "The 'extensions' key should contain an array in $file. Check your YAML syntax."
            );
        }

        foreach ($content['extensions'] as $extension) {
            if (!is_string($extension)) {
                throw new ConfigurationException(
                    "The 'extensions' key should contain an array of string in $file. Check your YAML syntax."
                );
            }

            $this->loader->addExtension($extension);
        }
    }

    /**
     * Parses all permissions definitions.
     *
     * @param array  $content
     * @param string $file
     *
     * @throws ConfigurationException
     */
    private function parsePermissions(array $content, string $file): void
    {
        if (!isset($content['permissions'])) {
            return;
        }

        if (!is_array($content['permissions'])) {
            throw new ConfigurationException(
                "The 'permissions' key should contain an array in $file. Check your YAML syntax."
            );
        }

        foreach ($content['permissions'] as $name => $options) {
            if (!is_array($options)) {
                throw new ConfigurationException(
                    "The 'permissions.$name' key should contain an array in $file. Check your YAML syntax."
                );
            }

            $this->loader->addPermission($name, array_merge($options, [
                'name' => $name,
            ]));
        }
    }

    /**
     * Parses all namespaces definitions.
     *
     * @param array  $content
     * @param string $file
     *
     * @throws ConfigurationException
     */
    private function parseNamespaces(array $content, string $file): void
    {
        if (!isset($content['namespaces'])) {
            return;
        }

        if (!is_array($content['namespaces'])) {
            throw new ConfigurationException(
                "The 'namespaces' key should contain an array in $file. Check your YAML syntax."
            );
        }

        foreach ($content['namespaces'] as $name => $options) {
            if (!is_array($options)) {
                throw new ConfigurationException(
                    "The 'namespaces.$name' key should contain an array in $file. Check your YAML syntax."
                );
            }

            $this->loader->addNamespace($name, array_merge($options, [
                'name' => $name,
            ]));
        }
    }

    /**
     * Parses all actions definitions.
     *
     * @param array  $content
     * @param string $file
     *
     * @throws ConfigurationException
     */
    private function parseActions(array $content, string $file): void
    {
        if (!isset($content['actions'])) {
            return;
        }

        if (!is_array($content['actions'])) {
            throw new ConfigurationException(
                "The 'actions' key should contain an array in file $file. Check your YAML syntax."
            );
        }

        foreach ($content['actions'] as $name => $options) {
            if (is_null($options)) {
                $options = [];
            }

            if (!is_array($options)) {
                throw new ConfigurationException(
                    "Expected '$name' action config as array in file $file. Check your YAML syntax."
                );
            }

            if (class_exists($name)) {
                if (!isset($options['class'])) {
                    $options['class'] = $name;
                }
            } elseif (!isset($options['name'])) {
                $options['name'] = $name;
            }

            $this->loader->addAction($name, $options);
        }
    }

    /**
     * Parses all behaviors definitions.
     *
     * @param array  $content
     * @param string $file
     *
     * @throws ConfigurationException
     */
    private function parseBehaviors(array $content, string $file): void
    {
        if (!isset($content['behaviors'])) {
            return;
        }

        if (!is_array($content['behaviors'])) {
            throw new ConfigurationException(
                "The 'behaviors' key should contain an array in $file. Check your YAML syntax."
            );
        }

        foreach ($content['behaviors'] as $name => $options) {
            if (is_null($options)) {
                $options = [];
            }

            if (!is_array($options)) {
                throw new ConfigurationException(
                    "Expected '$name' behavior config as array in file $file. Check your YAML syntax."
                );
            }

            if (class_exists($name)) {
                if (!isset($options['class'])) {
                    $options['class'] = $name;
                }
            } elseif (!isset($options['name'])) {
                $options['name'] = $name;
            }

            $this->loader->addBehavior($name, $options);
        }
    }

    /**
     * Parses all resources definitions.
     *
     * @param array  $content
     * @param string $file
     *
     * @throws ConfigurationException
     */
    private function parseResources(array $content, string $file)
    {
        if (!isset($content['resources'])) {
            return;
        }

        if (!is_array($content['resources'])) {
            throw new ConfigurationException(
                "The 'resources' key should contain an array in $file. Check your YAML syntax."
            );
        }

        foreach ($content['resources'] as $namespace => $definitions) {
            if (!is_array($definitions)) {
                throw new ConfigurationException(
                    "The 'resources.$namespace' key should contain an array in $file. Check your YAML syntax."
                );
            }

            foreach ($definitions as $name => $options) {
                if (!is_array($options)) {
                    throw new ConfigurationException(
                        "The 'resources.$namespace.$name' key should contain an array in $file. Check your YAML syntax."
                    );
                }

                $this->loader->addResource($namespace . '.' . $name, array_replace($options, [
                    'namespace' => $namespace,
                    'name'      => $name,
                ]));
            }
        }
    }

    /**
     * Loads a YAML file.
     *
     * @param string $file
     *
     * @return array The file content
     *
     * @throws InvalidArgumentException When the given file is not a local file,
     *                                  when it does not exist or when it is not valid.
     */
    private function loadFile(string $file): ?array
    {
        if (!class_exists('Symfony\Component\Yaml\Parser')) {
            throw new RuntimeException('Unable to load YAML config files as the Symfony Yaml Component is not installed.');
        }

        if (!stream_is_local($file)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('The service file "%s" is not valid.', $file));
        }

        if (null === $this->parser) {
            $this->parser = new Parser();
        }

        try {
            $config = $this->parser->parse(file_get_contents($file));
        } catch (ParseException $exception) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $file), 0, $exception);
        }

        // empty file
        if (null === $config) {
            return null;
        }

        // not an array
        if (!is_array($config)) {
            throw new InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $file));
        }

        return $config;
    }
}
