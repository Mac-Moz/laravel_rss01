<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedItem extends Model
{
    protected $fillable = [
        'tag_name',
        'article_title',
        'article_date',
        'article_link',
        'article_image'
    ];
}
