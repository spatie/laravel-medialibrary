<?php namespace Spatie\MediaLibrary\Interfaces;


interface MediaModelInterface {

    public function getMedia($collectionName);

    public static function getImageProfileProperties();

    public static function addMedia($file, $collectionName, $preserveOriginal = false);

    public static function removeMedia($id);

    public static function media();
}
