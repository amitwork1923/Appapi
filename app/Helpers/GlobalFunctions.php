<?php
namespace App\Helpers;
use Mail;
use Image;
use Storage;
use App\User;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;




class GlobalFunctions {
  public static function uploadImage($file,$path){
      $name = time() . '.' . $file->getClientOriginalName();
      //save original
      $img = Image::make($file->getRealPath());
      $img->stream();
      Storage::disk('local')->put($path.'/'.$name, $img, 'public');
      //savethumb
      $img = Image::make($file->getRealPath());
      $img->resize(256, 256, function ($constraint) {
          $constraint->aspectRatio();
      });
      $img->stream();
      Storage::disk('local')->put($path.'/thumb/'.$name, $img, 'public');
      return $name;
  }  
}
