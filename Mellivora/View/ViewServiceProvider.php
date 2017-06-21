<?php

namespace Mellivora\View;

use Mellivora\Application\ServiceProvider;
use Mellivora\View\Compilers\BladeCompiler;
use Mellivora\View\Engines\CompilerEngine;
use Mellivora\View\Engines\EngineResolver;
use Mellivora\View\Engines\FileEngine;
use Mellivora\View\Engines\PhpEngine;
use Mellivora\View\Factory;
use Mellivora\View\FileViewFinder;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFactory();
        $this->registerViewFinder();
        $this->registerEngineResolver();
    }

    /**
     * Register the view environment.
     *
     * @return void
     */
    public function registerFactory()
    {
        $this->container['view'] = function ($container) {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
            $resolver = $container['view.engine.resolver'];

            $finder = $container['view.finder'];

            $view = new Factory($resolver, $finder);

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $view->setContainer($container);

            $view->share('container', $container);

            return $view;
        };
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->container['view.finder'] = function ($container) {
            return new FileViewFinder(
                $container['config']->get('view.paths')->toArray());
        };
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        $this->container['view.engine.resolver'] = function () {
            $resolver = new EngineResolver;

            // Next, we will register the various view engines with the resolver so that the
            // environment will resolve the engines needed for various views based on the
            // extension of view file. We call a method for each of the view's engines.
            foreach (['file', 'php', 'blade'] as $engine) {
                $this->{'register' . ucfirst($engine) . 'Engine'}($resolver);
            }

            return $resolver;
        };
    }

    /**
     * Register the file engine implementation.
     *
     * @param  \Mellivora\View\Engines\EngineResolver $resolver
     * @return void
     */
    public function registerFileEngine($resolver)
    {
        $resolver->register('file', function () {
            return new FileEngine;
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param  \Mellivora\View\Engines\EngineResolver $resolver
     * @return void
     */
    public function registerPhpEngine($resolver)
    {
        $resolver->register('php', function () {
            return new PhpEngine;
        });
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param  \Mellivora\View\Engines\EngineResolver $resolver
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $this->container['blade.compiler'] = function ($container) {
            return new BladeCompiler($container['config']->get('view.compiled'));
        };

        $resolver->register('blade', function () {
            return new CompilerEngine($this->container['blade.compiler']);
        });
    }
}
