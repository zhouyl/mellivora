<?php

namespace Mellivora\Translation;

use Mellivora\Support\Arr;
use Mellivora\Support\ServiceProvider;

class TranslatorServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['translator'] = function ($container) {
            $config = $container['config']->get('translator');

            $translator = new Translator(Arr::convert($config->paths));

            foreach (Arr::convert($config->aliases) as $key => $value) {
                $translator->setAlias($key, $value);
            }

            $translator->import(Arr::convert($config->required));
            $translator->setDefault(value($config->default));

            return $translator;
        };
    }
}
