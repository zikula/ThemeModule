<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine;

use InvalidArgumentException;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\RouterInterface;
use function Symfony\Component\String\s;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ThemeModule\Engine\Exception\AssetNotFoundException;

/**
 * Class Asset
 *
 * This class locates assets accounting for possible overrides in public/overrides/$bundleName or in the
 * active theme. It is foremost used by the zasset() Twig template plugin, but can be utilized as a standalone
 * service as well. All asset types (js, css, images) will work.
 *
 * Asset paths must begin with `@` in order to be processed (and possibly overridden) by this class.
 * Assets that do not contain `@` are passed through to the standard symfony asset management.
 *
 * Overrides are in this order:
 *  1) public/overrides/$bundleName/*
 *  2) public/themes/$theme/$bundleName/*
 *  3) public/modules/$bundleName/*
 */
class Asset
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var Packages
     */
    private $assetPackages;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var Engine
     */
    private $themeEngine;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        Packages $assetPackages,
        RouterInterface $router,
        Filesystem $fileSystem,
        Engine $themeEngine
    ) {
        $this->kernel = $kernel;
        $this->assetPackages = $assetPackages;
        $this->router = $router;
        $this->fileSystem = $fileSystem;
        $this->themeEngine = $themeEngine;
    }

    /**
     * Returns path for asset.
     * Confirms actual file existence before returning path
     */
    public function resolve(string $path): string
    {
        $publicDir = str_replace('\\', '/', $this->kernel->getProjectDir()) . '/public';
        $basePath = $this->router->getContext()->getBaseUrl();
        $httpRootDir = str_replace($basePath, '', $publicDir);

        // return immediately for straight asset paths
        $path = s($path);
        if (!$path->startsWith('@')) {
            $path = $path->trimStart('/');
            $publicPath = $this->assetPackages->getUrl($path->toString());
            if (false !== realpath($httpRootDir . $publicPath)) {
                return $publicPath;
            }
            throw new AssetNotFoundException(sprintf('Could not find asset "%s"', $httpRootDir . $publicPath));
        }

        [$bundleName, $relativeAssetPath] = explode(':', $path->toString());

        $bundleNameForAssetPath = s($bundleName)->trimStart('@')->lower()->toString();
        $bundleAssetPath = $this->getBundleAssetPath($bundleName);
        $themeName = $this->themeEngine->getTheme()->getName();

        $foldersToCheck = [
            // public override path (e.g. public/overrides/zikulacontentmodule)
            'overrides/' . $bundleNameForAssetPath,
            // public theme path (e.g. public/themes/zikuladefaulttheme/zikulacontentmodule)
            'themes/' . mb_strtolower($themeName) . '/' . $bundleNameForAssetPath,
            // public bundle directory (e.g. public/modules/zikulacontent)
            $bundleAssetPath
        ];

        foreach ($foldersToCheck as $folder) {
            $fullPath = $publicDir . '/' . $folder . '/' . $relativeAssetPath;
            if (false !== realpath($fullPath)) {
                return str_replace($httpRootDir, '', $fullPath);
            }
        }

        // asset not found in public/.
        // copy the asset from the bundle directory to /public
        // and then locate it in the bundle's normal public directory
        $fullPath = $this->kernel->locateResource($bundleName . '/Resources/public/' . $relativeAssetPath);
        $this->fileSystem->copy($fullPath, $publicDir . '/' . $bundleAssetPath . '/' . $relativeAssetPath);

        return $this->assetPackages->getUrl($bundleAssetPath . '/' . $relativeAssetPath);
    }

    /**
     * Maps and returns zasset base path.
     * e.g. "@AcmeNewsModule" to `modules/acmenews`
     * e.g. "@AcmeCustomTheme" to `themes/acmecustom`
     * e.g. "@SomeBundle" to `bundles/some`
     */
    private function getBundleAssetPath(?string $bundleName): string
    {
        if (!isset($bundleName)) {
            throw new InvalidArgumentException('No bundle name resolved, must be like "@AcmeBundle"');
        }
        $bundle = $this->kernel->getBundle(s($bundleName)->trimStart('@')->toString());
        if (!$bundle instanceof Bundle) {
            throw new InvalidArgumentException('Bundle ' . $bundleName . ' not found.');
        }

        if ($bundle instanceof AbstractExtension) {
            return $bundle->getRelativeAssetPath();
        }

        return 'bundles/' . s($bundle->getName())->lower()->beforeLast('bundle');
    }
}
