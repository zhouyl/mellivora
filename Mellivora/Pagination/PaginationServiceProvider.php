<?php

namespace Mellivora\Pagination;

use Mellivora\Support\ServiceProvider;

class PaginationServiceProvider extends ServiceProvider
{
    /**
     * Register a view file namespace.
     *
     * @param string $path
     * @param string $namespace
     *
     * @return void
     */
    protected function loadViewsFrom($path, $namespace)
    {
        if (is_dir($appPath = resource_path('views/vendor/' . $namespace))) {
            $this->container['view']->addNamespace($namespace, $appPath);
        }

        $this->container['view']->addNamespace($namespace, $path);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'pagination');

        Paginator::viewFactoryResolver(function () {
            return $this->container['view'];
        });

        Paginator::currentPathResolver(function () {
            return $this->container['request']->fullurl();
        });

        Paginator::currentPageResolver(function ($pageName = 'page') {
            $page = $this->container['request']->input($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return $page;
            }

            return 1;
        });
    }
}
