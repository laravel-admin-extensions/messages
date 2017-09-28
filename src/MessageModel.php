<?php

namespace Encore\Admin\Message;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;

class MessageModel extends Model
{
    /**
     * Settings constructor.
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('admin.database.connection') ?: config('database.default'));

        $this->setTable(config('admin.extensions.messages.table', 'admin_messages'));
    }

    public function sender()
    {
        return $this->belongsTo(Administrator::class, 'from');
    }

    public function receiver()
    {
        return $this->belongsTo(Administrator::class, 'to');
    }

    public function scopeInbox($query)
    {
        return $query->where('to', Admin::user()->id);
    }

    public function scopeOutbox($query)
    {
        return $query->where('from', Admin::user()->id);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->from = Admin::user()->id;

            if (is_array($model->to)) {
                foreach ($model->to as $to) {
                    $new = clone $model;
                    $new->to = $to;
                    $new->save();
                }

                return false;
            }
        });
    }
}
