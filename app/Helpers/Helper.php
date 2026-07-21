<?php

namespace App\Helpers;

use App\Models\User;
use DB, Auth, File, Mail;
use Carbon\Carbon;


class Helper
{
   
    public static function admin(){
        $admin = User::where('role','admin')->first();
        return $admin;
    }


    public static function slug($table, $name)
    {
        $slug = str_replace(' ', '-', $name);
        $slug = strtolower($slug);
        $i = 1;
        while ($i > 0) {
            $check_slug = DB::table($table)->where('slug', $slug)->first();
            if($check_slug) {
                $slug = str_replace(' ', '-', $name) . '-' . $i;
                $slug = strtolower($slug);
                $i++;
                continue;
            }else{
                break;
            }
        }

        return $slug;
    }

    public static function slugUpdate($table, $name,$id)
    {
        $slug = str_replace(' ', '-', $name);
        $slug = strtolower($slug);
        $i = 1;
        while ($i > 0) {
            $check_slug = DB::table($table)->where('slug', $slug)->where('id','!=',$id)->first();
            if($check_slug) {
                $slug = str_replace(' ', '-', $name) . '-' . $i;
                $slug = strtolower($slug);
                $i++;
                continue;
            }else{
                break;
            }
        }

        return $slug;
    }


    public static function cleanImage($string)
    {
        $string = str_replace(' ', '-', $string);
        return preg_replace('/[^A-Za-z0-9._\-]/', '', $string);
    }


    public static function userDetail($user_id)
    {
        $user_detail = User::find($user_id);
        return $user_detail;
    }

   

    public static function urlValidation(){
        $regex = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
        return $regex;
    }

}
