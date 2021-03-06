<?php

namespace Blast\BaseEntitiesBundle\Entity\Traits;

trait Stringable
{
    public function __toString()
    {
        if ( method_exists(get_class($this), 'getName') )
            return (string)$this->getName();
        if ( method_exists(get_class($this), 'getId') )
            return (string)$this->getId();
        return '';
    }
}
