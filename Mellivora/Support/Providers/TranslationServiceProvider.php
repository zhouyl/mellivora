<?php

namespace Mellivora\Support\Providers;

use Mellivora\Support\Arr;
use Mellivora\Support\Providers\ServiceProvider;
use Mellivora\Translation\Translator;

class TranslationServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['translation'] = function ($container) {
            $config = $container['config']->get('translation');

            $translator = new Translator(Arr::convert($config->paths));

            foreach (Arr::convert($config->aliases) as $key => $value) {
                $translator->setAlias($key, $value);
            }

            $translator
                ->import(Arr::convert($config->required))
                ->setDefault(value($config->default));

            return $translator;
        };
    }
}
