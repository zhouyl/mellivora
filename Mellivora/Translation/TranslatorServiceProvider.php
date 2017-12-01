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

            $translator = new Translator($config->get('paths', [])->toArray());

            // 别名设置
            foreach ($config->aliases as $key => $value) {
                $translator->alias($key, $value->toArray());
            }

            // 默认语言类型
            $translator->default(value($config->default));

            // 导入必须的语言包
            $translator->import($config->get('required', [])->toArray());

            return $translator;
        };
    }
}
