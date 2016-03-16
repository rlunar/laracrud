<?php

namespace LaraCrud;

use App\User;
use Illuminate\Database\Eloquent\Model;

class LaraCrudActivity extends Model
{

    protected $fillable = ['subject_id', 'subject_type', 'name', 'user_id'];

    /**
     * An activity belongs to a User
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * An activity morphs to
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subject()
    {
        return $this->morphTo();
    }
}
