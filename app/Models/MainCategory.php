<?php

namespace App\Models;

use App\Observers\MainCategoryObserver;
use Illuminate\Database\Eloquent\Model;

class MainCategory extends Model
{

    protected  static function boot(){
        parent::boot();
        MainCategory::observe(MainCategoryObserver::class);
    }

    protected $table = 'main_categories';

    protected $fillable = [
        'translation_lang', 'translation_of', 'name', 'slug', 'photo', 'active', 'created_at', 'updated_at'
    ];


    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopeSelection($query)
    {

        return $query->select('id', 'translation_lang', 'name', 'slug', 'photo', 'active', 'translation_of');

    }

    /* ('get') reserved word    ('photo')name column in database      ('Attribute')reserved word  */
    public function getPhotoAttribute($val)
    {
        /* if img has value return the bath img  else return the column null */
        return ($val !== null) ? asset('assets/' . $val) : "";

    }

    /* accessor data 1 & 0  Active & unActive */
    public function getActive()
    {
        return $this->active == 1 ? 'مفعل' : 'غير مفعل';

    }


    public function categories()
    {
        /* make relation on to many between tow column in one table  */
        return $this->hasMany(self::class, 'translation_of');
    }


    public function vendors()
    {

        return $this->hasMany('App\Models\Vendor', 'category_id', 'id');
    }

}
