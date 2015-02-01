<?php namespace Metrakit\VideoConverter;
 
use Illuminate\Support\Facades\Facade;
 
class VideoConverterFacade extends Facade {
 
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'videoConverter'; }
 
}