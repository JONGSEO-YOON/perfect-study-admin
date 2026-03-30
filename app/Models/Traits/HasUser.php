<?php

namespace App\Models\Traits;

trait HasUser
{
  protected static function bootHasUser()
  {
    static::deleting(function ($model) {
      // userable과 연결된 user가 있다면 삭제
      if ($model->user) {
        $model->user->delete();
      }
    });

  }

  /**
   * Get the user that belongs to the model.
   */
  public function user()
  {
    return $this->morphOne('App\Models\User', 'userable');
  }
}
