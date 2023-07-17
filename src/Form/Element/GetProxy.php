<?php

declare(strict_types=1);

namespace DoctrineModule\Form\Element;

trait GetProxy
{
    protected Proxy|null $proxy = null;

    public function getProxy(): Proxy
    {
        if ($this->proxy === null) {
            $this->proxy = new Proxy();
        }

        return $this->proxy;
    }
}
