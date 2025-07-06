<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    
    protected $fillable = ['name', 'logo', 'group_id'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'team_players')->withTimestamps();
    }
}
