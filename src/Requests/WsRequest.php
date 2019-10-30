<?php

namespace Shamaseen\Laravel\Ratchet\Requests;
class WsRequest extends \Illuminate\Support\Collection
{
    public function __construct($items = [])
    {
        unset($items['session']);
        parent::__construct($items);

        $this->put('route',$this->get('route'));
        $this->forget('route');
    }

    public function __get($property)
    {
        if($this->has($property))
            return $this->get($property);

        return Parent::get($property);
    }
}
