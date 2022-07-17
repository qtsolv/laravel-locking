<?php

namespace Quarks\Laravel\Locking;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait LocksVersion
{
    protected $lockEnabled = true;

    /**
     * Initialize the lock version as "1" on creation.
     *
     * @return void
     */
    protected static function bootLocksVersion()
    {
        static::creating(function (Model $model) {
            /** @var LocksVersion $model */
            if ($model->currentLockVersion() === null) {
                $model->{static::lockVersionColumnName()} = 1;
            }
        });
    }

    /**
     * Returns the current, locked version for this model.
     *
     * @return int|null
     */
    public function currentLockVersion()
    {
        return $this->{static::lockVersionColumnName()};
    }

    /**
     * Fills the current locked version with the one received in request input.
     *
     * @return void
     */
    public function fillLockVersion()
    {
        $this->{static::lockVersionColumnName()} = request()->input(static::lockVersionColumnName());
    }

    /**
     * Default column to fetch/update current lock version from/into.
     *
     * @return string
     */
    protected static function lockVersionColumnName()
    {
        return 'lock_version';
    }

    /**
     * Based on https://github.com/illuminate/database/blob/master/Eloquent/Model.php
     */
    protected function performUpdate(Builder $query)
    {
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $dirty = $this->getDirty();
        if (count($dirty) > 0) {
            $this->setKeysForSaveQuery($query);
            if ($this->lockEnabled) {
                $query->where(static::lockVersionColumnName(), '=', $this->currentLockVersion());
            }

            $lockVersionPrevious = $this->currentLockVersion();
            $this->setAttribute(static::lockVersionColumnName(), $lockVersionNext = $lockVersionPrevious + 1);
            $dirty[static::lockVersionColumnName()] = $lockVersionNext;
            if ($query->update($dirty) === 0) {
                $this->setAttribute(static::lockVersionColumnName(), $lockVersionPrevious);
                throw new LockedVersionMismatchException('Model was changed during update.');
            }

            $this->syncChanges();
            $this->fireModelEvent('updated', false);
        }

        return true;
    }
}
