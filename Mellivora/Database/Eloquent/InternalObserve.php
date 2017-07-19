<?php

namespace Mellivora\Database\Eloquent;

use Mellivora\Database\Eloquent\Model;

/**
 * 扩展 laravel/eloquent 的 model 事件管理机制
 *
 * 允许在 model 内部实现事件管理方法
 *
 * 可通过在 model 内部实现以下方法来进行事件管理
 *
 *    onCreated, onUpdated, onSaved, onDeleted, onRestored
 *    onCreating, onUpdating, onSaving, onDeleting, onRestoring
 *
 * 注册方法：
 *
 *    Mellivora\Database\Eloquent\Model::observe(Mellivora\Database\Eloquent::InternalObserve);
 */
class InternalObserve
{

    //-------------------------------------------------------------------------
    // 以下事件仅触发
    //-------------------------------------------------------------------------

    public function created(Model $model)
    {
        if (method_exists($model, 'onCreated')) {
            $model->onCreated();
        }
    }

    public function updated(Model $model)
    {
        if (method_exists($model, 'onUpdated')) {
            $model->onUpdated();
        }
    }

    public function saved(Model $model)
    {
        if (method_exists($model, 'onSaved')) {
            $model->onSaved();
        }
    }

    public function deleted(Model $model)
    {
        if (method_exists($model, 'onDeleted')) {
            $model->onDeleted();
        }
    }

    public function restored(Model $model)
    {
        if (method_exists($model, 'onRestored')) {
            $model->onRestored();
        }
    }

    //-------------------------------------------------------------------------
    // 以下事件触发后，返回 false 将停止
    //-------------------------------------------------------------------------

    public function creating(Model $model)
    {
        if (method_exists($model, 'onCreating')) {
            return $model->onCreating();
        }

        return true;
    }

    public function updating(Model $model)
    {
        if (method_exists($model, 'onUpdating')) {
            return $model->onUpdating();
        }

        return true;
    }

    public function saving(Model $model)
    {
        if (method_exists($model, 'onSaving')) {
            return $model->onSaving();
        }

        return true;
    }

    public function deleting(Model $model)
    {
        if (method_exists($model, 'onDeleting')) {
            return $model->onDeleting();
        }

        return true;
    }

    public function restoring(Model $model)
    {
        if (method_exists($model, 'onRestoring')) {
            return $model->onRestoring();
        }

        return true;
    }
}
